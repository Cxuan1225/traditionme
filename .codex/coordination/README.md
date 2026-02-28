# Coordination Question Bus

This folder enables asynchronous backend/frontend/orchestrator coordination using Markdown files.

## Primary vs Fallback

- Primary execution path: native Codex multi-agent orchestration (`.codex/config.toml`, `[features] multi_agent = true`).
- Fallback/observability path: this question/answer bus and watcher scripts.
- Keep both enabled: runtime orchestration for execution, bus for guaranteed low-friction signaling.

## Folders

- `.codex/coordination/questions/incoming` - new questions from agents
- `.codex/coordination/questions/critical` - critical questions waiting for human reply
- `.codex/coordination/questions/resolved` - processed questions
- `.codex/coordination/answers/outgoing` - answers published for agents
- `.codex/coordination/answers/consumed` - answers already read by agents
- `.codex/coordination/answers/human` - your replies for critical questions
- `.codex/coordination/logs` - dispatcher and watcher logs

## Question format

```md
---
id: q-20260228-120001-backend-routing
from: backend
task: security-v1-spatie-rbac-foundation
priority: normal
blocking: true
status: open
created_at: 2026-02-28T12:00:01
---

## Question
How should frontend route references be implemented?

## Context
Need to pass route names to Vue components.

## Proposed Options
- hard-code URLs
- use Wayfinder helpers
```

Priority values: `low`, `normal`, `high`, `critical`.

## Human reply format (critical only)

Create `.codex/coordination/answers/human/<id>.md`:

```md
---
id: q-20260228-120001-backend-routing
from: user
status: answered
answered_at: 2026-02-28T12:05:00
---

## Answer
Use Wayfinder-generated helpers only.
```

## Start all 3 terminals (auto-watch + auto-restart)

Orchestrator terminal:

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/run-orchestrator-watch.ps1
```

This watcher now performs both:
- question dispatch (`dispatch-questions.ps1`)
- handoff/pipeline auto-sync (`sync-handoffs.ps1`)

Backend terminal:

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/run-backend-watch.ps1
```

Frontend terminal:

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/run-frontend-watch.ps1
```

## Agent question command

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/new-question.ps1 `
  -Agent backend `
  -Task security-v1-spatie-rbac-foundation `
  -Priority normal `
  -Blocking `
  -Title routing `
  -Question "How should frontend route references be implemented?" `
  -Context "Need to pass route names to Vue components."
```

## Optional manual commands

Run dispatcher once:

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/dispatch-questions.ps1
```

Run answer watcher once:

```powershell
pwsh -ExecutionPolicy Bypass -File .codex/scripts/get-answers.ps1 -Agent backend
```

