---
title: Security v1 foundation with Spatie RBAC
status: done
created: 2026-02-28
completed: 2026-02-28
stage: archive
assignees: [orchestrator, backend, qa]
---

## Scope

Establish phase-1 security foundations for the new repo using Fortify + Spatie Permission, following the required flow:

`Request -> DTO -> Controller -> Action -> Model -> Database -> Resource`

Initial batch includes:
- Spatie Permission package + config + migrations
- Core security schema (`mfa_factors`, `mfa_recovery_codes`, `user_sessions`, `audit_logs`)
- RBAC backend primitives (requests, DTOs, actions, controllers, resources)
- Protected RBAC routes using permission middleware

## Acceptance Criteria

- [x] `spatie/laravel-permission` installed and configured
- [x] `User` model uses `HasRoles`
- [x] Spatie permission tables are present via published migrations
- [x] Security tables added with indexes for audit/session/mfa paths
- [x] RBAC flow implemented for:
- [x] create role
- [x] sync role permissions
- [x] assign user roles
- [x] All structured outputs use `JsonResource`
- [x] Route protection uses `role` / `permission` / `role_or_permission` middleware
- [x] Feature tests cover RBAC enforcement and idempotent sync/assign behavior

## Design Notes

- v1 uses `web` guard only.
- Spatie tables are source of truth (no custom pivot tables for RBAC).
- Keep controllers thin and push use-case behavior into invokable actions.

## Implementation Log

- Created task and initialized first implementation batch.
- Installed and published Spatie Permission (`config/permission.php`, migration).
- Added `HasRoles` to `User`, middleware aliases in `bootstrap/app.php`, and `routes/security.php`.
- Implemented request -> DTO -> controller -> action -> resource flow for:
  - `CreateRoleAction`
  - `SyncRolePermissionsAction`
  - `AssignUserRolesAction`
- Added security resources: `RoleResource`, `PermissionResource`, `UserSecurityResource`, `SessionResource`, `AuditLogResource`.
- Added app security models: `AuditLog`, `UserSession`.
- Added security schema migration for `mfa_factors`, `mfa_recovery_codes`, `user_sessions`, `audit_logs`.
- Added RBAC seed baseline via `SecurityRbacSeeder`.
- Added feature tests for RBAC route enforcement and idempotent sync behavior.

## Verification

- [x] php artisan test --compact tests/Feature/Security
- [x] vendor/bin/phpstan analyse --memory-limit=512M
- [x] vendor/bin/pint --test
- [x] php artisan test --compact

### Verify Run Log (2026-02-28)

- `vendor/bin/phpstan analyse --memory-limit=512M` -> pass (no errors)
- `./vendor/bin/pint` -> pass (14 style issues auto-fixed)
- `./vendor/bin/pint --test` -> pass
- `php artisan test --compact tests/Feature/Security` -> pass (4 tests, 10 assertions)
- `php artisan test --compact` -> pass (45 tests, 142 assertions)

## Documentation

- Task log updated with final verification outcomes and QA sign-off.

## QA Sign-off

- [x] QA checks complete; task approved for archive on 2026-02-28.
