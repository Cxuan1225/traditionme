param(
    [switch]$Watch,
    [int]$IntervalSeconds = 5
)

$rootDir = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$tasksDir = Join-Path $rootDir '.codex\tasks'
$handoffDir = Join-Path $tasksDir 'handoffs'
$backendHandoff = Join-Path $handoffDir 'backend.md'
$frontendHandoff = Join-Path $handoffDir 'frontend.md'
$logFile = Join-Path $rootDir '.codex\coordination\logs\orchestrator-sync.log'
$answersOutgoingDir = Join-Path $rootDir '.codex\coordination\answers\outgoing'

function Write-Log {
    param([string]$Message)
    $line = "[$([DateTime]::Now.ToString('s'))] $Message"
    Add-Content -Path $logFile -Value $line
    Write-Output $line
}

function Get-KeyValue {
    param(
        [string]$Text,
        [string]$Key
    )

    $match = [regex]::Match($Text, "(?m)^$([regex]::Escape($Key)):\s*(.+)$")
    if ($match.Success) {
        return $match.Groups[1].Value.Trim()
    }

    return $null
}

function Set-KeyValueLine {
    param(
        [string]$Text,
        [string]$Key,
        [string]$Value
    )

    $pattern = "(?m)^$([regex]::Escape($Key)):\s*.+$"
    if ([regex]::IsMatch($Text, $pattern)) {
        return [regex]::Replace($Text, $pattern, "${Key}: $Value", 1)
    }

    $coordinationHeaderPattern = "(?m)^## Coordination\s*$"
    if ([regex]::IsMatch($Text, $coordinationHeaderPattern)) {
        return [regex]::Replace($Text, $coordinationHeaderPattern, "## Coordination`r`n${Key}: $Value", 1)
    }

    return "${Key}: $Value`r`n$Text"
}

function Get-ActiveTaskFile {
    $taskFiles = Get-ChildItem -Path $tasksDir -File -Filter '*.md' -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -ne '.gitkeep' }

    if (-not $taskFiles) {
        return $null
    }

    return $taskFiles | Sort-Object LastWriteTime -Descending | Select-Object -First 1
}

function Set-FrontmatterValue {
    param(
        [string]$Text,
        [string]$Key,
        [string]$Value
    )

    if (-not $Text.StartsWith('---')) {
        return $Text
    }

    $parts = $Text -split "(?ms)^---\r?\n"
    if ($parts.Count -lt 3) {
        return $Text
    }

    $frontmatter = $parts[1]
    $rest = $parts[2]
    $pattern = "(?m)^$([regex]::Escape($Key)):\s*.*$"

    if ([regex]::IsMatch($frontmatter, $pattern)) {
        $frontmatter = [regex]::Replace($frontmatter, $pattern, "${Key}: $Value", 1)
    } else {
        $frontmatter = $frontmatter.TrimEnd() + "`r`n${Key}: $Value"
    }

    return "---`r`n$frontmatter`r`n---`r`n$rest"
}

function Ensure-QaHandoff {
    param([string]$TaskText)

    if ($TaskText -match '(?m)^### Handoff -> qa\s*$') {
        return $TaskText
    }

    $qaBlock = @"

### Handoff -> qa
**Task:** automated-qa-verification
**Stage:** verify
**Context:** Backend and frontend handoffs are marked complete. Run verification gates and report final sign-off.
**Acceptance criteria:** Relevant lint/tests/type checks pass and task can proceed to document/archive.
"@

    return $TaskText.TrimEnd() + "`r`n$qaBlock`r`n"
}

function Publish-AgentSignal {
    param(
        [string]$Agent,
        [string]$Body
    )

    $id = "orch-" + [DateTime]::Now.ToString('yyyyMMdd-HHmmss') + "-$Agent-sync"
    $answerPath = Join-Path $answersOutgoingDir "$id.md"
    $content = @"
---
id: $id
to: $Agent
status: answered
answered_by: orchestrator-sync
answered_at: $([DateTime]::Now.ToString('s'))
---

## Answer
$Body
"@

    Set-Content -Path $answerPath -Value $content -NoNewline
    Write-Log "Published orchestrator signal to $Agent ($id)."
}

