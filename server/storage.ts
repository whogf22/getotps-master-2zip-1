import {
  type User, type InsertUser, users,
  type Service, type InsertService, services,
  type Order, type InsertOrder, orders,
  type Rental, type InsertRental, rentals,
  type RentalMessage, type InsertRentalMessage, rentalMessages,
  type Setting, settings,
  type Transaction, type InsertTransaction, transactions,
  type CryptoDeposit, type InsertCryptoDeposit, cryptoDeposits,
} from "@shared/schema";
import { drizzle } from "drizzle-orm/better-sqlite3";
import Database from "better-sqlite3";
import { eq, and, desc, or, inArray } from "drizzle-orm";
import bcrypt from "bcryptjs";
import crypto from "crypto";

// ── Money helpers ────────────────────────────────────────────────────────────
// Balances/amounts are stored as fixed 2-decimal strings. Do all arithmetic in
// integer cents to avoid floating-point accumulation errors.
export function toCents(value: string | number): number {
  const n = typeof value === "number" ? value : parseFloat(value);
  if (!Number.isFinite(n)) return NaN;
  return Math.round(n * 100);
}

export function centsToStr(cents: number): string {
  return (cents / 100).toFixed(2);
}

// Honor DATABASE_URL when provided (default keeps the existing ./data.db file).
// Never point this at an ephemeral build dir — the SQLite file must persist.
export const sqlite = new Database(process.env.DATABASE_URL || "data.db");
sqlite.pragma("journal_mode = WAL");

export const db = drizzle(sqlite);

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

