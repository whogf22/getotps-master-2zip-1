import type { Express, Request, Response } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { proxnumApi, getCachedServices, getCachedCountries, getCachedPrices, getUSCountryCode, findCountryCode, friendlyError, type ProxnumService } from "./proxnum";
import * as circle from "./circle";
import session from "express-session";
import passport from "passport";
import { Strategy as LocalStrategy } from "passport-local";
import bcrypt from "bcryptjs";
import rateLimit from "express-rate-limit";

declare module "express-session" {
  interface SessionData {
    userId?: number;
  }
}

const SERVICE_CATEGORIES: Record<string, string> = {
  WhatsApp: "Messaging", Whatsapp: "Messaging", Telegram: "Messaging", Discord: "Messaging", Signal: "Messaging",
  Viber: "Messaging", LINE: "Messaging", WeChat: "Messaging", KakaoTalk: "Messaging",
  Google: "Tech", Microsoft: "Tech", Apple: "Tech", AWS: "Tech", GitHub: "Tech", Anthropic: "Tech",
  Facebook: "Social", Instagram: "Social", Twitter: "Social", TikTok: "Social",
  Snapchat: "Social", LinkedIn: "Social", Reddit: "Social", Pinterest: "Social",
  Amazon: "Shopping", eBay: "Shopping", Walmart: "Shopping", BestBuy: "Shopping",
  Uber: "Transport", Lyft: "Transport", DoorDash: "Food", Grubhub: "Food", UberEats: "Food",
  Airbnb: "Travel", Booking: "Travel",
  PayPal: "Finance", CashApp: "Finance", Venmo: "Finance", Chime: "Finance", Zelle: "Finance",
  Coinbase: "Crypto", Binance: "Crypto", Kraken: "Crypto",
  Netflix: "Entertainment", Spotify: "Entertainment", Hulu: "Entertainment", Disney: "Entertainment",
  Bumble: "Dating", Tinder: "Dating", Hinge: "Dating", Badoo: "Dating",
};

async function getMarkupMultiplier(): Promise<number> {
  const val = await storage.getSetting("price_multiplier");
  return val ? parseFloat(val) : 1.5;
}

async function getServiceMultiplier(service: string, country: string): Promise<number> {
  const specific = await storage.getSetting(`multiplier_${service}_${country}`);
  if (specific) return parseFloat(specific);
  const general = await storage.getSetting(`multiplier_${service}`);
  if (general) return parseFloat(general);
  return 1.0;
}

async function calculatePrice(basePrice: number, service: string, country: string): Promise<number> {
  const globalMult = await getMarkupMultiplier();
  const serviceMult = await getServiceMultiplier(service, country);
  return basePrice * globalMult * serviceMult;
}

/** Returns a safe error message — hides implementation details in production */
function safeError(err: any, fallback = "An unexpected error occurred"): string {
  if (process.env.NODE_ENV !== "production") return err?.message || fallback;
  return fallback;
}

function extractOTPFromText(text: string): string | null {
  const patterns = [
    /\b(\d{6})\b/,
    /\b(\d{4})\b/,
    /\b(\d{5})\b/,
    /\b(\d{7,8})\b/,
    /code[:\s]+(\d{4,8})/i,
    /pin[:\s]+(\d{4,8})/i,
    /verification[:\s]+(\d{4,8})/i,
  ];
  for (const p of patterns) {
    const match = text.match(p);
    if (match) return match[1];
  }
  return null;
}

async function syncProxnumServices(): Promise<void> {
  try {
    const apiServices = await getCachedServices();

    let allPrices: Record<string, Record<string, any>> = {};
    try {
      allPrices = await getCachedPrices();
    } catch (e) {
      console.error("Failed to fetch prices:", e);
    }

    const servicePriceMap = new Map<string, { basePrice: number; available: number }>();
    for (const [_countryCode, countryPrices] of Object.entries(allPrices)) {
      if (typeof countryPrices !== "object" || Array.isArray(countryPrices)) continue;
      for (const [svcCode, info] of Object.entries(countryPrices as Record<string, any>)) {
        const existing = servicePriceMap.get(svcCode);
        const price = info.sell_price || info.base_price || 0;
        const avail = info.available || 0;
        if (!existing || price < existing.basePrice) {
          servicePriceMap.set(svcCode, { basePrice: price, available: (existing?.available || 0) + avail });
        } else {
          servicePriceMap.set(svcCode, { ...existing, available: existing.available + avail });
        }
      }
    }

    const globalMult = await getMarkupMultiplier();
    const dbServices = [];
    for (const svc of apiServices) {
      const serviceCode = svc.service || "";
      const name = svc.name || serviceCode;
      const slug = serviceCode.toLowerCase();
      if (!slug) continue;

      const priceInfo = servicePriceMap.get(serviceCode);
      const basePrice = priceInfo ? priceInfo.basePrice : 0.50;
      const totalAvailable = priceInfo ? priceInfo.available : 0;
      const finalPrice = basePrice * globalMult;

      const displayName = name.split(",")[0].trim();
      dbServices.push({
        name: displayName,
        slug,
        price: finalPrice.toFixed(2),
        icon: svc.icon || null,
        category: SERVICE_CATEGORIES[displayName] || "Other",
        isActive: totalAvailable > 0 ? 1 : 0,
      });
    }

    if (dbServices.length > 0) {
      await storage.upsertServices(dbServices);
    }
    console.log(`Synced ${dbServices.length} services from Proxnum (${dbServices.filter(s => s.isActive).length} active)`);
  } catch (err) {
    console.error("Proxnum service sync error:", err);
  }
}

const CRYPTO_WALLETS: Record<string, string> = {
  BTC: process.env.WALLET_BTC || "bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh",
  ETH: process.env.WALLET_ETH || "0x71C7656EC7ab88b098defB751B7401B5f6d8976F",
  USDT_TRC20: process.env.WALLET_USDT_TRC20 || "TN2Y5mFKbE2BC3RLeFz4BEMnGpGEaVNbHv",
  USDT_ERC20: process.env.WALLET_USDT_ERC20 || "0x71C7656EC7ab88b098defB751B7401B5f6d8976F",
  USDC: process.env.WALLET_USDC || "0x71C7656EC7ab88b098defB751B7401B5f6d8976F",
  LTC: process.env.WALLET_LTC || "ltc1qw508d6qejxtdg4y5r3zarvary0c5xw7kgmn4n9",
};

const MAX_DEPOSIT_USD = 10000;

function parseId(param: string): number | null {
  const n = Number(param);
  if (isNaN(n) || !Number.isInteger(n) || n <= 0) return null;
  return n;
}

const CRYPTO_RATES: Record<string, number> = {
  BTC: 84250.00, ETH: 3420.00, USDT_TRC20: 1.00,
  USDT_ERC20: 1.00, USDC: 1.00, LTC: 92.50,
};

