---
title: RBAC role matrix (admin/user/seller) + frontend admin flow
status: in_progress
created: 2026-02-28
stage: implement
assignees: [orchestrator, backend, frontend]
---

## Scope

Implement coordinated RBAC follow-up after security v1 foundation:
- Backend: finalize role/permission matrix and seed data for `admin`, `user`, `seller`.
- Frontend: build RBAC admin UI flow (roles/permissions/users) using Wayfinder modules only.

## Acceptance Criteria

- [x] Backend seeder defines `admin`, `user`, `seller` with explicit permission bundles.
- [x] Seeder is idempotent and safe to rerun.
- [x] Any DB changes required for role bootstrap are added via migration(s).
- [ ] Frontend RBAC admin pages are implemented with `<script setup lang="ts">`.
- [ ] Frontend route/action calls use `resources/js/routes/*` and `resources/js/actions/*` (no hard-coded URLs).
- [ ] UI visibility/actions reflect permission capabilities.
- [ ] QA verification passes for backend + frontend relevant gates.

## Backend Implementation Log

- Updated `database/seeders/SecurityRbacSeeder.php` to seed explicit `admin` / `seller` / `user` permission bundles on `web` guard.
- Kept seeding idempotent via `findOrCreate` + `syncPermissions` and cache resets before/after sync.
- Updated `routes/security.php` middleware role references from `super-admin` to `admin` to match canonical role contract.
- Added `tests/Feature/Security/RbacRoleMatrixSeederTest.php` to assert matrix correctness and rerun idempotency.
- No additional DB schema changes were required for bootstrap; existing Spatie permission tables already cover this contract.

## Backend Verification

- `php artisan test tests/Feature/Security --compact` passed.
- `vendor/bin/pint --test` passed.
- `php artisan test --compact` passed.

## Coordination Plan

Execution order: `backend -> frontend -> qa`

Backend first to lock role/permission contract, then frontend consumes stabilized actions/routes.

### Handoff -> backend
**Task:** rbac-role-matrix-admin-user-seller
**Stage:** implement
**Context:** Define role matrix and seeding contract for `admin`, `user`, `seller` in security RBAC foundation. Primary files expected in `database/seeders/`, optionally `database/migrations/`, and tests in `tests/Feature/Security`.
**Acceptance criteria:** Seeder creates/updates roles and permissions idempotently for `web` guard, and tests cover expected role bundles and rerun safety.

### Handoff -> frontend
**Task:** rbac-admin-ui-wayfinder
**Stage:** implement
**Context:** Build RBAC admin screens after backend role/permission contract is finalized. Scope is `resources/js/` and related styles/views only. Must use Wayfinder-generated modules.
**Acceptance criteria:** Admin can view roles, create/update roles, sync permissions, and assign user roles through Inertia/Vue screens without hard-coded URL strings.
