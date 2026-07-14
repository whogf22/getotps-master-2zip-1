import type { Express, Request, Response } from "express";
import { createServer, type Server } from "http";
import { storage, sqlite as sqliteClient } from "./storage";
import { proxnumApi, getCachedServices, getCachedCountries, getCachedPrices, getUSCountryCode, findCountryCode, friendlyError, type ProxnumService } from "./proxnum";
import session from "express-session";
import passport from "passport";
import { Strategy as LocalStrategy } from "passport-local";
import bcrypt from "bcryptjs";
import rateLimit from "express-rate-limit";
import BetterSqlite3SessionStore from "better-sqlite3-session-store";

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

function safeError(err: any): string {
  if (process.env.NODE_ENV === "production") {
    return "Something went wrong. Please try again.";
  }
  return err?.message || "Unknown error";
}

// Validate a user-supplied monetary amount. Rejects NaN, Infinity, negatives,
// non-positive values and absurdly large numbers. Never trust client money input.
function parseMoney(input: unknown, { min = 0.01, max = 1_000_000 }: { min?: number; max?: number } = {}): number | null {
  const value = typeof input === "number" ? input : parseFloat(String(input));
  if (!Number.isFinite(value)) return null;
  if (value < min || value > max) return null;
  return value;
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

// Crypto config: env vars are checked first, then DB settings (admin-configurable)
const CRYPTO_CURRENCY_META: Record<string, { name: string; network: string }> = {
  BTC: { name: "BTC", network: "Bitcoin" },
  ETH: { name: "ETH", network: "Ethereum" },
  USDT_TRC20: { name: "USDT (TRC20)", network: "Tron" },
  USDT_ERC20: { name: "USDT (ERC20)", network: "Ethereum" },
  USDC: { name: "USDC", network: "Ethereum" },
  LTC: { name: "LTC", network: "Litecoin" },
};

const DEFAULT_CRYPTO_RATES: Record<string, number> = {
  BTC: 84250, ETH: 3420, USDT_TRC20: 1, USDT_ERC20: 1, USDC: 1, LTC: 92.50,
};

// Helper: resolve wallet address from env first, then DB setting
async function getCryptoWallets(): Promise<Record<string, string>> {
  const wallets: Record<string, string> = {};
  for (const key of Object.keys(CRYPTO_CURRENCY_META)) {
    // env var takes priority
    const envKey = `CRYPTO_WALLET_${key}`;
    const envVal = process.env[envKey];
    if (envVal) {
      wallets[key] = envVal;
    } else {
      // fall back to DB setting
      const dbVal = await storage.getSetting(`crypto_wallet_${key.toLowerCase()}`);
      wallets[key] = dbVal || "";
    }
  }
  return wallets;
}

async function getCryptoRates(): Promise<Record<string, number>> {
  const rates: Record<string, number> = {};
  for (const [key, defaultRate] of Object.entries(DEFAULT_CRYPTO_RATES)) {
    const envKey = `CRYPTO_RATE_${key === "USDT_TRC20" || key === "USDT_ERC20" ? "USDT" : key}`;
    const envVal = process.env[envKey];
    if (envVal) {
      rates[key] = parseFloat(envVal);
    } else {
      const dbVal = await storage.getSetting(`crypto_rate_${key.toLowerCase()}`);
      rates[key] = dbVal ? parseFloat(dbVal) : defaultRate;
    }
  }
  return rates;
}

export async function registerRoutes(
  httpServer: Server,
  app: Express
): Promise<Server> {

  // SQLite-backed session store (no memory leaks)
  const SqliteStore = BetterSqlite3SessionStore(session);

  const isProduction = process.env.NODE_ENV === "production";

  // Trust proxy for secure cookies behind reverse proxy (Render/Cloudflare)
  if (isProduction) {
    app.set("trust proxy", 1);
  }

  if (!process.env.SESSION_SECRET) {
    if (isProduction) {
      throw new Error("FATAL: SESSION_SECRET must be set in production. Refusing to start with insecure default.");
    }
    console.warn("WARNING: SESSION_SECRET not set. Using insecure default. Set it in .env for production!");
  }

  app.use(
    session({
      store: new SqliteStore({
        client: sqliteClient,
        expired: { clear: true, intervalMs: 15 * 60 * 1000 },
      }),
      secret: process.env.SESSION_SECRET || "getotps-dev-insecure-fallback",
      resave: false,
      saveUninitialized: false,
      cookie: {
        secure: isProduction,
        httpOnly: true,
        maxAge: 7 * 24 * 60 * 60 * 1000,
        sameSite: "lax",
      },
    })
  );

  app.use(passport.initialize());
  app.use(passport.session());

  passport.use(
    new LocalStrategy({ usernameField: "email" }, async (email, password, done) => {
      try {
        const normalizedEmail = String(email || "").trim().toLowerCase();
        const user = await storage.getUserByEmail(normalizedEmail);
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

  // ========== RATE LIMITING ==========

  const authLimiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 10,
    standardHeaders: true,
    legacyHeaders: false,
    message: { message: "Too many attempts. Please try again in 15 minutes." },
  });

  const apiLimiter = rateLimit({
    windowMs: 1 * 60 * 1000,
    max: 60,
    standardHeaders: true,
    legacyHeaders: false,
    message: { message: "Too many requests. Please slow down." },
  });

  const orderLimiter = rateLimit({
    windowMs: 1 * 60 * 1000,
    max: 10,
    standardHeaders: true,
    legacyHeaders: false,
    message: { message: "Too many order requests. Please slow down." },
  });

  app.use("/api", apiLimiter);

  // ========== AUTH ROUTES ==========

  app.post("/api/auth/register", authLimiter, async (req, res) => {
    try {
      const { username, password } = req.body;
      const email = String(req.body?.email || "").trim().toLowerCase();
      if (!username || !email || !password) {
        return res.status(400).json({ message: "All fields required" });
      }
      if (typeof password !== "string" || password.length < 8) {
        return res.status(400).json({ message: "Password must be at least 8 characters" });
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return res.status(400).json({ message: "Invalid email format" });
      }
      if (typeof username !== "string" || username.length < 3 || username.length > 32 || !/^[a-zA-Z0-9_-]+$/.test(username)) {
        return res.status(400).json({ message: "Username must be 3-32 characters (letters, numbers, _ or -)" });
      }
      const existing = await storage.getUserByEmail(email);
      if (existing) return res.status(400).json({ message: "Email already registered" });
      const existingUsername = await storage.getUserByUsername(username);
      if (existingUsername) return res.status(400).json({ message: "Username already taken" });

      const hashedPassword = await bcrypt.hash(password, 10);
      const user = await storage.createUser({ username, email, password: hashedPassword });

      // Prevent session fixation: establish a fresh session, then log in.
      req.session.regenerate((regenErr) => {
        if (regenErr) return res.status(500).json({ message: "Login failed after registration" });
        req.login(user, (err) => {
          if (err) return res.status(500).json({ message: "Login failed after registration" });
          const { password: _, apiKey: __, ...safeUser } = user;
          res.json(safeUser);
        });
      });
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  app.post("/api/auth/login", authLimiter, (req, res, next) => {
    passport.authenticate("local", (err: any, user: any, info: any) => {
      if (err) return res.status(500).json({ message: safeError(err) });
      if (!user) return res.status(401).json({ message: info?.message || "Invalid credentials" });
      // Regenerate the session on login to prevent session fixation attacks.
      req.session.regenerate((regenErr) => {
        if (regenErr) return res.status(500).json({ message: "Login failed" });
        req.login(user, (loginErr) => {
          if (loginErr) return res.status(500).json({ message: "Login failed" });
          const { password: _, apiKey: __, ...safeUser } = user;
          res.json(safeUser);
        });
      });
    })(req, res, next);
  });

  app.post("/api/auth/logout", (req, res) => {
    req.logout(() => {
      // Fully destroy the server-side session and clear the cookie.
      req.session.destroy(() => {
        res.clearCookie("connect.sid");
        res.json({ message: "Logged out" });
      });
    });
  });

  app.get("/api/auth/me", requireAuth, async (req, res) => {
    const user = req.user as any;
    const freshUser = await storage.getUser(user.id);
    if (!freshUser) return res.status(404).json({ message: "User not found" });
    const { password: _, apiKey: __, ...safeUser } = freshUser;
    res.json(safeUser);
  });

  // ========== SERVICES (Proxnum-backed) ==========

  // Public endpoint — landing page. No auth; exposes no internal slugs/prices.
  app.get("/api/public/services", async (_req, res) => {
    try {
      await syncProxnumServices();
      const dbServices = await storage.getAllServices();
      const publicServices = (dbServices as any[]).map((s: any) => ({
        id: s.id,
        name: s.name,
        icon: s.icon,
        category: s.category,
        priceFrom: parseFloat(s.price) < 1 ? "< $1" : `$${Math.ceil(parseFloat(s.price))}`,
        isActive: s.isActive,
      }));
      res.json(publicServices);
    } catch (_err) {
      const dbServices = await storage.getAllServices();
      res.json((dbServices as any[]).map((s: any) => ({
        id: s.id, name: s.name, icon: s.icon,
        category: s.category, priceFrom: "< $1", isActive: s.isActive,
      })));
    }
  });

  // Authenticated — full service data (slugs + exact prices) for dashboard users.
  app.get("/api/services", requireAuth, async (_req, res) => {
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

  app.post("/api/orders", requireAuth, orderLimiter, async (req, res) => {
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

      const balance = parseFloat(freshUser.balance);
      // Price is always taken from our DB, never from the client request body.
      const price = parseFloat(service.price);
      if (balance < price) return res.status(400).json({ message: "Insufficient balance" });

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
      const chargePrice = parseFloat(service.price);
      const newBalance = (balance - chargePrice).toFixed(2);
      await storage.updateUserBalance(user.id, newBalance);

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
    const user = req.user as any;
    const order = await storage.getOrder(Number(req.params.id));
    if (!order) return res.status(404).json({ message: "Order not found" });
    if (order.userId !== user.id && (req.user as any)?.role !== "admin") {
      return res.status(403).json({ message: "Forbidden" });
    }
    res.json(order);
  });

  app.post("/api/orders/:id/check-sms", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const order = await storage.getOrder(Number(req.params.id));
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
      const user = req.user as any;
      const order = await storage.getOrder(Number(req.params.id));
      if (!order) return res.status(404).json({ message: "Order not found" });
      if (order.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      // Only pending/waiting orders can be cancelled; this also guarantees a
      // refund can never be issued twice for the same order.
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

      const freshUser = await storage.getUser(user.id);
      if (freshUser) {
        const newBalance = (parseFloat(freshUser.balance) + parseFloat(order.price)).toFixed(2);
        await storage.updateUserBalance(user.id, newBalance);
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
      const user = req.user as any;
      const order = await storage.getOrder(Number(req.params.id));
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

  app.post("/api/rentals", requireAuth, orderLimiter, async (req, res) => {
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

      const balance = parseFloat(freshUser.balance);
      if (balance < finalPrice) return res.status(400).json({ message: "Insufficient balance" });

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
      const newBalance = (balance - finalPrice).toFixed(2);
      await storage.updateUserBalance(user.id, newBalance);

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
    const user = req.user as any;
    const rental = await storage.getRental(Number(req.params.id));
    if (!rental) return res.status(404).json({ message: "Rental not found" });
    if (rental.userId !== user.id && (req.user as any)?.role !== "admin") {
      return res.status(403).json({ message: "Forbidden" });
    }
    res.json(rental);
  });

  app.get("/api/rentals/:id/messages", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const rental = await storage.getRental(Number(req.params.id));
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
      const user = req.user as any;
      const rental = await storage.getRental(Number(req.params.id));
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

  app.get("/api/crypto/currencies", requireAuth, async (_req, res) => {
    try {
      const wallets = await getCryptoWallets();
      const rates = await getCryptoRates();
      const currencies = Object.entries(CRYPTO_CURRENCY_META)
        .filter(([key]) => wallets[key]) // only show currencies with a configured address
        .map(([key, meta]) => ({
          id: key,
          name: meta.name,
          network: meta.network,
          address: wallets[key],
          rate: rates[key] || 1,
        }));
      res.json(currencies);
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/crypto/create-deposit", requireAuth, async (req, res) => {
    try {
      const user = req.user as any;
      const { currency, amount } = req.body;
      if (!currency || !amount) return res.status(400).json({ message: "Currency and amount are required" });
      // Reject NaN, Infinity, negatives and out-of-range amounts.
      const usdAmount = parseMoney(amount, { min: 1, max: 1_000_000 });
      if (usdAmount === null) return res.status(400).json({ message: "Enter a valid amount between $1.00 and $1,000,000.00" });
      if (!Object.prototype.hasOwnProperty.call(CRYPTO_CURRENCY_META, currency)) {
        return res.status(400).json({ message: "Unsupported currency" });
      }
      const wallets = await getCryptoWallets();
      const walletAddress = wallets[currency];
      if (!walletAddress) return res.status(400).json({ message: "This currency is not configured. Please contact admin." });
      const rates = await getCryptoRates();
      const rate = rates[currency] || 1;
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
    try {
      const user = req.user as any;
      const { txHash } = req.body;
      if (!txHash || typeof txHash !== "string") return res.status(400).json({ message: "Transaction hash is required" });
      const trimmedHash = txHash.trim();
      if (trimmedHash.length < 10 || trimmedHash.length > 128) return res.status(400).json({ message: "Invalid transaction hash format" });
      const deposit = await storage.getCryptoDeposit(Number(req.params.id));
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      if (deposit.status !== "pending") return res.status(400).json({ message: "Deposit is not pending" });
      await storage.updateCryptoDeposit(deposit.id, { txHash: trimmedHash, status: "confirming" });
      res.json({ message: "Transaction hash submitted. Awaiting confirmation." });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  // Simulate confirm — DEV/DEMO ONLY. Hard-blocked in production so it can never
  // be used to credit real balances without an actual on-chain payment.
  app.post("/api/crypto/:id/simulate-confirm", requireAuth, async (req, res) => {
    if (process.env.NODE_ENV === "production") return res.status(404).json({ message: "Not found" });
    try {
      const user = req.user as any;
      const deposit = await storage.getCryptoDeposit(Number(req.params.id));
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.userId !== user.id) return res.status(403).json({ message: "Forbidden" });
      // Guard against double-crediting the same deposit.
      if (deposit.status === "completed") return res.status(400).json({ message: "Already completed" });
      const now = new Date().toISOString();
      await storage.updateCryptoDeposit(deposit.id, { status: "completed", completedAt: now });
      const freshUser = await storage.getUser(deposit.userId);
      if (freshUser) {
        const newBalance = (parseFloat(freshUser.balance) + parseFloat(deposit.amount)).toFixed(2);
        await storage.updateUserBalance(deposit.userId, newBalance);
        await storage.createTransaction({
          userId: deposit.userId, type: "deposit", amount: deposit.amount,
          description: `Crypto deposit (${deposit.currency}) confirmed`,
          orderId: null, stripeSessionId: null, createdAt: now,
        });
        res.json({ message: "Deposit confirmed", newBalance });
      } else {
        res.json({ message: "Deposit confirmed" });
      }
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/admin/crypto/:id/confirm", requireAdmin, async (req, res) => {
    try {
      const deposit = await storage.getCryptoDeposit(Number(req.params.id));
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      // Guard against double-crediting the same deposit.
      if (deposit.status === "completed") return res.status(400).json({ message: "Already completed" });
      const now = new Date().toISOString();
      await storage.updateCryptoDeposit(deposit.id, { status: "completed", completedAt: now });
      const freshUser = await storage.getUser(deposit.userId);
      if (freshUser) {
        const newBalance = (parseFloat(freshUser.balance) + parseFloat(deposit.amount)).toFixed(2);
        await storage.updateUserBalance(deposit.userId, newBalance);
        await storage.createTransaction({
          userId: deposit.userId, type: "deposit", amount: deposit.amount,
          description: `Crypto deposit (${deposit.currency}) confirmed by admin`,
          orderId: null, stripeSessionId: null, createdAt: now,
        });
      }
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

  app.get("/api/transactions", requireAuth, async (req, res) => {
    const user = req.user as any;
    res.json(await storage.getUserTransactions(user.id));
  });

  // ========== ADMIN ==========

  app.get("/api/admin/users", requireAdmin, async (_req, res) => {
    const allUsers = await storage.getAllUsers();
    const allOrders = await storage.getAllOrders();
    const result = allUsers.map(({ password: _, apiKey: __, ...u }) => {
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
      await storage.updateService(Number(req.params.id), req.body);
      res.json({ message: "Service updated" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.get("/api/admin/settings", requireAdmin, async (_req, res) => {
    const multiplier = await storage.getSetting("price_multiplier");
    const defaultCountry = await storage.getSetting("default_country");
    const allSettings = await storage.getAllSettings();
    const serviceMultipliers: Record<string, string> = {};
    const cryptoWallets: Record<string, string> = {};
    for (const s of allSettings) {
      if (s.key.startsWith("multiplier_")) {
        const rest = s.key.replace("multiplier_", "");
        serviceMultipliers[rest] = s.value;
      }
      if (s.key.startsWith("crypto_wallet_")) {
        const rest = s.key.replace("crypto_wallet_", "").toUpperCase();
        cryptoWallets[rest] = s.value;
      }
    }
    res.json({
      price_multiplier: multiplier || "1.5",
      default_country: defaultCountry || "us",
      service_multipliers: serviceMultipliers,
      crypto_wallets: cryptoWallets,
    });
  });

  app.put("/api/admin/settings", requireAdmin, async (req, res) => {
    try {
      const { price_multiplier, default_country, service_multipliers, crypto_wallets } = req.body;
      if (price_multiplier !== undefined) await storage.setSetting("price_multiplier", String(price_multiplier));
      if (default_country !== undefined) await storage.setSetting("default_country", String(default_country));
      if (service_multipliers && typeof service_multipliers === "object") {
        for (const [slug, val] of Object.entries(service_multipliers)) {
          if (val === null || val === "" || val === "0") {
            await storage.deleteSetting(`multiplier_${slug}`);
          } else {
            await storage.setSetting(`multiplier_${slug}`, String(val));
          }
        }
      }
      if (crypto_wallets && typeof crypto_wallets === "object") {
        for (const [currency, address] of Object.entries(crypto_wallets)) {
          const key = `crypto_wallet_${currency.toLowerCase()}`;
          if (!address || address === "") {
            await storage.deleteSetting(key);
          } else {
            await storage.setSetting(key, String(address));
          }
        }
      }
      res.json({ message: "Settings updated" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/admin/users/:id/add-balance", requireAdmin, async (req, res) => {
    try {
      const { amount, description } = req.body;
      // Reject NaN, Infinity, negatives and out-of-range amounts.
      const value = parseMoney(amount, { min: 0.01, max: 1_000_000 });
      if (value === null) {
        return res.status(400).json({ message: "Valid positive amount is required" });
      }
      const targetUser = await storage.getUser(Number(req.params.id));
      if (!targetUser) return res.status(404).json({ message: "User not found" });
      const newBalance = (parseFloat(targetUser.balance) + value).toFixed(2);
      await storage.updateUserBalance(targetUser.id, newBalance);
      await storage.createTransaction({
        userId: targetUser.id,
        type: "deposit",
        amount: value.toFixed(2),
        description: description || "Admin balance adjustment",
        orderId: null,
        stripeSessionId: null,
        createdAt: new Date().toISOString(),
      });
      res.json({ message: "Balance updated", newBalance });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  app.post("/api/admin/crypto/:id/reject", requireAdmin, async (req, res) => {
    try {
      const deposit = await storage.getCryptoDeposit(Number(req.params.id));
      if (!deposit) return res.status(404).json({ message: "Deposit not found" });
      if (deposit.status === "completed") return res.status(400).json({ message: "Cannot reject completed deposit" });
      await storage.updateCryptoDeposit(deposit.id, { status: "rejected", completedAt: new Date().toISOString() });
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
    const key = (req.headers["x-api-key"] as string) || (req.query.api_key as string);
    if (!key) return res.status(401).json({ error: "API key required" });
    const user = await storage.getUserByApiKey(key);
    if (!user) return res.status(401).json({ error: "Invalid API key" });
    (req as any).apiUser = user;
    next();
  }

  app.get("/api/v1/services", async (_req, res) => {
    const allServices = await storage.getAllServices();
    res.json({ services: allServices });
  });

  app.get("/api/v1/balance", requireApiKey, async (req, res) => {
    const user = (req as any).apiUser;
    res.json({ balance: user.balance });
  });

  app.post("/api/v1/order", requireApiKey, async (req, res) => {
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
      const newBalance = (balance - price).toFixed(2);
      await storage.updateUserBalance(user.id, newBalance);

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

  app.get("/api/v1/order/:id", requireApiKey, async (req, res) => {
    const user = (req as any).apiUser;
    const order = await storage.getOrder(Number(req.params.id));
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

  app.post("/api/v1/order/:id/cancel", requireApiKey, async (req, res) => {
    try {
      const user = (req as any).apiUser;
      const order = await storage.getOrder(Number(req.params.id));
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
      const freshUser = await storage.getUser(user.id);
      if (freshUser) {
        const newBalance = (parseFloat(freshUser.balance) + parseFloat(order.price)).toFixed(2);
        await storage.updateUserBalance(user.id, newBalance);
      }
      res.json({ message: "Order cancelled and refunded" });
    } catch (err: any) { res.status(500).json({ error: safeError(err) }); }
  });

  app.get("/api/v1/price", requireApiKey, async (req, res) => {
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

  app.post("/api/v1/order/:id/resend", requireApiKey, async (req, res) => {
    try {
      const user = (req as any).apiUser;
      const order = await storage.getOrder(Number(req.params.id));
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

  app.post("/api/v1/rental", requireApiKey, async (req, res) => {
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

      const balance = parseFloat(freshUser.balance);
      if (balance < finalPrice) return res.status(400).json({ error: "Insufficient balance" });

      const pnResult = await proxnumApi.buyRental(svc.slug || svc.name, rentalCountry, rentalDays);
      if (pnResult.error) {
        return res.status(503).json({ error: pnResult.error.message || "No rental numbers available" });
      }

      const pnData = pnResult.data || pnResult;
      const proxnumId = String(pnData.id || pnData.rental_id || "");
      const phoneNumber = pnData.number || pnData.phone || "";
      if (!phoneNumber) return res.status(503).json({ error: "No rental numbers available" });

      const formattedPhone = phoneNumber.startsWith("+") ? phoneNumber : `+${phoneNumber}`;
      const newBalance = (balance - finalPrice).toFixed(2);
      await storage.updateUserBalance(user.id, newBalance);

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
      if (typeof newPassword !== "string" || newPassword.length < 8) {
        return res.status(400).json({ message: "New password must be at least 8 characters" });
      }
      const freshUser = await storage.getUser(user.id);
      if (!freshUser) return res.status(404).json({ message: "User not found" });
      const isValid = await bcrypt.compare(currentPassword, freshUser.password);
      if (!isValid) return res.status(400).json({ message: "Current password is incorrect" });
      const hashed = await bcrypt.hash(newPassword, 10);
      await storage.updateUserPassword(user.id, hashed);
      res.json({ message: "Password updated" });
    } catch (err: any) { res.status(500).json({ message: safeError(err) }); }
  });

  // Proxnum balance endpoint (admin only)
  app.get("/api/proxnum/balance", requireAdmin, async (_req, res) => {
    try {
      const result = await proxnumApi.getUserBalance();
      res.json(result.data || result);
    } catch (err: any) {
      res.status(500).json({ message: safeError(err) });
    }
  });

  // JSON 404 for any unmatched API route — never fall through to the SPA shell.
  app.use("/api", (_req, res) => {
    res.status(404).json({ message: "Not found" });
  });

  syncProxnumServices().then(() => {
    console.log("Proxnum services synced on startup");
  }).catch(err => {
    console.error("Proxnum initial sync failed:", err);
  });

  // ========== LEGAL PAGES (server-rendered) ==========

  app.get("/privacy", (_req, res) => {
    const date = new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
    res.type("text/html").send(`<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Privacy Policy – GetOTPs</title>
<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:0 auto;padding:2rem;color:#1e293b;line-height:1.7}h1{color:#6366f1}h2{color:#334155;margin-top:2rem}a{color:#6366f1}.back{font-weight:600;text-decoration:none}</style>
</head><body>
<nav><a class="back" href="/">← Back to GetOTPs</a></nav>
<h1>Privacy Policy</h1><p><strong>Last updated:</strong> ${date}</p>
<p>GetOTPs ("we","our","us") is operated by CARDXC LLC, a Wyoming limited liability company (USA). This policy explains what data we collect, how we use it, and your choices.</p>
<h2>1. Data We Collect</h2><ul>
<li><strong>Account data:</strong> username, email address, hashed password</li>
<li><strong>Transaction data:</strong> wallet balance, order history, crypto deposit records</li>
<li><strong>Usage data:</strong> IP address (rate limiting, fraud prevention), browser type</li>
<li><strong>API data:</strong> API keys generated for your account</li></ul>
<h2>2. How We Use Your Data</h2><ul>
<li>To provide virtual phone number and OTP delivery services</li>
<li>To process wallet top-ups and maintain account balances</li>
<li>To prevent fraud, abuse, and unauthorized access</li>
<li>To comply with applicable laws and regulations</li></ul>
<h2>3. Data Sharing</h2>
<p>We do <strong>not</strong> sell your personal data. We share only with Proxnum (SMS provider — service slug + country code to provision numbers) and law enforcement when legally required.</p>
<h2>4. Data Retention</h2>
<p>Account data is retained while your account is active. You may request deletion at any time.</p>
<h2>5. Your Choices</h2>
<p>You may request access to, correction of, or deletion of your account data by emailing <a href="mailto:support@getotps.online">support@getotps.online</a>.</p>
<h2>6. Cookies</h2>
<p>We use a single session cookie (<code>connect.sid</code>): httpOnly, Secure in production, and it expires after 7 days. We do not use advertising or tracking cookies.</p>
<h2>7. Security</h2>
<p>Passwords are hashed with bcrypt and sessions are stored server-side. Production traffic is served over HTTPS.</p>
<h2>8. Contact</h2>
<p>Email: <a href="mailto:support@getotps.online">support@getotps.online</a> · Operator: CARDXC LLC (Wyoming, USA)</p>
</body></html>`);
  });

  app.get("/terms", (_req, res) => {
    const date = new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
    res.type("text/html").send(`<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Terms of Service – GetOTPs</title>
<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:0 auto;padding:2rem;color:#1e293b;line-height:1.7}h1{color:#6366f1}h2{color:#334155;margin-top:2rem}a{color:#6366f1}.back{font-weight:600;text-decoration:none}</style>
</head><body>
<nav><a class="back" href="/">← Back to GetOTPs</a></nav>
<h1>Terms of Service</h1><p><strong>Last updated:</strong> ${date}</p>
<p>By using GetOTPs you agree to these Terms. If you do not agree, do not use the service. GetOTPs is operated by CARDXC LLC, a Wyoming limited liability company (USA).</p>
<h2>1. Service Description</h2>
<p>GetOTPs provides temporary virtual phone numbers for receiving SMS verification codes. Numbers are not real SIM cards.</p>
<h2>2. Acceptable Use</h2>
<p>You must not use GetOTPs to: create fraudulent accounts, commit identity fraud, bypass security in ways violating third-party terms, send spam, conduct illegal activity, or resell without written permission.</p>
<h2>3. Wallet and Payments</h2>
<ul><li>Wallet top-ups are made via cryptocurrency and are final once confirmed on-chain.</li>
<li>Wallet balances do not expire while your account is active.</li></ul>
<h2 id="refunds">4. Refund Policy</h2>
<p>If a virtual number does not receive the expected SMS, you may cancel the order while it is still pending and the order price is credited back to your wallet balance. Wallet top-ups themselves are non-refundable.</p>
<h2>5. Account Termination</h2>
<p>We may suspend or terminate accounts that violate these Terms.</p>
<h2>6. Limitation of Liability</h2>
<p>GetOTPs is provided "as is". Our liability is limited to the amount paid for the specific order. We are not liable for indirect or consequential damages.</p>
<h2>7. Governing Law</h2>
<p>These Terms are governed by the laws of the State of Wyoming, USA.</p>
<h2>8. Contact</h2>
<p>Email: <a href="mailto:support@getotps.online">support@getotps.online</a></p>
</body></html>`);
  });

  // ========== SEO ==========

  app.get("/robots.txt", (_req, res) => {
    res.type("text/plain");
    res.send(
      `User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /admin\nDisallow: /profile\nDisallow: /api/\n\nUser-agent: Amazonbot\nDisallow: /\nUser-agent: GPTBot\nDisallow: /\nUser-agent: CCBot\nDisallow: /\nUser-agent: Bytespider\nDisallow: /\n\nSitemap: https://getotps.online/sitemap.xml`
    );
  });

  app.get("/sitemap.xml", async (_req, res) => {
    try {
      const services = await getCachedServices();
      const serviceNames: string[] = (services as any[]).map((s: any) => s.name || s).filter(Boolean);

      const staticUrls = [
        { loc: "https://getotps.online/", priority: "1.0", changefreq: "daily" },
        { loc: "https://getotps.online/login", priority: "0.3", changefreq: "monthly" },
        { loc: "https://getotps.online/register", priority: "0.5", changefreq: "monthly" },
        { loc: "https://getotps.online/privacy", priority: "0.3", changefreq: "yearly" },
        { loc: "https://getotps.online/terms", priority: "0.3", changefreq: "yearly" },
      ];

      const serviceUrls = serviceNames.map((name) => ({
        loc: `https://getotps.online/buy?service=${encodeURIComponent(name)}`,
        priority: "0.6",
        changefreq: "weekly",
      }));

      const urlEntries = [...staticUrls, ...serviceUrls]
        .map((u) => `  <url>\n    <loc>${u.loc}</loc>\n    <changefreq>${u.changefreq}</changefreq>\n    <priority>${u.priority}</priority>\n  </url>`)
        .join("\n");

      const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urlEntries}\n</urlset>`;

      res.setHeader("Content-Type", "application/xml; charset=utf-8");
      res.setHeader("Cache-Control", "public, max-age=3600");
      res.send(xml);
    } catch (_sitemapErr) {
      res.setHeader("Content-Type", "application/xml; charset=utf-8");
      res.send(`<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n  <url><loc>https://getotps.online/</loc><priority>1.0</priority></url>\n</urlset>`);
    }
  });

  return httpServer;
}
