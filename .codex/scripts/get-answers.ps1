param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('backend', 'frontend', 'qa', 'docs')]
    [string]$Agent,

    [switch]$Watch,
    [int]$IntervalSeconds = 5,
    [switch]$Keep
)

$baseDir = Join-Path $PSScriptRoot '..\\coordination'
$outgoingDir = Join-Path $baseDir 'answers\\outgoing'
$consumedDir = Join-Path $baseDir 'answers\\consumed'
$logsDir = Join-Path $baseDir 'logs'
$logFile = Join-Path $logsDir ("answers-$Agent.log")
$rootDir = Resolve-Path (Join-Path $PSScriptRoot '..\\..')
$codexDir = Join-Path $rootDir '.codex'
$handoffPath = Join-Path (Join-Path (Join-Path $codexDir 'tasks') 'handoffs') "$Agent.md"
$stateDir = Join-Path $baseDir 'state'
$handoffStateFile = Join-Path $stateDir ("handoff-$Agent.json")
try {
    New-Item -ItemType Directory -Path $stateDir -Force -ErrorAction Stop | Out-Null
} catch {
    # Keep watcher running even in restricted environments where state dir cannot be created.
}

function Write-Log {
    param([string]$Message)
    $line = "[$([DateTime]::Now.ToString('s'))] $Message"
    try {
        Add-Content -Path $logFile -Value $line -ErrorAction Stop
    } catch {
        # Keep output visible even if log path is not writable.
    }
    Write-Output $line
}

function Parse-Frontmatter {
    param([string]$Text)
    $meta = @{}
    if (-not $Text.StartsWith('---')) { return $meta }

    $lines = $Text -split "`r?`n"
    $i = 1
    while ($i -lt $lines.Length) {
        $line = $lines[$i]
        if ($line -eq '---') { break }
        if ($line -match '^([A-Za-z0-9_\-]+):\s*(.*)$') {
            $meta[$matches[1].ToLower()] = $matches[2]
        }
        $i++
    }

    return $meta
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

function Get-TaskTitle {
    param([string]$Text)

    $lines = $Text -split "`r?`n"
    for ($i = 0; $i -lt $lines.Length; $i++) {
        if ($lines[$i].Trim() -eq '## Task') {
            for ($j = $i + 1; $j -lt $lines.Length; $j++) {
                $line = $lines[$j].Trim()
                if ($line -and -not $line.StartsWith('#')) {
                    return $line
                }
            }
        }
    }

    return $null
}

function Read-HandoffState {
    if (-not (Test-Path $handoffPath)) {
        return $null
    }

    $text = Get-Content -Path $handoffPath -Raw
    $status = Get-KeyValue -Text $text -Key 'SUBTASK STATUS'
    if ([string]::IsNullOrWhiteSpace($status)) {
        $status = 'pending'
    }

    return [ordered]@{
        status = $status.ToLower()
        lock = Get-KeyValue -Text $text -Key 'LOCK'
        resume_from = Get-KeyValue -Text $text -Key 'RESUME FROM'
        last_updated = Get-KeyValue -Text $text -Key 'LAST UPDATED'
        task = Get-TaskTitle -Text $text
        file = $handoffPath
    }
}

function Load-Json {
    param([string]$Path)

    if (-not (Test-Path $Path)) {
        return $null
    }

    try {
        return Get-Content -Path $Path -Raw | ConvertFrom-Json
    } catch {
        return $null
    }
}

function Save-Json {
    param(
        [string]$Path,
        $Object
    )

    try {
        ($Object | ConvertTo-Json -Depth 6) | Set-Content -Path $Path -NoNewline -ErrorAction Stop
    } catch {
        Write-Log "State write skipped (non-fatal): $Path"
    }
}

function Emit-HandoffAlertIfChanged {
    $currentState = Read-HandoffState
    if ($null -eq $currentState) {
        return
    }

    $previousState = Load-Json -Path $handoffStateFile
    $previousStatus = $null
    if ($null -ne $previousState) {
        $previousStatus = $previousState.status
    }

    if ($previousStatus -ne $currentState.status) {
        Write-Output ""
        Write-Output "=== HANDOFF STATE CHANGED ($Agent) ==="
        Write-Output "Task:   $($currentState.task)"
        Write-Output "Status: $previousStatus -> $($currentState.status)"
        if (-not [string]::IsNullOrWhiteSpace($currentState.lock)) {
            Write-Output "LOCK:   $($currentState.lock)"
        }
        if (-not [string]::IsNullOrWhiteSpace($currentState.resume_from)) {
            Write-Output "RESUME: $($currentState.resume_from)"
        }
        Write-Output "File:   $($currentState.file)"
        Write-Log "Handoff transition: $previousStatus -> $($currentState.status)"
    }

    Save-Json -Path $handoffStateFile -Object $currentState
}

function Print-Answer {
    param([string]$Path)

    try {
        $text = Get-Content -Path $Path -Raw
        $meta = Parse-Frontmatter -Text $text
        $to = $meta['to']

        if ($to -ne $Agent) {
            return
        }

        Write-Output ""
        Write-Output "=== ANSWER: $($meta['id']) ==="
        Write-Output $text

        if (-not $Keep.IsPresent) {
            Move-Item -Path $Path -Destination (Join-Path $consumedDir ([IO.Path]::GetFileName($Path))) -Force
            Write-Log "Consumed answer: $($meta['id'])"
        }
    } catch {
        Write-Log "Error reading answer file $Path : $($_.Exception.Message)"
    }
}

while ($true) {
    try {
        $files = Get-ChildItem -Path $outgoingDir -File -Filter '*.md' -ErrorAction SilentlyContinue | Sort-Object LastWriteTime
        foreach ($file in $files) {
            Print-Answer -Path $file.FullName
        }
        Emit-HandoffAlertIfChanged
    } catch {
        Write-Log "Answer watch loop error: $($_.Exception.Message)"
    }

    if (-not $Watch.IsPresent) {
        break
    }

    Start-Sleep -Seconds $IntervalSeconds
}
