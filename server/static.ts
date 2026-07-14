import express, { type Express, type Request, type Response } from "express";
import fs from "fs";
import path from "path";

// SPA routes that should serve index.html with HTTP 200.
const SPA_ROUTES = new Set([
  "/", "/login", "/register", "/dashboard", "/buy",
  "/active", "/history", "/add-funds", "/funds", "/api-docs",
  "/profile", "/admin", "/admin/users", "/admin/deposits",
  "/admin/settings",
]);

function resolveDistPath(): string {
  // Works whether running from the bundled CJS (dist/index.cjs) or an
  // unexpected layout. Native/dev fallbacks keep this robust.
  const candidates = [
    path.resolve(__dirname, "public"),
    path.resolve(process.cwd(), "dist/public"),
    path.resolve(__dirname, "../dist/public"),
  ];
  const found = candidates.find((p) => fs.existsSync(p));
  if (!found) {
    throw new Error(
      `Could not find the build directory. Tried: ${candidates.join(", ")}. Run "npm run build" first.`
    );
  }
  return found;
}

export function serveStatic(app: Express) {
  const distPath = resolveDistPath();

  const sendApp = (res: Response, status: number) => {
    // index.html must never be cached so new deploys are picked up immediately.
    res.status(status);
    res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
    res.sendFile(path.resolve(distPath, "index.html"));
  };

  app.use(
    express.static(distPath, {
      index: false, // let the SPA fallback own "/" so index.html stays uncached
      setHeaders: (res, filePath) => {
        if (filePath.endsWith(".html")) {
          res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
        } else {
          // Vite emits content-hashed asset filenames, safe to cache long-term.
          res.setHeader("Cache-Control", "public, max-age=31536000, immutable");
        }
      },
    })
  );

  // Path-less catch-all so req.path is the FULL request path (a mounted path
  // pattern would strip the prefix and misclassify every route as "/").
  app.use((req: Request, res: Response) => {
    // Never hand the SPA shell to an unmatched API call.
    if (req.path.startsWith("/api")) {
      return res.status(404).json({ message: "Not found" });
    }

    // Only GET/HEAD should ever receive the SPA shell.
    if (req.method !== "GET" && req.method !== "HEAD") {
      return res.status(404).json({ message: "Not found" });
    }

    const cleanPath = req.path.replace(/\/$/, "") || "/";

    // Missing files with an extension are true 404s (no directory traversal:
    // express.static already rejected the path, and we only send a fixed file).
    if (path.extname(cleanPath) !== "") {
      return res.status(404).send("Not found");
    }

    // Known SPA routes (and admin subtree) → serve the app with 200.
    if (SPA_ROUTES.has(cleanPath) || cleanPath.startsWith("/admin")) {
      return sendApp(res, 200);
    }

    // Unknown route → serve the SPA shell but signal 404 for crawlers.
    return sendApp(res, 404);
  });
}
