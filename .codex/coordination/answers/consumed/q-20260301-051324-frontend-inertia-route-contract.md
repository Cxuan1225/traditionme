---
id: q-20260301-051324-frontend-inertia-route-contract
to: frontend
status: answered
answered_by: orchestrator-auto
answered_at: 2026-03-01T05:13:29
---

## Answer
Use Wayfinder-generated helpers only for frontend route/action references.
Do not hard-code URL strings in Vue/TS.
If backend routes changed, regenerate with php artisan wayfinder:generate.