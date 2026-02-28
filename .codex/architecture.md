# Architecture

## 1) Project Lifecycle — Planning → Documentation

Every feature follows this full flow:

| Phase              | Artefact / Tool                                      |
| ------------------ | ---------------------------------------------------- |
| **Planning**       | GitHub Issue / `.codex/` task spec                   |
| **Routing**        | Laravel Wayfinder (**mandatory** — see §3)           |
| **Validation**     | `FormRequest`                                        |
| **Data transfer**  | Readonly DTO                                         |
| **Business logic** | Invokable Action class                               |
| **Persistence**    | Eloquent Model                                       |
| **Output**         | `JsonResource` (API + Inertia props)                 |
| **Frontend**       | Vue 3 + Inertia — consume Wayfinder-generated routes |
| **Testing**        | Pest (feature + unit)                                |
| **Documentation**  | **VitePress 2** (confirmed — see §7)                 |

---

## 2) Architecture Rules (unchanged)

1. **`declare(strict_types=1)`** in every PHP file — no exceptions.
2. **PHPStan level 10** — all code must pass `vendor/bin/phpstan analyse` at max level (via Larastan).
3. FormRequest for input validation.
4. Readonly DTOs for typed payloads.
5. Thin controllers only orchestrating DTO + action.
6. One invokable action per use-case.
7. Eloquent models for persistence only.
8. JsonResource for all structured outputs (including Inertia props payload objects).

---

## 3) Wayfinder (mandatory)

