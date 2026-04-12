# GetOTPs

## Overview
GetOTPs is a PERN-style app (SQLite + Express + React/Vite) where users rent temporary phone numbers to receive SMS OTP verification codes for 1000+ services. Powered by the Proxnum API for real virtual number provisioning and SMS delivery.

## Architecture
- **Backend**: Express.js with SQLite (better-sqlite3), Drizzle ORM, session-based auth (Passport.js), memorystore sessions
- **Frontend**: React 18 + Vite, wouter for routing (hash-based)
- **3D**: @react-three/fiber v8 + @react-three/drei v9 + three.js v0.183
- **SMS Provider**: Proxnum API (https://proxnum.com/api/v1) — Bearer token auth via PROXNUM_API_KEY env var
- **Styling**: Vanilla CSS with `@layer` system in `client/src/index.css`

## SMS Provider Integration (Proxnum Reseller API)
- **API Docs**: https://proxnum.com/reseller-api-docs
- **API Client**: `server/proxnum.ts` — typed wrapper with caching, 15s timeouts, 2 retries with exponential backoff
- **Auth**: Bearer token via `Authorization: Bearer {PROXNUM_API_KEY}` + `Accept: application/json` header
- **Service Sync**: On startup, fetches all services and aggregated prices from Proxnum, applies global markup multiplier, upserts into DB
- **Country Codes**: Proxnum uses numeric country codes (e.g., "12" = USA virtual, "187" = USA). Platform is USA-only — country is hardcoded via `getUSCountryCode()`
- **Price Markup**: `finalPrice = basePrice × globalMultiplier × serviceMultiplier` — settings stored in DB `settings` table
- **Virtual Numbers (Reseller Endpoints)**:
  - `POST /resell/virtual/buy` — body: `{service, country}` → response: `{success, activation: {id, phone, activation_id, msg, amount_paid, status}}`
  - `GET /resell/virtual/{activation_id}/status` → response: `{success, status: "completed", code: "1234", activation: {id, phone, activation_id}}`
  - `POST /resell/virtual/cancel` — body: `{activation_id}` → response: `{success, code: "cancel_accepted"|"cancel_rejected"}`
  - `POST /resell/virtual/resend` — body: `{activation_id}` → response: `{success, activation: {id, activation_id, phone}}`
  - `GET /resell/activations?page=1&per_page=25` — paginated activation list
  - `GET /resell/price?service=ig&country=6` — per-service price check
- **Normalized Error Codes**: `no_numbers`, `insufficient_balance`, `service_unavailable`, `cancel_rejected`
- **Rentals**: POST `/rental/buy`, GET `/rental/{id}/status`, POST `/rental/cancel`, GET `/rentals/{id}/messages`
- **Note**: PROXNUM_API_KEY must be a real API token generated from Proxnum Profile → API Keys (not the license key)

## Circle Programmable Wallets Integration
- **SDK**: `@circle-fin/developer-controlled-wallets` — Developer-Controlled wallet model
- **Service**: `server/circle.ts` — wraps Circle SDK (create wallet set, create wallet, get balance, list transactions)
- **Env Vars (both required for deployment)**: `CIRCLE_API_KEY` (Circle API key from Circle Console) + `CIRCLE_ENTITY_SECRET` (32-byte entity secret, must be generated and registered via Circle SDK before first use)
- **User Flow**: Each user gets a unique Ethereum wallet address for USDC deposits via Circle
- **Auto-detection**: `POST /api/circle/check-deposits` polls Circle for inbound USDC transfers, filters USDC-only by tokenSymbol/tokenName, deduplicates by circleTransferId+txHash, and auto-credits user balance
- **Auto-wallet**: When Circle is configured, `GET /api/circle/wallet` auto-creates a wallet on first visit (no manual button needed)
- **Deposit Method Detection**: `GET /api/deposit-method` returns which method is active (circle/manual/none). Frontend renders only the working method — no broken UI or confusing fallback links.
- **Fallback**: When Circle is not configured (missing env vars), the Add Funds page falls back to manual crypto deposit (static wallets + admin confirmation). When Circle IS configured, manual crypto endpoints are disabled and the UI only shows Circle USDC deposits. If neither is configured, a clear "deposits unavailable" message is shown.
- **Live Crypto Rates**: BTC, ETH, LTC, USDT, USDC prices fetched from CoinGecko API and cached for 5 minutes (replaces old hardcoded rates).
- **Background polling**: Server polls Circle every 2 minutes for all users with wallets (batched 5 at a time), auto-crediting confirmed USDC deposits without user action.
- **DB Fields**: `users.circle_wallet_id`, `users.circle_wallet_address`, `crypto_deposits.circle_transfer_id` — stores per-user Circle wallet info and transfer dedup
- **Endpoints**: `GET /api/circle/configured`, `GET /api/circle/wallet`, `POST /api/circle/wallet/create`, `POST /api/circle/check-deposits`

## Database Schema (SQLite)
- `users` — auth, balance, API key (hashed SHA-256 + prefix), role, status, circle_wallet_id, circle_wallet_address
- `services` — cached from Proxnum with slug=service code (e.g., "tg", "wa", "fb")
- `orders` — virtual number activations with proxnum_id, country, status tracking (waiting/pending/completed/cancelled/expired)
- `rentals` — longer-term number leases with days, expiry, proxnum_id
- `rental_messages` — SMS messages received on rented numbers
- `settings` — key/value config (price_multiplier, default_country, per-service multipliers)
- `transactions` — financial ledger (deposit, purchase, refund)
- `crypto_deposits` — crypto payment flow, optional circle_transfer_id
- `audit_logs` — admin action audit trail (in Drizzle schema)
- `password_reset_tokens` — token-based password reset flow

## Key Design Decisions
- **Single WebGL canvas**: Only the HeroScene uses R3F Canvas. PhoneMockup is CSS-only to prevent GPU context loss.
- **CSS Namespacing**: Landing uses `l-*` prefix, dashboard `dash-*`, network stats `net-*`, OTP ticker `otp-tick*`, glow cards `.glow-card`, reveal animations `.reveal`/`.revealed`
- **R3F v8 BufferAttribute pattern**: `array={...} count={n} itemSize={3}`
- **Design palette**: Dark `#020810`/`#030810` base, cyan `#22d3ee` primary, violet `#818cf8` secondary
- **3D Scene**: HexGrid, DataStreams, GlobeCore with wireframes/dots/arcs/rings, FloatingPanels, PulseNodes, SpaceDust, fog, 5-light cinematic setup, mouse-tracking CameraRig
- **SceneErrorBoundary**: Catches WebGL failures gracefully (headless environments)
- **API Key Security**: Raw keys never stored. SHA-256 hash + 8-char prefix stored. New key shown once on generation.
- **Order Status**: New orders use "waiting" status. Both "waiting" and "pending" treated as active/cancellable.
- **CSRF Protection**: Double-submit cookie pattern for all mutating endpoints (except API v1 which uses API keys)

## Project Structure
```
client/src/
  pages/Landing.tsx          - Main landing page with GlowCard, Reveal, parallax hero
  pages/Dashboard.tsx        - User dashboard overview
  pages/BuyNumber.tsx        - Service selection (USA-only, no country selector)
  pages/ActiveNumbers.tsx    - Active OTP orders with SMS polling
  pages/Rentals.tsx          - Long-term rental management with messages
  pages/ForgotPassword.tsx   - Password reset flow (request + reset)
  pages/AdminDashboard.tsx   - Admin overview: stats, recent users, transactions
  pages/AdminUsers.tsx       - User management: search, list, add-balance, suspend/activate, CSV export
  pages/AdminDeposits.tsx    - All deposits: pending/all toggle, Circle auto-deposits
  pages/AdminSettings.tsx    - Platform settings: price multiplier, default country
  components/3d/
    HeroScene.tsx            - Full 3D scene (globe, hexgrid, streams, panels, particles)
    LiveOTPFeed.tsx           - LiveOTPTicker + NetworkStats (dynamic from /api/stats)
    PhoneMockup.tsx          - CSS-only phone with OTP display
    SceneErrorBoundary.tsx   - Error boundary for WebGL failures
  components/DashboardLayout.tsx - Sidebar with rentals nav + admin nav (role-gated)
  components/Logo.tsx        - Logo component using --primary CSS var
  lib/queryClient.ts         - API client with CSRF token handling
  index.css                  - Complete design system (~1237 lines, @layer organized)
server/
  index.ts                   - Express server with CSRF, helmet, compression, trust proxy
  routes.ts                  - API routes (auth, orders, rentals, crypto, circle, admin)
  proxnum.ts                 - Proxnum API client with caching, timeouts, retries
  circle.ts                  - Circle Programmable Wallets SDK wrapper
  storage.ts                 - Database storage layer with transactional operations
  email.ts                   - Email service stub (ready for SMTP integration)
shared/
  schema.ts                  - Drizzle schema definitions
scripts/
  reset-admin-password.ts    - Admin password reset utility
```

## API Endpoints
- **Auth**: POST /api/auth/register, /login, /logout, GET /me, POST /forgot-password, /reset-password
- **CSRF**: GET /api/csrf-token
- **Services**: GET /api/services, /api/countries, /api/prices
- **Stats**: GET /api/stats (public)
- **Orders (Virtual)**: POST /api/orders, GET /api/orders/active, POST /:id/check-sms, /:id/cancel, /:id/resend
- **Rentals**: POST /api/rentals, GET /api/rentals/active, GET /:id/messages, POST /:id/cancel
- **Circle Wallet**: GET /api/circle/configured, GET /wallet, POST /wallet/create, POST /check-deposits
- **Crypto (Legacy)**: GET /api/crypto/currencies, POST /create-deposit, POST /:id/submit-hash
- **Admin**: GET /api/admin/stats, /users, /orders, /transactions, /audit-logs, PUT /services/:id, GET/PUT /settings, POST /users/:id/add-balance, POST /users/:id/suspend, POST /crypto/:id/confirm, POST /crypto/:id/reject, GET /crypto/pending, GET /crypto/all, GET /export/users|orders|transactions
- **API v1**: GET /api/v1/services, GET /price, POST /order, GET /order/:id, POST /order/:id/cancel, POST /order/:id/resend, POST /rental

## Admin Panel
- Routes: /admin, /admin/users, /admin/deposits, /admin/settings
- Protected by `AdminRoute` component (redirects non-admin users to /dashboard)
- Admin sidebar links (amber-themed) appear only for users with role "admin"
- Default admin: admin@getotps.com / admin123
- Admin can: suspend/activate users, confirm/reject deposits, export CSV, view audit logs

## Security & Performance
- **CSRF Protection**: Double-submit cookie on all mutating endpoints (API v1 exempt, uses API key auth)
- **Rate limiting**: express-rate-limit on login (10/15min), register (10/hr), API v1 (60/min)
- **API Key Hashing**: SHA-256 hash stored, raw key shown only once on generation, prefix for display
- **Session**: memorystore sessions, SESSION_SECRET enforced in production, httpOnly+sameSite+secure cookies
- **Trust Proxy**: Enabled for proper IP detection behind Replit proxy
- **Atomic balance operations**: `atomicDeductBalance()` and `atomicAddBalance()` use single SQL UPDATE with RETURNING
- **Transactional refunds**: `transactionalCancelAndRefund`, `transactionalRentalCancelAndRefund`, `transactionalExpireAndRefund`, `transactionalConfirmDeposit`
- **Expired order cleanup**: Background job runs every 60s, auto-refunds expired orders
- **Expired deposit cleanup**: Background job runs every 2 min, marks stale pending deposits as expired
- **FK pragma**: SQLite foreign key enforcement enabled
- **Input validation**: `parseId()` validates all route params; password min 8 chars; email format validation
- **Deposit caps**: Max $10,000 per crypto deposit
- **Gzip compression**: compression middleware enabled
- **Health endpoint**: GET /api/health
- **CSP**: Strict Content-Security-Policy via helmet
- **Audit logging**: Admin actions logged to audit_logs table
- **Sanitized logging**: API response bodies redacted for sensitive keys
- **Proxnum resilience**: 15s timeouts, 2 retries with exponential backoff, non-retryable error detection

## Important Notes
- Three.js packages pinned: fiber@8.18.0, drei@9.122.0, three@0.183.2 (React 18 compatible)
- Install three.js packages with `--legacy-peer-deps`
- WebGL unavailable in headless screenshot tool — always shows fallback. Works in real browsers.
- Proxnum API key stored as PROXNUM_API_KEY environment secret
- Default admin: admin@getotps.com / admin123
- Admin password reset: `npx tsx scripts/reset-admin-password.ts <new-password>`
- Email service is stubbed (`server/email.ts`) — configure SMTP_HOST/SMTP_USER/SMTP_PASS for production
- Password reset tokens returned in dev mode response body for testing
