param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('backend', 'frontend', 'qa', 'docs')]
    [string]$Agent,

    [Parameter(Mandatory = $true)]
    [string]$Task,

    [Parameter(Mandatory = $true)]
    [string]$Title,

    [Parameter(Mandatory = $true)]
    [string]$Question,

    [string]$Context = '',

    [ValidateSet('low', 'normal', 'high', 'critical')]
    [string]$Priority = 'normal',

    [switch]$Blocking
)

$baseDir = Join-Path $PSScriptRoot '..\\coordination'
$incomingDir = Join-Path $baseDir 'questions\\incoming'

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$slug = ($Title.ToLower() -replace '[^a-z0-9]+', '-') -replace '(^-+|-+$)', ''
if ([string]::IsNullOrWhiteSpace($slug)) {
    $slug = 'question'
}

$id = "q-$timestamp-$Agent-$slug"
$file = Join-Path $incomingDir "$id.md"
$blockingText = if ($Blocking.IsPresent) { 'true' } else { 'false' }

$content = @"
---
id: $id
from: $Agent
task: $Task
priority: $Priority
blocking: $blockingText
status: open
created_at: $([DateTime]::Now.ToString('s'))
---

## Question
$Question

## Context
$Context

## Proposed Options
- n/a
"@

Set-Content -Path $file -Value $content -NoNewline
Write-Output "Created question: $file"
Write-Output "ID: $id"
