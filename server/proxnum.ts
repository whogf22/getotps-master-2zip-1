const PROXNUM_API_URL = "https://proxnum.com/api/v1";
const PROXNUM_API_KEY = process.env.PROXNUM_API_KEY || "";

interface ProxnumResponse {
  success?: boolean;
  error?: {
    code: string;
    key: string;
    message: string;
  };
  [key: string]: any;
}

async function request(
  method: "GET" | "POST",
  endpoint: string,
  data: Record<string, any> = {},
  authenticated = true
): Promise<ProxnumResponse> {
  let url = PROXNUM_API_URL + endpoint;

  const headers: Record<string, string> = {
    "Content-Type": "application/json",
  };

  if (authenticated) {
    headers["Authorization"] = `Bearer ${PROXNUM_API_KEY}`;
  }

  const options: RequestInit = { method, headers };

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
        error: {
          code: `E${res.status}`,
          key: "not_json",
          message: `API returned non-JSON response (${res.status}). Auth may have failed.`,
        },
      };
    }
    const result = await res.json();

    if (!res.ok && (!result || !result.error)) {
      return {
        success: false,
        error: {
          code: `E${res.status}`,
          key: "http_error",
          message: `HTTP Error: ${res.status}`,
        },
      };
    }

    return result;
  } catch (err: any) {
    return {
      success: false,
      error: {
        code: "E9999",
        key: "connection_error",
        message: `Failed to connect to Proxnum API: ${err.message}`,
      },
    };
  }
}

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
    return request("GET", "/prices", params, false);
  },

  getAvailability(country: string, service: string) {
    return request("GET", "/availability", { country, service }, false);
  },

  buyVirtual(service: string, country: string) {
    return request("POST", "/virtual/buy", { service, country });
  },

  getVirtualStatus(id: string | number) {
    return request("GET", `/virtual/${id}/status`);
  },

  cancelVirtual(id: string | number) {
    return request("POST", `/virtual/${id}/cancel`);
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

  listActivations(limit = 50, page = 1) {
    return request("GET", "/resell/activations", { per_page: limit, page });
  },

  listRentals(limit = 50, page = 1) {
    return request("GET", "/resell/rentals", { per_page: limit, page });
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
