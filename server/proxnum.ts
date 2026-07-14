const PROXNUM_API_URL = "https://proxnum.com/api/v1";
const PROXNUM_API_KEY = process.env.PROXNUM_API_KEY || "";

if (!PROXNUM_API_KEY) {
  // Never log the key itself — only whether it is present.
  const msg = "PROXNUM_API_KEY is not set — virtual number purchases/rentals will fail.";
  if (process.env.NODE_ENV === "production") {
    console.error(`FATAL: ${msg}`);
  } else {
    console.warn(`WARNING: ${msg}`);
  }
}

// Abort provider requests that hang so a slow upstream can never wedge our API.
const REQUEST_TIMEOUT_MS = 15_000;

interface ProxnumResponse {
  success?: boolean;
  code?: string;
  message?: string;
  error?: {
    code: string;
    key: string;
    message: string;
  };
  // The Proxnum payload shape varies per endpoint; callers read dynamic fields.
  [key: string]: any;
}

const PROXNUM_ERROR_MESSAGES: Record<string, string> = {
  no_numbers: "No numbers available for this service in the selected country",
  insufficient_balance: "Proxnum account has insufficient balance. Contact admin.",
  service_unavailable: "This service is temporarily unavailable. Try again later.",
  cancel_rejected: "Cancellation rejected — activation already completed or not refundable",
};

function friendlyError(res: ProxnumResponse): string {
  const code = res.code || res.error?.code || "";
  if (PROXNUM_ERROR_MESSAGES[code]) return PROXNUM_ERROR_MESSAGES[code];
  return res.message || res.error?.message || "An unexpected error occurred with the SMS provider";
}

// ── Flag normalization ─────────────────────────────────────────────────────────
// Proxnum returns a broken placeholder flag (🇲🇲) for many countries. Map those
// names to their real ISO-3166 alpha-2 code and derive the correct emoji flag.
const COUNTRY_ISO_MAP: Record<string, string> = {
  "Japan": "JP", "South Korea": "KR", "North Korea": "KP", "Singapore": "SG",
  "USA": "US", "USA (virtual)": "US", "Palestine": "PS", "Fiji": "FJ",
  "Western Sahara": "EH", "Solomon Islands": "SB", "Jersey": "JE",
  "Bermuda": "BM", "Tonga": "TO", "Samoa": "WS", "Malta": "MT",
  "Liechtenstein": "LI", "Gibraltar": "GI", "Faroe Islands": "FO", "Kosovo": "XK",
  "Aruba": "AW", "Montserrat": "MS", "Anguilla": "AI", "North Macedonia": "MK",
  "Seychelles": "SC", "New Caledonia": "NC", "Cape Verde": "CV",
};

// The regional-indicator placeholder Proxnum sends when it has no flag (🇲🇲).
const BROKEN_FLAG = "\uD83C\uDDF2\uD83C\uDDF2";

function isoToFlag(iso2: string): string {
  return iso2
    .toUpperCase()
    .split("")
    .map((c) => String.fromCodePoint(0x1f1e6 + c.charCodeAt(0) - 65))
    .join("");
}

function normalizeFlag(name: string, flag?: string): string {
  if (flag && flag !== BROKEN_FLAG) return flag;
  const iso = COUNTRY_ISO_MAP[name];
  if (iso) return isoToFlag(iso);
  return flag || "";
}

function deduplicateCountries(countries: ProxnumCountry[]): ProxnumCountry[] {
  const seen = new Set<string>();
  return countries.filter((c) => {
    const key = c.name.toLowerCase().trim();
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}
// ────────────────────────────────────────────────────────────────────────────

async function request(
  method: "GET" | "POST",
  endpoint: string,
  data: Record<string, any> = {},
  authenticated = true
): Promise<ProxnumResponse> {
  let url = PROXNUM_API_URL + endpoint;

  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    "Accept": "application/json",
  };

  if (authenticated) {
    headers["Authorization"] = `Bearer ${PROXNUM_API_KEY}`;
  }

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);
  const options: RequestInit = { method, headers, signal: controller.signal };

  if (method === "GET" && Object.keys(data).length > 0) {
    const params = new URLSearchParams();
    for (const [k, v] of Object.entries(data)) {
      if (v !== undefined && v !== null) params.set(k, String(v));
    }
    url += "?" + params.toString();
  }

  if (method === "POST") {
    options.body = JSON.stringify(data);
  }

  try {
    const res = await fetch(url, options);
    const contentType = res.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
      return {
        success: false,
        code: "not_json",
        error: {
          code: `E${res.status}`,
          key: "not_json",
          message: `API returned non-JSON response (${res.status}). Auth may have failed.`,
        },
      };
    }
    const result = await res.json();

    if (!res.ok && !result.success && !result.error) {
      return {
        success: false,
        code: result.code || `E${res.status}`,
        message: result.message || `HTTP Error: ${res.status}`,
        error: {
          code: result.code || `E${res.status}`,
          key: "http_error",
          message: result.message || `HTTP Error: ${res.status}`,
        },
      };
    }

    return result;
  } catch (err: any) {
    const isTimeout = err?.name === "AbortError";
    return {
      success: false,
      code: isTimeout ? "timeout" : "connection_error",
      error: {
        code: isTimeout ? "E4080" : "E9999",
        key: isTimeout ? "timeout" : "connection_error",
        message: isTimeout
          ? "The SMS provider took too long to respond. Please try again."
          : `Failed to connect to Proxnum API: ${err?.message ?? "unknown error"}`,
      },
    };
  } finally {
    clearTimeout(timer);
  }
}

export { friendlyError };

