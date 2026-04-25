import express, { type Express } from "express";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname2 = path.dirname(__filename);

export function serveStatic(app: Express) {
  // Try multiple candidate paths so this works whether running from
  // `tsx server/index.ts` (dev) or `node dist/index.cjs` (prod build).
  const candidates = [
    path.resolve(process.cwd(), "dist/public"),
    path.resolve(__dirname2, "public"),
    path.resolve(__dirname2, "../dist/public"),
  ];
  const distPath = candidates.find((p) => fs.existsSync(p));

  if (!distPath) {
    throw new Error(
      `Could not find the build directory. Tried: ${candidates.join(", ")}. Run "npm run build" first.`
    );
  }

  app.use(express.static(distPath));

  // SPA fallback for client-side routing
  app.use("/*splat", (_req, res) => {
    res.sendFile(path.resolve(distPath, "index.html"));
  });
}
