// Reproducible concurrency test for wallet confirmation/refund atomicity.
//
// Spawns a fresh dev server against a throwaway SQLite DB, then fires genuinely
// parallel HTTP requests (Promise.all) to prove that a deposit is credited /
// an order is refunded AT MOST ONCE, even under simultaneous requests.
//
//   node script/concurrency-test.mjs
//
// Exits 0 on success, 1 on any failed assertion.

import { spawn } from "node:child_process";
import { mkdtempSync, rmSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { createRequire } from "node:module";
import net from "node:net";

const require = createRequire(import.meta.url);
const Database = require("better-sqlite3");

// Dynamically selected free port avoids collisions on CI / re-runs.
let PORT;
let BASE;
const ADMIN_EMAIL = "admin@getotps.online";
const ADMIN_PASSWORD = "concurrencyTestAdmin";
const tmp = mkdtempSync(join(tmpdir(), "getotps-conc-"));

function getFreePort() {
  return new Promise((resolve, reject) => {
    const srv = net.createServer();
    srv.unref();
    srv.on("error", reject);
    srv.listen(0, "127.0.0.1", () => {
      const { port } = srv.address();
      srv.close(() => resolve(port));
    });
  });
}
const DB_PATH = join(tmp, "test.db");

let failures = 0;
const check = (name, cond, extra = "") => {
  console.log(`${cond ? "PASS" : "FAIL"}: ${name}${extra ? " — " + extra : ""}`);
  if (!cond) failures++;
};

function getCookie(res) {
  const cookies = res.headers.getSetCookie ? res.headers.getSetCookie() : [];
  const sid = cookies.find((c) => c.startsWith("connect.sid="));
  return sid ? sid.split(";")[0] : null;
}

async function req(method, path, { cookie, body } = {}) {
  const res = await fetch(BASE + path, {
    method,
    headers: {
      "Content-Type": "application/json",
      ...(cookie ? { Cookie: cookie } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  let json = null;
  try { json = await res.json(); } catch {}
  return { status: res.status, json, cookie: getCookie(res) };
}

function waitForServer(retries = 60) {
  return new Promise((resolve, reject) => {
    const tick = async () => {
      try {
        const r = await fetch(BASE + "/");
        if (r.ok) return resolve();
      } catch {}
      if (--retries <= 0) return reject(new Error("server did not start"));
      setTimeout(tick, 500);
    };
    tick();
  });
}

async function run() {
  // ── Test 1: N simultaneous confirmations of the same pending deposit ────────
  const a = await req("POST", "/api/auth/register", {
    body: { username: "concA", email: "concA@example.com", password: "Password123" },
  });
  check("register user A", a.status === 200 && a.cookie, `status ${a.status}`);
  const cookieA = a.cookie;
  const userAId = a.json?.id;

  const dep = await req("POST", "/api/crypto/create-deposit", {
    cookie: cookieA, body: { currency: "BTC", amount: "50" },
  });
  check("create $50 deposit", dep.status === 200, `status ${dep.status}`);
  const depId = dep.json?.id;

  const N = 8;
  const confirmResults = await Promise.all(
    Array.from({ length: N }, () => req("POST", `/api/crypto/${depId}/simulate-confirm`, { cookie: cookieA })),
  );
  const confirmOk = confirmResults.filter((r) => r.status === 200).length;
  const confirmConflict = confirmResults.filter((r) => r.status === 409).length;
  check("T1 exactly ONE confirmation succeeded", confirmOk === 1, `200s=${confirmOk}, 409s=${confirmConflict}`);
  check("T1 all other confirmations returned 409", confirmConflict === N - 1, `409s=${confirmConflict}`);

  const balA1 = await req("GET", "/api/balance", { cookie: cookieA });
  check("T1 wallet credited EXACTLY once ($50.00)", balA1.json?.balance === "50.00", `balance=${balA1.json?.balance}`);

  // ── Test 4: idempotency — re-sending confirmation must not credit again ─────
  const reConfirm = await req("POST", `/api/crypto/${depId}/simulate-confirm`, { cookie: cookieA });
  const balA2 = await req("GET", "/api/balance", { cookie: cookieA });
  check("T4 re-confirm returns 409", reConfirm.status === 409, `status ${reConfirm.status}`);
  check("T4 balance unchanged after re-confirm ($50.00)", balA2.json?.balance === "50.00", `balance=${balA2.json?.balance}`);

  // ── Test 2: confirmation vs reject race on the same deposit ─────────────────
  const dep2 = await req("POST", "/api/crypto/create-deposit", {
    cookie: cookieA, body: { currency: "BTC", amount: "30" },
  });
  const dep2Id = dep2.json?.id;
  const admin = await req("POST", "/api/auth/login", {
    body: { email: ADMIN_EMAIL, password: ADMIN_PASSWORD },
  });
  check("admin login", admin.status === 200 && admin.cookie, `status ${admin.status}`);
  const cookieAdmin = admin.cookie;

  const [confirmR, rejectR] = await Promise.all([
    req("POST", `/api/crypto/${dep2Id}/simulate-confirm`, { cookie: cookieA }),
    req("POST", `/api/admin/crypto/${dep2Id}/reject`, { cookie: cookieAdmin }),
  ]);
  const winners = [confirmR.status, rejectR.status];
  const oneWon = winners.filter((s) => s === 200).length === 1;
  check("T2 exactly one terminal transition won", oneWon, `confirm=${confirmR.status}, reject=${rejectR.status}`);
  const balA3 = await req("GET", "/api/balance", { cookie: cookieA });
  // If confirm won: 50 + 30 = 80. If reject won: stays 50. Never both (110).
  const okBalance = balA3.json?.balance === "80.00" || balA3.json?.balance === "50.00";
  check("T2 wallet never got both credit AND refund", okBalance, `balance=${balA3.json?.balance}`);
  const confirmWon = confirmR.status === 200;
  check("T2 balance matches the winner", balA3.json?.balance === (confirmWon ? "80.00" : "50.00"),
    `confirmWon=${confirmWon}, balance=${balA3.json?.balance}`);

  const balanceBeforeRefund = balA3.json?.balance;

  // ── Test 3: two simultaneous refunds of the same order ──────────────────────
  // Seed a pending order directly (order creation itself needs the Proxnum API).
  const seed = new Database(DB_PATH);
  seed.pragma("busy_timeout = 5000");
  const now = new Date().toISOString();
  const info = seed.prepare(
    `INSERT INTO orders (user_id, service_id, service_name, phone_number, status, price, country, proxnum_id, created_at, expires_at)
     VALUES (?, 0, 'TestSvc', '+15550000000', 'pending', '10.00', 'us', NULL, ?, ?)`
  ).run(userAId, now, now);
  const orderId = Number(info.lastInsertRowid);
  seed.close();

  const refundResults = await Promise.all(
    Array.from({ length: N }, () => req("POST", `/api/orders/${orderId}/cancel`, { cookie: cookieA })),
  );
  const refundOk = refundResults.filter((r) => r.status === 200).length;
  // Losers are rejected either by the atomic guard (409) or the status pre-check
  // (400) depending on scheduling — both mean "no second refund".
  const refundRejected = refundResults.filter((r) => r.status === 400 || r.status === 409).length;
  check("T3 exactly ONE refund succeeded", refundOk === 1, `200s=${refundOk}`);
  check("T3 all other refunds were rejected (400/409)", refundRejected === N - 1, `rejected=${refundRejected}`);
  const balA4 = await req("GET", "/api/balance", { cookie: cookieA });
  const expectedAfterRefund = (parseFloat(balanceBeforeRefund) + 10).toFixed(2);
  check("T3 wallet refunded EXACTLY once (+$10.00)", balA4.json?.balance === expectedAfterRefund,
    `before=${balanceBeforeRefund}, after=${balA4.json?.balance}, expected=${expectedAfterRefund}`);

  // ── Test 5: cross-user isolation ────────────────────────────────────────────
  const b = await req("POST", "/api/auth/register", {
    body: { username: "concB", email: "concB@example.com", password: "Password123" },
  });
  const cookieB = b.cookie;
  const dep3 = await req("POST", "/api/crypto/create-deposit", { cookie: cookieA, body: { currency: "BTC", amount: "15" } });
  const dep3Id = dep3.json?.id;
  const xConfirm = await req("POST", `/api/crypto/${dep3Id}/simulate-confirm`, { cookie: cookieB });
  const xSubmit = await req("POST", `/api/crypto/${dep3Id}/submit-hash`, { cookie: cookieB, body: { txHash: "0xdeadbeef1234" } });
  const xOrderGet = await req("GET", `/api/orders/${orderId}`, { cookie: cookieB });
  check("T5 user B cannot confirm A's deposit (403)", xConfirm.status === 403, `status ${xConfirm.status}`);
  check("T5 user B cannot submit-hash A's deposit (403)", xSubmit.status === 403, `status ${xSubmit.status}`);
  check("T5 user B cannot read A's order (403)", xOrderGet.status === 403, `status ${xOrderGet.status}`);

  console.log(`\n${failures === 0 ? "ALL CONCURRENCY TESTS PASSED" : failures + " ASSERTION(S) FAILED"}`);
}

PORT = await getFreePort();
BASE = `http://localhost:${PORT}`;

const server = spawn("npx", ["tsx", "server/index.ts"], {
  env: {
    ...process.env,
    NODE_ENV: "development",
    PORT: String(PORT),
    DATABASE_URL: DB_PATH,
    SESSION_SECRET: "concurrency-test-secret",
    ADMIN_EMAIL,
    ADMIN_PASSWORD,
    // Self-contained + secret-free: no real .env, provider key, or wallet
    // secret. A dummy wallet address makes the BTC deposit path usable so the
    // test never depends on a committed .env or real crypto-wallet config.
    PROXNUM_API_KEY: "",
    CRYPTO_WALLET_BTC: "TEST_ONLY_BTC_WALLET_ADDRESS",
  },
  stdio: "ignore",
  // Own process group so we can kill npx + tsx + node together (npx does not
  // forward signals to the grandchild node process).
  detached: true,
});

function killServerGroup() {
  try { process.kill(-server.pid, "SIGKILL"); } catch {}
  try { server.kill("SIGKILL"); } catch {}
}

try {
  await waitForServer();
  await run();
} catch (err) {
  console.error("Test harness error:", err);
  failures++;
} finally {
  // Force-kill the whole process group and wait for exit so the port frees
  // immediately (avoids collisions when the test is re-run back-to-back).
  await new Promise((resolve) => {
    let done = false;
    const finish = () => { if (!done) { done = true; resolve(); } };
    server.once("exit", finish);
    killServerGroup();
    setTimeout(finish, 3000);
  });
  try { rmSync(tmp, { recursive: true, force: true }); } catch {}
  process.exit(failures === 0 ? 0 : 1);
}
