import { sqliteTable, text, integer } from "drizzle-orm/sqlite-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

export const users = sqliteTable("users", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  username: text("username").notNull().unique(),
  email: text("email").notNull().unique(),
  password: text("password").notNull(),
  balance: text("balance").notNull().default("0.00"),
  apiKey: text("api_key").unique(),
  role: text("role").notNull().default("user"),
});

export const insertUserSchema = createInsertSchema(users).omit({ id: true, balance: true, apiKey: true, role: true });
export type InsertUser = z.infer<typeof insertUserSchema>;
export type User = typeof users.$inferSelect;

export const services = sqliteTable("services", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  name: text("name").notNull(),
  slug: text("slug").notNull().unique(),
  price: text("price").notNull(),
  icon: text("icon"),
  category: text("category"),
  isActive: integer("is_active").notNull().default(1),
});

export const insertServiceSchema = createInsertSchema(services).omit({ id: true });
export type InsertService = z.infer<typeof insertServiceSchema>;
export type Service = typeof services.$inferSelect;

export const orders = sqliteTable("orders", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  userId: integer("user_id").notNull(),
  serviceId: integer("service_id").notNull(),
  serviceName: text("service_name").notNull().default(""),
  phoneNumber: text("phone_number").notNull(),
  status: text("status").notNull().default("pending"),
  otpCode: text("otp_code"),
  smsMessages: text("sms_messages"),
  price: text("price").notNull(),
  country: text("country").notNull().default("us"),
  proxnumId: text("proxnum_id"),
  createdAt: text("created_at").notNull(),
  expiresAt: text("expires_at").notNull(),
  completedAt: text("completed_at"),
});

export const insertOrderSchema = createInsertSchema(orders).omit({ id: true });
export type InsertOrder = z.infer<typeof insertOrderSchema>;
export type Order = typeof orders.$inferSelect;

export const rentals = sqliteTable("rentals", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  userId: integer("user_id").notNull(),
  serviceId: integer("service_id").notNull(),
  serviceName: text("service_name").notNull().default(""),
  phoneNumber: text("phone_number").notNull(),
  status: text("status").notNull().default("active"),
  price: text("price").notNull(),
  country: text("country").notNull().default("us"),
  days: integer("days").notNull().default(7),
  proxnumId: text("proxnum_id"),
  createdAt: text("created_at").notNull(),
  expiresAt: text("expires_at").notNull(),
  cancelledAt: text("cancelled_at"),
});

export const insertRentalSchema = createInsertSchema(rentals).omit({ id: true });
export type InsertRental = z.infer<typeof insertRentalSchema>;
export type Rental = typeof rentals.$inferSelect;

export const rentalMessages = sqliteTable("rental_messages", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  rentalId: integer("rental_id").notNull(),
  sender: text("sender"),
  message: text("message").notNull(),
  receivedAt: text("received_at").notNull(),
});

export const insertRentalMessageSchema = createInsertSchema(rentalMessages).omit({ id: true });
export type InsertRentalMessage = z.infer<typeof insertRentalMessageSchema>;
export type RentalMessage = typeof rentalMessages.$inferSelect;

export const settings = sqliteTable("settings", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  key: text("key").notNull().unique(),
  value: text("value"),
});

export type Setting = typeof settings.$inferSelect;

export const uptimeLogs = sqliteTable("uptime_logs", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  status: text("status").notNull(),
  statusCode: integer("status_code").notNull(),
  latencyMs: integer("latency_ms"),
  source: text("source").notNull().default("healthz"),
  checkedAt: text("checked_at").notNull(),
});

export const insertUptimeLogSchema = createInsertSchema(uptimeLogs).omit({ id: true });
export type InsertUptimeLog = z.infer<typeof insertUptimeLogSchema>;
export type UptimeLog = typeof uptimeLogs.$inferSelect;

export const transactions = sqliteTable("transactions", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  userId: integer("user_id").notNull(),
  type: text("type").notNull(),
  amount: text("amount").notNull(),
  description: text("description"),
  orderId: integer("order_id"),
  stripeSessionId: text("stripe_session_id"),
  createdAt: text("created_at").notNull(),
});

export const insertTransactionSchema = createInsertSchema(transactions).omit({ id: true });
export type InsertTransaction = z.infer<typeof insertTransactionSchema>;
export type Transaction = typeof transactions.$inferSelect;

export const cryptoDeposits = sqliteTable("crypto_deposits", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  userId: integer("user_id").notNull(),
  currency: text("currency").notNull(),
  amount: text("amount").notNull(),
  cryptoAmount: text("crypto_amount"),
  walletAddress: text("wallet_address").notNull(),
  txHash: text("tx_hash"),
  status: text("status").notNull().default("pending"),
  createdAt: text("created_at").notNull(),
  expiresAt: text("expires_at").notNull(),
  completedAt: text("completed_at"),
});

export const insertCryptoDepositSchema = createInsertSchema(cryptoDeposits).omit({ id: true });
export type InsertCryptoDeposit = z.infer<typeof insertCryptoDepositSchema>;
export type CryptoDeposit = typeof cryptoDeposits.$inferSelect;
