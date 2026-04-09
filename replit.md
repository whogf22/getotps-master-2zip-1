# GetOTPs

## Overview
GetOTPs is a PERN-style app (SQLite + Express + React/Vite) where users rent temporary US phone numbers to receive SMS OTP verification codes for 500+ services.

## Architecture
- **Backend**: Express.js with SQLite (better-sqlite3), session-based auth
- **Frontend**: React 18 + Vite, wouter for routing
- **3D**: @react-three/fiber v8 + @react-three/drei v9 + three.js v0.183
- **Styling**: Vanilla CSS with `@layer` system in `client/src/index.css`

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
  components/3d/
    HeroScene.tsx            - Full 3D scene (globe, hexgrid, streams, panels, particles)
    LiveOTPFeed.tsx           - LiveOTPTicker + NetworkStats components
    PhoneMockup.tsx          - CSS-only phone with OTP display
    SceneErrorBoundary.tsx   - Error boundary for WebGL failures
  components/Logo.tsx        - Logo component using --primary CSS var
  index.css                  - Complete design system (~1237 lines, @layer organized)
server/
  index.ts                   - Express server entry
  routes.ts                  - API routes
  db.ts                      - SQLite database
```

## Important Notes
- Three.js packages pinned: fiber@8.18.0, drei@9.122.0, three@0.183.2 (React 18 compatible)
- Install three.js packages with `--legacy-peer-deps`
- WebGL unavailable in headless screenshot tool — always shows fallback. Works in real browsers.
- Two core services: "Receive OTP" (Key icon, cyan) and "Rent a Number" (Server icon, violet)
