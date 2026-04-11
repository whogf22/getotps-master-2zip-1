# GetOTPs

## Overview
GetOTPs is a PERN-style app (SQLite + Express + React/Vite) where users rent temporary phone numbers to receive SMS OTP verification codes for 1000+ services. Powered by the Proxnum API for real virtual number provisioning and SMS delivery.

## Architecture
- **Backend**: Express.js with SQLite (better-sqlite3), Drizzle ORM, session-based auth (Passport.js)
- **Frontend**: React 18 + Vite, wouter for routing
- **3D**: @react-three/fiber v8 + @react-three/drei v9 + three.js v0.183
- **SMS Provider**: Proxnum API (https://proxnum.com/api/v1) — Bearer token auth via PROXNUM_API_KEY env var
- **Styling**: Vanilla CSS with `@layer` system in `client/src/index.css`

## SMS Provider Integration (Proxnum Reseller API)
- **API Docs**: https://proxnum.com/reseller-api-docs
- **API Client**: `server/proxnum.ts` — typed wrapper with caching for services/countries/prices
- **Auth**: Bearer token via `Authorization: Bearer {PROXNUM_API_KEY}` + `Accept: application/json` header
- **Service Sync**: On startup, fetches all services and aggregated prices from Proxnum, applies global markup multiplier, upserts into DB
- **Country Codes**: Proxnum uses numeric country codes (e.g., "12" = USA virtual, "187" = USA, "0" = Russia). Resolved via `findCountryCode()`
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
- **Env Vars**: `CIRCLE_API_KEY` (Circle API key) + `CIRCLE_ENTITY_SECRET` (32-byte entity secret)
- **User Flow**: Each user gets a unique Ethereum wallet address for USDC deposits via Circle
- **Auto-detection**: `POST /api/circle/check-deposits` polls Circle for inbound USDC transfers and auto-credits user balance
- **Fallback**: When Circle is not configured, the Add Funds page falls back to manual crypto deposit (static wallets + admin confirmation)
- **DB Fields**: `users.circle_wallet_id`, `users.circle_wallet_address` — stores per-user Circle wallet info
- **Endpoints**: `GET /api/circle/configured`, `GET /api/circle/wallet`, `POST /api/circle/wallet/create`, `POST /api/circle/check-deposits`

## Database Schema (SQLite)
- `users` — auth, balance, API key, role, circle_wallet_id, circle_wallet_address
- `services` — cached from Proxnum with slug=service code (e.g., "tg", "wa", "fb")
- `orders` — virtual number activations with proxnum_id, country, status tracking
- `rentals` — longer-term number leases with days, expiry, proxnum_id
- `rental_messages` — SMS messages received on rented numbers
- `settings` — key/value config (price_multiplier, default_country, per-service multipliers)
- `transactions` — financial ledger (deposit, purchase, refund)
- `crypto_deposits` — crypto payment flow, optional circle_transfer_id

## Key Design Decisions
- **Single WebGL canvas**: Only the HeroScene uses R3F Canvas. PhoneMockup is CSS-only to prevent GPU context loss.
- **CSS Namespacing**: Landing uses `l-*` prefix, dashboard `dash-*`, network stats `net-*`, OTP ticker `otp-tick*`, glow cards `.glow-card`, reveal animations `.reveal`/`.revealed`
- **R3F v8 BufferAttribute pattern**: `array={...} count={n} itemSize={3}`
- **Design palette**: Dark `#020810`/`#030810` base, cyan `#22d3ee` primary, violet `#818cf8` secondary
- **3D Scene**: HexGrid, DataStreams, GlobeCore with wireframes/dots/arcs/rings, FloatingPanels, PulseNodes, SpaceDust, fog, 5-light cinematic setup, mouse-tracking CameraRig
- **SceneErrorBoundary**: Catches WebGL failures gracefully (headless environments)

## Project Structure
```
client/src/
  pages/Landing.tsx          - Main landing page with GlowCard, Reveal, parallax hero
  pages/AdminDashboard.tsx   - Admin overview: stats, recent users, transactions
  pages/AdminUsers.tsx       - User management: search, list, add-balance modal
  pages/AdminDeposits.tsx    - Pending crypto deposits: confirm/reject
  pages/AdminSettings.tsx    - Platform settings: price multiplier, default country
  components/3d/
    HeroScene.tsx            - Full 3D scene (globe, hexgrid, streams, panels, particles)
    LiveOTPFeed.tsx           - LiveOTPTicker + NetworkStats components
    PhoneMockup.tsx          - CSS-only phone with OTP display
    SceneErrorBoundary.tsx   - Error boundary for WebGL failures
  components/DashboardLayout.tsx - Sidebar with admin nav (role-gated)
  components/Logo.tsx        - Logo component using --primary CSS var
  index.css                  - Complete design system (~1237 lines, @layer organized)
server/
  index.ts                   - Express server entry
  routes.ts                  - API routes (auth, orders, rentals, crypto, circle, admin)
  proxnum.ts                 - Proxnum API client with caching
  circle.ts                  - Circle Programmable Wallets SDK wrapper
  storage.ts                 - Database storage layer (Drizzle ORM)
shared/
  schema.ts                  - Drizzle schema definitions
```

## API Endpoints
- **Auth**: POST /api/auth/register, /login, /logout, GET /me
- **Services**: GET /api/services, /api/countries, /api/prices
- **Orders (Virtual)**: POST /api/orders, GET /api/orders/active, POST /:id/check-sms, /:id/cancel
- **Rentals**: POST /api/rentals, GET /api/rentals/active, GET /:id/messages, POST /:id/cancel
- **Circle Wallet**: GET /api/circle/configured, GET /wallet, POST /wallet/create, POST /check-deposits
- **Crypto (Legacy)**: GET /api/crypto/currencies, POST /create-deposit, POST /:id/submit-hash
- **Admin**: GET /api/admin/stats, /users, /transactions, PUT /services/:id, GET/PUT /settings, POST /users/:id/add-balance, POST /crypto/:id/confirm, POST /crypto/:id/reject, GET /crypto/pending
- **API v1**: GET /api/v1/services, GET /price, POST /order, GET /order/:id, POST /order/:id/cancel, POST /order/:id/resend, POST /rental

## Admin Panel
- Routes: /admin, /admin/users, /admin/deposits, /admin/settings
- Protected by `AdminRoute` component (redirects non-admin users to /dashboard)
- Admin sidebar links (amber-themed) appear only for users with role "admin"
- Default admin: admin@getotps.com / admin123

## Important Notes
- Three.js packages pinned: fiber@8.18.0, drei@9.122.0, three@0.183.2 (React 18 compatible)
- Install three.js packages with `--legacy-peer-deps`
- WebGL unavailable in headless screenshot tool — always shows fallback. Works in real browsers.
- Two core services: "Receive OTP" (Key icon, cyan) and "Rent a Number" (Server icon, violet)
- Proxnum API key stored as PROXNUM_API_KEY environment secret
- Default admin: admin@getotps.com / admin123