export const proxnumApi = {
  getCountries() {
    return request("GET", "/countries", {}, false);
  },

  getServices() {
    return request("GET", "/services", {}, false);
  },

  getPrices(country?: string, service?: string) {
    const params: Record<string, string> = {};
    if (country) params.country = country;
    if (service) params.service = service;
    return request("GET", "/prices", params, true);
  },

  getResellPrice(service: string, country: string | number) {
    return request("GET", "/resell/price", { service, country }, true);
  },

  getAvailability(country: string, service: string) {
    return request("GET", "/availability", { country, service }, false);
  },

  buyVirtual(service: string, country: string | number) {
    return request("POST", "/resell/virtual/buy", { service, country: Number(country) });
  },

  getVirtualStatus(activationId: string | number) {
    return request("GET", `/resell/virtual/${activationId}/status`);
  },

  cancelVirtual(activationId: string | number) {
    return request("POST", "/resell/virtual/cancel", { activation_id: String(activationId) });
  },

  resendVirtual(activationId: string | number) {
    return request("POST", "/resell/virtual/resend", { activation_id: String(activationId) });
  },

  getUserBalance() {
    return request("GET", "/user/balance");
  },

  buyRental(service: string, country: string, days = 7) {
    return request("POST", "/rental/buy", { service, country, days });
  },

  getRentalStatus(id: string | number) {
    return request("GET", `/rental/${id}/status`);
  },

  cancelRental(id: string | number) {
    return request("POST", "/rental/cancel", { id });
  },

  getRentalMessages(rentalId: string | number) {
    return request("GET", `/rentals/${rentalId}/messages`);
  },

  getRentalPrices(service: string, country: string) {
    return request("GET", "/rental/prices", { service, country }, false);
  },

  getRentalAvailability(country?: string, service?: string) {
    const params: Record<string, string> = {};
    if (country) params.country = country;
    if (service) params.service = service;
    return request("GET", "/rental/availability", params, false);
  },

  getServicesByCountry(country: string) {
    return request("GET", `/countries/${country}/services`, {}, false);
  },

  listActivations(page = 1, perPage = 50) {
    return request("GET", "/resell/activations", { page, per_page: perPage });
  },

  listRentals(page = 1, perPage = 50) {
    return request("GET", "/resell/rentals", { page, per_page: perPage });
  },
};

export interface ProxnumService {
  service: string;
  name: string;
  icon?: string;
}

export interface ProxnumCountry {
  code: string;
  name: string;
  flag?: string;
}

export interface ProxnumPriceEntry {
  base_price: number;
  sell_price: number;
  available: number;
}

let servicesCache: { data: ProxnumService[]; updatedAt: number } | null = null;
let countriesCache: { data: ProxnumCountry[]; updatedAt: number } | null = null;
let pricesCache: { data: Record<string, Record<string, ProxnumPriceEntry>>; updatedAt: number; cacheKey: string } | null = null;
const CACHE_TTL = 5 * 60 * 1000;

export async function getCachedServices(): Promise<ProxnumService[]> {
  if (servicesCache && Date.now() - servicesCache.updatedAt < CACHE_TTL) {
    return servicesCache.data;
  }
  const result = await proxnumApi.getServices();
  if (result.success && Array.isArray(result.services)) {
    servicesCache = { data: result.services, updatedAt: Date.now() };
    return result.services;
  }
  return servicesCache?.data || [];
}

export async function getCachedCountries(): Promise<ProxnumCountry[]> {
  if (countriesCache && Date.now() - countriesCache.updatedAt < CACHE_TTL) {
    return countriesCache.data;
  }
  const result = await proxnumApi.getCountries();
  if (result.success && Array.isArray(result.countries)) {
    const normalized = (result.countries as ProxnumCountry[]).map((c) => ({
      ...c,
      flag: normalizeFlag(c.name, c.flag),
    }));
    const deduped = deduplicateCountries(normalized);
    countriesCache = { data: deduped, updatedAt: Date.now() };
    // Return the normalized + deduplicated list, NOT the raw provider response.
    return deduped;
  }
  return countriesCache?.data || [];
}

export async function getCachedPrices(
  country?: string,
  service?: string
): Promise<Record<string, Record<string, ProxnumPriceEntry>>> {
  const cacheKey = `${country || "all"}_${service || "all"}`;
  if (pricesCache && Date.now() - pricesCache.updatedAt < CACHE_TTL && pricesCache.cacheKey === cacheKey) {
    return pricesCache.data;
  }
  const result = await proxnumApi.getPrices(country, service);
  if (result.success && result.prices) {
    const prices = result.prices as Record<string, Record<string, ProxnumPriceEntry>>;
    pricesCache = { data: prices, updatedAt: Date.now(), cacheKey };
    return prices;
  }
  // Only reuse the cache when it belongs to the same query key.
  if (pricesCache && pricesCache.cacheKey === cacheKey) return pricesCache.data;
  return {};
}

export function findCountryCode(countries: ProxnumCountry[], search: string): string | undefined {
  const lower = search.toLowerCase();
  const exactCode = countries.find((c) => c.code === search);
  if (exactCode) return exactCode.code;

  const exactName = countries.find((c) => c.name.toLowerCase() === lower);
  if (exactName) return exactName.code;

  const partialMatch = countries.find((c) => c.name.toLowerCase().includes(lower));
  return partialMatch?.code;
}

export function getUSCountryCode(countries: ProxnumCountry[]): string {
  const exact = countries.find((c) => c.name === "USA");
  if (exact) return exact.code;
  const virtual = countries.find((c) => c.name === "USA (virtual)");
  if (virtual) return virtual.code;
  return "187";
}
