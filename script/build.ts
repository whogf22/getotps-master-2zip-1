import { build as esbuild } from "esbuild";
import { build as viteBuild } from "vite";
import { rm, readFile } from "fs/promises";

// Bundle these into the server output. All other deps stay external
// (loaded from node_modules at runtime). Native modules MUST stay external.
const allowlist = [
  "@google/generative-ai",
  "axios",
  "cors",
  "date-fns",
  "drizzle-orm",
  "drizzle-zod",
  "express",
  "express-rate-limit",
  "express-session",
  "jsonwebtoken",
  "memorystore",
  "multer",
  "nanoid",
  "nodemailer",
  "openai",
  "passport",
  "passport-local",
  "stripe",
  "uuid",
  "ws",
  "xlsx",
  "zod",
  "zod-validation-error",
  "helmet",
  "dotenv",
  "bcryptjs",
];

// Native bindings: never bundle
const forceExternal = [
  "better-sqlite3",
  "better-sqlite3-session-store",
];

async function buildAll() {
  await rm("dist", { recursive: true, force: true });

  console.log("building client...");
  await viteBuild();

  console.log("building server...");
  const pkg = JSON.parse(await readFile("package.json", "utf-8"));
  const allDeps = [
    ...Object.keys(pkg.dependencies || {}),
    ...Object.keys(pkg.devDependencies || {}),
  ];
  const externals = Array.from(
    new Set([
      ...allDeps.filter((dep) => !allowlist.includes(dep)),
      ...forceExternal,
    ])
  );

  await esbuild({
    entryPoints: ["server/index.ts"],
    platform: "node",
    bundle: true,
    format: "cjs",
    outfile: "dist/index.cjs",
    define: {
      "process.env.NODE_ENV": '"production"',
    },
    minify: true,
    external: externals,
    logLevel: "info",
    banner: {
      js: "const { createRequire: __cr } = require('module'); const require2 = __cr(__filename);",
    },
  });
}

buildAll().catch((err) => {
  console.error(err);
  process.exit(1);
});
