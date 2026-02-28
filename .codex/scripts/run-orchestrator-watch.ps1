param(
    [int]$IntervalSeconds = 5,
    [int]$RestartDelaySeconds = 2
)

$dispatchScript = Join-Path $PSScriptRoot 'dispatch-questions.ps1'
$syncScript = Join-Path $PSScriptRoot 'sync-handoffs.ps1'
$logFile = Join-Path (Join-Path $PSScriptRoot '..\\coordination\\logs') 'orchestrator-watch.log'

function Write-Log {
    param([string]$Message)
    $line = "[$([DateTime]::Now.ToString('s'))] $Message"
    Add-Content -Path $logFile -Value $line
    Write-Output $line
}

while ($true) {
    try {
        Write-Log "Orchestrator cycle: dispatch + handoff sync"
        & pwsh -ExecutionPolicy Bypass -File $dispatchScript
        & pwsh -ExecutionPolicy Bypass -File $syncScript
    } catch {
        Write-Log "Orchestrator cycle failed: $($_.Exception.Message)"
    }

    Start-Sleep -Seconds $IntervalSeconds
}