function Invoke-SyncOnce {
    if (-not (Test-Path $backendHandoff) -or -not (Test-Path $frontendHandoff)) {
        Write-Log "Skipped sync: handoff files missing."
        return
    }

    $backendText = Get-Content -Path $backendHandoff -Raw
    $frontendText = Get-Content -Path $frontendHandoff -Raw

    $backendStatus = (Get-KeyValue -Text $backendText -Key 'SUBTASK STATUS')
    $frontendStatus = (Get-KeyValue -Text $frontendText -Key 'SUBTASK STATUS')

    if ([string]::IsNullOrWhiteSpace($backendStatus)) { $backendStatus = 'pending' }
    if ([string]::IsNullOrWhiteSpace($frontendStatus)) { $frontendStatus = 'pending' }

    $backendStatus = $backendStatus.ToLower()
    $frontendStatus = $frontendStatus.ToLower()
    $frontendNeedsBackend = $frontendText -match '(?im)\bblocker:\s*backend\b|\bbackend currently\b|\bbackend.+does not yet expose\b'

    $changed = $false

    if ($backendStatus -eq 'done' -and $frontendStatus -eq 'in_progress' -and $frontendNeedsBackend) {
        $frontendText = Set-KeyValueLine -Text $frontendText -Key 'SUBTASK STATUS' -Value 'blocked'
        Set-Content -Path $frontendHandoff -Value $frontendText -NoNewline

        $backendText = Set-KeyValueLine -Text $backendText -Key 'SUBTASK STATUS' -Value 'in_progress'
        $backendText = Set-KeyValueLine -Text $backendText -Key 'RESUME FROM' -Value ("orchestrator-unblock-" + [DateTime]::Now.ToString('s'))
        Set-Content -Path $backendHandoff -Value $backendText -NoNewline

        Write-Log "Detected frontend backend-blocker. Set frontend=blocked and reopened backend=in_progress."
        Publish-AgentSignal -Agent 'backend' -Body @"
Frontend reported a backend blocker.
Your handoff status is now in_progress.
Please address blocker in .codex/tasks/handoffs/frontend.md Open Issues / Handoff -> orchestrator and update backend.md when complete.
"@
        Publish-AgentSignal -Agent 'frontend' -Body @"
Your handoff status is now blocked pending backend unblock.
Monitor .codex/tasks/handoffs/backend.md and resume once backend status returns done.
"@
        $changed = $true
    }

    if ($backendStatus -eq 'done' -and $frontendStatus -eq 'pending') {
        $frontendText = Set-KeyValueLine -Text $frontendText -Key 'SUBTASK STATUS' -Value 'in_progress'
        $frontendText = Set-KeyValueLine -Text $frontendText -Key 'RESUME FROM' -Value ("orchestrator-auto-" + [DateTime]::Now.ToString('s'))
        Set-Content -Path $frontendHandoff -Value $frontendText -NoNewline
        Write-Log "Auto-advanced frontend handoff: pending -> in_progress (backend done)."
        Publish-AgentSignal -Agent 'frontend' -Body @"
Backend is complete; your handoff is now in_progress.
Proceed with implementation for the active task and mark SUBTASK STATUS: done when finished.
"@
        $changed = $true
    }

    $taskFile = Get-ActiveTaskFile
    if ($null -eq $taskFile) {
        if (-not $changed) {
            Write-Log "No active task file found for stage sync."
        }
        return
    }

    $taskText = Get-Content -Path $taskFile.FullName -Raw
    $taskStage = Get-KeyValue -Text $taskText -Key 'stage'
    if ([string]::IsNullOrWhiteSpace($taskStage)) {
        $taskStage = 'implement'
    }

    if ($backendStatus -eq 'done' -and $frontendStatus -eq 'done' -and $taskStage -eq 'implement') {
        $taskText = Set-FrontmatterValue -Text $taskText -Key 'stage' -Value 'verify'
        $taskText = Set-FrontmatterValue -Text $taskText -Key 'assignees' -Value '[orchestrator, qa]'
        $taskText = Ensure-QaHandoff -TaskText $taskText
        Set-Content -Path $taskFile.FullName -Value $taskText -NoNewline
        Write-Log "Auto-advanced task $($taskFile.Name): implement -> verify."
    } elseif (-not $changed) {
        Write-Log "No pipeline transition needed. backend=$backendStatus frontend=$frontendStatus stage=$taskStage"
    }
}

while ($true) {
    try {
        Invoke-SyncOnce
    } catch {
        Write-Log "Sync error: $($_.Exception.Message)"
    }

    if (-not $Watch.IsPresent) {
        break
    }

    Start-Sleep -Seconds $IntervalSeconds
}
