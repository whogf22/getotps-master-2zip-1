import {
  type User, type InsertUser, users,
  type Service, type InsertService, services,
  type Order, type InsertOrder, orders,
  type Rental, type InsertRental, rentals,
  type RentalMessage, type InsertRentalMessage, rentalMessages,
  type Setting, settings,
  type Transaction, type InsertTransaction, transactions,
  type CryptoDeposit, type InsertCryptoDeposit, cryptoDeposits,
  auditLogs,
} from "@shared/schema";
import { drizzle } from "drizzle-orm/better-sqlite3";
import Database from "better-sqlite3";
import { eq, and, desc, or, lt, sql } from "drizzle-orm";
import bcrypt from "bcryptjs";
import crypto from "crypto";

const sqlite = new Database("data.db");
sqlite.pragma("journal_mode = WAL");
sqlite.pragma("foreign_keys = ON");
sqlite.pragma("busy_timeout = 5000");
sqlite.pragma("synchronous = NORMAL");

export const db = drizzle(sqlite);

sqlite.exec(`
  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    balance TEXT NOT NULL DEFAULT '0.00',
    api_key TEXT UNIQUE,
    api_key_hash TEXT,
    api_key_prefix TEXT,
    role TEXT NOT NULL DEFAULT 'user',
    status TEXT NOT NULL DEFAULT 'active'
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
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE CASCADE,
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
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE CASCADE,
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
    rental_id INTEGER NOT NULL REFERENCES rentals(id) ON DELETE CASCADE,
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
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type TEXT NOT NULL,
    amount TEXT NOT NULL,
    description TEXT,
    order_id INTEGER,
    stripe_session_id TEXT,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS crypto_deposits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
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

  CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    entity TEXT,
    entity_id INTEGER,
    metadata TEXT,
    ip_address TEXT,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL,
    expires_at TEXT NOT NULL,
    used INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  );
`);

try {
  sqlite.exec(`ALTER TABLE orders ADD COLUMN country TEXT NOT NULL DEFAULT 'us'`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE orders ADD COLUMN proxnum_id TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN circle_wallet_id TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN circle_wallet_address TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE crypto_deposits ADD COLUMN circle_transfer_id TEXT`);
} catch (e) {}
try {
  sqlite.exec(`CREATE UNIQUE INDEX IF NOT EXISTS idx_crypto_deposits_circle_transfer_id ON crypto_deposits(circle_transfer_id) WHERE circle_transfer_id IS NOT NULL`);
} catch (e) {}

try {
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_rentals_user_id ON rentals(user_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_crypto_deposits_user_id ON crypto_deposits(user_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_rental_messages_rental_id ON rental_messages(rental_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_crypto_deposits_status ON crypto_deposits(status)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_orders_expires_at ON orders(expires_at)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id)`);
  sqlite.exec(`CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_hash ON password_reset_tokens(token_hash)`);
} catch (e) {}

try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN created_at TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN updated_at TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE services ADD COLUMN created_at TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE services ADD COLUMN updated_at TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN status TEXT NOT NULL DEFAULT 'active'`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN api_key_hash TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE users ADD COLUMN api_key_prefix TEXT`);
} catch (e) {}
try {
  sqlite.exec(`ALTER TABLE services ADD COLUMN cost_price TEXT`);
} catch (e) {}

function hashApiKey(key: string): string {
  return crypto.createHash("sha256").update(key).digest("hex");
}

function seedSettings() {
  const defaults: Record<string, string> = {
    price_multiplier: "1.0",
    default_country: "us",
  };
  for (const [key, value] of Object.entries(defaults)) {
    const existing = db.select().from(settings).where(eq(settings.key, key)).get();
    if (!existing) {
      db.insert(settings).values({ key, value }).run();
    }
  }
  const mult = db.select().from(settings).where(eq(settings.key, "price_multiplier")).get();
  if (mult && mult.value === "1.5") {
    db.update(settings).set({ value: "1.0" }).where(eq(settings.key, "price_multiplier")).run();
    console.log("[MIGRATION] Updated price_multiplier from 1.5 to 1.0 (tiered markup now handles pricing)");
  }
}

