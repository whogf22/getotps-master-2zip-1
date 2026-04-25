# AGENTS.md

This file defines project-specific guidance for human and AI coding agents working in this repository.

## Project Overview

- App: GetOTPs (virtual phone number + OTP verification platform)
- Frontend: React 18 + TypeScript (`client/`)
- Backend: Express 5 + TypeScript (`server/`)
- Shared contracts: `shared/`
- Database: SQLite via Drizzle ORM

## Setup and Run

1. Install dependencies:
   - `npm install`
2. Create local env file:
   - `cp .env.example .env`
3. Push schema to local SQLite DB:
   - `npm run db:push`
4. Start development server:
   - `npm run dev`

## Source of Truth for Commands

Use package scripts from `package.json`:

- `npm run dev` - run local dev server
- `npm run build` - build production output
- `npm run start` - run production build
- `npm run check` - TypeScript type check
- `npm run db:push` - apply schema changes to SQLite

## Codebase Conventions

- Keep API route logic in `server/routes.ts` and persistence logic in `server/storage.ts`.
- Keep shared types and database schema in `shared/schema.ts`.
- Use existing path aliases: `@/*` and `@shared/*`.
- Preserve strict TypeScript behavior (`tsconfig.json` has `strict: true`).
- Keep changes focused; avoid large refactors unless explicitly requested.

## Database and Schema Changes

- Update Drizzle schema in `shared/schema.ts`.
- Run `npm run db:push` after schema changes.
- Do not commit secrets or environment-specific sensitive values.

## Routes

- Health endpoint: `GET /healthz` (writes uptime checks to `uptime_logs` table).
- Pricing endpoint: `GET /api/pricing` (public pricing table from `services` table).
- OpenAPI spec: `server/openapi.yaml` (rendered in-app at `/api-docs`).
- Keep-alive workflow: `.github/workflows/keepalive-healthz.yml` pings `/healthz` every 5 minutes.
- Automatic rental refund sweep: runs every minute in `server/routes.ts` to refund expired rentals with zero SMS messages.

## CI/Automation Environment

- GitHub Actions keep-alive job expects repository secret:
  - `HEALTHCHECK_URL` (example: `https://getotps.online`)
- The workflow appends `/healthz` automatically.

## Environment Variables

- Backend:
  - `HCAPTCHA_SECRET` - secret key used to verify hCaptcha tokens on auth endpoints.
  - `RESEND_API_KEY` - Resend API key for transactional emails (password reset and verification).
  - `EMAIL_FROM` - sender address used for auth emails (example: `no-reply@getotps.online`).
  - `APP_BASE_URL` - public app origin used to generate reset/verification links.
- Frontend:
  - `VITE_HCAPTCHA_SITE_KEY` - public site key used to render hCaptcha on `/login` and `/register`.
  - `VITE_PLAUSIBLE_DOMAIN` - domain configured in Plausible (example: `getotps.online`).
  - `VITE_PLAUSIBLE_SRC` - optional custom script URL for self-hosted Plausible (default: `https://plausible.io/js/script.js`).

## Analytics Events

- Plausible script is loaded in `client/index.html` when `VITE_PLAUSIBLE_DOMAIN` is defined.
- Funnel events emitted from client:
  - `register` - successful account registration.
  - `topup` - successful deposit confirmation.
  - `first_otp` - first OTP received in active numbers flow.

## Testing Expectations

- For most code changes, run `npm run check`.
- Run `npm run build` when touching build-critical code paths.
- For frontend behavior changes, perform manual verification in the running app.
- Prefer targeted validation over broad, unrelated test runs.

## Commit and PR Guidance

- Make small, logically grouped commits with descriptive commit messages.
- Do not revert unrelated local changes you did not create.
- Keep PR descriptions concise and include what changed, why, and how it was tested.

## Cursor Cloud specific instructions

- Before edits, quickly scan this file and `README.md` for current workflow constraints.
- If you modify UI behavior or visuals, include a short video walkthrough artifact.
- For non-UI changes, include high-signal terminal evidence (type-check/build/targeted checks).
- Leave local dev services running after testing unless cleanup is required to continue work.
- If you add recurring setup steps (new dependencies, extra services, env requirements), update this file in the same change.
