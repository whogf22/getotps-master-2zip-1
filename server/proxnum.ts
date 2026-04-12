const PROXNUM_API_URL = "https://proxnum.com/api/v1";
const PROXNUM_API_KEY = process.env.PROXNUM_API_KEY || "";

const REQUEST_TIMEOUT_MS = 15000;
const MAX_RETRIES = 2;
const RETRY_DELAY_MS = 1000;

interface ProxnumResponse {
  success?: boolean;
  code?: string;
  message?: string;
  error?: {
    code: string;
    key: string;
    message: string;
  };
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

function sleep(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

const NON_RETRYABLE_CODES = new Set([
  "no_numbers", "insufficient_balance", "cancel_rejected",
  "invalid_service", "invalid_country",
]);

async function request(
  method: "GET" | "POST",
  endpoint: string,
  data: Record<string, any> = {},
  authenticated = true,
  retries = MAX_RETRIES
): Promise<ProxnumResponse> {
  let url = PROXNUM_API_URL + endpoint;

  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    "Accept": "application/json",
  };

  if (authenticated) {
    headers["Authorization"] = `Bearer ${PROXNUM_API_KEY}`;
  }

  const options: RequestInit = {
    method,
    headers,
    signal: AbortSignal.timeout(REQUEST_TIMEOUT_MS),
  };

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

  let lastError: any;

  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      const res = await fetch(url, options);
      const contentType = res.headers.get("content-type") || "";
      if (!contentType.includes("application/json")) {
        const errResponse: ProxnumResponse = {
          success: false,
          code: "not_json",
          error: {
            code: `E${res.status}`,
            key: "not_json",
            message: `API returned non-JSON response (${res.status}). Auth may have failed.`,
          },
        };
        if (res.status >= 500 && attempt < retries) {
          await sleep(RETRY_DELAY_MS * (attempt + 1));
          continue;
        }
        return errResponse;
      }

      const result = await res.json();

      if (!res.ok && !result.success && !result.error) {
        const errResponse: ProxnumResponse = {
          success: false,
          code: result.code || `E${res.status}`,
          message: result.message || `HTTP Error: ${res.status}`,
          error: {
            code: result.code || `E${res.status}`,
            key: "http_error",
            message: result.message || `HTTP Error: ${res.status}`,
          },
        };

        const errCode = result.code || "";
        if (NON_RETRYABLE_CODES.has(errCode) || res.status < 500) {
          return errResponse;
        }
        if (attempt < retries) {
          await sleep(RETRY_DELAY_MS * (attempt + 1));
          continue;
        }
        return errResponse;
      }

      return result;
    } catch (err: any) {
      lastError = err;
      const isTimeout = err.name === "TimeoutError" || err.name === "AbortError";
      const isNetwork = err.code === "ECONNREFUSED" || err.code === "ECONNRESET" || err.code === "ENOTFOUND";

      if ((isTimeout || isNetwork) && attempt < retries) {
        console.warn(`[Proxnum] ${method} ${endpoint} attempt ${attempt + 1} failed (${err.message}), retrying...`);
        await sleep(RETRY_DELAY_MS * (attempt + 1));
        continue;
      }

      return {
        success: false,
        code: "connection_error",
        error: {
          code: "E9999",
          key: isTimeout ? "timeout" : "connection_error",
          message: isTimeout
            ? `Request to Proxnum API timed out after ${REQUEST_TIMEOUT_MS}ms`
            : `Failed to connect to Proxnum API: ${err.message}`,
        },
      };
    }
  }

  return {
    success: false,
    code: "connection_error",
    error: {
      code: "E9999",
      key: "connection_error",
      message: `Failed after ${retries + 1} attempts: ${lastError?.message || "unknown error"}`,
    },
  };
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
    return request("POST", "/resell/virtual/buy", { service, country: Number(country) }, true, 0);
  },

  getVirtualStatus(activationId: string | number) {
    return request("GET", `/resell/virtual/${activationId}/status`);
  },

  cancelVirtual(activationId: string | number) {
    return request("POST", "/resell/virtual/cancel", { activation_id: String(activationId) }, true, 0);
  },

  resendVirtual(activationId: string | number) {
    return request("POST", "/resell/virtual/resend", { activation_id: String(activationId) }, true, 0);
  },

  getUserBalance() {
    return request("GET", "/user/balance");
  },

  buyRental(service: string, country: string, days = 7) {
    return request("POST", "/rental/buy", { service, country, days }, true, 0);
  },

  getRentalStatus(id: string | number) {
    return request("GET", `/rental/${id}/status`);
  },

  cancelRental(id: string | number) {
    return request("POST", "/rental/cancel", { id }, true, 0);
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
    countriesCache = { data: result.countries, updatedAt: Date.now() };
    return result.countries;
  }
  return countriesCache?.data || [];
}

export async function getCachedPrices(country?: string, service?: string): Promise<Record<string, Record<string, ProxnumPriceEntry>>> {
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
  return pricesCache?.data || {};
}

export function findCountryCode(countries: ProxnumCountry[], search: string): string | undefined {
  const lower = search.toLowerCase();
  let exactCode = countries.find((c) => c.code === search);
  if (exactCode) return exactCode.code;

  let exactName = countries.find((c) => c.name.toLowerCase() === lower);
  if (exactName) return exactName.code;

  let partialMatch = countries.find((c) => c.name.toLowerCase().includes(lower));
  return partialMatch?.code;
}

export function getUSCountryCode(countries: ProxnumCountry[]): string {
  const exact = countries.find((c) => c.name === "USA");
  if (exact) return exact.code;
  const virtual = countries.find((c) => c.name === "USA (virtual)");
  if (virtual) return virtual.code;
  return "187";
}