export async function registerRoutes(
  httpServer: Server,
  app: Express
): Promise<Server> {

  const isProduction = process.env.NODE_ENV === "production";

  if (!process.env.SESSION_SECRET) {
    if (isProduction) {
      throw new Error("SESSION_SECRET environment variable must be set in production");
    }
    console.warn("[SECURITY WARNING] SESSION_SECRET not set. Using insecure default for development only.");
  }

  // Rate limiters — login is stricter to mitigate brute-force, registration slightly more permissive
  const loginLimiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 10,
    standardHeaders: true,
    legacyHeaders: false,
    validate: { ip: false },
    message: { message: "Too many login attempts, please try again later" },
  });

  const registerLimiter = rateLimit({
    windowMs: 60 * 60 * 1000,
    max: 10,
    standardHeaders: true,
    legacyHeaders: false,
    validate: { ip: false },
    message: { message: "Too many registration attempts, please try again later" },
  });

  const apiKeyLimiter = rateLimit({
    windowMs: 60 * 1000,
    max: 60,
    standardHeaders: true,
    legacyHeaders: false,
    keyGenerator: (req) => {
      return (req.headers["x-api-key"] as string) || req.ip || "unknown";
    },
    validate: { ip: false },
    message: { error: "Rate limit exceeded" },
  });

  app.use(
    session({
      secret: process.env.SESSION_SECRET || "dev-only-secret-change-in-production",
      resave: false,
      saveUninitialized: false,
      cookie: {
        secure: isProduction,
        httpOnly: true,
        sameSite: "lax",
        maxAge: 7 * 24 * 60 * 60 * 1000,
      },
    })
  );

  app.use(passport.initialize());
  app.use(passport.session());

  passport.use(
    new LocalStrategy({ usernameField: "email" }, async (email, password, done) => {
      try {
        const user = await storage.getUserByEmail(email);
        if (!user) return done(null, false, { message: "Invalid email or password" });
        const isValid = await bcrypt.compare(password, user.password);
        if (!isValid) return done(null, false, { message: "Invalid email or password" });
        return done(null, user);
      } catch (err) {
        return done(err);
      }
    })
  );

  passport.serializeUser((user: any, done) => done(null, user.id));
  passport.deserializeUser(async (id: number, done) => {
    try {
      const user = await storage.getUser(id);
      done(null, user || false);
    } catch (err) {
      done(err);
    }
  });

  function requireAuth(req: Request, res: Response, next: any) {
    if (req.isAuthenticated()) return next();
    res.status(401).json({ message: "Unauthorized" });
  }

  function requireAdmin(req: Request, res: Response, next: any) {
    if (req.isAuthenticated() && (req.user as any)?.role === "admin") return next();
    res.status(403).json({ message: "Forbidden" });
  }

  // ========== AUTH ROUTES ==========

  app.post("/api/auth/register", registerLimiter, async (req, res) => {
    try {
      const { username, email, password } = req.body;
      if (!username || !email || !password) {
        return res.status(400).json({ message: "All fields required" });
      }

      // Validate email: cap length first to prevent ReDoS, then check structural validity
      if (typeof email !== "string" || email.length > 254) {
        return res.status(400).json({ message: "Invalid email format" });
      }
      const atIndex = email.indexOf("@");
      if (atIndex < 1 || atIndex !== email.lastIndexOf("@")) {
        return res.status(400).json({ message: "Invalid email format" });
      }
      const localPart = email.slice(0, atIndex);
      const domain = email.slice(atIndex + 1);
      // Local part and domain must not start/end with dots or have consecutive dots
      if (
        !domain.includes(".") ||
        domain.startsWith(".") || domain.endsWith(".") ||
        domain.includes("..") ||
        localPart.startsWith(".") || localPart.endsWith(".") ||
        localPart.includes("..")
      ) {
        return res.status(400).json({ message: "Invalid email format" });
      }

      // Validate username (alphanumeric + underscore, 3-32 chars)
      const usernameRegex = /^[a-zA-Z0-9_]{3,32}$/;
      if (!usernameRegex.test(username)) {
        return res.status(400).json({ message: "Username must be 3-32 characters (letters, numbers, underscores only)" });
      }

      // Enforce password strength
      if (password.length < 8) {
        return res.status(400).json({ message: "Password must be at least 8 characters" });
      }

      const existing = await storage.getUserByEmail(email);
      if (existing) return res.status(400).json({ message: "Email already registered" });
      const existingUsername = await storage.getUserByUsername(username);
      if (existingUsername) return res.status(400).json({ message: "Username already taken" });

      const hashedPassword = await bcrypt.hash(password, 12);
      const user = await storage.createUser({ username, email, password: hashedPassword });

      req.session.regenerate((err) => {
        if (err) return res.status(500).json({ message: "Session error" });
        req.login(user, (loginErr) => {
          if (loginErr) return res.status(500).json({ message: "Login failed after registration" });
          const { password: _, ...safeUser } = user;
          res.json(safeUser);
        });
      });
    } catch (err: any) {
      res.status(500).json({ message: "Registration failed" });
    }
  });

  app.post("/api/auth/login", loginLimiter, (req, res, next) => {
    passport.authenticate("local", (err: any, user: any, info: any) => {
      if (err) return res.status(500).json({ message: "Authentication error" });
      if (!user) return res.status(401).json({ message: info?.message || "Invalid credentials" });
      // Regenerate session to prevent session fixation
      req.session.regenerate((regenerateErr) => {
        if (regenerateErr) return res.status(500).json({ message: "Session error" });
        req.login(user, (loginErr) => {
          if (loginErr) return res.status(500).json({ message: "Login failed" });
          const { password: _, ...safeUser } = user;
          res.json(safeUser);
        });
      });
    })(req, res, next);
  });

  app.post("/api/auth/logout", (req, res) => {
    req.logout(() => { res.json({ message: "Logged out" }); });
  });

  app.get("/api/auth/me", requireAuth, async (req, res) => {
    const user = req.user as any;
    const freshUser = await storage.getUser(user.id);
    if (!freshUser) return res.status(404).json({ message: "User not found" });
    const { password: _, ...safeUser } = freshUser;
    res.json(safeUser);
  });

  // ========== SERVICES (Proxnum-backed) ==========

  app.get("/api/services", async (_req, res) => {
    try {
      await syncProxnumServices();
      const dbServices = await storage.getAllServices();
      res.json(dbServices);
    } catch (err) {
      const dbServices = await storage.getAllServices();
      res.json(dbServices);
    }
  });

  app.get("/api/countries", async (_req, res) => {
    try {
      const countries = await getCachedCountries();
      res.json(countries);
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.get("/api/prices", async (req, res) => {
    try {
      const { country, service } = req.query;
      const prices = await getCachedPrices(
        country as string | undefined,
        service as string | undefined
      );
      res.json(prices);
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  // ========== ORDERS (Proxnum virtual numbers) ==========

  app.post("/api/orders", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const { serviceId, serviceName, country } = req.body;
      const orderCountry = country || "us";

      if (!serviceId && !serviceName) return res.status(400).json({ message: "serviceId or serviceName required" });

      let service;
      if (serviceId) {
        service = await storage.getService(Number(serviceId));
      }
      if (!service && serviceName) {
        service = await storage.getServiceBySlug(serviceName.toLowerCase().replace(/[^a-z0-9]/g, ""));
        if (!service) {
          service = await storage.getServiceByName(serviceName);
        }
      }
      if (!service) return res.status(404).json({ message: "Service not found" });

      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });

      const price = parseFloat(service.price);
      if (isNaN(price) || price <= 0) return res.status(500).json({ message: "Invalid service price" });
      if (parseFloat(freshUser.balance) < price) return res.status(400).json({ message: "Insufficient balance" });

      const countries = await getCachedCountries();
      const resolvedCountry = findCountryCode(countries, orderCountry) || getUSCountryCode(countries);

      const priceCheck = await proxnumApi.getResellPrice(service.slug, resolvedCountry);
      if (!priceCheck.success) {
        return res.status(503).json({ message: friendlyError(priceCheck) });
      }

      const pnResult = await proxnumApi.buyVirtual(service.slug, resolvedCountry);

      if (!pnResult.success) {
        return res.status(503).json({
          message: friendlyError(pnResult),
        });
      }

      const activation = pnResult.activation || pnResult;
      const proxnumId = String(activation.activation_id || activation.id || "");
      const phoneNumber = activation.phone || activation.number || "";
      const amountPaid = activation.amount_paid != null ? Number(activation.amount_paid) : null;

      if (!phoneNumber) {
        return res.status(503).json({ message: "No numbers available right now. Try again shortly." });
      }

      const formattedPhone = phoneNumber.startsWith("+") ? phoneNumber : `+${phoneNumber}`;
      const deductResult = await storage.atomicDeductBalance(user.id, price);
      if (!deductResult.success) {
        return res.status(400).json({ message: "Insufficient balance" });
      }

      const now = new Date();
      const expiresAt = new Date(now.getTime() + 20 * 60 * 1000);

      const order = await storage.createOrder({
        userId: user.id,
        serviceId: service.id,
        serviceName: service.name,
        phoneNumber: formattedPhone,
        status: "pending",
        otpCode: null,
        smsMessages: null,
        price: service.price,
        country: resolvedCountry,
        proxnumId,
        createdAt: now.toISOString(),
        expiresAt: expiresAt.toISOString(),
        completedAt: null,
      });

      const costNote = amountPaid != null ? ` (provider cost: $${amountPaid.toFixed(6)})` : "";
      await storage.createTransaction({
        userId: user.id,
        type: "purchase",
        amount: `-${service.price}`,
        description: `${service.name} OTP number${costNote}`,
        orderId: order.id,
        stripeSessionId: null,
        createdAt: now.toISOString(),
      });

      res.json({ ...order, service });
    } catch (err: any) {
      console.error("Order error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.get("/api/orders", requireAuth, async (req, res) => {
    const user = req.user as any;
    const userOrders = await storage.getUserOrders(user.id);
    res.json(userOrders);
  });

  app.get("/api/orders/active", requireAuth, async (req, res) => {
    const user = req.user as any;
    const activeOrders = await storage.getActiveOrders(user.id);
    res.json(activeOrders);
  });

  app.get("/api/orders/:id", requireAuth, async (req, res) => {
    const id = parseId(req.params.id);
    if (!id) return res.status(400).json({ message: "Invalid order ID" });
    const user = req.user as any;
    const order = await storage.getOrder(id);
    if (!order) return res.status(404).json({ message: "Order not found" });
    if (order.userId !== user.id && (req.user as any)?.role !== "admin") {
      return res.status(403).json({ message: "Forbidden" });
    }
    res.json(order);
  });

  app.post("/api/orders/:id/check-sms", requireAuth, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ message: "Invalid order ID" });
      const user = req.user as any;
      const order = await storage.getOrder(id);
      if (!order) return res.status(404).json({ message: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (order.status !== "pending" && order.status !== "waiting") {
        return res.status(400).json({ message: "Order not in pending state" });
      }

      if (!order.proxnumId) {
        return res.status(400).json({ message: "No Proxnum activation linked" });
      }

      const pnResult = await proxnumApi.getVirtualStatus(order.proxnumId);

      if (!pnResult.success) {
        return res.json({ status: "pending", messages: [], otpCode: null });
      }

      const apiStatus = pnResult.status || "";
      const code = pnResult.code || null;
      const activation = pnResult.activation || {};
      const fullSms = activation.msg || activation.full_sms || activation.sms || "";

      if (apiStatus === "completed" && code) {
        const messages = [{ timestamp: Date.now().toString(), sender: "service", text: fullSms || `Code: ${code}` }];
        await storage.updateOrderSms(order.id, JSON.stringify(messages), code);
        return res.json({
          status: "received",
          messages,
          otpCode: code,
          fullText: fullSms || `Code: ${code}`,
        });
      }

      if (code && /^\d{4,8}$/.test(code)) {
        const messages = [{ timestamp: Date.now().toString(), sender: "service", text: fullSms || `Code: ${code}` }];
        await storage.updateOrderSms(order.id, JSON.stringify(messages), code);
        return res.json({
          status: "received",
          messages,
          otpCode: code,
          fullText: fullSms || `Code: ${code}`,
        });
      }

      if (fullSms) {
        const extracted = extractOTPFromText(fullSms);
        if (extracted) {
          const messages = [{ timestamp: Date.now().toString(), sender: "service", text: fullSms }];
          await storage.updateOrderSms(order.id, JSON.stringify(messages), extracted);
          return res.json({ status: "received", messages, otpCode: extracted, fullText: fullSms });
        }
      }

      if (apiStatus === "cancelled" || apiStatus === "expired") {
        await storage.updateOrderStatus(order.id, apiStatus);
        return res.json({ status: apiStatus, messages: [], otpCode: null });
      }

      res.json({ status: "pending", messages: [], otpCode: null });
    } catch (err: any) {
      console.error("Check SMS error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/orders/:id/cancel", requireAuth, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ message: "Invalid order ID" });
      const user = req.user as any;
      const order = await storage.getOrder(id);
      if (!order) return res.status(404).json({ message: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (order.status !== "pending" && order.status !== "waiting") {
        return res.status(400).json({ message: "Cannot cancel this order" });
      }

      if (order.proxnumId) {
        const cancelResult = await proxnumApi.cancelVirtual(order.proxnumId);
        if (cancelResult.code === "cancel_rejected") {
          return res.status(400).json({ message: friendlyError(cancelResult) });
        }
        if (!cancelResult.success && cancelResult.code !== "cancel_accepted") {
          return res.status(503).json({ message: friendlyError(cancelResult) });
        }
      }

      await storage.cancelOrder(order.id);

      const refundAmount = parseFloat(order.price);
      if (!isNaN(refundAmount) && refundAmount > 0) {
        await storage.atomicAddBalance(user.id, refundAmount);
        await storage.createTransaction({
          userId: user.id,
          type: "refund",
          amount: order.price,
          description: "Order cancelled - refund",
          orderId: order.id,
          stripeSessionId: null,
          createdAt: new Date().toISOString(),
        });
      }

      res.json({ message: "Order cancelled and refunded" });
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/orders/:id/resend", requireAuth, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ message: "Invalid order ID" });
      const user = req.user as any;
      const order = await storage.getOrder(id);
      if (!order) return res.status(404).json({ message: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (order.status !== "pending" && order.status !== "waiting") {
        return res.status(400).json({ message: "Cannot resend for this order" });
      }
      if (!order.proxnumId) {
        return res.status(400).json({ message: "No Proxnum activation linked" });
      }

      const pnResult = await proxnumApi.resendVirtual(order.proxnumId);

      if (!pnResult.success) {
        return res.status(400).json({ message: friendlyError(pnResult) });
      }

      const newActivation = pnResult.activation;
      if (newActivation && newActivation.activation_id) {
        await storage.updateOrderProxnumId(order.id, String(newActivation.activation_id));
        const newPhone = newActivation.phone || "";
        if (newPhone) {
          const formattedPhone = newPhone.startsWith("+") ? newPhone : `+${newPhone}`;
          await storage.updateOrderPhone(order.id, formattedPhone);
        }
      }

      res.json({ message: "Resend requested successfully", activation: newActivation });
    } catch (err: any) {
      console.error("Resend error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  // ========== RENTALS (Proxnum rental numbers) ==========

  app.post("/api/rentals", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const { serviceId, serviceName, country, days } = req.body;
      const rentalCountry = country || "us";
      const rentalDays = days || 7;

      if (!serviceId && !serviceName) return res.status(400).json({ message: "serviceId or serviceName required" });

      let service;
      if (serviceId) service = await storage.getService(Number(serviceId));
      if (!service && serviceName) {
        service = await storage.getServiceBySlug(serviceName.toLowerCase().replace(/[^a-z0-9]/g, ""));
        if (!service) service = await storage.getServiceByName(serviceName);
      }
      if (!service) return res.status(404).json({ message: "Service not found" });

      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });

      const countries = await getCachedCountries();
      const resolvedCountry = findCountryCode(countries, rentalCountry) || getUSCountryCode(countries);

      let rentalPriceData;
      try {
        rentalPriceData = await proxnumApi.getRentalPrices(service.slug, resolvedCountry);
      } catch (e) {}

      const baseDayPrice = rentalPriceData?.price
        ? parseFloat(rentalPriceData.price)
        : parseFloat(service.price) * 2;
      const totalBase = baseDayPrice * rentalDays;
      const finalPrice = await calculatePrice(totalBase, service.slug, resolvedCountry);

      if (parseFloat(freshUser.balance) < finalPrice) return res.status(400).json({ message: "Insufficient balance" });

      const pnResult = await proxnumApi.buyRental(service.slug, resolvedCountry, rentalDays);

      if (pnResult.error) {
        return res.status(503).json({
          message: pnResult.error.message || "No rental numbers available. Try again later.",
        });
      }

      const proxnumId = String(pnResult.id || pnResult.rental_id || "");
      const phoneNumber = pnResult.number || pnResult.phone || "";

      if (!phoneNumber) {
        return res.status(503).json({ message: "No rental numbers available right now." });
      }

      const formattedPhone = phoneNumber.startsWith("+") ? phoneNumber : `+${phoneNumber}`;
      const deductResult = await storage.atomicDeductBalance(user.id, finalPrice);
      if (!deductResult.success) {
        return res.status(400).json({ message: "Insufficient balance" });
      }

      const now = new Date();
      const expiresAt = new Date(now.getTime() + rentalDays * 24 * 60 * 60 * 1000);

      const rental = await storage.createRental({
        userId: user.id,
        serviceId: service.id,
        serviceName: service.name,
        phoneNumber: formattedPhone,
        status: "active",
        price: finalPrice.toFixed(2),
        country: rentalCountry,
        days: rentalDays,
        proxnumId,
        createdAt: now.toISOString(),
        expiresAt: expiresAt.toISOString(),
        cancelledAt: null,
      });

      await storage.createTransaction({
        userId: user.id,
        type: "purchase",
        amount: `-${finalPrice.toFixed(2)}`,
        description: `${service.name} rental (${rentalDays} days)`,
        orderId: rental.id,
        stripeSessionId: null,
        createdAt: now.toISOString(),
      });

      res.json(rental);
    } catch (err: any) {
      console.error("Rental error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.get("/api/rentals", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json(await storage.getUserRentals(user.id));
  });

  app.get("/api/rentals/active", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json(await storage.getActiveRentals(user.id));
  });

  app.get("/api/rentals/:id", requireAuth, async (req, res) => {
    const id = parseId(req.params.id);
    if (!id) return res.status(400).json({ message: "Invalid rental ID" });
    const user = req.user as any;
    const rental = await storage.getRental(id);
    if (!rental) return res.status(404).json({ message: "Rental not found" });
    if (rental.userId !== user.id && (req.user as any)?.role !== "admin") {
      return res.status(403).json({ message: "Forbidden" });
    }
    res.json(rental);
  });

  app.get("/api/rentals/:id/messages", requireAuth, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ message: "Invalid rental ID" });
      const user = req.user as any;
      const rental = await storage.getRental(id);
      if (!rental) return res.status(404).json({ message: "Rental not found" });
      if (rental.userId !== user.id) return res.status(403).json({ message: "Forbidden" });

      if (rental.proxnumId && rental.status === "active") {
        try {
          const pnResult = await proxnumApi.getRentalMessages(rental.proxnumId);
          const apiMessages = pnResult.data || pnResult;
          if (Array.isArray(apiMessages)) {
            for (const msg of apiMessages) {
              const existingMessages = await storage.getRentalMessages(rental.id);
              const msgText = msg.message || msg.text || msg.sms || "";
              const alreadyStored = existingMessages.some(
                (m) => m.message === msgText && m.sender === (msg.sender || msg.from || "")
              );
              if (!alreadyStored && msgText) {
                await storage.createRentalMessage({
                  rentalId: rental.id,
                  sender: msg.sender || msg.from || null,
                  message: msgText,
                  receivedAt: msg.received_at || msg.timestamp || new Date().toISOString(),
                });
              }
            }
          }
        } catch (e) {
          console.error("Fetch rental messages error:", e);
        }
      }

      const messages = await storage.getRentalMessages(rental.id);
      res.json(messages);
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/rentals/:id/cancel", requireAuth, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ message: "Invalid rental ID" });
      const user = req.user as any;
      const rental = await storage.getRental(id);
      if (!rental) return res.status(404).json({ message: "Rental not found" });
      if (rental.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (rental.status !== "active") return res.status(400).json({ message: "Rental is not active" });

      if (rental.proxnumId) {
        try {
          await proxnumApi.cancelRental(rental.proxnumId);
        } catch (e) {
          console.error("Proxnum rental cancel error:", e);
        }
      }

      await storage.cancelRental(rental.id);
      res.json({ message: "Rental cancelled" });
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  // ========== CRYPTO DEPOSITS ==========

  app.get("/api/balance", requireAuth, async (req, res) => {
    const user = req.user as any;
    const freshUser = await storage.getUser(user.id);
    res.json({ balance: freshUser?.balance || "0.00" });
  });

  app.get("/api/circle/configured", requireAuth, (_req, res) => {
    res.json({ configured: circle.isCircleConfigured() });
  });

  app.get("/api/circle/wallet", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      let freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });

      if (!freshUser.circleWalletId && circle.isCircleConfigured()) {
        try {
          const walletSetId = await circle.getOrCreateDefaultWalletSet();
          const wallet = await circle.createUserWallet(walletSetId, "ETH");
          await storage.updateUserCircleWallet(freshUser.id, wallet.walletId, wallet.address);
          freshUser = (await storage.getUser(freshUser.id))!;
        } catch (e) {
          console.error("Auto-create Circle wallet failed:", e);
        }
      }

      if (freshUser.circleWalletId && freshUser.circleWalletAddress) {
        let balance = "0";
        try {
          const walletBalance = await circle.getWalletBalance(freshUser.circleWalletId);
          const usdcBalance = walletBalance.balances.find(
            b => b.tokenSymbol === "USDC" || b.tokenName?.includes("USDC") || b.tokenName?.includes("USD Coin")
          );
          if (usdcBalance) balance = usdcBalance.amount;
        } catch (e) {}
        return res.json({
          walletId: freshUser.circleWalletId,
          address: freshUser.circleWalletAddress,
          balance,
          hasWallet: true,
        });
      }

      res.json({ hasWallet: false, walletId: null, address: null, balance: "0" });
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/circle/wallet/create", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });

      if (freshUser.circleWalletId && freshUser.circleWalletAddress) {
        return res.json({
          walletId: freshUser.circleWalletId,
          address: freshUser.circleWalletAddress,
          message: "Wallet already exists",
        });
      }

      if (!circle.isCircleConfigured()) {
        return res.status(503).json({ message: "Circle wallet service is not configured. Contact support." });
      }

      const walletSetId = await circle.getOrCreateDefaultWalletSet();
      const blockchain = req.body?.blockchain || "ETH";
      const wallet = await circle.createUserWallet(walletSetId, blockchain);

      await storage.updateUserCircleWallet(user.id, wallet.walletId, wallet.address);

      res.json({
        walletId: wallet.walletId,
        address: wallet.address,
        blockchain: wallet.blockchain,
        message: "Wallet created successfully",
      });
    } catch (err: any) {
      console.error("Circle wallet creation error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/circle/check-deposits", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const freshUser = await storage.getUser(user.id);
      if (!freshUser?.circleWalletId) {
        return res.status(400).json({ message: "No Circle wallet found" });
      }

      const txList = await circle.listWalletTransactions(freshUser.circleWalletId);
      const inboundConfirmed = txList.filter(tx =>
        tx.type === "INBOUND" && tx.state === "CONFIRMED"
      );

      const tokenCache = new Map<string, { name: string; symbol: string } | null>();
      const inboundUsdc: circle.CircleTransaction[] = [];
      for (const tx of inboundConfirmed) {
        if (!tx.tokenId) continue;
        if (!tokenCache.has(tx.tokenId)) {
          tokenCache.set(tx.tokenId, await circle.getTokenInfo(tx.tokenId));
        }
        const tokenInfo = tokenCache.get(tx.tokenId);
        if (tokenInfo && (tokenInfo.symbol === "USDC" || tokenInfo.name.includes("USDC") || tokenInfo.name.includes("USD Coin"))) {
          inboundUsdc.push(tx);
        }
      }

      const existingDeposits = await storage.getUserCryptoDeposits(freshUser.id);
      const recordedTransferIds = new Set(
        existingDeposits
          .map(d => d.circleTransferId)
          .filter(Boolean)
      );
      const recordedTxHashes = new Set(
        existingDeposits
          .map(d => d.txHash)
          .filter(Boolean)
      );

      let credited = 0;
      for (const tx of inboundUsdc) {
        if (recordedTransferIds.has(tx.id) || (tx.txHash && recordedTxHashes.has(tx.txHash))) {
          continue;
        }

        const rawAmount = Array.isArray(tx.amounts) && tx.amounts.length > 0 ? String(tx.amounts[0]) : "0";
        const usdAmount = parseFloat(rawAmount);
        if (isNaN(usdAmount) || usdAmount <= 0) continue;

        const now = new Date().toISOString();
        const wasInserted = await storage.creditCircleDeposit(
          {
            userId: freshUser.id,
            currency: "USDC",
            amount: usdAmount.toFixed(2),
            cryptoAmount: rawAmount,
            walletAddress: freshUser.circleWalletAddress || "",
            txHash: tx.txHash || null,
            circleTransferId: tx.id,
            status: "completed",
            createdAt: tx.createDate || now,
            expiresAt: now,
            completedAt: now,
          },
          {
            userId: freshUser.id,
            type: "deposit",
            amount: usdAmount.toFixed(2),
            description: `USDC deposit via Circle wallet (auto-detected)`,
            orderId: null,
            stripeSessionId: null,
            createdAt: now,
          },
          freshUser.id,
          usdAmount.toFixed(2)
        );

        if (wasInserted) {
          recordedTransferIds.add(tx.id);
          if (tx.txHash) recordedTxHashes.add(tx.txHash);
          credited++;
        }
      }

      const updatedUser = await storage.getUser(freshUser.id);
      res.json({
        message: credited > 0
          ? `${credited} new deposit(s) detected and credited`
          : "No new deposits found",
        credited,
        newBalance: updatedUser?.balance || freshUser.balance,
      });
    } catch (err: any) {
      console.error("Circle deposit check error:", err);
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.get("/api/crypto/currencies", requireAuth, (_req, res) => {
    if (circle.isCircleConfigured()) {
      return res.json([]);
    }
    const currencies = Object.entries(CRYPTO_WALLETS).map(([key, address]) => ({
      id: key,
      name: key === "USDT_TRC20" ? "USDT (TRC20)" : key === "USDT_ERC20" ? "USDT (ERC20)" : key,
      network: key === "BTC" ? "Bitcoin" : key === "ETH" ? "Ethereum" : key === "USDT_TRC20" ? "Tron" : key === "USDT_ERC20" ? "Ethereum" : key === "USDC" ? "Ethereum" : key === "LTC" ? "Litecoin" : "",
      address,
      rate: CRYPTO_RATES[key],
    }));
    res.json(currencies);
  });

  app.post("/api/crypto/create-deposit", requireAuth, async (req, res) => {
    if (circle.isCircleConfigured()) {
      return res.status(400).json({ message: "Manual deposits are disabled. Use Circle USDC wallet instead." });
    }
    try {
      const user = req.user as any;
      const { currency, amount } = req.body;
      if (!currency || !amount) return res.status(400).json({ message: "Currency and amount are required" });
      const usdAmount = parseFloat(amount);
      if (isNaN(usdAmount) || usdAmount < 1) return res.status(400).json({ message: "Minimum deposit is $1.00" });
      if (usdAmount > MAX_DEPOSIT_USD) return res.status(400).json({ message: `Maximum deposit is $${MAX_DEPOSIT_USD}` });
      const walletAddress = CRYPTO_WALLETS[currency];
      if (!walletAddress) return res.status(400).json({ message: "Unsupported currency" });
      const rate = CRYPTO_RATES[currency];
      const cryptoAmount = (usdAmount / rate).toFixed(8);
      const now = new Date();
      const expiresAt = new Date(now.getTime() + 60 * 60 * 1000);
      const deposit = await storage.createCryptoDeposit({
        userId: user.id, currency, amount: usdAmount.toFixed(2), cryptoAmount,
        walletAddress, txHash: null, status: "pending",
        createdAt: now.toISOString(), expiresAt: expiresAt.toISOString(), completedAt: null,
      });
      res.json(deposit);
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.get("/api/crypto/deposits", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json(await storage.getUserCryptoDeposits(user.id));
  });

  app.post("/api/crypto/:id/submit-hash", requireAuth, async (req, res) => {
    if (circle.isCircleConfigured()) {
      return res.status(400).json({ message: "Manual deposits are disabled. Use Circle USDC wallet instead." });
    }
    try {
      const user = req.user as any;
      const { txHash } = req.body;
      if (!txHash || typeof txHash !== "string") {
        return res.status(400).json({ message: "Transaction hash is required" });
      }
      const normalizedHash = txHash.startsWith("0x") || txHash.startsWith("0X")
        ? txHash.slice(2)
        : txHash;
      if (!/^[a-fA-F0-9]{64}$/.test(normalizedHash)) {
        return res.status(400).json({ message: "Invalid transaction hash format (expected 64 hex characters, optionally 0x-prefixed)" });
      }
      const depositId = parseId(req.params.id);
      if (!depositId) return res.status(400).json({ message: "Invalid deposit ID" });
      const deposit = await storage.getCryptoDeposit(depositId);
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (deposit.status !== "pending") return res.status(400).json({ message: "Deposit is not pending" });
      await storage.updateCryptoDeposit(deposit.id, { txHash, status: "confirming" });
      res.json({ message: "Transaction hash submitted. Awaiting admin confirmation." });
    } catch (err: any) { res.status(500).json({ message: "Submission failed" }); }
  });

  app.post("/api/admin/crypto/:id/confirm", requireAdmin, async (req, res) => {
    try {
      const depositId = parseId(req.params.id);
      if (!depositId) return res.status(400).json({ message: "Invalid deposit ID" });
      const deposit = await storage.getCryptoDeposit(depositId);
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.status === "completed") return res.status(400).json({ message: "Already completed" });
      const now = new Date().toISOString();
      await storage.updateCryptoDeposit(deposit.id, { status: "completed", completedAt: now });
      const creditAmount = parseFloat(deposit.amount);
      if (!isNaN(creditAmount) && creditAmount > 0) {
        await storage.atomicAddBalance(deposit.userId, creditAmount);
        await storage.createTransaction({
          userId: deposit.userId, type: "deposit", amount: deposit.amount,
          description: `Crypto deposit (${deposit.currency}) confirmed by admin`,
          orderId: null, stripeSessionId: null, createdAt: now,
        });
      }
      const admin = req.user as any;
      await storage.createAuditLog(admin.id, "confirm_deposit", "crypto_deposit", deposit.id, `$${deposit.amount} (${deposit.currency}) for user ${deposit.userId}`);
      res.json({ message: "Deposit confirmed and balance credited" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.get("/api/admin/crypto/pending", requireAdmin, async (_req, res) => {
    const deposits = await storage.getAllPendingCryptoDeposits();
    const enriched = await Promise.all(deposits.map(async (d) => {
      const u = await storage.getUser(d.userId);
      return { ...d, username: u?.username || "Unknown", email: u?.email || "" };
    }));
    res.json(enriched);
  });

  app.get("/api/admin/crypto/all", requireAdmin, async (_req, res) => {
    const deposits = await storage.getAllCryptoDeposits();
    const enriched = await Promise.all(deposits.map(async (d) => {
      const u = await storage.getUser(d.userId);
      return { ...d, username: u?.username || "Unknown", email: u?.email || "" };
    }));
    res.json(enriched);
  });

  app.get("/api/transactions", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json(await storage.getUserTransactions(user.id));
  });

  // ========== ADMIN ==========

  app.get("/api/admin/users", requireAdmin, async (_req, res) => {
    const allUsers = await storage.getAllUsers();
    const allOrders = await storage.getAllOrders();
    const result = allUsers.map(({ password: _, ...u }) => {
      const userOrders = allOrders.filter(o => o.userId === u.id);
      const lastOrder = userOrders.length > 0
        ? userOrders.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())[0]
        : null;
      return {
        ...u,
        orderCount: userOrders.length,
        lastOrderAt: lastOrder?.createdAt || null,
      };
    });
    result.sort((a, b) => b.id - a.id);
    res.json(result);
  });

  app.get("/api/admin/stats", requireAdmin, async (_req, res) => {
    const allUsers = await storage.getAllUsers();
    const allOrders = await storage.getAllOrders();
    const allRentals = await storage.getAllRentals();
    const completedOrders = allOrders.filter(o => o.status === "completed" || o.status === "received");
    const pendingOrders = allOrders.filter(o => o.status === "pending" || o.status === "waiting");
    const revenue = completedOrders.reduce((sum, o) => sum + parseFloat(o.price), 0)
      + allRentals.filter(r => r.status !== "cancelled").reduce((sum, r) => sum + parseFloat(r.price), 0);

    const todayStart = new Date();
    todayStart.setHours(0, 0, 0, 0);
    const todayOrders = allOrders.filter(o =>
      (o.status === "completed" || o.status === "received") &&
      new Date(o.createdAt) >= todayStart
    );
    const todayRevenue = todayOrders.reduce((sum, o) => sum + parseFloat(o.price), 0);

    const totalBalances = allUsers.reduce((sum, u) => sum + parseFloat(u.balance), 0);

    const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
    const activeUsers = allUsers.filter(u =>
      allOrders.some(o => o.userId === u.id && new Date(o.createdAt) >= oneDayAgo)
    ).length;

    let proxnumBalance = "N/A";
    try {
      const balResult = await proxnumApi.getUserBalance();
      const balData = balResult.data || balResult;
      if (balData.balance !== undefined) proxnumBalance = `$${balData.balance}`;
    } catch (e) {}

    res.json({
      totalUsers: allUsers.length,
      activeUsers,
      totalOrders: allOrders.length,
      pendingOrders: pendingOrders.length,
      totalRentals: allRentals.length,
      completedOrders: completedOrders.length,
      revenue: revenue.toFixed(2),
      todayRevenue: todayRevenue.toFixed(2),
      totalBalances: totalBalances.toFixed(2),
      proxnumBalance,
    });
  });

  app.put("/api/admin/services/:id", requireAdmin, async (req, res) => {
    try {
      // Whitelist allowed fields to prevent mass assignment
      const { name, price, icon, category, isActive } = req.body;
      const allowedFields: Record<string, any> = {};

      // Reject inputs containing HTML/script characters rather than attempting to strip them
      const containsHtml = (v: string) => v.includes("<") || v.includes(">");

      if (name !== undefined) {
        const n = String(name).slice(0, 100);
        if (containsHtml(n)) return res.status(400).json({ message: "Service name must not contain HTML" });
        allowedFields.name = n;
      }
      if (price !== undefined) {
        const p = parseFloat(price);
        if (isNaN(p) || p < 0) return res.status(400).json({ message: "Invalid price" });
        allowedFields.price = p.toFixed(2);
      }
      if (icon !== undefined) {
        const ic = icon ? String(icon).slice(0, 200) : null;
        if (ic && containsHtml(ic)) return res.status(400).json({ message: "Icon must not contain HTML" });
        allowedFields.icon = ic;
      }
      if (category !== undefined) {
        const cat = category ? String(category).slice(0, 50) : null;
        if (cat && containsHtml(cat)) return res.status(400).json({ message: "Category must not contain HTML" });
        allowedFields.category = cat;
      }
      if (isActive !== undefined) allowedFields.isActive = isActive ? 1 : 0;
      const serviceId = parseId(req.params.id);
      if (!serviceId) return res.status(400).json({ message: "Invalid service ID" });
      await storage.updateService(serviceId, allowedFields);
      const admin = req.user as any;
      await storage.createAuditLog(admin.id, "update_service", "service", serviceId, JSON.stringify(allowedFields));
      res.json({ message: "Service updated" });
    } catch (err: any) { res.status(500).json({ message: "Service update failed" }); }
  });

  app.get("/api/admin/settings", requireAdmin, async (_req, res) => {
    const multiplier = await storage.getSetting("price_multiplier");
    const defaultCountry = await storage.getSetting("default_country");
    const allSettings = await storage.getAllSettings();
    const serviceMultipliers: Record<string, string> = {};
    for (const s of allSettings) {
      if (s.key.startsWith("multiplier_")) {
        const rest = s.key.replace("multiplier_", "");
        serviceMultipliers[rest] = s.value;
      }
    }
    res.json({
      price_multiplier: multiplier || "1.5",
      default_country: defaultCountry || "us",
      service_multipliers: serviceMultipliers,
    });
  });

  app.put("/api/admin/settings", requireAdmin, async (req, res) => {
    try {
      const { price_multiplier, default_country, service_multipliers } = req.body;
      if (price_multiplier !== undefined) {
        const mult = parseFloat(String(price_multiplier));
        if (isNaN(mult) || mult < 0.1 || mult > 100) {
          return res.status(400).json({ message: "Price multiplier must be between 0.1 and 100" });
        }
        await storage.setSetting("price_multiplier", mult.toString());
      }
      if (default_country !== undefined) {
        const country = String(default_country).toLowerCase().replace(/[^a-z]/g, "").slice(0, 5);
        if (!country) return res.status(400).json({ message: "Invalid country code" });
        await storage.setSetting("default_country", country);
      }
      if (service_multipliers && typeof service_multipliers === "object") {
        for (const [slug, val] of Object.entries(service_multipliers)) {
          if (val === null || val === "" || val === "0") {
            await storage.deleteSetting(`multiplier_${slug}`);
          } else {
            const multVal = parseFloat(String(val));
            if (isNaN(multVal) || multVal < 0.1 || multVal > 100) {
              return res.status(400).json({ message: `Invalid multiplier for ${slug}: must be between 0.1 and 100` });
            }
            await storage.setSetting(`multiplier_${slug}`, multVal.toString());
          }
        }
      }
      const admin = req.user as any;
      await storage.createAuditLog(admin.id, "update_settings", "settings", null, JSON.stringify(req.body));
      res.json({ message: "Settings updated" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/admin/users/:id/add-balance", requireAdmin, async (req, res) => {
    try {
      const userId = parseId(req.params.id);
      if (!userId) return res.status(400).json({ message: "Invalid user ID" });
      const { amount, description } = req.body;
      const addAmount = Number(amount);
      if (!amount || isNaN(addAmount) || addAmount <= 0 || addAmount > MAX_DEPOSIT_USD) {
        return res.status(400).json({ message: `Valid positive amount required (max $${MAX_DEPOSIT_USD})` });
      }
      const targetUser = await storage.getUser(userId);
      if (!targetUser) return res.status(404).json({ message: "User not found" });
      const newBalance = await storage.atomicAddBalance(targetUser.id, addAmount);
      await storage.createTransaction({
        userId: targetUser.id,
        type: "deposit",
        amount: addAmount.toFixed(2),
        description: description || "Admin balance adjustment",
        orderId: null,
        stripeSessionId: null,
        createdAt: new Date().toISOString(),
      });
      const admin = req.user as any;
      await storage.createAuditLog(admin.id, "add_balance", "user", targetUser.id, `$${addAmount.toFixed(2)}: ${description || "Admin balance adjustment"}`);
      res.json({ message: "Balance updated", newBalance });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/admin/crypto/:id/reject", requireAdmin, async (req, res) => {
    try {
      const depositId = parseId(req.params.id);
      if (!depositId) return res.status(400).json({ message: "Invalid deposit ID" });
      const deposit = await storage.getCryptoDeposit(depositId);
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.status === "completed") return res.status(400).json({ message: "Cannot reject completed deposit" });
      await storage.updateCryptoDeposit(deposit.id, { status: "rejected", completedAt: new Date().toISOString() });
      const admin = req.user as any;
      await storage.createAuditLog(admin.id, "reject_deposit", "crypto_deposit", deposit.id, `$${deposit.amount} (${deposit.currency}) for user ${deposit.userId}`);
      res.json({ message: "Deposit rejected" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.get("/api/admin/transactions", requireAdmin, async (_req, res) => {
    try {
      const allUsers = await storage.getAllUsers();
      const txns: any[] = [];
      for (const u of allUsers) {
        const userTxns = await storage.getUserTransactions(u.id);
        txns.push(...userTxns.map(t => ({ ...t, username: u.username, email: u.email })));
      }
      txns.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
      res.json(txns.slice(0, 100));
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  // ========== API v1 (API key auth) ==========

  async function requireApiKey(req: Request, res: Response, next: any) {
    // Only accept API key from header; query string keys leak into logs/referrers
    const key = req.headers["x-api-key"] as string;
    if (!key) return res.status(401).json({ error: "API key required (use x-api-key header)" });
    const user = await storage.getUserByApiKey(key);
    if (!user) return res.status(401).json({ error: "Invalid API key" });
    (req as any).apiUser = user;
    next();
  }

  app.get("/api/v1/services", async (_req, res) => {
    const allServices = await storage.getAllServices();
    res.json({ services: allServices });
  });

  app.get("/api/v1/balance", apiKeyLimiter, requireApiKey, async (req, res) => {
    const user = (req as any).apiUser;
    res.json({ balance: user.balance });
  });

  app.post("/api/v1/order", apiKeyLimiter, requireApiKey, async (req, res) => {
    try {
      const user = (req as any).apiUser;
      const { service, country } = req.body;
      const orderCountry = country || "us";
      if (!service) return res.status(400).json({ error: "service name required" });

      const allServices = await storage.getAllServices();
      const svc = allServices.find(s => s.name === service || s.slug === service || s.id === Number(service));
      if (!svc) return res.status(404).json({ error: "Service not found" });

      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ error: "User not found" });
      const balance = parseFloat(freshUser.balance);
      const price = parseFloat(svc.price);
      if (balance < price) return res.status(400).json({ error: "Insufficient balance" });

      const countries = await getCachedCountries();
      const resolvedCountry = findCountryCode(countries, orderCountry) || getUSCountryCode(countries);

      const priceCheck = await proxnumApi.getResellPrice(svc.slug, resolvedCountry);
      if (!priceCheck.success) {
        return res.status(503).json({ error: friendlyError(priceCheck) });
      }

      const pnResult = await proxnumApi.buyVirtual(svc.slug, resolvedCountry);
      if (!pnResult.success) {
        return res.status(503).json({ error: friendlyError(pnResult) });
      }

      const activation = pnResult.activation || pnResult;
      const proxnumId = String(activation.activation_id || activation.id || "");
      const phoneNumber = activation.phone || activation.number || "";
      const amountPaid = activation.amount_paid != null ? Number(activation.amount_paid) : null;
      if (!phoneNumber) {
        return res.status(503).json({ error: "No numbers available" });
      }

      const formattedPhone = phoneNumber.startsWith("+") ? phoneNumber : `+${phoneNumber}`;
      const deductResult = await storage.atomicDeductBalance(user.id, price);
      if (!deductResult.success) {
        return res.status(400).json({ error: "Insufficient balance" });
      }

      const now = new Date();
      const expiresAt = new Date(now.getTime() + 20 * 60 * 1000);

      const order = await storage.createOrder({
        userId: user.id, serviceId: svc.id, serviceName: svc.name,
        phoneNumber: formattedPhone, status: "pending", otpCode: null,
        smsMessages: null,
        price: svc.price, country: resolvedCountry, proxnumId,
        createdAt: now.toISOString(), expiresAt: expiresAt.toISOString(), completedAt: null,
      });

      const costNote = amountPaid != null ? ` (provider cost: $${amountPaid.toFixed(6)})` : "";
      await storage.createTransaction({
        userId: user.id, type: "purchase", amount: `-${svc.price}`,
        description: `${svc.name} OTP number${costNote}`, orderId: order.id,
        stripeSessionId: null, createdAt: now.toISOString(),
      });

      res.json({ orderId: order.id, phoneNumber: formattedPhone, status: "pending", expiresAt: order.expiresAt });
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  app.get("/api/v1/order/:id", apiKeyLimiter, requireApiKey, async (req, res) => {
    const id = parseId(req.params.id);
    if (!id) return res.status(400).json({ error: "Invalid order ID" });
    const user = (req as any).apiUser;
    const order = await storage.getOrder(id);
    if (!order) return res.status(404).json({ error: "Order not found" });
    if (order.userId !== user.id) return res.status(403).json({ error: "Forbidden" });

    if ((order.status === "pending" || order.status === "waiting") && order.proxnumId) {
      try {
        const pnResult = await proxnumApi.getVirtualStatus(order.proxnumId);
        if (pnResult.success) {
          const apiStatus = pnResult.status || "";
          const code = pnResult.code || null;
          const activation = pnResult.activation || {};
          const fullSms = activation.msg || activation.full_sms || activation.sms || "";

          if ((apiStatus === "completed" && code) || (code && /^\d{4,8}$/.test(code))) {
            const messages = [{ timestamp: Date.now().toString(), sender: "service", text: fullSms || `Code: ${code}` }];
            await storage.updateOrderSms(order.id, JSON.stringify(messages), code);
            return res.json({
              orderId: order.id, phoneNumber: order.phoneNumber,
              status: "received", otpCode: code,
              messages: [fullSms || `Code: ${code}`],
              expiresAt: order.expiresAt,
            });
          }

          if (fullSms) {
            const extracted = extractOTPFromText(fullSms);
            if (extracted) {
              const messages = [{ timestamp: Date.now().toString(), sender: "service", text: fullSms }];
              await storage.updateOrderSms(order.id, JSON.stringify(messages), extracted);
              return res.json({
                orderId: order.id, phoneNumber: order.phoneNumber,
                status: "received", otpCode: extracted,
                messages: [fullSms],
                expiresAt: order.expiresAt,
              });
            }
          }
        }
      } catch (e) {}
    }

    res.json({
      orderId: order.id, phoneNumber: order.phoneNumber,
      status: order.status, otpCode: order.otpCode,
      messages: order.smsMessages ? (() => { try { const p = JSON.parse(order.smsMessages); return Array.isArray(p) ? p.map((m: any) => m.text) : []; } catch { return []; } })() : [],
      expiresAt: order.expiresAt,
    });
  });

  app.post("/api/v1/order/:id/cancel", apiKeyLimiter, requireApiKey, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ error: "Invalid order ID" });
      const user = (req as any).apiUser;
      const order = await storage.getOrder(id);
      if (!order) return res.status(404).json({ error: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ error: "Forbidden" });
      if (order.status !== "pending" && order.status !== "waiting") return res.status(400).json({ error: "Cannot cancel" });

      if (order.proxnumId) {
        const cancelResult = await proxnumApi.cancelVirtual(order.proxnumId);
        if (cancelResult.code === "cancel_rejected") {
          return res.status(400).json({ error: friendlyError(cancelResult) });
        }
        if (!cancelResult.success && cancelResult.code !== "cancel_accepted") {
          return res.status(503).json({ error: friendlyError(cancelResult) });
        }
      }

      await storage.cancelOrder(order.id);
      const refundAmount = parseFloat(order.price);
      if (!isNaN(refundAmount) && refundAmount > 0) {
        await storage.atomicAddBalance(user.id, refundAmount);
      }
      res.json({ message: "Order cancelled and refunded" });
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  app.get("/api/v1/price", apiKeyLimiter, requireApiKey, async (req, res) => {
    try {
      const { service, country } = req.query;
      if (!service || !country) return res.status(400).json({ error: "service and country required" });
      const pnResult = await proxnumApi.getResellPrice(String(service), String(country));
      if (!pnResult.success) {
        return res.status(400).json({ error: friendlyError(pnResult) });
      }
      res.json(pnResult);
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  app.post("/api/v1/order/:id/resend", apiKeyLimiter, requireApiKey, async (req, res) => {
    try {
      const id = parseId(req.params.id);
      if (!id) return res.status(400).json({ error: "Invalid order ID" });
      const user = (req as any).apiUser;
      const order = await storage.getOrder(id);
      if (!order) return res.status(404).json({ error: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ error: "Forbidden" });
      if (order.status !== "pending" && order.status !== "waiting") return res.status(400).json({ error: "Cannot resend for this order" });
      if (!order.proxnumId) return res.status(400).json({ error: "No activation linked" });

      const pnResult = await proxnumApi.resendVirtual(order.proxnumId);
      if (!pnResult.success) {
        return res.status(400).json({ error: friendlyError(pnResult) });
      }

      const newActivation = pnResult.activation;
      if (newActivation && newActivation.activation_id) {
        await storage.updateOrderProxnumId(order.id, String(newActivation.activation_id));
        const newPhone = newActivation.phone || "";
        if (newPhone) {
          const formattedPhone = newPhone.startsWith("+") ? newPhone : `+${newPhone}`;
          await storage.updateOrderPhone(order.id, formattedPhone);
        }
      }

      res.json({ message: "Resend requested successfully", activation: newActivation });
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  app.post("/api/v1/rental", apiKeyLimiter, requireApiKey, async (req, res) => {
    try {
      const user = (req as any).apiUser;
      const { service, country, days } = req.body;
      const rentalCountry = country || "us";
      const rentalDays = days || 7;
      if (!service) return res.status(400).json({ error: "service name required" });

      const allServices = await storage.getAllServices();
      const svc = allServices.find(s => s.name === service || s.slug === service || s.id === Number(service));
      if (!svc) return res.status(404).json({ error: "Service not found" });

      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ error: "User not found" });

      const baseDayPrice = parseFloat(svc.price) * 2;
      const totalBase = baseDayPrice * rentalDays;
      const finalPrice = await calculatePrice(totalBase, svc.slug, rentalCountry);

      if (parseFloat(freshUser.balance) < finalPrice) return res.status(400).json({ error: "Insufficient balance" });

      const pnResult = await proxnumApi.buyRental(svc.slug || svc.name, rentalCountry, rentalDays);
      if (pnResult.error) {
        return res.status(503).json({ error: pnResult.error.message || "No rental numbers available" });
      }

      const pnData = pnResult.data || pnResult;
      const proxnumId = String(pnData.id || pnData.rental_id || "");
      const phoneNumber = pnData.number || pnData.phone || "";
      if (!phoneNumber) return res.status(503).json({ error: "No rental numbers available" });

      const formattedPhone = phoneNumber.startsWith("+") ? phoneNumber : `+${phoneNumber}`;
      const deductResult = await storage.atomicDeductBalance(user.id, finalPrice);
      if (!deductResult.success) {
        return res.status(400).json({ error: "Insufficient balance" });
      }

      const now = new Date();
      const expiresAt = new Date(now.getTime() + rentalDays * 24 * 60 * 60 * 1000);

      const rental = await storage.createRental({
        userId: user.id, serviceId: svc.id, serviceName: svc.name,
        phoneNumber: formattedPhone, status: "active", price: finalPrice.toFixed(2),
        country: rentalCountry, days: rentalDays, proxnumId,
        createdAt: now.toISOString(), expiresAt: expiresAt.toISOString(), cancelledAt: null,
      });

      await storage.createTransaction({
        userId: user.id, type: "purchase", amount: `-${finalPrice.toFixed(2)}`,
        description: `${svc.name} rental (${rentalDays} days)`, orderId: rental.id,
        stripeSessionId: null, createdAt: now.toISOString(),
      });

      res.json({ rentalId: rental.id, phoneNumber: formattedPhone, status: "active", expiresAt: rental.expiresAt });
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  // Profile
  app.post("/api/profile/generate-api-key", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json({ apiKey: await storage.generateApiKey(user.id) });
  });

  app.post("/api/profile/change-password", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const { currentPassword, newPassword } = req.body;
      if (!currentPassword || !newPassword) {
        return res.status(400).json({ message: "Current and new passwords are required" });
      }
      if (newPassword.length < 8) {
        return res.status(400).json({ message: "New password must be at least 8 characters" });
      }
      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });
      const isValid = await bcrypt.compare(currentPassword, freshUser.password);
      if (!isValid) return res.status(400).json({ message: "Current password is incorrect" });
      const hashed = await bcrypt.hash(newPassword, 12);
      await storage.updateUserPassword(user.id, hashed);
      res.json({ message: "Password updated" });
    } catch (err: any) { res.status(500).json({ message: "Password update failed" }); }
  });

  // Proxnum balance endpoint
  app.get("/api/proxnum/balance", requireAdmin, async (_req, res) => {
    try {
      const result = await proxnumApi.getUserBalance();
      res.json(result.data || result);
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  syncProxnumServices().then(() => {
    console.log("Proxnum services synced on startup");
  }).catch(err => {
    console.error("Proxnum initial sync failed:", err);
  });

  if (circle.isCircleConfigured()) {
    const CIRCLE_POLL_INTERVAL_MS = 2 * 60 * 1000;
    const pollCircleDeposits = async () => {
      try {
        const allUsers = await storage.getAllUsers();
        const usersWithWallets = allUsers.filter(u => u.circleWalletId);

        for (const user of usersWithWallets) {
          try {
            const txList = await circle.listWalletTransactions(user.circleWalletId!);
            const inboundConfirmed = txList.filter(tx =>
              tx.type === "INBOUND" && tx.state === "CONFIRMED"
            );

            const tokenCache = new Map<string, { name: string; symbol: string } | null>();
            for (const tx of inboundConfirmed) {
              if (!tx.tokenId) continue;
              if (!tokenCache.has(tx.tokenId)) {
                tokenCache.set(tx.tokenId, await circle.getTokenInfo(tx.tokenId));
              }
              const tokenInfo = tokenCache.get(tx.tokenId);
              const isUsdc = tokenInfo && (
                tokenInfo.symbol === "USDC" ||
                tokenInfo.name.includes("USDC") ||
                tokenInfo.name.includes("USD Coin")
              );
              if (!isUsdc) continue;

              const rawAmount = Array.isArray(tx.amounts) && tx.amounts.length > 0 ? String(tx.amounts[0]) : "0";
              const usdAmount = parseFloat(rawAmount);
              if (isNaN(usdAmount) || usdAmount <= 0) continue;

              const now = new Date().toISOString();
              await storage.creditCircleDeposit(
                {
                  userId: user.id,
                  currency: "USDC",
                  amount: usdAmount.toFixed(2),
                  cryptoAmount: rawAmount,
                  walletAddress: user.circleWalletAddress || "",
                  txHash: tx.txHash || null,
                  circleTransferId: tx.id,
                  status: "completed",
                  createdAt: tx.createDate || now,
                  expiresAt: now,
                  completedAt: now,
                },
                {
                  userId: user.id,
                  type: "deposit",
                  amount: usdAmount.toFixed(2),
                  description: `USDC deposit via Circle wallet (auto-detected)`,
                  orderId: null,
                  stripeSessionId: null,
                  createdAt: now,
                },
                user.id,
                usdAmount.toFixed(2)
              );
            }
          } catch (userErr) {
            console.error(`Circle poll error for user ${user.id}:`, userErr);
          }
        }
      } catch (pollErr) {
        console.error("Circle background poll error:", pollErr);
      }
    };

    setInterval(pollCircleDeposits, CIRCLE_POLL_INTERVAL_MS);
    console.log("Circle deposit polling started (every 2 minutes)");
  }

  return httpServer;
}
