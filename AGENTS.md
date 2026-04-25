# AGENTS.md

This file defines project-specific guidance for coding agents working in this repository.

## Project Overview

- App: GetOTPs (virtual number + OTP verification platform)
- Frontend: React 18 + TypeScript (`client/`)
- Backend: Express 5 + TypeScript (`server/`)
- Shared schema/contracts: `shared/`
- Database: SQLite via Drizzle ORM

## Setup

1. `npm install`
2. `cp .env.example .env`
3. `npm run db:push`
4. `npm run dev`

## Core Commands

- `npm run dev` - run local dev server
- `npm run check` - TypeScript type-check
- `npm run build` - build client + server
- `npm run start` - run production build
- `npm run db:push` - apply Drizzle schema changes

## Routing and APIs

- App + API are served from the same Express server in `server/index.ts`.
- API routes live in `server/routes.ts`.
- Frontend routes live in `client/src/App.tsx`.

## Performance Notes

- Compression middleware is enabled globally in `server/index.ts` via `compression()`.
- Landing dashboard mockup is lazy-loaded from `client/src/components/landing/DashboardPanel.tsx`.
- Hero font uses preconnect + preload hints in `client/index.html`.

## Environment Variables

- Keep secrets out of git. Use `.env` for local development.
- Keep `.env.example` updated whenever new vars are introduced.

## Testing Expectations

- For each logical change group, run:
  - `npm run check`
  - `npm run build`
- For UI behavior/layout changes, also perform manual browser validation.
