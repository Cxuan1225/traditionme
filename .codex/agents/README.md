# Agents

This directory defines the **multi-agent system** for the tradition-me project. Each agent has a specific role and responsibility boundary.

## Runtime Model

- Native Codex multi-agent runtime is enabled in `.codex/config.toml` (`[features] multi_agent = true`).
- Agent runtime configs live in:
  - `.codex/agents/orchestrator.toml`
  - `.codex/agents/backend.toml`
  - `.codex/agents/frontend.toml`
  - `.codex/agents/qa.toml`
  - `.codex/agents/docs.toml`
  - `.codex/agents/monitor.toml`
- Markdown files (`backend.md`, `frontend.md`, etc.) remain role-policy references and project conventions.

## Agent Roster

| Agent            | Role       | File              | Scope                                                                  |
| ---------------- | ---------- | ----------------- | ---------------------------------------------------------------------- |
| **Orchestrator** | Lead       | `orchestrator.md` | Plans, delegates, manages pipeline & archival                          |
| **Backend**      | Specialist | `backend.md`      | PHP / Laravel — routes, controllers, actions, DTOs, models, resources  |
| **Frontend**     | Specialist | `frontend.md`     | Vue 3 / TypeScript / Inertia — pages, components, composables, styling |
| **QA**           | Specialist | `qa.md`           | Tests, linting, type-checking, acceptance validation                   |
| **Docs**         | Specialist | `docs.md`         | PHPDoc, JSDoc, READMEs, architecture updates, future docs site         |

## How They Work Together

```
                          ┌──────────────────┐
                          │   Orchestrator   │
                          │   (lead agent)   │
                          └──┬───┬───┬───┬───┘
                             │   │   │   │
              ┌──────────────┘   │   │   └──────────────┐
              ▼                  ▼   ▼                  ▼
        ┌──────────┐     ┌──────────┐ ┌──────────┐ ┌──────────┐
        │ Backend  │     │ Frontend │ │    QA    │ │   Docs   │
        │ Agent    │     │ Agent    │ │  Agent   │ │  Agent   │
        └──────────┘     └──────────┘ └──────────┘ └──────────┘
```

### Flow for a typical feature:

1. **User** submits a request.
2. **Orchestrator** creates a task in `.codex/tasks/`, breaks it into sub-tasks, delegates.
3. **Backend** implements server-side code (routes, DTOs, Actions, Resources).
4. **Frontend** implements client-side code (pages, components, Wayfinder imports).
5. **QA** runs all verification checks — tests, lint, types, acceptance criteria.
6. **Docs** updates documentation — PHPDoc, JSDoc, READMEs.
7. **Orchestrator** reviews, sets `status: done`, archives to `.codex/archive/YYYY-MM-DD/`.

### Delegation Routing

The Orchestrator routes work based on the type of change:

| Change type                      | Primary agent      | Secondary agent |
| -------------------------------- | ------------------ | --------------- |
| New API endpoint / backend logic | Backend            | QA              |
| New page / component             | Frontend           | QA              |
| Full-stack feature               | Backend → Frontend | QA              |
| Bug fix (PHP)                    | Backend            | QA              |
| Bug fix (Vue/TS)                 | Frontend           | QA              |
| Test coverage gap                | QA                 | —               |
| Documentation update             | Docs               | —               |
| Architecture / convention change | Orchestrator       | Docs            |

### Handoff Format

Every delegation uses this template:

```markdown
### Handoff → <agent-name>

**Task:** <task-slug>
**Stage:** <pipeline stage>
**Context:** <summary + relevant file paths>
**Acceptance criteria:** <what "done" looks like>
```

### Completion & Archival

When QA passes and Docs has updated documentation:

1. Orchestrator sets `status: done` and `completed: YYYY-MM-DD` in the task frontmatter.
2. Task file moves: `.codex/tasks/<slug>.md` → `.codex/archive/YYYY-MM-DD/<slug>.md`.
3. Archive directories are sorted **newest date first**.