[Laravel Wayfinder](https://github.com/laravel/wayfinder) is **required** for all route references on the frontend.

- Vite plugin enabled in `vite.config.ts` (`@laravel/vite-plugin-wayfinder`).
- Generated route helpers live in `resources/js/routes/` and `resources/js/actions/`.
- **Never** hard-code URL strings in Vue/TS — always import from Wayfinder-generated modules.
- Run `php artisan wayfinder:generate` (or rely on the Vite dev-server) whenever routes change.
- `formVariants: true` is enabled — use `.form` helpers for `<form>` actions where appropriate.

---

## 4) Agent Frontend Skills

The sections below describe what an AI coding agent **must know** to work effectively on this project's frontend.

### 4.1 Core Stack

| Layer          | Technology                            | Notes                                           |
| -------------- | ------------------------------------- | ----------------------------------------------- |
| Language       | **TypeScript** (ESNext, `bundler`)    | Strict-ish — `vue-tsc --noEmit` for type checks |
| Framework      | **Vue 3** (Composition API + SFC)     | `<script setup lang="ts">` is the default       |
| Meta-framework | **Inertia.js v2** (`@inertiajs/vue3`) | Server-driven SPA — no client router            |
| Bundler        | **Vite 7** + `laravel-vite-plugin`    | SSR build via `vite build --ssr`                |
| Routing (FE)   | **Wayfinder** (mandatory — §3)        | Generated typed helpers, never raw URL strings  |

### 4.2 UI & Styling

| Concern           | Tool                                        | Details                                                              |
| ----------------- | ------------------------------------------- | -------------------------------------------------------------------- |
| CSS framework     | **Tailwind CSS v4** (`@tailwindcss/vite`)   | Utility-first; no `tailwind.config.js` runtime — all via Vite plugin |
| Component library | **shadcn-vue** (New York v4 style)          | Primitives powered by `reka-ui`; source-owned in `components/ui/`    |
| Icons             | **Lucide** (`lucide-vue-next`)              | Tree-shakeable SVG icons                                             |
| Animations        | `tw-animate-css`                            | Tailwind-compatible animation utilities                              |
| Class helpers     | `clsx` + `tailwind-merge` via `@/lib/utils` | Merge conditional Tailwind classes cleanly                           |
| Variant API       | `class-variance-authority` (CVA)            | Typed component variants for `shadcn-vue` primitives                 |

### 4.3 Project Layout (`resources/js/`)

```
resources/js/
├── app.ts                 # Inertia client bootstrap
├── ssr.ts                 # Inertia SSR entry
├── actions/               # Wayfinder-generated controller action helpers
├── components/            # App-level Vue components
│   └── ui/                # shadcn-vue primitives (source-owned, do NOT auto-generate)
├── composables/           # Vue composables (useAppearance, useInitials, …)
├── layouts/               # Inertia persistent layouts (AppLayout, AuthLayout, …)
├── lib/                   # Shared pure utilities (utils.ts — clsx + twMerge)
├── pages/                 # Inertia page components (matched by controller)
├── routes/                # Wayfinder-generated route helpers
├── types/                 # Global TS types & declarations
└── wayfinder/             # Wayfinder runtime (auto-generated, do not edit)
```

### 4.4 Conventions the Agent Must Follow

1. **Composition API only** — no Options API, no `defineComponent()` unless wrapping a render function.
2. **`<script setup lang="ts">`** for every SFC.
3. **Type imports** — use `import type` (enforced by ESLint `consistent-type-imports`).
4. **Import order** — `builtin → external → internal → parent → sibling → index`, alphabetised (ESLint `import/order`).
5. **Path alias** — use `@/…` (maps to `resources/js/*`). Never use deep relative paths.
6. **Wayfinder for routes** — import from `@/routes/…` or `@/actions/…`. Zero hand-written URLs.
7. **shadcn-vue components** — import from `@/components/ui/…`. Add new primitives via `npx shadcn-vue@latest add <name>`.
8. **Props typed via `defineProps<T>()`** — `T` should match the `JsonResource` from the backend (see Architecture Rule 6).
9. **Formatting** — Prettier (`prettier-plugin-tailwindcss`) for code; run `npm run format`.
10. **Linting** — ESLint flat config (`eslint.config.js`); run `npm run lint`.
11. **Type checking** — `npm run types:check` (`vue-tsc --noEmit`) must pass before merge.

### 4.5 Useful Commands

```bash
npm run dev          # Vite dev server (HMR + Wayfinder watch)
npm run build        # Production build
npm run build:ssr    # Production + SSR build
npm run lint         # ESLint auto-fix
npm run lint:check   # ESLint check (CI)
npm run format       # Prettier auto-fix
npm run format:check # Prettier check (CI)
npm run types:check  # vue-tsc type verification
```

---

## 5) Agent Workflow Pipeline

Every task an agent works on **must** follow this pipeline. Active work lives in `.codex/tasks/`, and completed work is archived under `.codex/archive/`.

### 5.1 Pipeline Stages

```
┌──────────┐    ┌──────────┐    ┌───────────┐    ┌──────────┐    ┌──────────┐    ┌──────────────┐
│  PLAN    │ →  │  DESIGN  │ →  │ IMPLEMENT │ →  │  VERIFY  │ →  │ DOCUMENT │ →  │   ARCHIVE    │
└──────────┘    └──────────┘    └───────────┘    └──────────┘    └──────────┘    └──────────────┘
```

| Stage         | What happens                                                                 | Artefact updated                       |
| ------------- | ---------------------------------------------------------------------------- | -------------------------------------- |
| **Plan**      | Define scope, acceptance criteria, affected files                            | `.codex/tasks/<slug>.md` created       |
| **Design**    | Outline approach — DTOs, Actions, routes, components                         | Task file updated with design notes    |
| **Implement** | Write code following Architecture Rules (§2) and Frontend Conventions (§4.4) | Source files created / modified        |
| **Verify**    | Run lint, type-check, tests; confirm acceptance criteria met                 | Task file updated with verify results  |
| **Document**  | Add/update relevant docs, PHPDoc, JSDoc, README notes                        | Task file marked `status: done`        |
| **Archive**   | Move completed task file to `.codex/archive/YYYY-MM-DD/`                     | Task file moved; no longer in `tasks/` |

### 5.2 Task File Format (`.codex/tasks/<slug>.md`)

```markdown
---
title: <Short descriptive title>
status: plan | design | implement | verify | document | done
created: YYYY-MM-DD
completed: YYYY-MM-DD # filled on completion
---

## Scope

<What this task covers and why>

## Acceptance Criteria

- [ ] Criterion 1
- [ ] Criterion 2

## Design Notes

<Approach, affected files, new DTOs/Actions/Resources, components>

## Implementation Log

<Brief notes on what was done, decisions made>

## Verification

- [ ] `vendor/bin/phpstan analyse` passes (level 10)
- [ ] `vendor/bin/pint --test` passes
- [ ] `php artisan test` passes
- [ ] `npm run lint:check` passes
- [ ] `npm run types:check` passes
- [ ] `npm run format:check` passes
- [ ] Manual smoke-test (if applicable)
```

### 5.3 Archive Rules

Completed tasks are moved to `.codex/archive/` using this structure:

```
.codex/
├── config.toml                   # Multi-agent & pipeline configuration
├── tasks/                        # Active work only
│   └── add-user-avatar.md
├── archive/
│   ├── index.md                  # ★ Entry point — links to all archived tasks
│   ├── 2026-03-01/               # Latest date first when listing
│   │   ├── setup-two-factor.md
│   │   └── fix-password-reset.md
│   └── 2026-02-28/
│       └── initial-scaffolding.md
├── agents/                       # Agent definitions
└── architecture.md
```

**Rules:**

1. **Group by completion date** — folder name is `YYYY-MM-DD` (the date the task was marked `done`).
2. **Order by latest date** — when listing or reading archives, always sort directories **descending** (newest first).
3. **One move per completion** — as soon as a task reaches `status: done`, move it:
    ```
    .codex/tasks/<slug>.md  →  .codex/archive/YYYY-MM-DD/<slug>.md
    ```
4. **Set the `completed` frontmatter field** before archiving.
5. **Update `archive/index.md`** — add a link to the archived task under its date heading. Create the date heading if it doesn't exist. Newest dates stay at the top.
6. **Never delete archived files** — the archive is append-only.
7. **Keep `tasks/` clean** — only in-progress or queued work lives here.

### 5.4 Agent Checklist (per task)

```
□ Create task file in .codex/tasks/
□ Walk through each pipeline stage (Plan → Document)
□ Update status frontmatter at each stage transition
□ On completion: set status: done, completed: YYYY-MM-DD
□ Move file to .codex/archive/YYYY-MM-DD/
□ Confirm .codex/tasks/ has no stale done files
```

---

## 6) Multi-Agent System

This project uses **five specialised agents** that collaborate through the Orchestrator. Full definitions live in `.codex/agents/`.

### Agent Roster

| Agent            | Role       | Owns                                                                                    |
| ---------------- | ---------- | --------------------------------------------------------------------------------------- |
| **Orchestrator** | Lead       | Task planning, delegation, pipeline enforcement, archival                               |
| **Backend**      | Specialist | PHP / Laravel — routes, controllers, actions, DTOs, models, resources, migrations       |
| **Frontend**     | Specialist | Vue 3 / TS / Inertia — pages, components, composables, styling                          |
| **QA**           | Specialist | Tests (Pest), linting (ESLint + Pint), type-checking (`vue-tsc`), acceptance validation |
| **Docs**         | Specialist | PHPDoc, JSDoc, READMEs, architecture updates, future docs site                          |

### Collaboration Flow

```
User request
  └→ Orchestrator (plan + delegate)
       ├→ Backend agent  ─┐
       ├→ Frontend agent  ├→ QA agent (verify) → Docs agent (document)
       └→ (or both)      ─┘                        │
                                                    ▼
                                          Orchestrator (archive)
```

### Key Rules

1. **Orchestrator is the single entry point** — users talk to the Orchestrator; it delegates.
2. **No agent writes outside its scope** — Backend doesn't touch Vue files; Frontend doesn't touch PHP.
3. **QA must pass before archival** — the task cannot reach `status: done` without QA sign-off.
4. **Handoffs are explicit** — every delegation uses the handoff template (see `.codex/agents/README.md`).
5. **Agent definitions are in `.codex/agents/`** — one Markdown file per agent.

> See [`.codex/agents/README.md`](.codex/agents/README.md) for the full delegation routing table and handoff format.
> See [`.codex/config.toml`](.codex/config.toml) for the central multi-agent and pipeline configuration.

---

## 7) Documentation — VitePress 2

Project documentation will be built with **[VitePress 2](https://vitepress.dev/)**.

### 7.1 Directory Structure (future)

```
docs/
├── .vitepress/
│   └── config.ts          # VitePress configuration
├── index.md               # Home page
├── guide/                  # Developer guide
├── api/                    # API reference
└── architecture/           # Mirrors .codex/architecture.md for public consumption
```

### 7.2 Plugins

| Plugin                  | Purpose                                         | npm package                    |
| ----------------------- | ----------------------------------------------- | ------------------------------ |
| **Mermaid**             | Diagrams (flowcharts, sequence, ER, etc.)       | `vitepress-plugin-mermaid`     |
| **Tabs**                | Tabbed content blocks (e.g., npm / pnpm / yarn) | `vitepress-plugin-tabs`        |
| **Group Icons**         | Tech stack icon groups in code blocks           | `vitepress-plugin-group-icons` |
| **Search** _(built-in)_ | Full-text search via MiniSearch                 | _(included in VitePress)_      |

### 7.3 Setup Commands

```bash
# Install VitePress + plugins (run once)
npm install -D vitepress vitepress-plugin-mermaid vitepress-plugin-tabs vitepress-plugin-group-icons

# Dev server
npx vitepress dev docs

# Build static site
npx vitepress build docs

# Preview production build
npx vitepress preview docs
```

### 7.4 Configuration Skeleton (`docs/.vitepress/config.ts`)

```ts
import { withMermaid } from 'vitepress-plugin-mermaid';
import { tabsMarkdownPlugin } from 'vitepress-plugin-tabs';
import { groupIconMdPlugin } from 'vitepress-plugin-group-icons';

export default withMermaid({
    title: 'tradition-me',
    description: 'Project documentation',
    themeConfig: {
        search: { provider: 'local' }, // MiniSearch
        nav: [
            { text: 'Guide', link: '/guide/' },
            { text: 'API', link: '/api/' },
        ],
        sidebar: {
            /* auto-generated or manual */
        },
    },
    markdown: {
        config(md) {
            md.use(tabsMarkdownPlugin);
            md.use(groupIconMdPlugin);
        },
    },
});
```

### 7.5 Conventions

1. All docs are Markdown (`.md`) in `docs/`.
2. Use Mermaid fenced blocks (` ```mermaid `) for architecture / flow diagrams.
3. Use `:::tabs` for multi-option command blocks.
4. Keep docs in sync with code — the **Docs agent** is responsible (§6).
5. Configuration lives in `.codex/config.toml` under `[docs]`.