try {
  sqlite.exec(`ALTER TABLE orders ADD COLUMN country TEXT NOT NULL DEFAULT 'us'`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE orders ADD COLUMN proxnum_id TEXT`);
} catch (e) {}

function seedSettings() {
  const defaults: Record<string, string> = {
    price_multiplier: "1.5",
    default_country: "us",
  };
  for (const [key, value] of Object.entries(defaults)) {
    const existing = db.select().from(settings).where(eq(settings.key, key)).get();
    if (!existing) {
      db.insert(settings).values({ key, value }).run();
    }
  }
}

async function seedDatabase() {
  const adminEmail = process.env.ADMIN_EMAIL || "admin@getotps.online";
  const adminPassword = process.env.ADMIN_PASSWORD || (process.env.NODE_ENV === "production" ? (() => { throw new Error("ADMIN_PASSWORD must be set in production"); })() : "admin123");
  const adminUsername = process.env.ADMIN_USERNAME || "admin";
  const existingAdmin = db.select().from(users).where(eq(users.email, adminEmail)).get();
  if (!existingAdmin) {
    const hashedPassword = await bcrypt.hash(adminPassword, 10);
    const apiKey = crypto.randomBytes(32).toString("hex");
    db.insert(users).values({
      username: adminUsername,
      email: adminEmail,
      password: hashedPassword,
      balance: "100.00",
      apiKey,
      role: "admin",
    }).run();
    console.log(`Created admin user: ${adminEmail}`);
  } else {
    // Sync admin password with env var on every startup (so Render env var changes take effect)
    const passwordMatches = await bcrypt.compare(adminPassword, existingAdmin.password);
    if (!passwordMatches) {
      const hashedPassword = await bcrypt.hash(adminPassword, 10);
      db.update(users).set({ password: hashedPassword }).where(eq(users.email, adminEmail)).run();
      console.log(`Synced admin password from ADMIN_PASSWORD env var`);
    }
  }
  seedSettings();
}

seedDatabase().catch(console.error);

export interface IStorage {
  getUser(id: number): Promise<User | undefined>;
  getUserByEmail(email: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  getUserByApiKey(apiKey: string): Promise<User | undefined>;
  createUser(user: { username: string; email: string; password: string }): Promise<User>;
  updateUserBalance(userId: number, balance: string): Promise<void>;
  updateUserPassword(userId: number, password: string): Promise<void>;
  generateApiKey(userId: number): Promise<string>;
  getAllUsers(): Promise<User[]>;

  getAllServices(): Promise<Service[]>;
  getService(id: number): Promise<Service | undefined>;
  getServiceBySlug(slug: string): Promise<Service | undefined>;
  getServiceByName(name: string): Promise<Service | undefined>;
  updateService(id: number, data: Partial<InsertService>): Promise<void>;
  upsertServices(serviceList: InsertService[]): Promise<void>;

  createOrder(data: InsertOrder): Promise<Order>;
  getOrder(id: number): Promise<Order | undefined>;
  getOrderByProxnumId(proxnumId: string): Promise<Order | undefined>;
  getUserOrders(userId: number): Promise<Order[]>;
  getActiveOrders(userId: number): Promise<Order[]>;
  getPendingOrders(): Promise<Order[]>;
  updateOrderStatus(id: number, status: string, otpCode?: string): Promise<void>;
  updateOrderSms(id: number, smsMessages: string, otpCode?: string): Promise<void>;
  cancelOrder(id: number): Promise<void>;
  updateOrderProxnumId(id: number, proxnumId: string): Promise<void>;
  updateOrderPhone(id: number, phoneNumber: string): Promise<void>;
  getAllOrders(): Promise<Order[]>;

  createRental(data: InsertRental): Promise<Rental>;
  getRental(id: number): Promise<Rental | undefined>;
  getRentalByProxnumId(proxnumId: string): Promise<Rental | undefined>;
  getUserRentals(userId: number): Promise<Rental[]>;
  getActiveRentals(userId: number): Promise<Rental[]>;
  updateRentalStatus(id: number, status: string): Promise<void>;
  cancelRental(id: number): Promise<void>;
  getAllRentals(): Promise<Rental[]>;

  createRentalMessage(data: InsertRentalMessage): Promise<RentalMessage>;
  getRentalMessages(rentalId: number): Promise<RentalMessage[]>;

  getSetting(key: string): Promise<string | null>;
  setSetting(key: string, value: string): Promise<void>;
  deleteSetting(key: string): Promise<void>;
  getAllSettings(): Promise<{ key: string; value: string }[]>;

  createTransaction(data: InsertTransaction): Promise<Transaction>;
  getUserTransactions(userId: number): Promise<Transaction[]>;

  createCryptoDeposit(data: InsertCryptoDeposit): Promise<CryptoDeposit>;
  getCryptoDeposit(id: number): Promise<CryptoDeposit | undefined>;
  getUserCryptoDeposits(userId: number): Promise<CryptoDeposit[]>;
  updateCryptoDeposit(id: number, data: Partial<CryptoDeposit>): Promise<void>;
  getAllPendingCryptoDeposits(): Promise<CryptoDeposit[]>;

  // ── Atomic financial operations ─────────────────────────────────────────────
  // These run synchronously inside a single better-sqlite3 transaction. The
  // conditional status transition + balance mutation cannot interleave with
  // another request, so a deposit is credited/refunded at most once.
  confirmCryptoDepositAtomic(depositId: number, expectedUserId: number | null, descriptionSuffix: string): { result: "credited" | "already"; newBalance?: string };
  rejectCryptoDepositAtomic(depositId: number): { result: "rejected" | "already" };
  cancelOrderWithRefundAtomic(orderId: number): { result: "refunded" | "already"; newBalance?: string };
  cancelRentalAtomic(rentalId: number): { result: "cancelled" | "already" };
  addUserBalanceAtomic(userId: number, amount: string, description: string): { result: "ok" | "user_not_found"; newBalance?: string };
  createOrderWithDebitAtomic(userId: number, price: string, order: InsertOrder, description: string): { result: "ok"; order: Order; newBalance: string } | { result: "insufficient" } | { result: "user_not_found" };
  createRentalWithDebitAtomic(userId: number, price: string, rental: InsertRental, description: string): { result: "ok"; rental: Rental; newBalance: string } | { result: "insufficient" } | { result: "user_not_found" };
}

export class DatabaseStorage implements IStorage {
  async getUser(id: number): Promise<User | undefined> {
    return db.select().from(users).where(eq(users.id, id)).get();
  }

  async getUserByEmail(email: string): Promise<User | undefined> {
    return db.select().from(users).where(eq(users.email, email)).get();
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    return db.select().from(users).where(eq(users.username, username)).get();
  }

  async getUserByApiKey(apiKey: string): Promise<User | undefined> {
    return db.select().from(users).where(eq(users.apiKey, apiKey)).get();
  }

  async createUser(data: { username: string; email: string; password: string }): Promise<User> {
    const apiKey = crypto.randomBytes(32).toString("hex");
    return db.insert(users).values({ ...data, apiKey }).returning().get();
  }

  async updateUserBalance(userId: number, balance: string): Promise<void> {
    db.update(users).set({ balance }).where(eq(users.id, userId)).run();
  }

  async updateUserPassword(userId: number, password: string): Promise<void> {
    db.update(users).set({ password }).where(eq(users.id, userId)).run();
  }

  async generateApiKey(userId: number): Promise<string> {
    const apiKey = crypto.randomBytes(32).toString("hex");
    db.update(users).set({ apiKey }).where(eq(users.id, userId)).run();
    return apiKey;
  }

  async getAllUsers(): Promise<User[]> {
    return db.select().from(users).all();
  }

  async getAllServices(): Promise<Service[]> {
    return db.select().from(services).where(eq(services.isActive, 1)).all();
  }

  async getService(id: number): Promise<Service | undefined> {
    return db.select().from(services).where(eq(services.id, id)).get();
  }

  async getServiceBySlug(slug: string): Promise<Service | undefined> {
    return db.select().from(services).where(eq(services.slug, slug)).get();
  }

  async getServiceByName(name: string): Promise<Service | undefined> {
    return db.select().from(services).where(eq(services.name, name)).get();
  }

  async updateService(id: number, data: Partial<InsertService>): Promise<void> {
    db.update(services).set(data).where(eq(services.id, id)).run();
  }

  async upsertServices(serviceList: InsertService[]): Promise<void> {
    for (const svc of serviceList) {
      const existing = db.select().from(services).where(eq(services.slug, svc.slug)).get();
      if (existing) {
        db.update(services).set({ price: svc.price, isActive: svc.isActive, category: svc.category })
          .where(eq(services.slug, svc.slug)).run();
      } else {
        db.insert(services).values(svc).run();
      }
    }
  }

  async createOrder(data: InsertOrder): Promise<Order> {
    return db.insert(orders).values(data).returning().get();
  }

  async getOrder(id: number): Promise<Order | undefined> {
    return db.select().from(orders).where(eq(orders.id, id)).get();
  }

  async getOrderByProxnumId(proxnumId: string): Promise<Order | undefined> {
    return db.select().from(orders).where(eq(orders.proxnumId, proxnumId)).get();
  }

  async getUserOrders(userId: number): Promise<Order[]> {
    return db.select().from(orders).where(eq(orders.userId, userId)).orderBy(desc(orders.id)).all();
  }

  async getActiveOrders(userId: number): Promise<Order[]> {
    return db.select().from(orders)
      .where(and(
        eq(orders.userId, userId),
        or(eq(orders.status, "pending"), eq(orders.status, "waiting"), eq(orders.status, "received"))
      ))
      .orderBy(desc(orders.id))
      .all();
  }

  async getPendingOrders(): Promise<Order[]> {
    return db.select().from(orders)
      .where(or(eq(orders.status, "pending"), eq(orders.status, "waiting")))
      .orderBy(desc(orders.id))
      .all();
  }

  async updateOrderStatus(id: number, status: string, otpCode?: string): Promise<void> {
    const updateData: any = { status };
    if (otpCode) updateData.otpCode = otpCode;
    if (status === "completed") updateData.completedAt = new Date().toISOString();
    db.update(orders).set(updateData).where(eq(orders.id, id)).run();
  }

  async updateOrderSms(id: number, smsMessages: string, otpCode?: string): Promise<void> {
    const updateData: any = { smsMessages, status: "received" };
    if (otpCode) updateData.otpCode = otpCode;
    db.update(orders).set(updateData).where(eq(orders.id, id)).run();
  }

  async cancelOrder(id: number): Promise<void> {
    db.update(orders).set({ status: "cancelled", completedAt: new Date().toISOString() }).where(eq(orders.id, id)).run();
  }

  async updateOrderProxnumId(id: number, proxnumId: string): Promise<void> {
    db.update(orders).set({ proxnumId }).where(eq(orders.id, id)).run();
  }

  async updateOrderPhone(id: number, phoneNumber: string): Promise<void> {
    db.update(orders).set({ phoneNumber }).where(eq(orders.id, id)).run();
  }

  async getAllOrders(): Promise<Order[]> {
    return db.select().from(orders).orderBy(desc(orders.id)).all();
  }

  async createRental(data: InsertRental): Promise<Rental> {
    return db.insert(rentals).values(data).returning().get();
  }

  async getRental(id: number): Promise<Rental | undefined> {
    return db.select().from(rentals).where(eq(rentals.id, id)).get();
  }

  async getRentalByProxnumId(proxnumId: string): Promise<Rental | undefined> {
    return db.select().from(rentals).where(eq(rentals.proxnumId, proxnumId)).get();
  }

  async getUserRentals(userId: number): Promise<Rental[]> {
    return db.select().from(rentals).where(eq(rentals.userId, userId)).orderBy(desc(rentals.id)).all();
  }

  async getActiveRentals(userId: number): Promise<Rental[]> {
    return db.select().from(rentals)
      .where(and(eq(rentals.userId, userId), eq(rentals.status, "active")))
      .orderBy(desc(rentals.id))
      .all();
  }

  async updateRentalStatus(id: number, status: string): Promise<void> {
    db.update(rentals).set({ status }).where(eq(rentals.id, id)).run();
  }

  async cancelRental(id: number): Promise<void> {
    db.update(rentals).set({ status: "cancelled", cancelledAt: new Date().toISOString() }).where(eq(rentals.id, id)).run();
  }

  async getAllRentals(): Promise<Rental[]> {
    return db.select().from(rentals).orderBy(desc(rentals.id)).all();
  }

  async createRentalMessage(data: InsertRentalMessage): Promise<RentalMessage> {
    return db.insert(rentalMessages).values(data).returning().get();
  }

  async getRentalMessages(rentalId: number): Promise<RentalMessage[]> {
    return db.select().from(rentalMessages).where(eq(rentalMessages.rentalId, rentalId)).orderBy(desc(rentalMessages.id)).all();
  }

  async getSetting(key: string): Promise<string | null> {
    const row = db.select().from(settings).where(eq(settings.key, key)).get();
    return row?.value ?? null;
  }

  async setSetting(key: string, value: string): Promise<void> {
    const existing = db.select().from(settings).where(eq(settings.key, key)).get();
    if (existing) {
      db.update(settings).set({ value }).where(eq(settings.key, key)).run();
    } else {
      db.insert(settings).values({ key, value }).run();
    }
  }

  async deleteSetting(key: string): Promise<void> {
    db.delete(settings).where(eq(settings.key, key)).run();
  }

  async getAllSettings(): Promise<{ key: string; value: string }[]> {
    const rows = db.select().from(settings).all();
    return rows.map(r => ({ key: r.key, value: r.value ?? "" }));
  }

  async createTransaction(data: InsertTransaction): Promise<Transaction> {
    return db.insert(transactions).values(data).returning().get();
  }

  async getUserTransactions(userId: number): Promise<Transaction[]> {
    return db.select().from(transactions).where(eq(transactions.userId, userId)).orderBy(desc(transactions.id)).all();
  }

  async createCryptoDeposit(data: InsertCryptoDeposit): Promise<CryptoDeposit> {
    return db.insert(cryptoDeposits).values(data).returning().get();
  }

  async getCryptoDeposit(id: number): Promise<CryptoDeposit | undefined> {
    return db.select().from(cryptoDeposits).where(eq(cryptoDeposits.id, id)).get();
  }

  async getUserCryptoDeposits(userId: number): Promise<CryptoDeposit[]> {
    return db.select().from(cryptoDeposits).where(eq(cryptoDeposits.userId, userId)).orderBy(desc(cryptoDeposits.id)).all();
  }

  async updateCryptoDeposit(id: number, data: Partial<CryptoDeposit>): Promise<void> {
    db.update(cryptoDeposits).set(data as any).where(eq(cryptoDeposits.id, id)).run();
  }

  async getAllPendingCryptoDeposits(): Promise<CryptoDeposit[]> {
    return db.select().from(cryptoDeposits).where(
      or(eq(cryptoDeposits.status, "pending"), eq(cryptoDeposits.status, "confirming"))
    ).orderBy(desc(cryptoDeposits.id)).all();
  }

  // ── Atomic financial operations ─────────────────────────────────────────────

  confirmCryptoDepositAtomic(
    depositId: number,
    expectedUserId: number | null,
    descriptionSuffix: string,
  ): { result: "credited" | "already"; newBalance?: string } {
    const now = new Date().toISOString();
    const run = sqlite.transaction(() => {
      // Conditional terminal transition: only a pending/confirming deposit can be
      // completed. A concurrent request sees status="completed" -> 0 rows changed.
      const conds = [
        eq(cryptoDeposits.id, depositId),
        inArray(cryptoDeposits.status, ["pending", "confirming"]),
      ];
      if (expectedUserId != null) conds.push(eq(cryptoDeposits.userId, expectedUserId));
      const upd = db.update(cryptoDeposits)
        .set({ status: "completed", completedAt: now })
        .where(and(...conds))
        .run();
      if (upd.changes !== 1) return { result: "already" as const };

      // Read the AUTHORITATIVE amount from the stored record (never the client).
      const dep = db.select().from(cryptoDeposits).where(eq(cryptoDeposits.id, depositId)).get()!;
      const user = db.select().from(users).where(eq(users.id, dep.userId)).get();
      if (!user) return { result: "credited" as const };
      const newBalance = centsToStr(toCents(user.balance) + toCents(dep.amount));
      db.update(users).set({ balance: newBalance }).where(eq(users.id, dep.userId)).run();
      db.insert(transactions).values({
        userId: dep.userId, type: "deposit", amount: dep.amount,
        description: `Crypto deposit (${dep.currency})${descriptionSuffix}`,
        orderId: null, stripeSessionId: null, createdAt: now,
      }).run();
      return { result: "credited" as const, newBalance };
    });
    return run();
  }

  rejectCryptoDepositAtomic(depositId: number): { result: "rejected" | "already" } {
    const now = new Date().toISOString();
    const run = sqlite.transaction(() => {
      const upd = db.update(cryptoDeposits)
        .set({ status: "rejected", completedAt: now })
        .where(and(
          eq(cryptoDeposits.id, depositId),
          inArray(cryptoDeposits.status, ["pending", "confirming"]),
        ))
        .run();
      return { result: (upd.changes === 1 ? "rejected" : "already") as "rejected" | "already" };
    });
    return run();
  }

  cancelOrderWithRefundAtomic(orderId: number): { result: "refunded" | "already"; newBalance?: string } {
    const now = new Date().toISOString();
    const run = sqlite.transaction(() => {
      // Only a pending/waiting order can be cancelled, so the refund runs once.
      const upd = db.update(orders)
        .set({ status: "cancelled", completedAt: now })
        .where(and(
          eq(orders.id, orderId),
          inArray(orders.status, ["pending", "waiting"]),
        ))
        .run();
      if (upd.changes !== 1) return { result: "already" as const };

      const ord = db.select().from(orders).where(eq(orders.id, orderId)).get()!;
      const user = db.select().from(users).where(eq(users.id, ord.userId)).get();
      if (!user) return { result: "refunded" as const };
      const newBalance = centsToStr(toCents(user.balance) + toCents(ord.price));
      db.update(users).set({ balance: newBalance }).where(eq(users.id, ord.userId)).run();
      db.insert(transactions).values({
        userId: ord.userId, type: "refund", amount: ord.price,
        description: "Order cancelled - refund", orderId: ord.id,
        stripeSessionId: null, createdAt: now,
      }).run();
      return { result: "refunded" as const, newBalance };
    });
    return run();
  }

  cancelRentalAtomic(rentalId: number): { result: "cancelled" | "already" } {
    const now = new Date().toISOString();
    const run = sqlite.transaction(() => {
      const upd = db.update(rentals)
        .set({ status: "cancelled", cancelledAt: now })
        .where(and(eq(rentals.id, rentalId), eq(rentals.status, "active")))
        .run();
      return { result: (upd.changes === 1 ? "cancelled" : "already") as "cancelled" | "already" };
    });
    return run();
  }

  addUserBalanceAtomic(
    userId: number,
    amount: string,
    description: string,
  ): { result: "ok" | "user_not_found"; newBalance?: string } {
    const now = new Date().toISOString();
    const run = sqlite.transaction(() => {
      // Atomic read-modify-write prevents lost updates under concurrency.
      const user = db.select().from(users).where(eq(users.id, userId)).get();
      if (!user) return { result: "user_not_found" as const };
      const newBalance = centsToStr(toCents(user.balance) + toCents(amount));
      db.update(users).set({ balance: newBalance }).where(eq(users.id, userId)).run();
      db.insert(transactions).values({
        userId, type: "deposit", amount, description,
        orderId: null, stripeSessionId: null, createdAt: now,
      }).run();
      return { result: "ok" as const, newBalance };
    });
    return run();
  }

  createOrderWithDebitAtomic(
    userId: number,
    price: string,
    order: InsertOrder,
    description: string,
  ): { result: "ok"; order: Order; newBalance: string } | { result: "insufficient" } | { result: "user_not_found" } {
    const now = order.createdAt || new Date().toISOString();
    const run = sqlite.transaction(() => {
      const user = db.select().from(users).where(eq(users.id, userId)).get();
      if (!user) return { result: "user_not_found" as const };
      const balCents = toCents(user.balance);
      const priceCents = toCents(price);
      // Conditional debit: never let concurrent purchases drive the balance negative.
      if (!Number.isFinite(balCents) || !Number.isFinite(priceCents) || balCents < priceCents) {
        return { result: "insufficient" as const };
      }
      const newBalance = centsToStr(balCents - priceCents);
      db.update(users).set({ balance: newBalance }).where(eq(users.id, userId)).run();
      const created = db.insert(orders).values(order).returning().get();
      db.insert(transactions).values({
        userId, type: "purchase", amount: `-${price}`,
        description, orderId: created.id, stripeSessionId: null, createdAt: now,
      }).run();
      return { result: "ok" as const, order: created, newBalance };
    });
    return run();
  }

  createRentalWithDebitAtomic(
    userId: number,
    price: string,
    rental: InsertRental,
    description: string,
  ): { result: "ok"; rental: Rental; newBalance: string } | { result: "insufficient" } | { result: "user_not_found" } {
    const now = rental.createdAt || new Date().toISOString();
    const run = sqlite.transaction(() => {
      const user = db.select().from(users).where(eq(users.id, userId)).get();
      if (!user) return { result: "user_not_found" as const };
      const balCents = toCents(user.balance);
      const priceCents = toCents(price);
      if (!Number.isFinite(balCents) || !Number.isFinite(priceCents) || balCents < priceCents) {
        return { result: "insufficient" as const };
      }
      const newBalance = centsToStr(balCents - priceCents);
      db.update(users).set({ balance: newBalance }).where(eq(users.id, userId)).run();
      const created = db.insert(rentals).values(rental).returning().get();
      db.insert(transactions).values({
        userId, type: "purchase", amount: `-${price}`,
        description, orderId: created.id, stripeSessionId: null, createdAt: now,
      }).run();
      return { result: "ok" as const, rental: created, newBalance };
    });
    return run();
  }
}

export const storage = new DatabaseStorage();
