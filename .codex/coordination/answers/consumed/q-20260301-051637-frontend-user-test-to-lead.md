---
id: q-20260301-051637-frontend-user-test-to-lead
to: frontend
status: answered
answered_by: orchestrator-auto
answered_at: 2026-03-01T05:16:38
---

## Answer
Follow the full verify gate before marking complete:
- vendor/bin/phpstan analyse --memory-limit=512M
- vendor/bin/pint --test
- php artisan test
- npm run lint:check
- npm run types:check
- npm run format:check