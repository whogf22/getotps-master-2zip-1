import express, { type Request, Response, NextFunction } from "express";
import { registerRoutes } from "./routes";
import { serveStatic } from "./static";
import { createServer } from "http";
import helmet from "helmet";
import compression from "compression";
import crypto from "crypto";

const app = express();
const httpServer = createServer(app);

declare module "http" {
  interface IncomingMessage {
    rawBody: unknown;
  }
}

const isProduction = process.env.NODE_ENV === "production";

app.set("trust proxy", 1);

app.use(compression());

app.use(
  helmet({
    contentSecurityPolicy: {
      directives: {
        defaultSrc: ["'self'"],
        scriptSrc: isProduction
          ? ["'self'", "'unsafe-inline'"]
          : ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        styleSrc: ["'self'", "'unsafe-inline'", "https://api.fontshare.com"],
        imgSrc: ["'self'", "data:", "https:"],
        connectSrc: ["'self'", "wss:", "ws:"],
        fontSrc: ["'self'", "data:", "https://api.fontshare.com", "https://cdn.fontshare.com"],
        objectSrc: ["'none'"],
        frameAncestors: ["'none'"],
      },
    },
    crossOriginEmbedderPolicy: false,
  })
);

app.use(
  express.json({
    limit: "1mb",
    verify: (req, _res, buf) => {
      req.rawBody = buf;
    },
  }),
);

app.use(express.urlencoded({ extended: false, limit: "1mb" }));

app.get("/api/csrf-token", (req, res) => {
  let token = (req as any).cookies?.["csrf-token"];
  if (!token) {
    token = crypto.randomBytes(32).toString("hex");
  }
  res.cookie("csrf-token", token, {
    httpOnly: false,
    sameSite: "lax",
    secure: isProduction,
    maxAge: 7 * 24 * 60 * 60 * 1000,
    path: "/",
  });
  res.json({ csrfToken: token });
});

const CSRF_SAFE_METHODS = new Set(["GET", "HEAD", "OPTIONS"]);
const CSRF_EXEMPT_PATHS = ["/api/v1/", "/api/health", "/api/csrf-token", "/api/auth/login", "/api/auth/register", "/api/auth/forgot-password", "/api/auth/reset-password"];

app.use((req, res, next) => {
  if (CSRF_SAFE_METHODS.has(req.method)) return next();
  if (CSRF_EXEMPT_PATHS.some(p => req.path.startsWith(p))) return next();
  if (req.headers["x-api-key"]) return next();

  const cookieHeader = req.headers.cookie || "";
  const cookieToken = cookieHeader.split(";").map(c => c.trim()).find(c => c.startsWith("csrf-token="))?.split("=")[1];
  const headerToken = req.headers["x-csrf-token"] as string;

  if (!cookieToken || !headerToken || cookieToken !== headerToken) {
    return res.status(403).json({ message: "CSRF token mismatch" });
  }
  next();
});

app.get("/api/health", (_req, res) => {
  res.json({ status: "ok", uptime: process.uptime(), timestamp: new Date().toISOString() });
});

const SENSITIVE_KEYS = new Set([
  "apikey", "api_key", "token", "password", "secret",
  "authorization", "cookie", "sessionid", "balance",
  "apiKey", "hash", "txHash", "tx_hash",
]);

function sanitizeForLogging(obj: any, depth = 0): any {
  if (depth > 5 || obj === null || obj === undefined) return obj;
  if (typeof obj === "string") return obj.length > 200 ? obj.slice(0, 200) + "..." : obj;
  if (typeof obj !== "object") return obj;
  if (Array.isArray(obj)) {
    if (obj.length > 5) return `[Array(${obj.length})]`;
    return obj.map(item => sanitizeForLogging(item, depth + 1));
  }
  const sanitized: Record<string, any> = {};
  for (const [key, value] of Object.entries(obj)) {
    if (SENSITIVE_KEYS.has(key) || SENSITIVE_KEYS.has(key.toLowerCase())) {
      sanitized[key] = "[REDACTED]";
    } else {
      sanitized[key] = sanitizeForLogging(value, depth + 1);
    }
  }
  return sanitized;
}

export function log(message: string, source = "express") {
  const formattedTime = new Date().toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    second: "2-digit",
    hour12: true,
  });

  console.log(`${formattedTime} [${source}] ${message}`);
}

app.use((req, res, next) => {
  const start = Date.now();
  const path = req.path;
  let capturedJsonResponse: Record<string, any> | undefined = undefined;

  const originalResJson = res.json;
  res.json = function (bodyJson, ...args) {
    capturedJsonResponse = bodyJson;
    return originalResJson.apply(res, [bodyJson, ...args]);
  };

  res.on("finish", () => {
    const duration = Date.now() - start;
    if (path.startsWith("/api")) {
      let logLine = `${req.method} ${path} ${res.statusCode} in ${duration}ms`;
      if (capturedJsonResponse) {
        const safe = sanitizeForLogging(capturedJsonResponse);
        logLine += ` :: ${JSON.stringify(safe)}`;
      }

      log(logLine);
    }
  });

  next();
});

(async () => {
  await registerRoutes(httpServer, app);

  app.use((err: any, _req: Request, res: Response, next: NextFunction) => {
    const status = err.status || err.statusCode || 500;
    const message = err.message || "Internal Server Error";

    if (!isProduction) {
      console.error("Internal Server Error:", err);
    } else {
      console.error(`[ERROR] ${status}: ${message}`);
    }

    if (res.headersSent) {
      return next(err);
    }

    return res.status(status).json({ message: isProduction ? "Internal Server Error" : message });
  });

  if (process.env.NODE_ENV === "production") {
    serveStatic(app);
  } else {
    const { setupVite } = await import("./vite");
    await setupVite(httpServer, app);
  }

  const port = parseInt(process.env.PORT || "5000", 10);
  httpServer.listen(
    {
      port,
      host: "0.0.0.0",
      reusePort: true,
    },
    () => {
      log(`serving on port ${port}`);
    },
  );
})();
