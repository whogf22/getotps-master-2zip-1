/**
 * Integration tests for the Express API routes.
 *
 * Strategy:
 *  - Mock both `../storage` and `../proxnum` modules via vi.mock so that
 *    storage uses an in-memory SQLite database and the Proxnum API never
 *    makes real HTTP calls.
 *  - Register the actual route handlers from routes.ts to test real logic.
 *  - Use supertest to fire HTTP requests and assert on responses.
 */

import { describe, it, expect, beforeAll, vi } from "vitest";
import request from "supertest";
import express from "express";
import { createServer } from "http";
import bcrypt from "bcryptjs";
import crypto from "crypto";
import Database from "better-sqlite3";
import { drizzle } from "drizzle-orm/better-sqlite3";
import { eq, and, or, desc } from "drizzle-orm";

import {
  users, services, orders, rentals, rentalMessages,
  settings, transactions, cryptoDeposits,
} from "../../shared/schema";
import type {
  InsertService, InsertOrder, InsertRental,
  InsertRentalMessage, InsertTransaction, InsertCryptoDeposit,
} from "../../shared/schema";

// ---------------------------------------------------------------------------
// In-memory storage factory
// Named "mockSharedStorage" (starts with "mock") so Vitest hoists it alongside
// vi.mock() calls, allowing the mock factory to close over it safely.
// ---------------------------------------------------------------------------
function buildInMemoryStorage() {
  const sqlite = new Database(":memory:");
  sqlite.pragma("journal_mode = WAL");
  sqlite.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE,
      email TEXT NOT NULL UNIQUE, password TEXT NOT NULL,
      balance TEXT NOT NULL DEFAULT '0.00', api_key TEXT UNIQUE,
      role TEXT NOT NULL DEFAULT 'user'
    );
    CREATE TABLE IF NOT EXISTS services (
      id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL,
      slug TEXT NOT NULL UNIQUE, price TEXT NOT NULL,
      icon TEXT, category TEXT, is_active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL,
      service_id INTEGER NOT NULL, service_name TEXT NOT NULL DEFAULT '',
      phone_number TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'pending',
      otp_code TEXT, sms_messages TEXT, price TEXT NOT NULL,
      country TEXT NOT NULL DEFAULT 'us', proxnum_id TEXT,
      created_at TEXT NOT NULL, expires_at TEXT NOT NULL, completed_at TEXT
    );
    CREATE TABLE IF NOT EXISTS rentals (
      id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL,
      service_id INTEGER NOT NULL, service_name TEXT NOT NULL DEFAULT '',
      phone_number TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'active',
      price TEXT NOT NULL, country TEXT NOT NULL DEFAULT 'us',
      days INTEGER NOT NULL DEFAULT 7, proxnum_id TEXT,
      created_at TEXT NOT NULL, expires_at TEXT NOT NULL, cancelled_at TEXT
    );
    CREATE TABLE IF NOT EXISTS rental_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT, rental_id INTEGER NOT NULL,
      sender TEXT, message TEXT NOT NULL, received_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS settings (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      key TEXT NOT NULL UNIQUE, value TEXT
    );
    CREATE TABLE IF NOT EXISTS transactions (
      id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL,
      type TEXT NOT NULL, amount TEXT NOT NULL, description TEXT,
      order_id INTEGER, stripe_session_id TEXT, created_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS crypto_deposits (
      id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL,
      currency TEXT NOT NULL, amount TEXT NOT NULL, crypto_amount TEXT,
      wallet_address TEXT NOT NULL, tx_hash TEXT,
      status TEXT NOT NULL DEFAULT 'pending',
      created_at TEXT NOT NULL, expires_at TEXT NOT NULL, completed_at TEXT
    );
  `);
  const db = drizzle(sqlite);
  db.insert(settings).values({ key: "price_multiplier", value: "1.5" }).run();
  db.insert(settings).values({ key: "default_country", value: "us" }).run();

  return {
    _db: db,
    _sqlite: sqlite,
    async getUser(id: number) { return db.select().from(users).where(eq(users.id, id)).get(); },
    async getUserByEmail(email: string) { return db.select().from(users).where(eq(users.email, email)).get(); },
    async getUserByUsername(u: string) { return db.select().from(users).where(eq(users.username, u)).get(); },
    async getUserByApiKey(k: string) { return db.select().from(users).where(eq(users.apiKey, k)).get(); },
    async createUser(data: { username: string; email: string; password: string }) {
      const apiKey = crypto.randomBytes(32).toString("hex");
      return db.insert(users).values({ ...data, apiKey }).returning().get();
    },
    async updateUserBalance(userId: number, balance: string) { db.update(users).set({ balance }).where(eq(users.id, userId)).run(); },
    async updateUserPassword(userId: number, password: string) { db.update(users).set({ password }).where(eq(users.id, userId)).run(); },
    async generateApiKey(userId: number) {
      const apiKey = crypto.randomBytes(32).toString("hex");
      db.update(users).set({ apiKey }).where(eq(users.id, userId)).run();
      return apiKey;
    },
    async getAllUsers() { return db.select().from(users).all(); },
    async getAllServices() { return db.select().from(services).where(eq(services.isActive, 1)).all(); },
    async getService(id: number) { return db.select().from(services).where(eq(services.id, id)).get(); },
    async getServiceBySlug(slug: string) { return db.select().from(services).where(eq(services.slug, slug)).get(); },
    async getServiceByName(name: string) { return db.select().from(services).where(eq(services.name, name)).get(); },
    async updateService(id: number, data: Partial<InsertService>) { db.update(services).set(data).where(eq(services.id, id)).run(); },
    async upsertServices(list: InsertService[]) {
      for (const s of list) {
        const ex = db.select().from(services).where(eq(services.slug, s.slug)).get();
        if (ex) db.update(services).set({ price: s.price, isActive: s.isActive, category: s.category ?? null }).where(eq(services.slug, s.slug)).run();
        else db.insert(services).values(s).run();
      }
    },
    async createOrder(data: InsertOrder) { return db.insert(orders).values(data).returning().get(); },
    async getOrder(id: number) { return db.select().from(orders).where(eq(orders.id, id)).get(); },
    async getOrderByProxnumId(proxnumId: string) { return db.select().from(orders).where(eq(orders.proxnumId, proxnumId)).get(); },
    async getUserOrders(userId: number) { return db.select().from(orders).where(eq(orders.userId, userId)).orderBy(desc(orders.id)).all(); },
    async getActiveOrders(userId: number) {
      return db.select().from(orders)
        .where(and(eq(orders.userId, userId), or(eq(orders.status, "pending"), eq(orders.status, "waiting"), eq(orders.status, "received"))))
        .orderBy(desc(orders.id)).all();
    },
    async getPendingOrders() { return db.select().from(orders).where(or(eq(orders.status, "pending"), eq(orders.status, "waiting"))).orderBy(desc(orders.id)).all(); },
    async updateOrderStatus(id: number, status: string, otpCode?: string) {
      const d: any = { status };
      if (otpCode) d.otpCode = otpCode;
      if (status === "completed") d.completedAt = new Date().toISOString();
      db.update(orders).set(d).where(eq(orders.id, id)).run();
    },
    async updateOrderSms(id: number, smsMessages: string, otpCode?: string) {
      const d: any = { smsMessages, status: "received" };
      if (otpCode) d.otpCode = otpCode;
      db.update(orders).set(d).where(eq(orders.id, id)).run();
    },
    async cancelOrder(id: number) { db.update(orders).set({ status: "cancelled", completedAt: new Date().toISOString() }).where(eq(orders.id, id)).run(); },
    async updateOrderProxnumId(id: number, proxnumId: string) { db.update(orders).set({ proxnumId }).where(eq(orders.id, id)).run(); },
    async updateOrderPhone(id: number, phoneNumber: string) { db.update(orders).set({ phoneNumber }).where(eq(orders.id, id)).run(); },
    async getAllOrders() { return db.select().from(orders).orderBy(desc(orders.id)).all(); },
    async createRental(data: InsertRental) { return db.insert(rentals).values(data).returning().get(); },
    async getRental(id: number) { return db.select().from(rentals).where(eq(rentals.id, id)).get(); },
    async getRentalByProxnumId(proxnumId: string) { return db.select().from(rentals).where(eq(rentals.proxnumId, proxnumId)).get(); },
    async getUserRentals(userId: number) { return db.select().from(rentals).where(eq(rentals.userId, userId)).orderBy(desc(rentals.id)).all(); },
    async getActiveRentals(userId: number) { return db.select().from(rentals).where(and(eq(rentals.userId, userId), eq(rentals.status, "active"))).orderBy(desc(rentals.id)).all(); },
    async updateRentalStatus(id: number, status: string) { db.update(rentals).set({ status }).where(eq(rentals.id, id)).run(); },
    async cancelRental(id: number) { db.update(rentals).set({ status: "cancelled", cancelledAt: new Date().toISOString() }).where(eq(rentals.id, id)).run(); },
    async getAllRentals() { return db.select().from(rentals).orderBy(desc(rentals.id)).all(); },
    async createRentalMessage(data: InsertRentalMessage) { return db.insert(rentalMessages).values(data).returning().get(); },
    async getRentalMessages(rentalId: number) { return db.select().from(rentalMessages).where(eq(rentalMessages.rentalId, rentalId)).orderBy(desc(rentalMessages.id)).all(); },
    async getSetting(key: string) { const r = db.select().from(settings).where(eq(settings.key, key)).get(); return r?.value ?? null; },
    async setSetting(key: string, value: string) {
      const ex = db.select().from(settings).where(eq(settings.key, key)).get();
      if (ex) db.update(settings).set({ value }).where(eq(settings.key, key)).run();
      else db.insert(settings).values({ key, value }).run();
    },
    async deleteSetting(key: string) { db.delete(settings).where(eq(settings.key, key)).run(); },
    async getAllSettings() { return db.select().from(settings).all().map(r => ({ key: r.key, value: r.value ?? "" })); },
    async createTransaction(data: InsertTransaction) { return db.insert(transactions).values(data).returning().get(); },
    async getUserTransactions(userId: number) { return db.select().from(transactions).where(eq(transactions.userId, userId)).orderBy(desc(transactions.id)).all(); },
    async createCryptoDeposit(data: InsertCryptoDeposit) { return db.insert(cryptoDeposits).values(data).returning().get(); },
    async getCryptoDeposit(id: number) { return db.select().from(cryptoDeposits).where(eq(cryptoDeposits.id, id)).get(); },
    async getUserCryptoDeposits(userId: number) { return db.select().from(cryptoDeposits).where(eq(cryptoDeposits.userId, userId)).orderBy(desc(cryptoDeposits.id)).all(); },
    async updateCryptoDeposit(id: number, data: Record<string, unknown>) { db.update(cryptoDeposits).set(data as any).where(eq(cryptoDeposits.id, id)).run(); },
    async getAllPendingCryptoDeposits() { return db.select().from(cryptoDeposits).where(or(eq(cryptoDeposits.status, "pending"), eq(cryptoDeposits.status, "confirming"))).orderBy(desc(cryptoDeposits.id)).all(); },
  };
}

// Shared storage – name starts with "mock" so Vitest hoists it with vi.mock()
const mockSharedStorage = buildInMemoryStorage();

// ---------------------------------------------------------------------------
// vi.mock – storage module → in-memory implementation
// ---------------------------------------------------------------------------
vi.mock("../storage", () => ({
  storage: mockSharedStorage,
  sqlite: mockSharedStorage._sqlite,
}));

// ---------------------------------------------------------------------------
// vi.mock – Proxnum API → no real HTTP calls
// ---------------------------------------------------------------------------
vi.mock("../proxnum", async (importOriginal) => {
  const original = await importOriginal<typeof import("../proxnum")>();
  return {
    ...original,
    proxnumApi: {
      getResellPrice: vi.fn(async () => ({ success: true, price: "1.00" })),
      buyVirtual: vi.fn(async () => ({
        success: true,
        activation: { activation_id: "pn-test-001", phone: "14155550100", amount_paid: 0.5 },
      })),
      getVirtualStatus: vi.fn(async () => ({ success: false })),
      cancelVirtual: vi.fn(async () => ({ success: true })),
      resendVirtual: vi.fn(async () => ({
        success: true,
        activation: { activation_id: "pn-test-002", phone: "14155550199" },
      })),
      buyRental: vi.fn(async () => ({ id: "rental-001", number: "12025550100" })),
      getRentalMessages: vi.fn(async () => ({ data: [] })),
      cancelRental: vi.fn(async () => ({ success: true })),
      getUserBalance: vi.fn(async () => ({ data: { balance: "100.00" } })),
      getRentalPrices: vi.fn(async () => ({ price: "2.00" })),
    },
    getCachedServices: vi.fn(async () => []),
    getCachedCountries: vi.fn(async () => [{ code: "187", name: "USA" }]),
    getCachedPrices: vi.fn(async () => ({})),
    getUSCountryCode: original.getUSCountryCode,
    findCountryCode: original.findCountryCode,
    friendlyError: original.friendlyError,
  };
});

// Bypass all rate limiters so test runs don't hit 429s
vi.mock("express-rate-limit", () => ({
  default: () => (_req: any, _res: any, next: any) => next(),
  rateLimit: () => (_req: any, _res: any, next: any) => next(),
}));

// ---------------------------------------------------------------------------
// Shared Express app (built once, re-used across describe blocks)
// registerRoutes() sets up its own session, passport, and strategies,
// so we only add the body parsers here.
// ---------------------------------------------------------------------------
let sharedApp: express.Express;

async function getApp(): Promise<express.Express> {
  if (sharedApp) return sharedApp;

  const app = express();
  const httpServer = createServer(app);
  app.use(express.json());
  app.use(express.urlencoded({ extended: false }));

  const { registerRoutes } = await import("../routes");
  await registerRoutes(httpServer, app);

  sharedApp = app;
  return app;
}

// Helper: register user + return session agent already logged in
async function createAuthAgent(
  username: string, email: string, password = "password123"
): Promise<ReturnType<typeof request.agent>> {
  const app = await getApp();
  const agent = request.agent(app);
  await agent.post("/api/auth/register").send({ username, email, password });
  return agent;
}

// ---------------------------------------------------------------------------
// Auth tests
// ---------------------------------------------------------------------------
describe("POST /api/auth/register", () => {
  let app: express.Express;
  beforeAll(async () => { app = await getApp(); });

  it("registers a new user successfully", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "newuser", email: "newuser@example.com", password: "password123" });
    expect(res.status).toBe(200);
    expect(res.body.username).toBe("newuser");
    expect(res.body.password).toBeUndefined();
    expect(res.body.apiKey).toBeUndefined();
  });

  it("rejects registration with missing fields", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "u", email: "a@b.com" }); // no password
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/required/i);
  });

  it("rejects password shorter than 8 characters", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "shortpw", email: "shortpw@example.com", password: "abc" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/8 characters/i);
  });

  it("rejects invalid email format", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "badmail", email: "notanemail", password: "password123" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/email/i);
  });

  it("rejects username shorter than 3 characters", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "ab", email: "ab@example.com", password: "password123" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/username/i);
  });

  it("rejects username with special characters", async () => {
    const res = await request(app).post("/api/auth/register")
      .send({ username: "bad name!", email: "badname@example.com", password: "password123" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/username/i);
  });

  it("rejects duplicate email", async () => {
    await request(app).post("/api/auth/register")
      .send({ username: "dup1a", email: "dup1@example.com", password: "password123" });
    const res = await request(app).post("/api/auth/register")
      .send({ username: "dup1b", email: "dup1@example.com", password: "password123" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/already registered/i);
  });

  it("rejects duplicate username", async () => {
    await request(app).post("/api/auth/register")
      .send({ username: "sameuser1", email: "sameuser1a@example.com", password: "password123" });
    const res = await request(app).post("/api/auth/register")
      .send({ username: "sameuser1", email: "sameuser1b@example.com", password: "password123" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/username already taken/i);
  });
});

describe("POST /api/auth/login and GET /api/auth/me", () => {
  let app: express.Express;
  beforeAll(async () => { app = await getApp(); });

  it("logs in with correct credentials", async () => {
    const agent = await createAuthAgent("loginuser", "loginuser@example.com");
    const res = await agent.get("/api/auth/me");
    expect(res.status).toBe(200);
    expect(res.body.email).toBe("loginuser@example.com");
  });

  it("rejects login with wrong password", async () => {
    await request(app).post("/api/auth/register")
      .send({ username: "wrongpw", email: "wrongpw@example.com", password: "password123" });
    const res = await request(app).post("/api/auth/login")
      .send({ email: "wrongpw@example.com", password: "wrong" });
    expect(res.status).toBe(401);
  });

  it("rejects login with non-existent email", async () => {
    const res = await request(app).post("/api/auth/login")
      .send({ email: "nobody@example.com", password: "password123" });
    expect(res.status).toBe(401);
  });

  it("GET /api/auth/me returns 401 when not authenticated", async () => {
    const res = await request(app).get("/api/auth/me");
    expect(res.status).toBe(401);
  });

  it("POST /api/auth/logout ends the session", async () => {
    const agent = await createAuthAgent("logoutuser", "logoutuser@example.com");
    await agent.post("/api/auth/logout");
    const res = await agent.get("/api/auth/me");
    expect(res.status).toBe(401);
  });
});

// ---------------------------------------------------------------------------
// Services
// ---------------------------------------------------------------------------
describe("GET /api/services", () => {
  let app: express.Express;
  beforeAll(async () => {
    app = await getApp();
    await mockSharedStorage.upsertServices([
      { name: "WhatsApp", slug: "whatsapp", price: "1.50", isActive: 1, icon: null, category: "Messaging" },
    ]);
  });

  it("returns a list of active services without authentication", async () => {
    const res = await request(app).get("/api/services");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Orders
// ---------------------------------------------------------------------------
describe("Orders API", () => {
  let app: express.Express;
  let agent: ReturnType<typeof request.agent>;
  let serviceId: number;

  beforeAll(async () => {
    app = await getApp();
    await mockSharedStorage.upsertServices([
      { name: "Telegram", slug: "telegram", price: "1.00", isActive: 1, icon: null, category: "Messaging" },
    ]);
    serviceId = (await mockSharedStorage.getServiceBySlug("telegram"))!.id;

    agent = await createAuthAgent("orderuser", "orderuser@example.com");
    const user = await mockSharedStorage.getUserByEmail("orderuser@example.com");
    await mockSharedStorage.updateUserBalance(user!.id, "50.00");
  });

  it("returns 401 for unauthenticated requests", async () => {
    expect((await request(app).get("/api/orders")).status).toBe(401);
  });

  it("creates an order successfully", async () => {
    const res = await agent.post("/api/orders").send({ serviceId });
    expect(res.status).toBe(200);
    expect(res.body.phoneNumber).toMatch(/^\+/);
    expect(res.body.status).toBe("pending");
  });

  it("returns 400 when serviceId and serviceName are both missing", async () => {
    const res = await agent.post("/api/orders").send({});
    expect(res.status).toBe(400);
  });

  it("returns 404 when service does not exist", async () => {
    const res = await agent.post("/api/orders").send({ serviceId: 99999 });
    expect(res.status).toBe(404);
  });

  it("GET /api/orders returns user orders list", async () => {
    const res = await agent.get("/api/orders");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
    expect(res.body.length).toBeGreaterThan(0);
  });

  it("GET /api/orders/active returns active orders", async () => {
    const res = await agent.get("/api/orders/active");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });

  it("GET /api/orders/:id returns a specific order", async () => {
    const ordersRes = await agent.get("/api/orders");
    const orderId = ordersRes.body[0].id;
    const res = await agent.get(`/api/orders/${orderId}`);
    expect(res.status).toBe(200);
    expect(res.body.id).toBe(orderId);
  });

  it("GET /api/orders/:id returns 403 when accessing another user's order", async () => {
    const ordersRes = await agent.get("/api/orders");
    const orderId = ordersRes.body[0].id;
    const agent2 = await createAuthAgent("hacker1", "hacker1@example.com");
    const res = await agent2.get(`/api/orders/${orderId}`);
    expect(res.status).toBe(403);
  });

  it("cancels an order and refunds the balance", async () => {
    const createRes = await agent.post("/api/orders").send({ serviceId });
    expect(createRes.status).toBe(200);
    const orderId = createRes.body.id;
    const balBefore = parseFloat((await agent.get("/api/balance")).body.balance);
    const cancelRes = await agent.post(`/api/orders/${orderId}/cancel`);
    expect(cancelRes.status).toBe(200);
    expect(cancelRes.body.message).toMatch(/cancelled/i);
    const balAfter = parseFloat((await agent.get("/api/balance")).body.balance);
    expect(balAfter).toBeGreaterThan(balBefore);
  });
});

// ---------------------------------------------------------------------------
// Balance
// ---------------------------------------------------------------------------
describe("GET /api/balance", () => {
  let app: express.Express;
  let agent: ReturnType<typeof request.agent>;
  beforeAll(async () => {
    app = await getApp();
    agent = await createAuthAgent("baluser", "baluser@example.com");
  });

  it("returns balance for authenticated user", async () => {
    const res = await agent.get("/api/balance");
    expect(res.status).toBe(200);
    expect(res.body).toHaveProperty("balance");
  });

  it("returns 401 for unauthenticated requests", async () => {
    expect((await request(app).get("/api/balance")).status).toBe(401);
  });
});

// ---------------------------------------------------------------------------
// Transactions
// ---------------------------------------------------------------------------
describe("GET /api/transactions", () => {
  let app: express.Express;
  let agent: ReturnType<typeof request.agent>;
  beforeAll(async () => {
    app = await getApp();
    agent = await createAuthAgent("txroute", "txroute@example.com");
  });

  it("returns an array for authenticated user", async () => {
    const res = await agent.get("/api/transactions");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });

  it("returns 401 for unauthenticated requests", async () => {
    expect((await request(app).get("/api/transactions")).status).toBe(401);
  });
});

// ---------------------------------------------------------------------------
// Crypto deposits
// ---------------------------------------------------------------------------
describe("Crypto deposits API", () => {
  let app: express.Express;
  let agent: ReturnType<typeof request.agent>;
  beforeAll(async () => {
    app = await getApp();
    await mockSharedStorage.setSetting("crypto_wallet_usdt_trc20", "TXXX_wallet_address");
    agent = await createAuthAgent("cryptouser2", "cryptouser2@example.com");
  });

  it("GET /api/crypto/currencies returns configured currencies", async () => {
    const res = await agent.get("/api/crypto/currencies");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
    expect(res.body.find((c: any) => c.id === "USDT_TRC20")).toBeDefined();
  });

  it("returns 401 for unauthenticated requests", async () => {
    expect((await request(app).get("/api/crypto/currencies")).status).toBe(401);
  });

  it("creates a crypto deposit", async () => {
    const res = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "USDT_TRC20", amount: "10.00" });
    expect(res.status).toBe(200);
    expect(res.body.currency).toBe("USDT_TRC20");
    expect(res.body.status).toBe("pending");
  });

  it("rejects deposit with missing currency", async () => {
    expect((await agent.post("/api/crypto/create-deposit").send({ amount: "10.00" })).status).toBe(400);
  });

  it("rejects deposit below minimum amount", async () => {
    const res = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "USDT_TRC20", amount: "0.50" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/minimum/i);
  });

  it("rejects deposit for unconfigured currency", async () => {
    const res = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "BTC", amount: "10.00" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/not configured/i);
  });

  it("GET /api/crypto/deposits lists user deposits", async () => {
    const res = await agent.get("/api/crypto/deposits");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });

  it("submit-hash accepts a valid transaction hash", async () => {
    const cr = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "USDT_TRC20", amount: "5.00" });
    const res = await agent.post(`/api/crypto/${cr.body.id}/submit-hash`)
      .send({ txHash: "0xabc123def456abc123def456abc123def456" });
    expect(res.status).toBe(200);
    expect(res.body.message).toMatch(/submitted/i);
  });

  it("submit-hash rejects missing hash", async () => {
    const cr = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "USDT_TRC20", amount: "5.00" });
    expect((await agent.post(`/api/crypto/${cr.body.id}/submit-hash`).send({})).status).toBe(400);
  });

  it("simulate-confirm credits user balance", async () => {
    const cr = await agent.post("/api/crypto/create-deposit")
      .send({ currency: "USDT_TRC20", amount: "20.00" });
    const balBefore = parseFloat((await agent.get("/api/balance")).body.balance);
    const confirmRes = await agent.post(`/api/crypto/${cr.body.id}/simulate-confirm`);
    expect(confirmRes.status).toBe(200);
    const balAfter = parseFloat((await agent.get("/api/balance")).body.balance);
    expect(balAfter).toBeGreaterThan(balBefore);
  });
});

// ---------------------------------------------------------------------------
// Admin routes
// ---------------------------------------------------------------------------
describe("Admin API", () => {
  let app: express.Express;
  let adminAgent: ReturnType<typeof request.agent>;
  let userAgent: ReturnType<typeof request.agent>;
  let regularUserId: number;

  beforeAll(async () => {
    app = await getApp();

    // Create admin user
    const adminPw = await bcrypt.hash("adminpass123", 4);
    const adminUser = await mockSharedStorage.createUser({
      username: "admintest", email: "admintest@example.com", password: adminPw,
    });
    mockSharedStorage._db.update(users).set({ role: "admin" }).where(eq(users.id, adminUser.id)).run();

    adminAgent = request.agent(app);
    await adminAgent.post("/api/auth/login").send({ email: "admintest@example.com", password: "adminpass123" });

    userAgent = await createAuthAgent("regularuser1", "regularuser1@example.com");
    regularUserId = (await mockSharedStorage.getUserByEmail("regularuser1@example.com"))!.id;
  });

  it("GET /api/admin/users returns 403 for regular users", async () => {
    expect((await userAgent.get("/api/admin/users")).status).toBe(403);
  });

  it("GET /api/admin/users returns all users for admin", async () => {
    const res = await adminAgent.get("/api/admin/users");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
    expect(res.body.every((u: any) => u.password === undefined)).toBe(true);
  });

  it("GET /api/admin/stats returns statistics", async () => {
    const res = await adminAgent.get("/api/admin/stats");
    expect(res.status).toBe(200);
    expect(res.body).toHaveProperty("totalUsers");
    expect(res.body).toHaveProperty("revenue");
  });

  it("POST /api/admin/users/:id/add-balance adds balance", async () => {
    const balBefore = parseFloat((await mockSharedStorage.getUser(regularUserId))!.balance);
    const res = await adminAgent.post(`/api/admin/users/${regularUserId}/add-balance`)
      .send({ amount: "25.00", description: "Test top-up" });
    expect(res.status).toBe(200);
    expect(res.body.message).toMatch(/balance updated/i);
    const balAfter = parseFloat((await mockSharedStorage.getUser(regularUserId))!.balance);
    expect(balAfter).toBeGreaterThan(balBefore);
  });

  it("POST /api/admin/users/:id/add-balance rejects invalid amount", async () => {
    expect((await adminAgent.post(`/api/admin/users/${regularUserId}/add-balance`).send({ amount: "-5" })).status).toBe(400);
  });

  it("GET /api/admin/settings returns settings", async () => {
    const res = await adminAgent.get("/api/admin/settings");
    expect(res.status).toBe(200);
    expect(res.body).toHaveProperty("price_multiplier");
    expect(res.body).toHaveProperty("default_country");
  });

  it("PUT /api/admin/settings updates price_multiplier", async () => {
    const res = await adminAgent.put("/api/admin/settings").send({ price_multiplier: "2.0" });
    expect(res.status).toBe(200);
    expect(await mockSharedStorage.getSetting("price_multiplier")).toBe("2.0");
  });

  it("GET /api/admin/transactions returns recent transactions", async () => {
    const res = await adminAgent.get("/api/admin/transactions");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });

  it("GET /api/admin/crypto/pending returns pending deposits", async () => {
    const res = await adminAgent.get("/api/admin/crypto/pending");
    expect(res.status).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Profile routes
// ---------------------------------------------------------------------------
describe("Profile API", () => {
  let app: express.Express;
  let agent: ReturnType<typeof request.agent>;
  beforeAll(async () => {
    app = await getApp();
    agent = await createAuthAgent("profileuser", "profileuser@example.com");
  });

  it("generates a new API key", async () => {
    const res = await agent.post("/api/profile/generate-api-key");
    expect(res.status).toBe(200);
    expect(typeof res.body.apiKey).toBe("string");
    expect(res.body.apiKey.length).toBeGreaterThan(30);
  });

  it("changes password successfully with correct current password", async () => {
    const res = await agent.post("/api/profile/change-password")
      .send({ currentPassword: "password123", newPassword: "newpassword456" });
    expect(res.status).toBe(200);
    expect(res.body.message).toMatch(/password updated/i);
  });

  it("rejects change-password with wrong current password", async () => {
    const res = await agent.post("/api/profile/change-password")
      .send({ currentPassword: "wrongpassword", newPassword: "newpassword456" });
    expect(res.status).toBe(400);
    expect(res.body.message).toMatch(/incorrect/i);
  });
});

// ---------------------------------------------------------------------------
// API v1 – API-key-authenticated endpoints
// ---------------------------------------------------------------------------
describe("API v1 – key-authenticated endpoints", () => {
  let app: express.Express;
  let apiKey: string;
  let serviceId: number;

  beforeAll(async () => {
    app = await getApp();
    await mockSharedStorage.upsertServices([
      { name: "Discord", slug: "discord", price: "1.00", isActive: 1, icon: null, category: "Messaging" },
    ]);
    serviceId = (await mockSharedStorage.getServiceBySlug("discord"))!.id;

    const pw = await bcrypt.hash("password123", 4);
    const user = await mockSharedStorage.createUser({ username: "apiv1user", email: "apiv1@example.com", password: pw });
    await mockSharedStorage.updateUserBalance(user.id, "100.00");
    apiKey = user.apiKey!;
  });

  it("GET /api/v1/services returns services without auth", async () => {
    const res = await request(app).get("/api/v1/services");
    expect(res.status).toBe(200);
    expect(res.body.services).toBeDefined();
  });

  it("GET /api/v1/balance returns 401 without API key", async () => {
    expect((await request(app).get("/api/v1/balance")).status).toBe(401);
  });

  it("GET /api/v1/balance returns balance with valid API key", async () => {
    const res = await request(app).get("/api/v1/balance").set("x-api-key", apiKey);
    expect(res.status).toBe(200);
    expect(res.body).toHaveProperty("balance");
  });

  it("POST /api/v1/order creates an order", async () => {
    const res = await request(app).post("/api/v1/order")
      .set("x-api-key", apiKey)
      .send({ service: "discord", country: "us" });
    expect(res.status).toBe(200);
    expect(res.body.orderId).toBeDefined();
    expect(res.body.phoneNumber).toMatch(/^\+/);
  });

  it("POST /api/v1/order returns 400 when service is missing", async () => {
    const res = await request(app).post("/api/v1/order")
      .set("x-api-key", apiKey).send({ country: "us" });
    expect(res.status).toBe(400);
  });

  it("GET /api/v1/order/:id returns order status", async () => {
    const cr = await request(app).post("/api/v1/order")
      .set("x-api-key", apiKey).send({ service: "discord" });
    const res = await request(app).get(`/api/v1/order/${cr.body.orderId}`)
      .set("x-api-key", apiKey);
    expect(res.status).toBe(200);
    expect(res.body.orderId).toBe(cr.body.orderId);
  });

  it("POST /api/v1/order/:id/cancel cancels an order", async () => {
    const cr = await request(app).post("/api/v1/order")
      .set("x-api-key", apiKey).send({ service: "discord" });
    const res = await request(app).post(`/api/v1/order/${cr.body.orderId}/cancel`)
      .set("x-api-key", apiKey);
    expect(res.status).toBe(200);
    expect(res.body.message).toMatch(/cancelled/i);
  });
});