async function seedDatabase() {
  const existingAdmin = db.select().from(users).where(eq(users.email, "admin@getotps.com")).get();
  if (!existingAdmin) {
    const hashedPassword = await bcrypt.hash("admin123", 12);
    const apiKey = crypto.randomBytes(32).toString("hex");
    const keyHash = hashApiKey(apiKey);
    const keyPrefix = `gotp_${apiKey.slice(0, 8)}`;
    sqlite.prepare(
      `INSERT INTO users (username, email, password, balance, api_key, api_key_hash, api_key_prefix, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`
    ).run("admin", "admin@getotps.com", hashedPassword, "100.00", null, keyHash, keyPrefix, "admin", "active");
    console.warn("============================================================");
    console.warn("[SECURITY] Default admin user created: admin@getotps.com");
    console.warn("[SECURITY] Default password: admin123");
    console.warn("[SECURITY] CHANGE THIS PASSWORD IMMEDIATELY after first login!");
    console.warn(`[SECURITY] Admin API key (SAVE NOW — shown only once): ${apiKey}`);
    console.warn("============================================================");
  } else {
    if (existingAdmin.apiKey && !existingAdmin.apiKeyHash) {
      const keyHash = hashApiKey(existingAdmin.apiKey);
      const keyPrefix = `gotp_${existingAdmin.apiKey.slice(0, 8)}`;
      sqlite.prepare(`UPDATE users SET api_key_hash = ?, api_key_prefix = ?, api_key = NULL WHERE id = ?`).run(keyHash, keyPrefix, existingAdmin.id);
    }
  }
  seedSettings();

  migrateExistingApiKeys();
}

function migrateExistingApiKeys() {
  const usersWithPlainKeys = sqlite.prepare(`SELECT id, api_key FROM users WHERE api_key IS NOT NULL AND api_key != '' AND (api_key_hash IS NULL OR api_key_hash = '')`).all() as any[];
  for (const u of usersWithPlainKeys) {
    const keyHash = hashApiKey(u.api_key);
    const keyPrefix = `gotp_${u.api_key.slice(0, 8)}`;
    sqlite.prepare(`UPDATE users SET api_key_hash = ?, api_key_prefix = ?, api_key = NULL WHERE id = ?`).run(keyHash, keyPrefix, u.id);
  }
  if (usersWithPlainKeys.length > 0) {
    console.log(`[MIGRATION] Migrated ${usersWithPlainKeys.length} plain-text API keys to hashed.`);
  }
}

seedDatabase().catch(console.error);

export interface IStorage {
  getUser(id: number): Promise<User | undefined>;
  getUserByEmail(email: string): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  getUserByApiKey(apiKey: string): Promise<User | undefined>;
  getUserByApiKeyHash(keyHash: string): Promise<User | undefined>;
  createUser(user: { username: string; email: string; password: string }): Promise<User>;
  updateUserBalance(userId: number, balance: string): Promise<void>;
  updateUserPassword(userId: number, password: string): Promise<void>;
  updateUserProfile(userId: number, data: { username?: string; email?: string }): Promise<void>;
  updateUserStatus(userId: number, status: string): Promise<void>;
  generateApiKey(userId: number): Promise<{ apiKey: string; prefix: string }>;
  updateUserCircleWallet(userId: number, walletId: string, walletAddress: string): Promise<void>;
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
  getExpiredPendingOrders(): Promise<Order[]>;
  updateOrderStatus(id: number, status: string, otpCode?: string): Promise<void>;
  updateOrderSms(id: number, smsMessages: string, otpCode?: string): Promise<void>;
  cancelOrder(id: number): Promise<void>;
  updateOrderProxnumId(id: number, proxnumId: string): Promise<void>;
  updateOrderPhone(id: number, phoneNumber: string): Promise<void>;
  getAllOrders(): Promise<Order[]>;

