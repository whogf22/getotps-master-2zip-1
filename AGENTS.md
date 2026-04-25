# AGENTS.md

Project guidance for GetOTPs cloud development.

## Core scripts
- `npm run dev` - start app (API + client).
- `npm run check` - TypeScript checks.
- `npm run build` - production build.
- `npm run db:push` - apply Drizzle schema changes.

## New routes
- `GET /healthz` - service health check.
- `GET /api/status` - latest uptime history from `uptime_logs`.
- `GET /status` - public status page consuming `/api/status`.

## Data model notes
- `uptime_logs` stores:
  - `status` (`ok` / `down`)
  - `statusCode`
  - `latencyMs`
  - `source`
  - `checkedAt` (ISO timestamp)

## Environment reminders
- `SESSION_SECRET` is required for production.
- `PROXNUM_API_KEY` is required for live provider operations.

## Operational notes
- Keep `/healthz` and `/api/status` unauthenticated for monitoring visibility.
- Prefer Drizzle query builders for DB access; avoid string-concatenated SQL queries.
