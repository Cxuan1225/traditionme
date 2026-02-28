---
id: q-20260301-051324-frontend-inertia-route-contract
from: frontend
task: rbac-admin-ui-wayfinder
priority: high
blocking: true
status: open
created_at: 2026-03-01T05:13:24
---

## Question
Can backend/orchestrator confirm and wire an Inertia render contract for settings/security/Roles (security.roles.index currently JSON-only), including capability props canViewRoles/canCreateRoles/canManageRolePermissions/canAssignUserRoles?

## Context
Frontend page is implemented at resources/js/pages/settings/security/Roles.vue and validated via types check + file-level lint; QA is blocked until route render contract is exposed.

## Proposed Options
- n/a