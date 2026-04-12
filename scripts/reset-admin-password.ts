import Database from "better-sqlite3";
import bcrypt from "bcryptjs";
import crypto from "crypto";

const args = process.argv.slice(2);
const newPassword = args[0];

if (!newPassword || newPassword.length < 8) {
  console.error("Usage: npx tsx scripts/reset-admin-password.ts <new-password>");
  console.error("Password must be at least 8 characters.");
  process.exit(1);
}

const dbPath = process.env.DATABASE_URL?.replace("file:", "") || "data.db";
const db = new Database(dbPath);

const admin = db.prepare("SELECT id, username, email FROM users WHERE role = 'admin' LIMIT 1").get() as any;

if (!admin) {
  console.error("No admin user found in the database.");
  process.exit(1);
}

const hashed = bcrypt.hashSync(newPassword, 12);
db.prepare("UPDATE users SET password = ? WHERE id = ?").run(hashed, admin.id);

console.log(`Admin password reset successfully.`);
console.log(`  User: ${admin.username} (${admin.email})`);
console.log(`  ID: ${admin.id}`);

db.close();
