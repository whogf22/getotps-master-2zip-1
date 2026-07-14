# AGENTS.md

## Cursor Cloud specific instructions

### What this is
GetOTPs is a single full-stack app (no monorepo): an Express 5 API + a Vite/React 18 SPA
served together by one Node process, backed by a local SQLite file (`data.db`, auto-created).
There is only **one service** to run.

- Backend entry: `server/index.ts` (Express). In dev it mounts Vite as middleware, so the
  frontend is served from the same origin/port — do **not** start a separate Vite dev server.
- Frontend: `client/` (React, `wouter` **hash-based** routing — URLs look like
  `http://localhost:5000/#/dashboard`).
- DB layer: `server/storage.ts` creates all tables with `CREATE TABLE IF NOT EXISTS` and
  seeds an admin user on startup, so **`npm run db:push` is not required** to boot. The
  Drizzle schema lives in `shared/schema.ts`.

### Running / lint / test / build
Standard commands live in `package.json` (`scripts`). Key ones:
- Run (dev): `npm run dev` → serves on port **5000** (`http://localhost:5000`).
- Lint / typecheck: `npm run check` (runs `tsc`; this repo has no separate ESLint config).
- Build (prod bundle): `npm run build`; prod run: `npm start`.
- There is **no automated test suite** in this repo.

### Non-obvious caveats
- `.env` is git-ignored. Copy it from `.env.example` (`cp .env.example .env`) — the update
  script does this automatically. Without it the app still boots (dev fallbacks for
  `SESSION_SECRET`/`ADMIN_PASSWORD`), but the crypto wallet addresses come from env vars, so
  the "Add Funds" crypto-deposit flow only shows currencies when `.env` (with the
  `CRYPTO_WALLET_*` placeholders) is present.
- Default dev admin login: `admin@getotps.online` / `admin123` (from `server/storage.ts`).
- External integrations need real secrets to exercise fully: `PROXNUM_API_KEY` (SMS number
  provisioning / ordering) and Stripe. Ordering a real number requires Proxnum. The service
  catalog can populate on startup without a valid key, but buying/checking OTPs will not.
- Core flows that work fully **locally, no external keys**: register, login, dashboard, and
  wallet top-up via `POST /api/crypto/create-deposit` + `.../simulate-confirm` (the
  "Simulate Confirmation (Demo)" button on the Add Funds page credits the wallet balance).

### KNOWN BLOCKER (as of the current `main` commit `efe4863`)
The app **does not start** from the committed source because three files were committed in a
corrupted state (they appear to have been passed through a markdown/HTML sanitizer):
- `server/routes.ts` and `server/proxnum.ts`: markdown-escape backslashes were injected
  (e.g. `\_`, `\|\|`, `\[`, `` \` ``) and TypeScript generic type arguments were stripped
  (e.g. `Record = {` instead of `Record<string, string> = {`). `tsx`/esbuild fails
  immediately: `server/routes.ts:3:24: ERROR: Syntax error "_"`.
- `server/index.ts`: helmet CSP sets `upgradeInsecureRequests: undefined` in dev, which
  helmet 8 rejects (`invalid directive value for "upgrade-insecure-requests"`).

Clean, working versions of all three files exist in the **parent commit `5f7f9a0`**
(`git show 5f7f9a0:server/routes.ts`, etc.). To get a runnable app, restore those files
from `5f7f9a0` (re-applying any intended `efe4863` feature changes cleanly), or fix the
corruption in place. This is a source-code defect, independent of environment setup.