  transactionalCancelAndRefund(orderId: number, userId: number, refundAmount: number): Promise<void>;
  transactionalRentalCancelAndRefund(rentalId: number, userId: number, refundAmount: number): Promise<void>;
  transactionalExpireAndRefund(orderId: number, userId: number, refundAmount: number): Promise<void>;
  transactionalConfirmDeposit(depositId: number, userId: number, creditAmount: number, currency: string, now: string): Promise<void>;

  createRental(data: InsertRental): Promise<Rental>;
  getRental(id: number): Promise<Rental | undefined>;
  getRentalByProxnumId(proxnumId: string): Promise<Rental | undefined>;
  getUserRentals(userId: number): Promise<Rental[]>;
  getActiveRentals(userId: number): Promise<Rental[]>;
  updateRentalStatus(id: number, status: string): Promise<void>;
  cancelRental(id: number): Promise<void>;
  extendRental(id: number, additionalDays: number, additionalCost: number, userId: number): Promise<void>;
  getAllRentals(): Promise<Rental[]>;

  createRentalMessage(data: InsertRentalMessage): Promise<RentalMessage>;
  getRentalMessages(rentalId: number): Promise<RentalMessage[]>;

  getSetting(key: string): Promise<string | null>;
  setSetting(key: string, value: string): Promise<void>;
  deleteSetting(key: string): Promise<void>;
  getAllSettings(): Promise<{ key: string; value: string }[]>;

  createTransaction(data: InsertTransaction): Promise<Transaction>;
  getUserTransactions(userId: number): Promise<Transaction[]>;
  getAllTransactions(): Promise<Transaction[]>;

  createCryptoDeposit(data: InsertCryptoDeposit): Promise<CryptoDeposit>;
  getCryptoDeposit(id: number): Promise<CryptoDeposit | undefined>;
  getUserCryptoDeposits(userId: number): Promise<CryptoDeposit[]>;
  updateCryptoDeposit(id: number, data: Partial<CryptoDeposit>): Promise<void>;
  getAllPendingCryptoDeposits(): Promise<CryptoDeposit[]>;
  getAllCryptoDeposits(): Promise<CryptoDeposit[]>;
  creditCircleDeposit(depositData: InsertCryptoDeposit, txData: InsertTransaction, userId: number, creditAmount: string): Promise<boolean>;
  atomicDeductBalance(userId: number, amount: number): Promise<{ success: boolean; newBalance: string }>;
  atomicAddBalance(userId: number, amount: number): Promise<string>;
  createAuditLog(userId: number | null, action: string, entity: string | null, entityId: number | null, metadata?: string, ipAddress?: string): Promise<void>;
  getAuditLogs(limit?: number, offset?: number): Promise<any[]>;

  createPasswordResetToken(userId: number, tokenHash: string, expiresAt: string): Promise<void>;
  getPasswordResetToken(tokenHash: string): Promise<any | undefined>;
  markPasswordResetTokenUsed(tokenHash: string): Promise<void>;

