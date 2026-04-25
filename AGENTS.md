# AGENTS.md

This file defines project-specific guidance for coding agents.

## Security Hardening

- Helmet is enabled in `server/index.ts` with CSP and secure defaults.
- CORS is strict-allowlist based in `server/index.ts`:
  - Configure `CORS_ALLOWLIST` as a comma-separated list of trusted origins.
  - In development, localhost origins are allowed by default.
- CSRF protection is enabled:
  - `GET /api/csrf-token` issues a token tied to the session.
  - State-changing routes under `/api` and `/api/v1` require `x-csrf-token`, except:
    - `/api/auth/login`
    - `/api/auth/register`
    - `/api/v1/webhooks/proxnum`
    - `/healthz`
- Client requests automatically attach CSRF token header via `client/src/lib/queryClient.ts`.

## Email Authentication (SPF/DKIM/DMARC)

For production sending domains, configure all three:

1. SPF
   - Publish TXT record: `v=spf1 include:_spf.resend.com ~all` (or provider-specific include).
2. DKIM
   - Add provider-generated DKIM CNAME/TXT records and verify in provider dashboard.
3. DMARC
   - Start with monitoring:
     - `_dmarc.example.com TXT "v=DMARC1; p=none; rua=mailto:dmarc@example.com; fo=1"`
   - Move to stricter policy (`quarantine`/`reject`) after monitoring alignment.

## SQL Parameterization Audit

- Storage queries are implemented through Drizzle query builders (`eq`, `and`, `or`, `insert/update/delete`) in `server/storage.ts`.
- No runtime user input is concatenated into SQL strings for query execution.
- Startup schema bootstrap uses static `sqlite.exec` DDL statements only (non-user input).

