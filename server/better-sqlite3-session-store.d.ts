// The `better-sqlite3-session-store` package ships no type declarations.
// Provide a minimal ambient module so `tsc` (strict mode) can resolve it.
declare module "better-sqlite3-session-store" {
  import type session from "express-session";
  type SessionStoreFactory = (s: typeof session) => new (options: {
    client: unknown;
    expired?: { clear?: boolean; intervalMs?: number };
  }) => session.Store;
  const BetterSqlite3SessionStore: SessionStoreFactory;
  export default BetterSqlite3SessionStore;
}
