import express, { type Express } from "express";
import fs from "fs";
import path from "path";

// SPA routes that should serve index.html with HTTP 200
const SPA_ROUTES = new Set([
  "/", "/login", "/register", "/dashboard", "/buy",
  "/active", "/history", "/add-funds", "/api-docs",
  "/profile", "/admin", "/admin/users", "/admin/deposits",
  "/admin/settings", "/privacy", "/terms",
]);

export function serveStatic(app: Express) {
  const distPath = path.resolve(__dirname, "public");
  if (!fs.existsSync(distPath)) {
    throw new Error(
      `Could not find the build directory: ${distPath}, make sure to build the client first`,
    );
  }

  app.use(express.static(distPath));

  // Serve index.html for known SPA routes — HTTP 200
  // Return HTTP 404 for unknown paths (files with extensions, unknown routes)
  app.use("/{*path}", (req, res) => {
    const cleanPath = req.path.replace(/\/$/, "") || "/";

    // Files with extensions that don't exist → 404
    if (path.extname(cleanPath) !== "") {
      return res.status(404).send("Not found");
    }

    // Known SPA routes → serve app
    if (SPA_ROUTES.has(cleanPath) || cleanPath.startsWith("/admin")) {
      return res.sendFile(path.resolve(distPath, "index.html"));
    }

    // /buy?service=X → serve app (query-param based routes)
    if (cleanPath === "/buy") {
      return res.sendFile(path.resolve(distPath, "index.html"));
    }

    // Unknown path → 404 with a proper HTML page
    res.status(404).sendFile(path.resolve(distPath, "index.html"));
  });
}
