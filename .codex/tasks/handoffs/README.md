# Parallel Handoff Files

Use these files for parallel Codex sessions so the orchestrator terminal can read progress without copy/paste.

- Backend agent writes: `.codex/tasks/handoffs/backend.md`
- Frontend agent writes: `.codex/tasks/handoffs/frontend.md`

Each agent should update its file in place as work progresses and before completion.

## Coordination protocol (required)

1. Backend updates only `backend.md`; Frontend updates only `frontend.md`.
2. Each handoff file must include `SUBTASK STATUS: pending|in_progress|blocked|done` under `## Coordination`.
3. When orchestrator needs to integrate/fix shared code:
   - set `LOCK: orchestrator integrating <timestamp>` in both files
   - agents pause code changes
4. Orchestrator writes integration decisions under `## Orchestrator Notes`.
5. Orchestrator unlocks with:
   - `LOCK: none`
   - `RESUME FROM: <timestamp or commit>`
6. Agents rebase/pull latest and continue from `RESUME FROM`.
7. Orchestrator watcher auto-progresses pipeline:
   - `backend done + frontend pending` -> frontend becomes `in_progress`
   - `backend done + frontend done` -> active task moves to `verify` with QA handoff

## Question bus automation

For issue handling and unblock flow, use `.codex/coordination/README.md`.

- Agent asks question: `pwsh -ExecutionPolicy Bypass -File .codex/scripts/new-question.ps1 ...`
- Orchestrator dispatches/triages: `pwsh -ExecutionPolicy Bypass -File .codex/scripts/dispatch-questions.ps1`
- Agent auto-reads answers: `pwsh -ExecutionPolicy Bypass -File .codex/scripts/get-answers.ps1 -Agent backend -Watch`

Critical questions must be submitted with `-Priority critical`; orchestrator will hold them in `.codex/coordination/questions/critical` until your reply is placed into `.codex/coordination/answers/human/<id>.md`.

## Required format

```markdown
## Task
<short task summary>

## Coordination
LOCK: none
RESUME FROM: n/a
SUBTASK STATUS: pending
LAST UPDATED: YYYY-MM-DD

## Scope
<paths touched>

## Files
- <path>

## Routes (if any)
- <METHOD URI name>

## Commands Run
- `<command>`

## Results
- <pass/fail + short output summary>

## Open Issues
- <none or list>

## Next Step
<what should happen next>
```

