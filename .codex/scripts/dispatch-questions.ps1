param(
    [switch]$Watch,
    [int]$IntervalSeconds = 5
)

$baseDir = Join-Path $PSScriptRoot '..\\coordination'
$questionsIncomingDir = Join-Path $baseDir 'questions\\incoming'
$questionsCriticalDir = Join-Path $baseDir 'questions\\critical'
$questionsResolvedDir = Join-Path $baseDir 'questions\\resolved'
$answersOutgoingDir = Join-Path $baseDir 'answers\\outgoing'
$answersHumanDir = Join-Path $baseDir 'answers\\human'
$answersConsumedDir = Join-Path $baseDir 'answers\\consumed'
$logsDir = Join-Path $baseDir 'logs'
$logFile = Join-Path $logsDir 'dispatch.log'

function Write-Log {
    param([string]$Message)
    $line = "[$([DateTime]::Now.ToString('s'))] $Message"
    Add-Content -Path $logFile -Value $line
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

function Get-Section {
    param(
        [string]$Text,
        [string]$Header
    )

    $escaped = [Regex]::Escape($Header)
    $pattern = "(?s)$escaped\s*`r?`n(.*?)(`r?`n##\s+|$)"
    $match = [regex]::Match($Text, $pattern)
    if ($match.Success) {
        return $match.Groups[1].Value.Trim()
    }

    return ''
}

function Get-AutoAnswer {
    param(
        [string]$Question,
        [string]$Context
    )

    $text = ($Question + "`n" + $Context).ToLower()

    if ($text -match 'route|url|href|endpoint|link') {
        return @"
Use Wayfinder-generated helpers only for frontend route/action references.
Do not hard-code URL strings in Vue/TS.
If backend routes changed, regenerate with `php artisan wayfinder:generate`.
"@.Trim()
    }

    if ($text -match 'validation|formrequest|request class') {
        return @"
Use a dedicated FormRequest for input validation.
Keep controllers thin and pass validated data into a readonly DTO, then an invokable Action.
"@.Trim()
    }

    if ($text -match 'dto|payload') {
        return @"
Use a readonly DTO for typed transfer between controller and action.
Keep transformation in DTO/resource, not in controller.
"@.Trim()
    }

    if ($text -match 'resource|inertia props|json') {
        return @"
Return structured output through JsonResource for both API and Inertia payload objects.
Avoid passing raw model arrays when a resource contract exists.
"@.Trim()
    }

    if ($text -match 'test|qa|lint|type|phpstan|pint') {
        return @"
Follow the full verify gate before marking complete:
- vendor/bin/phpstan analyse --memory-limit=512M
- vendor/bin/pint --test
- php artisan test
- npm run lint:check
- npm run types:check
- npm run format:check
"@.Trim()
    }

    return @"
Proceed with the smallest safe implementation that matches current task acceptance criteria.
Document assumptions in your handoff file under Open Issues and continue.
Escalate only if the decision changes security, data model compatibility, or public API behavior.
"@.Trim()
}

function Publish-Answer {
    param(
        [hashtable]$Meta,
        [string]$AnswerBody,
        [string]$AnsweredBy
    )

    $id = $Meta['id']
    $to = $Meta['from']
    if ([string]::IsNullOrWhiteSpace($id) -or [string]::IsNullOrWhiteSpace($to)) {
        return
    }

    $answerPath = Join-Path $answersOutgoingDir "$id.md"
    $content = @"
---
id: $id
to: $to
status: answered
answered_by: $AnsweredBy
answered_at: $([DateTime]::Now.ToString('s'))
---

## Answer
$AnswerBody
"@

    Set-Content -Path $answerPath -Value $content -NoNewline
    Write-Log "Published answer for $id to $to"
}

function Process-HumanReplies {
    $replyFiles = Get-ChildItem -Path $answersHumanDir -File -Filter '*.md' -ErrorAction SilentlyContinue
    foreach ($reply in $replyFiles) {
        try {
            $text = Get-Content -Path $reply.FullName -Raw
            $meta = Parse-Frontmatter -Text $text
            $id = $meta['id']
            if ([string]::IsNullOrWhiteSpace($id)) {
                Write-Log "Skipped human reply without id: $($reply.Name)"
                continue
            }

            $criticalQuestion = Join-Path $questionsCriticalDir "$id.md"
            if (-not (Test-Path $criticalQuestion)) {
                Write-Log "Human reply $($reply.Name) has no matching critical question"
                Move-Item -Path $reply.FullName -Destination (Join-Path $answersConsumedDir ("orphan-" + $reply.Name)) -Force
                continue
            }

            $questionText = Get-Content -Path $criticalQuestion -Raw
            $questionMeta = Parse-Frontmatter -Text $questionText
            $answerBody = Get-Section -Text $text -Header '## Answer'
            if ([string]::IsNullOrWhiteSpace($answerBody)) {
                $answerBody = 'Human reply received with no explicit answer section. Please check with user.'
            }

            Publish-Answer -Meta $questionMeta -AnswerBody $answerBody -AnsweredBy 'user'
            Move-Item -Path $criticalQuestion -Destination (Join-Path $questionsResolvedDir "$id.md") -Force
            Move-Item -Path $reply.FullName -Destination (Join-Path $answersConsumedDir $reply.Name) -Force
            Write-Log "Resolved critical question via human reply: $id"
        } catch {
            Write-Log "Error processing human reply $($reply.Name): $($_.Exception.Message)"
        }
    }
}

function Process-IncomingQuestions {
    $files = Get-ChildItem -Path $questionsIncomingDir -File -Filter '*.md' -ErrorAction SilentlyContinue | Sort-Object LastWriteTime
    foreach ($file in $files) {
        try {
            $text = Get-Content -Path $file.FullName -Raw
            $meta = Parse-Frontmatter -Text $text

            $id = $meta['id']
            if ([string]::IsNullOrWhiteSpace($id)) {
                $id = [IO.Path]::GetFileNameWithoutExtension($file.Name)
                $meta['id'] = $id
            }

            $priority = ($meta['priority'] | ForEach-Object { $_.ToLower() })
            $question = Get-Section -Text $text -Header '## Question'
            $context = Get-Section -Text $text -Header '## Context'

            if ($priority -eq 'critical') {
                Move-Item -Path $file.FullName -Destination (Join-Path $questionsCriticalDir "$id.md") -Force
                Write-Log "Escalated critical question: $id"
                continue
            }

            $answer = Get-AutoAnswer -Question $question -Context $context
            Publish-Answer -Meta $meta -AnswerBody $answer -AnsweredBy 'orchestrator-auto'
            Move-Item -Path $file.FullName -Destination (Join-Path $questionsResolvedDir "$id.md") -Force
            Write-Log "Auto-resolved question: $id"
        } catch {
            Write-Log "Error processing incoming question $($file.Name): $($_.Exception.Message)"
        }
    }
}

while ($true) {
    try {
        Process-HumanReplies
        Process-IncomingQuestions
    } catch {
        Write-Log "Dispatcher loop error: $($_.Exception.Message)"
    }

    if (-not $Watch.IsPresent) {
        break
    }

    Start-Sleep -Seconds $IntervalSeconds
}
