/**
 * Integration tests for storage operations.
 * Each describe block uses a fresh in-memory SQLite database to stay isolated.
 */
import { describe, it, expect, beforeEach } from "vitest";
import Database from "better-sqlite3";
import { drizzle } from "drizzle-orm/better-sqlite3";
import { eq, and, or, desc } from "drizzle-orm";
import bcrypt from "bcryptjs";
import crypto from "crypto";

import {
  users, services, orders, rentals, rentalMessages,
  settings, transactions, cryptoDeposits,
} from "../../shared/schema";
import type {
  InsertService, InsertOrder, InsertRental,
  InsertRentalMessage, InsertTransaction, InsertCryptoDeposit,
} from "../../shared/schema";

// ---------------------------------------------------------------------------
// In-memory database factory
// ---------------------------------------------------------------------------
function buildDb() {
  const sqlite = new Database(":memory:");
  sqlite.pragma("journal_mode = WAL");
  sqlite.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL UNIQUE,
      email TEXT NOT NULL UNIQUE,
      password TEXT NOT NULL,
      balance TEXT NOT NULL DEFAULT '0.00',
      api_key TEXT UNIQUE,
      role TEXT NOT NULL DEFAULT 'user'
    );
    CREATE TABLE IF NOT EXISTS services (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      slug TEXT NOT NULL UNIQUE,
      price TEXT NOT NULL,
      icon TEXT,
      category TEXT,
      is_active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      service_id INTEGER NOT NULL,
      service_name TEXT NOT NULL DEFAULT '',
      phone_number TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'pending',
      otp_code TEXT,
      sms_messages TEXT,
      price TEXT NOT NULL,
      country TEXT NOT NULL DEFAULT 'us',
      proxnum_id TEXT,
      created_at TEXT NOT NULL,
      expires_at TEXT NOT NULL,
      completed_at TEXT
    );
    CREATE TABLE IF NOT EXISTS rentals (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      service_id INTEGER NOT NULL,
      service_name TEXT NOT NULL DEFAULT '',
      phone_number TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'active',
      price TEXT NOT NULL,
      country TEXT NOT NULL DEFAULT 'us',
      days INTEGER NOT NULL DEFAULT 7,
      proxnum_id TEXT,
      created_at TEXT NOT NULL,
      expires_at TEXT NOT NULL,
      cancelled_at TEXT
    );
    CREATE TABLE IF NOT EXISTS rental_messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      rental_id INTEGER NOT NULL,
      sender TEXT,
      message TEXT NOT NULL,
      received_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS settings (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      key TEXT NOT NULL UNIQUE,
      value TEXT
    );
    CREATE TABLE IF NOT EXISTS transactions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      type TEXT NOT NULL,
      amount TEXT NOT NULL,
      description TEXT,
      order_id INTEGER,
      stripe_session_id TEXT,
      created_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS crypto_deposits (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      currency TEXT NOT NULL,
      amount TEXT NOT NULL,
      crypto_amount TEXT,
      wallet_address TEXT NOT NULL,
      tx_hash TEXT,
      status TEXT NOT NULL DEFAULT 'pending',
      created_at TEXT NOT NULL,
      expires_at TEXT NOT NULL,
      completed_at TEXT
    );
  `);
  return drizzle(sqlite);
}

// ---------------------------------------------------------------------------
// Thin storage helper that mirrors DatabaseStorage but uses the injected db
// ---------------------------------------------------------------------------
function makeStore(db: ReturnType<typeof buildDb>) {
  return {
    // --- users ---
    async createUser(data: { username: string; email: string; password: string }) {
      const apiKey = crypto.randomBytes(32).toString("hex");
      return db.insert(users).values({ ...data, apiKey }).returning().get();
    },
    async getUser(id: number) {
      return db.select().from(users).where(eq(users.id, id)).get();
    },
    async getUserByEmail(email: string) {
      return db.select().from(users).where(eq(users.email, email)).get();
    },
    async getUserByUsername(username: string) {
      return db.select().from(users).where(eq(users.username, username)).get();
    },
    async getUserByApiKey(apiKey: string) {
      return db.select().from(users).where(eq(users.apiKey, apiKey)).get();
    },
    async updateUserBalance(userId: number, balance: string) {
      db.update(users).set({ balance }).where(eq(users.id, userId)).run();
    },
    async updateUserPassword(userId: number, password: string) {
      db.update(users).set({ password }).where(eq(users.id, userId)).run();
    },
    async generateApiKey(userId: number) {
      const apiKey = crypto.randomBytes(32).toString("hex");
      db.update(users).set({ apiKey }).where(eq(users.id, userId)).run();
      return apiKey;
    },
    async getAllUsers() {
      return db.select().from(users).all();
    },

    // --- services ---
    async upsertServices(serviceList: InsertService[]) {
      for (const svc of serviceList) {
        const existing = db.select().from(services).where(eq(services.slug, svc.slug)).get();
        if (existing) {
          db.update(services)
            .set({ price: svc.price, isActive: svc.isActive, category: svc.category ?? null })
            .where(eq(services.slug, svc.slug))
            .run();
        } else {
          db.insert(services).values(svc).run();
        }
      }
    },
    async getAllServices() {
      return db.select().from(services).where(eq(services.isActive, 1)).all();
    },
    async getService(id: number) {
      return db.select().from(services).where(eq(services.id, id)).get();
    },
    async getServiceBySlug(slug: string) {
      return db.select().from(services).where(eq(services.slug, slug)).get();
    },
    async getServiceByName(name: string) {
      return db.select().from(services).where(eq(services.name, name)).get();
    },
    async updateService(id: number, data: Partial<InsertService>) {
      db.update(services).set(data).where(eq(services.id, id)).run();
    },

    // --- orders ---
    async createOrder(data: InsertOrder) {
      return db.insert(orders).values(data).returning().get();
    },
    async getOrder(id: number) {
      return db.select().from(orders).where(eq(orders.id, id)).get();
    },
    async getOrderByProxnumId(proxnumId: string) {
      return db.select().from(orders).where(eq(orders.proxnumId, proxnumId)).get();
    },
    async getUserOrders(userId: number) {
      return db.select().from(orders).where(eq(orders.userId, userId)).orderBy(desc(orders.id)).all();
    },
    async getActiveOrders(userId: number) {
      return db.select().from(orders)
        .where(and(
          eq(orders.userId, userId),
          or(eq(orders.status, "pending"), eq(orders.status, "waiting"), eq(orders.status, "received")),
        ))
        .orderBy(desc(orders.id)).all();
    },
    async getPendingOrders() {
      return db.select().from(orders)
        .where(or(eq(orders.status, "pending"), eq(orders.status, "waiting")))
        .orderBy(desc(orders.id)).all();
    },
    async updateOrderStatus(id: number, status: string, otpCode?: string) {
      const updateData: any = { status };
      if (otpCode) updateData.otpCode = otpCode;
      if (status === "completed") updateData.completedAt = new Date().toISOString();
      db.update(orders).set(updateData).where(eq(orders.id, id)).run();
    },
    async updateOrderSms(id: number, smsMessages: string, otpCode?: string) {
      const updateData: any = { smsMessages, status: "received" };
      if (otpCode) updateData.otpCode = otpCode;
      db.update(orders).set(updateData).where(eq(orders.id, id)).run();
    },
    async cancelOrder(id: number) {
      db.update(orders)
        .set({ status: "cancelled", completedAt: new Date().toISOString() })
        .where(eq(orders.id, id)).run();
    },
    async updateOrderProxnumId(id: number, proxnumId: string) {
      db.update(orders).set({ proxnumId }).where(eq(orders.id, id)).run();
    },
    async updateOrderPhone(id: number, phoneNumber: string) {
      db.update(orders).set({ phoneNumber }).where(eq(orders.id, id)).run();
    },
    async getAllOrders() {
      return db.select().from(orders).orderBy(desc(orders.id)).all();
    },

    // --- rentals ---
    async createRental(data: InsertRental) {
      return db.insert(rentals).values(data).returning().get();
    },
    async getRental(id: number) {
      return db.select().from(rentals).where(eq(rentals.id, id)).get();
    },
    async getRentalByProxnumId(proxnumId: string) {
      return db.select().from(rentals).where(eq(rentals.proxnumId, proxnumId)).get();
    },
    async getUserRentals(userId: number) {
      return db.select().from(rentals).where(eq(rentals.userId, userId)).orderBy(desc(rentals.id)).all();
    },
    async getActiveRentals(userId: number) {
      return db.select().from(rentals)
        .where(and(eq(rentals.userId, userId), eq(rentals.status, "active")))
        .orderBy(desc(rentals.id)).all();
    },
    async updateRentalStatus(id: number, status: string) {
      db.update(rentals).set({ status }).where(eq(rentals.id, id)).run();
    },
    async cancelRental(id: number) {
      db.update(rentals)
        .set({ status: "cancelled", cancelledAt: new Date().toISOString() })
        .where(eq(rentals.id, id)).run();
    },
    async getAllRentals() {
      return db.select().from(rentals).orderBy(desc(rentals.id)).all();
    },

    // --- rental messages ---
    async createRentalMessage(data: InsertRentalMessage) {
      return db.insert(rentalMessages).values(data).returning().get();
    },
    async getRentalMessages(rentalId: number) {
      return db.select().from(rentalMessages)
        .where(eq(rentalMessages.rentalId, rentalId))
        .orderBy(desc(rentalMessages.id)).all();
    },

    // --- settings ---
    async getSetting(key: string) {
      const row = db.select().from(settings).where(eq(settings.key, key)).get();
      return row?.value ?? null;
    },
    async setSetting(key: string, value: string) {
      const existing = db.select().from(settings).where(eq(settings.key, key)).get();
      if (existing) {
        db.update(settings).set({ value }).where(eq(settings.key, key)).run();
      } else {
        db.insert(settings).values({ key, value }).run();
      }
    },
    async deleteSetting(key: string) {
      db.delete(settings).where(eq(settings.key, key)).run();
    },
    async getAllSettings() {
      const rows = db.select().from(settings).all();
      return rows.map(r => ({ key: r.key, value: r.value ?? "" }));
    },

    // --- transactions ---
    async createTransaction(data: InsertTransaction) {
      return db.insert(transactions).values(data).returning().get();
    },
    async getUserTransactions(userId: number) {
      return db.select().from(transactions)
        .where(eq(transactions.userId, userId))
        .orderBy(desc(transactions.id)).all();
    },

    // --- crypto deposits ---
    async createCryptoDeposit(data: InsertCryptoDeposit) {
      return db.insert(cryptoDeposits).values(data).returning().get();
    },
    async getCryptoDeposit(id: number) {
      return db.select().from(cryptoDeposits).where(eq(cryptoDeposits.id, id)).get();
    },
    async getUserCryptoDeposits(userId: number) {
      return db.select().from(cryptoDeposits)
        .where(eq(cryptoDeposits.userId, userId))
        .orderBy(desc(cryptoDeposits.id)).all();
    },
    async updateCryptoDeposit(id: number, data: Record<string, unknown>) {
      db.update(cryptoDeposits).set(data as any).where(eq(cryptoDeposits.id, id)).run();
    },
    async getAllPendingCryptoDeposits() {
      return db.select().from(cryptoDeposits)
        .where(or(eq(cryptoDeposits.status, "pending"), eq(cryptoDeposits.status, "confirming")))
        .orderBy(desc(cryptoDeposits.id)).all();
    },
  };
}

type Store = ReturnType<typeof makeStore>;

// ---------------------------------------------------------------------------
// User tests
// ---------------------------------------------------------------------------
describe("Storage – users", () => {
  let store: Store;

  beforeEach(() => {
    store = makeStore(buildDb());
  });

  it("creates a user and retrieves it by id", async () => {
    const pw = await bcrypt.hash("password123", 4);
    const user = await store.createUser({ username: "alice", email: "alice@example.com", password: pw });
    expect(user.id).toBeGreaterThan(0);
    expect(user.username).toBe("alice");
    expect(user.role).toBe("user");

    const fetched = await store.getUser(user.id);
    expect(fetched?.email).toBe("alice@example.com");
  });

  it("returns undefined for non-existent user id", async () => {
    expect(await store.getUser(9999)).toBeUndefined();
  });

  it("retrieves user by email", async () => {
    const pw = await bcrypt.hash("pw", 4);
    await store.createUser({ username: "bob", email: "bob@example.com", password: pw });
    const user = await store.getUserByEmail("bob@example.com");
    expect(user?.username).toBe("bob");
  });

  it("returns undefined for unknown email", async () => {
    expect(await store.getUserByEmail("nobody@example.com")).toBeUndefined();
  });

  it("retrieves user by username", async () => {
    const pw = await bcrypt.hash("pw", 4);
    await store.createUser({ username: "carol", email: "carol@example.com", password: pw });
    const user = await store.getUserByUsername("carol");
    expect(user?.email).toBe("carol@example.com");
  });

  it("returns undefined for unknown username", async () => {
    expect(await store.getUserByUsername("nobody")).toBeUndefined();
  });

  it("retrieves user by API key", async () => {
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "dave", email: "dave@example.com", password: pw });
    const fetched = await store.getUserByApiKey(user.apiKey!);
    expect(fetched?.id).toBe(user.id);
  });

  it("updates user balance", async () => {
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "eve", email: "eve@example.com", password: pw });
    await store.updateUserBalance(user.id, "50.00");
    const updated = await store.getUser(user.id);
    expect(updated?.balance).toBe("50.00");
  });

  it("updates user password", async () => {
    const pw = await bcrypt.hash("oldpw", 4);
    const user = await store.createUser({ username: "frank", email: "frank@example.com", password: pw });
    const newHashed = await bcrypt.hash("newpw", 4);
    await store.updateUserPassword(user.id, newHashed);
    const updated = await store.getUser(user.id);
    expect(await bcrypt.compare("newpw", updated!.password)).toBe(true);
  });

  it("generates a new API key that differs from the old one", async () => {
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "grace", email: "grace@example.com", password: pw });
    const oldKey = user.apiKey;
    const newKey = await store.generateApiKey(user.id);
    expect(newKey).not.toBe(oldKey);
    expect(newKey.length).toBeGreaterThan(30);
    const fetched = await store.getUserByApiKey(newKey);
    expect(fetched?.id).toBe(user.id);
  });

  it("getAllUsers returns all created users", async () => {
    const pw = await bcrypt.hash("pw", 4);
    await store.createUser({ username: "u1", email: "u1@example.com", password: pw });
    await store.createUser({ username: "u2", email: "u2@example.com", password: pw });
    const all = await store.getAllUsers();
    expect(all.length).toBe(2);
  });
});

// ---------------------------------------------------------------------------
// Service tests
// ---------------------------------------------------------------------------
describe("Storage – services", () => {
  let store: Store;

  beforeEach(() => {
    store = makeStore(buildDb());
  });

  async function insertService(overrides: Partial<InsertService> = {}) {
    const svc: InsertService = {
      name: "WhatsApp",
      slug: "whatsapp",
      price: "1.50",
      icon: null,
      category: "Messaging",
      isActive: 1,
      ...overrides,
    };
    await store.upsertServices([svc]);
    return store.getServiceBySlug(svc.slug);
  }

  it("upserts a new service", async () => {
    const svc = await insertService();
    expect(svc?.name).toBe("WhatsApp");
    expect(svc?.price).toBe("1.50");
  });

  it("upsert updates price for existing service", async () => {
    await insertService();
    await store.upsertServices([{ name: "WhatsApp", slug: "whatsapp", price: "2.00", isActive: 1 }]);
    const updated = await store.getServiceBySlug("whatsapp");
    expect(updated?.price).toBe("2.00");
  });

  it("getService returns service by id", async () => {
    const svc = await insertService();
    const fetched = await store.getService(svc!.id);
    expect(fetched?.slug).toBe("whatsapp");
  });

  it("getService returns undefined for unknown id", async () => {
    expect(await store.getService(9999)).toBeUndefined();
  });

  it("getServiceByName returns service by name", async () => {
    await insertService();
    const svc = await store.getServiceByName("WhatsApp");
    expect(svc?.slug).toBe("whatsapp");
  });

  it("getAllServices only returns active services", async () => {
    await insertService({ slug: "active-svc", name: "ActiveSvc", isActive: 1 });
    await insertService({ slug: "inactive-svc", name: "InactiveSvc", isActive: 0 });
    const active = await store.getAllServices();
    expect(active.some(s => s.slug === "active-svc")).toBe(true);
    expect(active.some(s => s.slug === "inactive-svc")).toBe(false);
  });

  it("updateService updates fields", async () => {
    const svc = await insertService();
    await store.updateService(svc!.id, { price: "3.00", isActive: 0 });
    const updated = await store.getService(svc!.id);
    expect(updated?.price).toBe("3.00");
    expect(updated?.isActive).toBe(0);
  });
});

// ---------------------------------------------------------------------------
// Order tests
// ---------------------------------------------------------------------------
describe("Storage – orders", () => {
  let store: Store;
  let userId: number;
  let serviceId: number;

  beforeEach(async () => {
    store = makeStore(buildDb());
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "testuser", email: "test@example.com", password: pw });
    userId = user.id;
    await store.upsertServices([{ name: "Telegram", slug: "telegram", price: "1.00", isActive: 1 }]);
    const svc = await store.getServiceBySlug("telegram");
    serviceId = svc!.id;
  });

  function buildOrderData(overrides: Partial<InsertOrder> = {}): InsertOrder {
    const now = new Date().toISOString();
    return {
      userId,
      serviceId,
      serviceName: "Telegram",
      phoneNumber: "+14155550100",
      status: "pending",
      otpCode: null,
      smsMessages: null,
      price: "1.00",
      country: "us",
      proxnumId: "pn123",
      createdAt: now,
      expiresAt: new Date(Date.now() + 20 * 60 * 1000).toISOString(),
      completedAt: null,
      ...overrides,
    };
  }

  it("creates and retrieves an order", async () => {
    const order = await store.createOrder(buildOrderData());
    expect(order.id).toBeGreaterThan(0);
    expect(order.status).toBe("pending");
    const fetched = await store.getOrder(order.id);
    expect(fetched?.phoneNumber).toBe("+14155550100");
  });

  it("returns undefined for unknown order id", async () => {
    expect(await store.getOrder(9999)).toBeUndefined();
  });

  it("retrieves order by proxnum id", async () => {
    const order = await store.createOrder(buildOrderData({ proxnumId: "unique-pn-id" }));
    const fetched = await store.getOrderByProxnumId("unique-pn-id");
    expect(fetched?.id).toBe(order.id);
  });

  it("getUserOrders returns orders in descending id order", async () => {
    await store.createOrder(buildOrderData());
    await store.createOrder(buildOrderData());
    const userOrders = await store.getUserOrders(userId);
    expect(userOrders[0].id).toBeGreaterThan(userOrders[1].id);
  });

  it("getActiveOrders includes pending and waiting statuses", async () => {
    await store.createOrder(buildOrderData({ status: "pending" }));
    await store.createOrder(buildOrderData({ status: "waiting" }));
    await store.createOrder(buildOrderData({ status: "cancelled" }));
    const active = await store.getActiveOrders(userId);
    expect(active.length).toBe(2);
  });

  it("getActiveOrders includes received status", async () => {
    await store.createOrder(buildOrderData({ status: "received" }));
    const active = await store.getActiveOrders(userId);
    expect(active.length).toBe(1);
  });

  it("getPendingOrders returns pending and waiting", async () => {
    await store.createOrder(buildOrderData({ status: "pending" }));
    await store.createOrder(buildOrderData({ status: "waiting" }));
    await store.createOrder(buildOrderData({ status: "completed" }));
    const pending = await store.getPendingOrders();
    expect(pending.length).toBe(2);
  });

  it("updateOrderStatus sets status and otpCode", async () => {
    const order = await store.createOrder(buildOrderData());
    await store.updateOrderStatus(order.id, "received", "123456");
    const updated = await store.getOrder(order.id);
    expect(updated?.status).toBe("received");
    expect(updated?.otpCode).toBe("123456");
  });

  it("updateOrderStatus sets completedAt when status is 'completed'", async () => {
    const order = await store.createOrder(buildOrderData());
    await store.updateOrderStatus(order.id, "completed");
    const updated = await store.getOrder(order.id);
    expect(updated?.completedAt).toBeTruthy();
  });

  it("updateOrderSms sets smsMessages and status to received", async () => {
    const order = await store.createOrder(buildOrderData());
    await store.updateOrderSms(order.id, JSON.stringify([{ text: "Your code is 999999" }]), "999999");
    const updated = await store.getOrder(order.id);
    expect(updated?.status).toBe("received");
    expect(updated?.otpCode).toBe("999999");
    expect(updated?.smsMessages).toContain("999999");
  });

  it("cancelOrder sets status to cancelled and completedAt", async () => {
    const order = await store.createOrder(buildOrderData());
    await store.cancelOrder(order.id);
    const updated = await store.getOrder(order.id);
    expect(updated?.status).toBe("cancelled");
    expect(updated?.completedAt).toBeTruthy();
  });

  it("updateOrderProxnumId updates the proxnum id", async () => {
    const order = await store.createOrder(buildOrderData({ proxnumId: "old-id" }));
    await store.updateOrderProxnumId(order.id, "new-id");
    const updated = await store.getOrder(order.id);
    expect(updated?.proxnumId).toBe("new-id");
  });

  it("updateOrderPhone updates the phone number", async () => {
    const order = await store.createOrder(buildOrderData());
    await store.updateOrderPhone(order.id, "+19998887777");
    const updated = await store.getOrder(order.id);
    expect(updated?.phoneNumber).toBe("+19998887777");
  });

  it("getAllOrders returns all orders", async () => {
    await store.createOrder(buildOrderData());
    await store.createOrder(buildOrderData());
    expect((await store.getAllOrders()).length).toBe(2);
  });
});

// ---------------------------------------------------------------------------
// Rental tests
// ---------------------------------------------------------------------------
describe("Storage – rentals", () => {
  let store: Store;
  let userId: number;
  let serviceId: number;

  beforeEach(async () => {
    store = makeStore(buildDb());
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "renter", email: "renter@example.com", password: pw });
    userId = user.id;
    await store.upsertServices([{ name: "Signal", slug: "signal", price: "2.00", isActive: 1 }]);
    const svc = await store.getServiceBySlug("signal");
    serviceId = svc!.id;
  });

  function buildRentalData(overrides: Partial<InsertRental> = {}): InsertRental {
    const now = new Date().toISOString();
    return {
      userId,
      serviceId,
      serviceName: "Signal",
      phoneNumber: "+12025550100",
      status: "active",
      price: "14.00",
      country: "us",
      days: 7,
      proxnumId: "rental-pn",
      createdAt: now,
      expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
      cancelledAt: null,
      ...overrides,
    };
  }

  it("creates and retrieves a rental", async () => {
    const rental = await store.createRental(buildRentalData());
    expect(rental.id).toBeGreaterThan(0);
    const fetched = await store.getRental(rental.id);
    expect(fetched?.phoneNumber).toBe("+12025550100");
  });

  it("returns undefined for unknown rental id", async () => {
    expect(await store.getRental(9999)).toBeUndefined();
  });

  it("retrieves rental by proxnum id", async () => {
    const rental = await store.createRental(buildRentalData({ proxnumId: "unique-rental" }));
    const fetched = await store.getRentalByProxnumId("unique-rental");
    expect(fetched?.id).toBe(rental.id);
  });

  it("getUserRentals returns rentals in descending id order", async () => {
    await store.createRental(buildRentalData());
    await store.createRental(buildRentalData());
    const result = await store.getUserRentals(userId);
    expect(result.length).toBe(2);
    expect(result[0].id).toBeGreaterThan(result[1].id);
  });

  it("getActiveRentals only returns active rentals", async () => {
    await store.createRental(buildRentalData({ status: "active" }));
    await store.createRental(buildRentalData({ status: "cancelled" }));
    const active = await store.getActiveRentals(userId);
    expect(active.length).toBe(1);
    expect(active[0].status).toBe("active");
  });

  it("updateRentalStatus changes status", async () => {
    const rental = await store.createRental(buildRentalData());
    await store.updateRentalStatus(rental.id, "expired");
    const updated = await store.getRental(rental.id);
    expect(updated?.status).toBe("expired");
  });

  it("cancelRental sets cancelled status and cancelledAt", async () => {
    const rental = await store.createRental(buildRentalData());
    await store.cancelRental(rental.id);
    const updated = await store.getRental(rental.id);
    expect(updated?.status).toBe("cancelled");
    expect(updated?.cancelledAt).toBeTruthy();
  });

  it("getAllRentals returns all rentals", async () => {
    await store.createRental(buildRentalData());
    await store.createRental(buildRentalData());
    expect((await store.getAllRentals()).length).toBe(2);
  });
});

// ---------------------------------------------------------------------------
// Rental message tests
// ---------------------------------------------------------------------------
describe("Storage – rental messages", () => {
  let store: Store;
  let rentalId: number;

  beforeEach(async () => {
    store = makeStore(buildDb());
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "msguser", email: "msg@example.com", password: pw });
    await store.upsertServices([{ name: "Svc", slug: "svc", price: "1.00", isActive: 1 }]);
    const svc = await store.getServiceBySlug("svc");
    const now = new Date().toISOString();
    const rental = await store.createRental({
      userId: user.id,
      serviceId: svc!.id,
      serviceName: "Svc",
      phoneNumber: "+1000",
      status: "active",
      price: "7.00",
      country: "us",
      days: 7,
      proxnumId: null,
      createdAt: now,
      expiresAt: now,
      cancelledAt: null,
    });
    rentalId = rental.id;
  });

  it("creates and retrieves rental messages", async () => {
    await store.createRentalMessage({
      rentalId,
      sender: "+15550001111",
      message: "Hello",
      receivedAt: new Date().toISOString(),
    });
    const msgs = await store.getRentalMessages(rentalId);
    expect(msgs.length).toBe(1);
    expect(msgs[0].message).toBe("Hello");
  });

  it("returns empty array when no messages exist", async () => {
    expect(await store.getRentalMessages(rentalId)).toEqual([]);
  });

  it("returns messages in descending id order", async () => {
    const now = new Date().toISOString();
    await store.createRentalMessage({ rentalId, sender: null, message: "first", receivedAt: now });
    await store.createRentalMessage({ rentalId, sender: null, message: "second", receivedAt: now });
    const msgs = await store.getRentalMessages(rentalId);
    expect(msgs[0].message).toBe("second");
  });
});

// ---------------------------------------------------------------------------
// Settings tests
// ---------------------------------------------------------------------------
describe("Storage – settings", () => {
  let store: Store;

  beforeEach(() => {
    store = makeStore(buildDb());
  });

  it("getSetting returns null for missing key", async () => {
    expect(await store.getSetting("nonexistent")).toBeNull();
  });

  it("setSetting inserts new setting and getSetting retrieves it", async () => {
    await store.setSetting("price_multiplier", "2.0");
    expect(await store.getSetting("price_multiplier")).toBe("2.0");
  });

  it("setSetting updates existing setting", async () => {
    await store.setSetting("price_multiplier", "1.5");
    await store.setSetting("price_multiplier", "2.5");
    expect(await store.getSetting("price_multiplier")).toBe("2.5");
  });

  it("deleteSetting removes the setting", async () => {
    await store.setSetting("temp_key", "value");
    await store.deleteSetting("temp_key");
    expect(await store.getSetting("temp_key")).toBeNull();
  });

  it("getAllSettings returns all key/value pairs", async () => {
    await store.setSetting("a", "1");
    await store.setSetting("b", "2");
    const all = await store.getAllSettings();
    expect(all.some(s => s.key === "a" && s.value === "1")).toBe(true);
    expect(all.some(s => s.key === "b" && s.value === "2")).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Transaction tests
// ---------------------------------------------------------------------------
describe("Storage – transactions", () => {
  let store: Store;
  let userId: number;

  beforeEach(async () => {
    store = makeStore(buildDb());
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "txuser", email: "tx@example.com", password: pw });
    userId = user.id;
  });

  it("creates a transaction and retrieves it", async () => {
    const tx = await store.createTransaction({
      userId,
      type: "deposit",
      amount: "10.00",
      description: "Test deposit",
      orderId: null,
      stripeSessionId: null,
      createdAt: new Date().toISOString(),
    });
    expect(tx.id).toBeGreaterThan(0);
    expect(tx.type).toBe("deposit");

    const txns = await store.getUserTransactions(userId);
    expect(txns.length).toBe(1);
    expect(txns[0].amount).toBe("10.00");
  });

  it("getUserTransactions returns empty array when none exist", async () => {
    expect(await store.getUserTransactions(userId)).toEqual([]);
  });

  it("getUserTransactions returns transactions in descending id order", async () => {
    const now = new Date().toISOString();
    await store.createTransaction({ userId, type: "purchase", amount: "-1.00", description: "first", orderId: null, stripeSessionId: null, createdAt: now });
    await store.createTransaction({ userId, type: "purchase", amount: "-2.00", description: "second", orderId: null, stripeSessionId: null, createdAt: now });
    const txns = await store.getUserTransactions(userId);
    expect(txns[0].id).toBeGreaterThan(txns[1].id);
  });
});

// ---------------------------------------------------------------------------
// Crypto deposit tests
// ---------------------------------------------------------------------------
describe("Storage – crypto deposits", () => {
  let store: Store;
  let userId: number;

  beforeEach(async () => {
    store = makeStore(buildDb());
    const pw = await bcrypt.hash("pw", 4);
    const user = await store.createUser({ username: "cryptouser", email: "crypto@example.com", password: pw });
    userId = user.id;
  });

  function buildDepositData(overrides: Partial<InsertCryptoDeposit> = {}): InsertCryptoDeposit {
    const now = new Date().toISOString();
    return {
      userId,
      currency: "USDT_TRC20",
      amount: "25.00",
      cryptoAmount: "25.00000000",
      walletAddress: "TXXX123",
      txHash: null,
      status: "pending",
      createdAt: now,
      expiresAt: new Date(Date.now() + 60 * 60 * 1000).toISOString(),
      completedAt: null,
      ...overrides,
    };
  }

  it("creates and retrieves a crypto deposit", async () => {
    const deposit = await store.createCryptoDeposit(buildDepositData());
    expect(deposit.id).toBeGreaterThan(0);
    const fetched = await store.getCryptoDeposit(deposit.id);
    expect(fetched?.currency).toBe("USDT_TRC20");
    expect(fetched?.status).toBe("pending");
  });

  it("returns undefined for unknown deposit id", async () => {
    expect(await store.getCryptoDeposit(9999)).toBeUndefined();
  });

  it("getUserCryptoDeposits returns all user deposits", async () => {
    await store.createCryptoDeposit(buildDepositData());
    await store.createCryptoDeposit(buildDepositData({ currency: "ETH" }));
    const deposits = await store.getUserCryptoDeposits(userId);
    expect(deposits.length).toBe(2);
  });

  it("updateCryptoDeposit changes status and txHash", async () => {
    const deposit = await store.createCryptoDeposit(buildDepositData());
    await store.updateCryptoDeposit(deposit.id, { txHash: "0xabc123", status: "confirming" });
    const updated = await store.getCryptoDeposit(deposit.id);
    expect(updated?.txHash).toBe("0xabc123");
    expect(updated?.status).toBe("confirming");
  });

  it("getAllPendingCryptoDeposits returns pending and confirming statuses", async () => {
    await store.createCryptoDeposit(buildDepositData({ status: "pending" }));
    await store.createCryptoDeposit(buildDepositData({ status: "confirming" }));
    await store.createCryptoDeposit(buildDepositData({ status: "completed" }));
    const pending = await store.getAllPendingCryptoDeposits();
    expect(pending.length).toBe(2);
    expect(pending.every(d => d.status === "pending" || d.status === "confirming")).toBe(true);
  });
});