  getPublicStats(): Promise<{ totalOrders: number; totalUsers: number; totalCountries: number }>;
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
    const keyHash = hashApiKey(apiKey);
    return this.getUserByApiKeyHash(keyHash);
  }

  async getUserByApiKeyHash(keyHash: string): Promise<User | undefined> {
    const row = sqlite.prepare(`SELECT * FROM users WHERE api_key_hash = ?`).get(keyHash) as any;
    if (!row) return undefined;
    return {
      id: row.id,
      username: row.username,
      email: row.email,
      password: row.password,
      balance: row.balance,
      apiKey: row.api_key,
      role: row.role,
      circleWalletId: row.circle_wallet_id,
      circleWalletAddress: row.circle_wallet_address,
      apiKeyHash: row.api_key_hash,
      apiKeyPrefix: row.api_key_prefix,
      status: row.status,
    } as any;
  }

  async createUser(data: { username: string; email: string; password: string }): Promise<User> {
    return db.insert(users).values({ ...data }).returning().get();
  }

  async updateUserBalance(userId: number, balance: string): Promise<void> {
    db.update(users).set({ balance }).where(eq(users.id, userId)).run();
  }

  async updateUserPassword(userId: number, password: string): Promise<void> {
    db.update(users).set({ password }).where(eq(users.id, userId)).run();
  }

  async updateUserProfile(userId: number, data: { username?: string; email?: string }): Promise<void> {
    const setData: any = {};
    if (data.username) setData.username = data.username;
    if (data.email) setData.email = data.email;
    if (Object.keys(setData).length > 0) {
      db.update(users).set(setData).where(eq(users.id, userId)).run();
    }
  }

  async updateUserStatus(userId: number, status: string): Promise<void> {
    sqlite.prepare(`UPDATE users SET status = ? WHERE id = ?`).run(status, userId);
  }

  async generateApiKey(userId: number): Promise<{ apiKey: string; prefix: string }> {
    const apiKey = crypto.randomBytes(32).toString("hex");
    const keyHash = hashApiKey(apiKey);
    const keyPrefix = `gotp_${apiKey.slice(0, 8)}`;
    sqlite.prepare(`UPDATE users SET api_key = NULL, api_key_hash = ?, api_key_prefix = ? WHERE id = ?`).run(keyHash, keyPrefix, userId);
    return { apiKey, prefix: keyPrefix };
  }

  async updateUserCircleWallet(userId: number, walletId: string, walletAddress: string): Promise<void> {
    db.update(users).set({ circleWalletId: walletId, circleWalletAddress: walletAddress }).where(eq(users.id, userId)).run();
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
        const updateData: any = { price: svc.price, isActive: svc.isActive, category: svc.category };
        if (svc.costPrice !== undefined) updateData.costPrice = svc.costPrice;
        db.update(services).set(updateData).where(eq(services.slug, svc.slug)).run();
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

  async getExpiredPendingOrders(): Promise<Order[]> {
    const now = new Date().toISOString();
    const rows = sqlite.prepare(
      `SELECT * FROM orders WHERE (status = 'pending' OR status = 'waiting') AND expires_at < ? ORDER BY id DESC`
    ).all(now) as any[];
    return rows;
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

  async transactionalCancelAndRefund(orderId: number, userId: number, refundAmount: number): Promise<void> {
    sqlite.exec("BEGIN IMMEDIATE");
    try {
      sqlite.prepare(`UPDATE orders SET status = 'cancelled', completed_at = ? WHERE id = ?`).run(new Date().toISOString(), orderId);
      sqlite.prepare(`UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) + ?) WHERE id = ?`).run(refundAmount, userId);
      sqlite.prepare(
        `INSERT INTO transactions (user_id, type, amount, description, order_id, stripe_session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).run(userId, "refund", refundAmount.toFixed(2), "Order cancelled - refund", orderId, null, new Date().toISOString());
      sqlite.exec("COMMIT");
    } catch (err) {
      try { sqlite.exec("ROLLBACK"); } catch (_) {}
      throw err;
    }
  }

  async transactionalRentalCancelAndRefund(rentalId: number, userId: number, refundAmount: number): Promise<void> {
    sqlite.exec("BEGIN IMMEDIATE");
    try {
      sqlite.prepare(`UPDATE rentals SET status = 'cancelled', cancelled_at = ? WHERE id = ?`).run(new Date().toISOString(), rentalId);
      if (refundAmount > 0) {
        sqlite.prepare(`UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) + ?) WHERE id = ?`).run(refundAmount, userId);
        sqlite.prepare(
          `INSERT INTO transactions (user_id, type, amount, description, order_id, stripe_session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
        ).run(userId, "refund", refundAmount.toFixed(2), `Rental cancelled - prorated refund`, rentalId, null, new Date().toISOString());
      }
      sqlite.exec("COMMIT");
    } catch (err) {
      try { sqlite.exec("ROLLBACK"); } catch (_) {}
      throw err;
    }
  }

  async transactionalExpireAndRefund(orderId: number, userId: number, refundAmount: number): Promise<void> {
    sqlite.exec("BEGIN IMMEDIATE");
    try {
      sqlite.prepare(`UPDATE orders SET status = 'expired', completed_at = ? WHERE id = ?`).run(new Date().toISOString(), orderId);
      sqlite.prepare(`UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) + ?) WHERE id = ?`).run(refundAmount, userId);
      sqlite.prepare(
        `INSERT INTO transactions (user_id, type, amount, description, order_id, stripe_session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).run(userId, "refund", refundAmount.toFixed(2), "Order expired - automatic refund", orderId, null, new Date().toISOString());
      sqlite.exec("COMMIT");
    } catch (err) {
      try { sqlite.exec("ROLLBACK"); } catch (_) {}
      throw err;
    }
  }

  async transactionalConfirmDeposit(depositId: number, userId: number, creditAmount: number, currency: string, now: string): Promise<void> {
    sqlite.exec("BEGIN IMMEDIATE");
    try {
      sqlite.prepare(`UPDATE crypto_deposits SET status = 'completed', completed_at = ? WHERE id = ? AND status IN ('pending', 'confirming')`).run(now, depositId);
      sqlite.prepare(`UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) + ?) WHERE id = ?`).run(creditAmount, userId);
      sqlite.prepare(
        `INSERT INTO transactions (user_id, type, amount, description, order_id, stripe_session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).run(userId, "deposit", creditAmount.toFixed(2), `Crypto deposit (${currency}) confirmed by admin`, null, null, now);
      sqlite.exec("COMMIT");
    } catch (err) {
      try { sqlite.exec("ROLLBACK"); } catch (_) {}
      throw err;
    }
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

  async extendRental(id: number, additionalDays: number, additionalCost: number, userId: number): Promise<void> {
    sqlite.exec("BEGIN IMMEDIATE");
    try {
      const deductResult = sqlite.prepare(
        `UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) - ?) WHERE id = ? AND CAST(balance AS REAL) >= ?`
      ).run(additionalCost, userId, additionalCost);
      if (deductResult.changes === 0) {
        throw new Error("Insufficient balance");
      }

      const rental = sqlite.prepare(`SELECT * FROM rentals WHERE id = ? AND status = 'active'`).get(id) as any;
      if (!rental) throw new Error("Rental not found or not active");
      const currentExpiry = new Date(rental.expires_at);
      const newExpiry = new Date(currentExpiry.getTime() + additionalDays * 24 * 60 * 60 * 1000);
      const newDays = (rental.days || 0) + additionalDays;
      const newPrice = (parseFloat(rental.price) + additionalCost).toFixed(2);
      sqlite.prepare(`UPDATE rentals SET expires_at = ?, days = ?, price = ? WHERE id = ?`).run(newExpiry.toISOString(), newDays, newPrice, id);

      sqlite.prepare(
        `INSERT INTO transactions (user_id, type, amount, description, order_id, stripe_session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
      ).run(userId, "purchase", `-${additionalCost.toFixed(2)}`, `Rental extension (+${additionalDays} days)`, id, null, new Date().toISOString());
      sqlite.exec("COMMIT");
    } catch (err) {
      try { sqlite.exec("ROLLBACK"); } catch (_) {}
      throw err;
    }
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

  async getAllTransactions(): Promise<Transaction[]> {
    return db.select().from(transactions).orderBy(desc(transactions.id)).all();
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

  async getAllCryptoDeposits(): Promise<CryptoDeposit[]> {
    return db.select().from(cryptoDeposits).orderBy(desc(cryptoDeposits.id)).all();
  }

  async atomicDeductBalance(userId: number, amount: number): Promise<{ success: boolean; newBalance: string }> {
    const result = sqlite.prepare(
      `UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) - ?) WHERE id = ? AND CAST(balance AS REAL) >= ? RETURNING balance`
    ).get(amount, userId, amount) as { balance: string } | undefined;
    if (!result) return { success: false, newBalance: "0.00" };
    return { success: true, newBalance: result.balance };
  }

  async atomicAddBalance(userId: number, amount: number): Promise<string> {
    const result = sqlite.prepare(
      `UPDATE users SET balance = printf('%.2f', CAST(balance AS REAL) + ?) WHERE id = ? RETURNING balance`
    ).get(amount, userId) as { balance: string } | undefined;
    return result?.balance || "0.00";
  }

  async createAuditLog(userId: number | null, action: string, entity: string | null, entityId: number | null, metadata?: string, ipAddress?: string): Promise<void> {
    sqlite.prepare(
      `INSERT INTO audit_logs (user_id, action, entity, entity_id, metadata, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)`
    ).run(userId, action, entity, entityId, metadata || null, ipAddress || null, new Date().toISOString());
  }

  async getAuditLogs(limit = 100, offset = 0): Promise<any[]> {
    return sqlite.prepare(
      `SELECT al.*, u.username, u.email FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.id DESC LIMIT ? OFFSET ?`
    ).all(limit, offset) as any[];
  }

  async creditCircleDeposit(depositData: InsertCryptoDeposit, txData: InsertTransaction, userId: number, creditAmount: string): Promise<boolean> {
    try {
      sqlite.exec("BEGIN IMMEDIATE");
      try {
        const existing = sqlite.prepare(
          "SELECT id FROM crypto_deposits WHERE circle_transfer_id = ?"
        ).get(depositData.circleTransferId);
        if (existing) {
          sqlite.exec("ROLLBACK");
          return false;
        }

        db.insert(cryptoDeposits).values(depositData).run();

        const user = db.select().from(users).where(eq(users.id, userId)).get();
        if (user) {
          const currentBalance = parseFloat(user.balance);
          const newBalance = (currentBalance + parseFloat(creditAmount)).toFixed(2);
          db.update(users).set({ balance: newBalance }).where(eq(users.id, userId)).run();
        }

        db.insert(transactions).values(txData).run();
        sqlite.exec("COMMIT");
        return true;
      } catch (innerErr) {
        try { sqlite.exec("ROLLBACK"); } catch (_) {}
        throw innerErr;
      }
    } catch (err: any) {
      if (err?.message?.includes("UNIQUE constraint failed")) {
        return false;
      }
      throw err;
    }
  }

  async createPasswordResetToken(userId: number, tokenHash: string, expiresAt: string): Promise<void> {
    sqlite.prepare(`DELETE FROM password_reset_tokens WHERE user_id = ?`).run(userId);
    sqlite.prepare(
      `INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, used, created_at) VALUES (?, ?, ?, 0, ?)`
    ).run(userId, tokenHash, expiresAt, new Date().toISOString());
  }

  async getPasswordResetToken(tokenHash: string): Promise<any | undefined> {
    return sqlite.prepare(
      `SELECT * FROM password_reset_tokens WHERE token_hash = ? AND used = 0 AND expires_at > ?`
    ).get(tokenHash, new Date().toISOString()) as any;
  }

  async markPasswordResetTokenUsed(tokenHash: string): Promise<void> {
    sqlite.prepare(`UPDATE password_reset_tokens SET used = 1 WHERE token_hash = ?`).run(tokenHash);
  }

  async getPublicStats(): Promise<{ totalOrders: number; totalUsers: number; totalCountries: number }> {
    const orderCount = sqlite.prepare(`SELECT COUNT(*) as cnt FROM orders WHERE status IN ('completed','received')`).get() as any;
    const userCount = sqlite.prepare(`SELECT COUNT(*) as cnt FROM users`).get() as any;
    const countryCount = sqlite.prepare(`SELECT COUNT(DISTINCT country) as cnt FROM orders`).get() as any;
    return {
      totalOrders: orderCount?.cnt || 0,
      totalUsers: userCount?.cnt || 0,
      totalCountries: countryCount?.cnt || 0,
    };
  }
}

export const storage = new DatabaseStorage();
