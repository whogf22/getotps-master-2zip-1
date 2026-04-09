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
import { eq, and, desc, or } from "drizzle-orm";
import bcrypt from "bcryptjs";
import crypto from "crypto";

const sqlite = new Database("data.db");
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
  const existingAdmin = db.select().from(users).where(eq(users.email, "admin@getotps.com")).get();
  if (!existingAdmin) {
    const hashedPassword = await bcrypt.hash("admin123", 10);
    const apiKey = crypto.randomBytes(32).toString("hex");
    db.insert(users).values({
      username: "admin",
      email: "admin@getotps.com",
      password: hashedPassword,
      balance: "100.00",
      apiKey,
      role: "admin",
    }).run();
    console.log("Created default admin user: admin@getotps.com / admin123");
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

  createTransaction(data: InsertTransaction): Promise<Transaction>;
  getUserTransactions(userId: number): Promise<Transaction[]>;

  createCryptoDeposit(data: InsertCryptoDeposit): Promise<CryptoDeposit>;
  getCryptoDeposit(id: number): Promise<CryptoDeposit | undefined>;
  getUserCryptoDeposits(userId: number): Promise<CryptoDeposit[]>;
  updateCryptoDeposit(id: number, data: Partial<CryptoDeposit>): Promise<void>;
  getAllPendingCryptoDeposits(): Promise<CryptoDeposit[]>;
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
}

export const storage = new DatabaseStorage();
