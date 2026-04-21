import { describe, it, expect } from "vitest";
import { friendlyError, findCountryCode, getUSCountryCode } from "../proxnum";
import type { ProxnumCountry } from "../proxnum";

// ===== friendlyError =====

describe("friendlyError", () => {
  it("returns a message for known error code 'no_numbers'", () => {
    const result = friendlyError({ success: false, code: "no_numbers" });
    expect(result).toBe(
      "No numbers available for this service in the selected country"
    );
  });

  it("returns a message for known error code 'insufficient_balance'", () => {
    const result = friendlyError({ code: "insufficient_balance" });
    expect(result).toBe(
      "Proxnum account has insufficient balance. Contact admin."
    );
  });

  it("returns a message for known error code 'service_unavailable'", () => {
    const result = friendlyError({ code: "service_unavailable" });
    expect(result).toBe(
      "This service is temporarily unavailable. Try again later."
    );
  });

  it("returns a message for known error code 'cancel_rejected'", () => {
    const result = friendlyError({ code: "cancel_rejected" });
    expect(result).toBe(
      "Cancellation rejected — activation already completed or not refundable"
    );
  });

  it("prefers top-level code over nested error.code", () => {
    const result = friendlyError({
      code: "no_numbers",
      error: { code: "insufficient_balance", key: "x", message: "nested" },
    });
    expect(result).toBe(
      "No numbers available for this service in the selected country"
    );
  });

  it("falls back to nested error.message when top-level code is unknown", () => {
    // When top-level code is unrecognised, the function falls through to
    // res.message || res.error?.message, so it returns the nested message.
    const result = friendlyError({
      code: "unknown_code",
      error: { code: "no_numbers", key: "x", message: "nested error msg" },
    });
    expect(result).toBe("nested error msg");
  });

  it("returns top-level message when code is unknown and no nested code matches", () => {
    const result = friendlyError({
      code: "random_error",
      message: "Something happened",
    });
    expect(result).toBe("Something happened");
  });

  it("returns nested error message as final fallback", () => {
    const result = friendlyError({
      error: { code: "E500", key: "server_error", message: "Internal error" },
    });
    expect(result).toBe("Internal error");
  });

  it("returns default fallback when no useful fields are present", () => {
    const result = friendlyError({});
    expect(result).toBe(
      "An unexpected error occurred with the SMS provider"
    );
  });
});

// ===== findCountryCode =====

const SAMPLE_COUNTRIES: ProxnumCountry[] = [
  { code: "1", name: "USA" },
  { code: "44", name: "United Kingdom" },
  { code: "49", name: "Germany" },
  { code: "33", name: "France" },
  { code: "91", name: "India" },
];

describe("findCountryCode", () => {
  it("returns code for an exact code match", () => {
    expect(findCountryCode(SAMPLE_COUNTRIES, "44")).toBe("44");
  });

  it("returns code for an exact name match (case-insensitive)", () => {
    expect(findCountryCode(SAMPLE_COUNTRIES, "germany")).toBe("49");
    expect(findCountryCode(SAMPLE_COUNTRIES, "GERMANY")).toBe("49");
    expect(findCountryCode(SAMPLE_COUNTRIES, "Germany")).toBe("49");
  });

  it("returns code for a partial name match", () => {
    expect(findCountryCode(SAMPLE_COUNTRIES, "kingdom")).toBe("44");
    expect(findCountryCode(SAMPLE_COUNTRIES, "united")).toBe("44");
  });

  it("returns undefined when no country matches", () => {
    expect(findCountryCode(SAMPLE_COUNTRIES, "mars")).toBeUndefined();
  });

  it("prioritises exact code match over name match", () => {
    // "1" is an exact code match for USA; partial match on "1" could also hit India "91"
    expect(findCountryCode(SAMPLE_COUNTRIES, "1")).toBe("1");
  });

  it("prioritises exact name match over partial name match", () => {
    const countries: ProxnumCountry[] = [
      { code: "100", name: "France" },
      { code: "101", name: "New France" },
    ];
    expect(findCountryCode(countries, "France")).toBe("100");
  });

  it("returns undefined for an empty country list", () => {
    expect(findCountryCode([], "USA")).toBeUndefined();
  });
});

// ===== getUSCountryCode =====

describe("getUSCountryCode", () => {
  it("returns code for exact 'USA' match", () => {
    const countries: ProxnumCountry[] = [
      { code: "187", name: "USA" },
      { code: "188", name: "Canada" },
    ];
    expect(getUSCountryCode(countries)).toBe("187");
  });

  it("falls back to 'USA (virtual)' when 'USA' is absent", () => {
    const countries: ProxnumCountry[] = [
      { code: "200", name: "USA (virtual)" },
      { code: "188", name: "Canada" },
    ];
    expect(getUSCountryCode(countries)).toBe("200");
  });

  it("returns hardcoded fallback '187' when neither entry exists", () => {
    const countries: ProxnumCountry[] = [{ code: "188", name: "Canada" }];
    expect(getUSCountryCode(countries)).toBe("187");
  });

  it("returns hardcoded fallback '187' for empty list", () => {
    expect(getUSCountryCode([])).toBe("187");
  });

  it("prefers 'USA' over 'USA (virtual)'", () => {
    const countries: ProxnumCountry[] = [
      { code: "200", name: "USA (virtual)" },
      { code: "187", name: "USA" },
    ];
    expect(getUSCountryCode(countries)).toBe("187");
  });
});
