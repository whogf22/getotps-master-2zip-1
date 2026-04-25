const PROXNUM\_API\_URL = "https://proxnum.com/api/v1";
const PROXNUM\_API\_KEY = process.env.PROXNUM\_API\_KEY \|\| "";

interface ProxnumResponse {
 success?: boolean;
 code?: string;
 message?: string;
 error?: {
 code: string;
 key: string;
 message: string;
 };
 \[key: string\]: any;
}

const PROXNUM\_ERROR\_MESSAGES: Record = {
 no\_numbers: "No numbers available for this service in the selected country",
 insufficient\_balance: "Proxnum account has insufficient balance. Contact admin.",
 service\_unavailable: "This service is temporarily unavailable. Try again later.",
 cancel\_rejected: "Cancellation rejected — activation already completed or not refundable",
};

function friendlyError(res: ProxnumResponse): string {
 const code = res.code \|\| res.error?.code \|\| "";
 if (PROXNUM\_ERROR\_MESSAGES\[code\]) return PROXNUM\_ERROR\_MESSAGES\[code\];
 return res.message \|\| res.error?.message \|\| "An unexpected error occurred with the SMS provider";
}

// ── Flag normalization ─────────────────────────────────────────────────────────
const COUNTRY_ISO_MAP: Record<string, string> = {
  "Japan": "JP", "South Korea": "KR", "North Korea": "KP", "Singapore": "SG",
  "USA": "US", "USA (virtual)": "US", "Palestine": "PS", "Fiji": "FJ",
  "Western Sahara": "EH", "Solomon Islands": "SB", "Jersey": "JE",
  "Bermuda": "BM", "Tonga": "TO", "Samoa": "WS", "Malta": "MT",
  "Liechtenstein": "LI", "Gibraltar": "GI", "Faroe Islands": "FO", "Kosovo": "XK",
  "Aruba": "AW", "Montserrat": "MS", "Anguilla": "AI", "North Macedonia": "MK",
  "Seychelles": "SC", "New Caledonia": "NC", "Cape Verde": "CV",
};

function isoToFlag(iso2: string): string {
  return iso2.toUpperCase().split("").map((c) =>
    String.fromCodePoint(0x1f1e0 + c.charCodeAt(0) - 65)
  ).join("");
}

function normalizeFlag(name: string, flag: string): string {
  const BROKEN = "\u{1F1F2}\u{1F1F2}"; // 🇲🇲
  if (flag !== "\uD83C\uDDF2\uD83C\uDDF2") return flag;
  const iso = COUNTRY_ISO_MAP[name];
  return iso ? isoToFlag(iso) : flag;
}

function deduplicateCountries(countries: ProxnumCountry[]): ProxnumCountry[] {
  const seen = new Set<string>();
  return countries.filter((c) => {
    const key = c.name.toLowerCase().trim();
    if (seen.has(key)) return false;
    seen.set(key, true);
    return true;
  });
}
// ──────────────────────────────────────────────────────────────────────────────


