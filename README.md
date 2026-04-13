# GetOTPs

Virtual phone number and OTP verification platform. Rent temporary phone numbers to receive SMS OTP verification codes for 1000+ services worldwide.

## Features

- Virtual phone number rental for OTP verification
- Support for 1000+ services (WhatsApp, Telegram, Google, etc.)
- Multiple country support with real virtual numbers
- Admin dashboard for managing services, pricing, and users
- Secure authentication with session-based auth
- Real-time SMS delivery via Proxnum API
- Responsive UI with dark/light theme support

## Tech Stack

- **Frontend:** React 18, TypeScript, Tailwind CSS, Radix UI, Framer Motion
- **Backend:** Express.js 5, Node.js, TypeScript
- **Database:** SQLite (better-sqlite3) with Drizzle ORM
- **Auth:** Passport.js with session-based authentication
- **Payments:** Stripe integration

## Getting Started

### Prerequisites

- Node.js 18+
- npm or yarn

### Installation

```bash
# Clone the repository
git clone https://github.com/whogf22/getotps-master-2zip-1.git
cd getotps-master-2zip-1

# Install dependencies
npm install

# Copy environment variables
cp .env.example .env

# Push database schema
npm run db:push

# Start development server
npm run dev
```

### Environment Variables

See `.env.example` for all required environment variables.

### Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Start development server |
| `npm run build` | Build for production |
| `npm start` | Start production server |
| `npm run check` | TypeScript type checking |
| `npm run db:push` | Push database schema changes |

## Project Structure

```
.
├── client/          # React frontend
├── server/          # Express.js backend
│   ├── routes.ts    # API routes
│   ├── storage.ts   # Database layer
│   ├── proxnum.ts   # SMS provider integration
│   └── index.ts     # Server entry point
├── shared/          # Shared types and schema
├── script/          # Build scripts
└── attached_assets/ # Static assets
```

## License

MIT
