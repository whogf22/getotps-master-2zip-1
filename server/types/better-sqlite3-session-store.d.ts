declare module "better-sqlite3-session-store" {
  import type session from "express-session";
  import type Database from "better-sqlite3";

  interface ExpiredConfig {
    clear?: boolean;
    intervalMs?: number;
  }

  interface SqliteStoreOptions {
    client: Database;
    expired?: ExpiredConfig;
  }

  interface SessionStoreFactory {
    new (options: SqliteStoreOptions): session.Store;
  }

  export default function betterSqlite3SessionStore(
    sessionModule: typeof session,
  ): SessionStoreFactory;
}
