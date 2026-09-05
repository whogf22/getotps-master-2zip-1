#!/bin/sh
# Static client build — produces a deployable dist/public/ with no server.
#
# Why not `vite build`? On Alpine/aarch64 under emulation (iSH) rollup's
# WASM-backed import analysis fails, and esbuild's JS API gets its IPC service
# killed mid-build. Driving the native esbuild binary and the pure-JS Tailwind
# CLI directly works on every platform, including CI.
#
# Output: dist/public/{index.html,404.html,assets/*} — upload anywhere static.
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/dist/public"
TW="$ROOT/node_modules/tailwindcss/lib/cli.js"

# Prefer the platform-specific native binary; fall back to the wrapper on CI.
ESB="$ROOT/node_modules/@esbuild/linux-arm64/bin/esbuild"
[ -x "$ESB" ] || ESB="$ROOT/node_modules/esbuild/bin/esbuild"

cd "$ROOT"
rm -rf "$ROOT/dist"
mkdir -p "$OUT/assets"

echo "[1/4] bundling app (esbuild)..."
"$ESB" client/src/main.tsx \
  --bundle \
  --minify \
  --splitting \
  --format=esm \
  --target=es2020 \
  --jsx=automatic \
  --loader:.svg=dataurl \
  --loader:.png=dataurl \
  --define:process.env.NODE_ENV='"production"' \
  --alias:@="$ROOT/client/src" \
  --alias:@shared="$ROOT/shared" \
  --alias:@assets="$ROOT/attached_assets" \
  --outdir="$OUT/assets"

# esbuild emits the entry's CSS as main.css; Tailwind overwrites it next.
rm -f "$OUT/assets/main.css"

echo "[2/4] compiling tailwind css..."
node "$TW" \
  -c "$ROOT/tailwind.config.ts" \
  -i "$ROOT/client/src/index.css" \
  -o "$OUT/assets/index.css" \
  --minify

echo "[3/4] emitting index.html..."
node "$ROOT/script/emit-html.mjs"

echo "[4/4] copying public assets..."
if [ -d "$ROOT/client/public" ]; then
  cp -r "$ROOT/client/public/." "$OUT/"
fi

echo ""
echo "✓ static build complete → dist/public"
du -sh "$OUT" 2>/dev/null || true
ls -1 "$OUT" "$OUT/assets"