async function request(
 method: "GET" \| "POST",
 endpoint: string,
 data: Record = {},
 authenticated = true
): Promise {
 let url = PROXNUM\_API\_URL + endpoint;

 const headers: Record = {
 "Content-Type": "application/json",
 "Accept": "application/json",
 };

 if (authenticated) {
 headers\["Authorization"\] = \`Bearer ${PROXNUM\_API\_KEY}\`;
 }

 const options: RequestInit = { method, headers };

 if (method === "GET" && Object.keys(data).length > 0) {
 const params = new URLSearchParams();
 for (const \[k, v\] of Object.entries(data)) {
 if (v !== undefined && v !== null) params.set(k, String(v));
 }
 url += "?" + params.toString();
 }

 if (method === "POST") {
 options.body = JSON.stringify(data);
 }

 try {
 const res = await fetch(url, options);
 const contentType = res.headers.get("content-type") \|\| "";
 if (!contentType.includes("application/json")) {
 return {
 success: false,
 code: "not\_json",
 error: {
 code: \`E${res.status}\`,
 key: "not\_json",
 message: \`API returned non-JSON response (${res.status}). Auth may have failed.\`,
 },
 };
 }
 const result = await res.json();

 if (!res.ok && !result.success && !result.error) {
 return {
 success: false,
 code: result.code \|\| \`E${res.status}\`,
 message: result.message \|\| \`HTTP Error: ${res.status}\`,
 error: {
 code: result.code \|\| \`E${res.status}\`,
 key: "http\_error",
 message: result.message \|\| \`HTTP Error: ${res.status}\`,
 },
 };
 }

 return result;
 } catch (err: any) {
 return {
 success: false,
 code: "connection\_error",
 error: {
 code: "E9999",
 key: "connection\_error",
 message: \`Failed to connect to Proxnum API: ${err.message}\`,
 },
 };
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
 const params: Record = {};
 if (country) params.country = country;
 if (service) params.service = service;
 return request("GET", "/prices", params, true);
 },

 getResellPrice(service: string, country: string \| number) {
 return request("GET", "/resell/price", { service, country }, true);
 },

 getAvailability(country: string, service: string) {
 return request("GET", "/availability", { country, service }, false);
 },

 buyVirtual(service: string, country: string \| number) {
 return request("POST", "/resell/virtual/buy", { service, country: Number(country) });
 },

 getVirtualStatus(activationId: string \| number) {
 return request("GET", \`/resell/virtual/${activationId}/status\`);
 },

 cancelVirtual(activationId: string \| number) {
 return request("POST", "/resell/virtual/cancel", { activation\_id: String(activationId) });
 },

 resendVirtual(activationId: string \| number) {
 return request("POST", "/resell/virtual/resend", { activation\_id: String(activationId) });
 },

 getUserBalance() {
 return request("GET", "/user/balance");
 },

 buyRental(service: string, country: string, days = 7) {
 return request("POST", "/rental/buy", { service, country, days });
 },

 getRentalStatus(id: string \| number) {
 return request("GET", \`/rental/${id}/status\`);
 },

 cancelRental(id: string \| number) {
 return request("POST", "/rental/cancel", { id });
 },

 getRentalMessages(rentalId: string \| number) {
 return request("GET", \`/rentals/${rentalId}/messages\`);
 },

 getRentalPrices(service: string, country: string) {
 return request("GET", "/rental/prices", { service, country }, false);
 },

 getRentalAvailability(country?: string, service?: string) {
 const params: Record = {};
 if (country) params.country = country;
 if (service) params.service = service;
 return request("GET", "/rental/availability", params, false);
 },

 getServicesByCountry(country: string) {
 return request("GET", \`/countries/${country}/services\`, {}, false);
 },

 listActivations(page = 1, perPage = 50) {
 return request("GET", "/resell/activations", { page, per\_page: perPage });
 },

 listRentals(page = 1, perPage = 50) {
 return request("GET", "/resell/rentals", { page, per\_page: perPage });
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
 base\_price: number;
 sell\_price: number;
 available: number;
}

let servicesCache: { data: ProxnumService\[\]; updatedAt: number } \| null = null;
let countriesCache: { data: ProxnumCountry\[\]; updatedAt: number } \| null = null;
let pricesCache: { data: Record>; updatedAt: number; cacheKey: string } \| null = null;
const CACHE\_TTL = 5 \* 60 \* 1000;

export async function getCachedServices(): Promise {
 if (servicesCache && Date.now() - servicesCache.updatedAt < CACHE\_TTL) {
 return servicesCache.data;
 }
 const result = await proxnumApi.getServices();
 if (result.success && Array.isArray(result.services)) {
 servicesCache = { data: result.services, updatedAt: Date.now() };
 return result.services;
 }
 return servicesCache?.data \|\| \[\];
}

export async function getCachedCountries(): Promise {
 if (countriesCache && Date.now() - countriesCache.updatedAt < CACHE\_TTL) {
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
 return result.countries;
 }
 return countriesCache?.data \|\| \[\];
}

export async function getCachedPrices(country?: string, service?: string): Promise>\> {
 const cacheKey = \`${country \|\| "all"}\_${service \|\| "all"}\`;
 if (pricesCache && Date.now() - pricesCache.updatedAt < CACHE\_TTL && pricesCache.cacheKey === cacheKey) {
 return pricesCache.data;
 }
 const result = await proxnumApi.getPrices(country, service);
 if (result.success && result.prices) {
 const prices = result.prices as Record>;
 pricesCache = { data: prices, updatedAt: Date.now(), cacheKey };
 return prices;
 }
 return pricesCache?.data \|\| {};
}

export function findCountryCode(countries: ProxnumCountry\[\], search: string): string \| undefined {
 const lower = search.toLowerCase();
 let exactCode = countries.find((c) => c.code === search);
 if (exactCode) return exactCode.code;

 let exactName = countries.find((c) => c.name.toLowerCase() === lower);
 if (exactName) return exactName.code;

 let partialMatch = countries.find((c) => c.name.toLowerCase().includes(lower));
 return partialMatch?.code;
}

export function getUSCountryCode(countries: ProxnumCountry\[\]): string {
 const exact = countries.find((c) => c.name === "USA");
 if (exact) return exact.code;
 const virtual = countries.find((c) => c.name === "USA (virtual)");
 if (virtual) return virtual.code;
 return "187";
}