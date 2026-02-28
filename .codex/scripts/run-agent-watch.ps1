param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('backend', 'frontend', 'qa', 'docs')]
    [string]$Agent,

    [int]$IntervalSeconds = 5,
    [int]$RestartDelaySeconds = 2
)

$watchScript = Join-Path $PSScriptRoot 'get-answers.ps1'
$logFile = Join-Path (Join-Path $PSScriptRoot '..\\coordination\\logs') ("watch-$Agent.log")

function Write-Log {
    param([string]$Message)
    $line = "[$([DateTime]::Now.ToString('s'))] $Message"
    Add-Content -Path $logFile -Value $line
    Write-Output $line
}

while ($true) {
    try {
        Write-Log "Starting answer watch for $Agent"
        & pwsh -ExecutionPolicy Bypass -File $watchScript -Agent $Agent -Watch -IntervalSeconds $IntervalSeconds
        Write-Log "Answer watch exited for $Agent; restarting in $RestartDelaySeconds s"
    } catch {
        Write-Log "Answer watch crashed for $Agent : $($_.Exception.Message)"
    }

    Start-Sleep -Seconds $RestartDelaySeconds
}

