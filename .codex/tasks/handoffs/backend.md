## Coordination
LOCK: none
RESUME FROM: orchestrator-unblock-2026-03-01T05:18:07
SUBTASK STATUS: in_progress
LAST UPDATED: 2026-02-28

## Task
RBAC role matrix for `admin`, `user`, `seller` (DB/seeder contract)

## Scope
`database/`, `app/` (if needed), `tests/Feature/Security`

## Files
- `.codex/tasks/rbac-admin-user-seller-matrix-and-frontend-admin.md`
- `database/seeders/SecurityRbacSeeder.php` (expected)
- `database/migrations/*` (only if required)
- `tests/Feature/Security/*` (expected)

## Routes (if any)
- `security.roles.index` now uses `role_or_permission:admin|roles.view`
- `security.audit-logs.index` now uses `role:admin`
- Route names and URIs are unchanged.

## Commands Run
- `php artisan test tests/Feature/Security --compact`
- `vendor/bin/pint --test`
- `php artisan test --compact`

## Results
- Implemented canonical role matrix seeding for `admin`, `seller`, `user`.
- Added seeder contract/idempotency tests in `tests/Feature/Security/RbacRoleMatrixSeederTest.php`.
- All executed backend checks passed.

## Open Issues
- none

## Orchestrator Notes
- Canonical roles for v1 are fixed to `admin`, `user`, `seller`.
- Define explicit permission bundles; avoid implicit role-name conditionals in business logic.
- Seeder must be idempotent (`firstOrCreate`/`updateOrCreate` style behavior).
- If routes/actions change, note exact names for frontend Wayfinder consumption.

### Handoff -> backend
**Task:** rbac-role-matrix-admin-user-seller
**Stage:** implement
**Context:** Implement DB/seeder role matrix contract for `admin`, `user`, `seller` using Spatie Permission (`web` guard). Keep flow aligned with security foundation.
**Acceptance criteria:** Roles and permissions are reproducibly seeded; reruns do not duplicate data; tests assert role bundle expectations and idempotence.

## Next Step
handoff to frontend for RBAC admin UI consumption of canonical role matrix

### Handoff -> orchestrator
**Task:** rbac-role-matrix-admin-user-seller
**Stage:** implement (backend complete)
**Context:** Backend RBAC matrix contract is finalized for canonical roles `admin`, `seller`, `user` on `web` guard. Seeder is idempotent and route role gates now use `admin` instead of `super-admin`.
**Acceptance criteria:** Met for backend scope (matrix seeded, rerun-safe, tests added and passing).
**Contract notes for downstream:**
- Canonical roles: `admin`, `seller`, `user`
- Permission bundles:
  - `admin`: `roles.view`, `roles.create`, `roles.manage_permissions`, `permissions.view`, `permissions.create`, `users.assign_roles`, `security.audit.view`, `security.sessions.revoke`, `security.mfa.manage`
  - `seller`: `security.mfa.manage`
  - `user`: `security.mfa.manage`
- Route middleware updates (names unchanged):
  - `security.roles.index` -> `role_or_permission:admin|roles.view`
  - `security.audit-logs.index` -> `role:admin`
- Verification run:
  - `php artisan test tests/Feature/Security --compact`
  - `vendor/bin/pint --test`
  - `php artisan test --compact`
