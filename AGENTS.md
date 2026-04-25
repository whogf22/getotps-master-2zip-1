# AGENTS.md

This file defines project-specific guidance for human and AI coding agents.

## Commands

- `npm run dev` - Start development server.
- `npm run check` - TypeScript validation.
- `npm run build` - Production build for client/server.

## Routes

- `GET /healthz` - health probe endpoint, writes uptime logs.
- `GET /api/status` - latest uptime history from `uptime_logs`.
- `GET /status` - public status page consuming `/api/status`.
- `POST /api/contact` - public contact submission endpoint for support requests.

## Support Integration

- Landing footer includes Telegram support link (`https://t.me/getotps_support`).
- Landing footer includes a contact form that posts to `/api/contact`.
- Contact endpoint is intentionally a stub until external messaging/email credentials are configured.

## Contact Delivery TODO

- Set `CONTACT_FORWARD_TARGET` (email/webhook/CRM destination) once provider is chosen.
- Replace in-memory stub in `POST /api/contact` with provider integration and delivery confirmation.
- Keep throttling on `/api/contact` enabled to reduce abuse.

## Operational Notes

- Keep `/healthz` and `/api/status` unauthenticated for monitoring visibility.
- Keep contact endpoint payload validation strict and avoid logging full message content in production logs.
