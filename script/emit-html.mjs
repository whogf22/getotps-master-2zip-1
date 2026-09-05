// Emits dist/public/index.html (+404.html SPA fallback) from client/index.html,
// rewiring the dev module entry to the built asset paths.
//
// Asset URLs carry a content hash query so a rebuilt bundle is never served from
// a stale HTTP cache (the JS entry chunk is hashed by esbuild; the Tailwind CSS
// is written to a fixed name, so it needs the query).
import { readFile, writeFile } from "fs/promises";
import { createHash } from "crypto";
import path from "path";

const ROOT = path.resolve(import.meta.dirname, "..");
const OUT = path.join(ROOT, "dist", "public");

const hashOf = async rel => {
  const buf = await readFile(path.join(OUT, rel));
  return createHash("sha256").update(buf).digest("hex").slice(0, 8);
};

const cssHash = await hashOf("assets/index.css");
const jsHash = await hashOf("assets/main.js");

let html = await readFile(path.join(ROOT, "client", "index.html"), "utf8");

html = html.replace(
  '<script type="module" src="/src/main.tsx"></script>',
  `<script type="module" src="./assets/main.js?v=${jsHash}"></script>`,
);

if (!html.includes("assets/index.css")) {
  html = html.replace(
    "</head>",
    `  <link rel="stylesheet" href="./assets/index.css?v=${cssHash}" />\n  </head>`,
  );
}

// Relative favicon paths so the bundle works from any base path
// (GitHub Pages project sites serve from /<repo>/).
html = html
  .replace('href="/favicon.svg"', 'href="./favicon.svg"')
  .replace('href="/apple-touch-icon.png"', 'href="./apple-touch-icon.png"');

await writeFile(path.join(OUT, "index.html"), html);
await writeFile(path.join(OUT, "404.html"), html);

console.log(`      wrote index.html + 404.html (css ${cssHash}, js ${jsHash})`);
