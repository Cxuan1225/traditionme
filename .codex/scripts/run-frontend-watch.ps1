param(
    [int]$IntervalSeconds = 5,
    [int]$RestartDelaySeconds = 2
)

& pwsh -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'run-agent-watch.ps1') -Agent frontend -IntervalSeconds $IntervalSeconds -RestartDelaySeconds $RestartDelaySeconds

