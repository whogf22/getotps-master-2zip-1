import { describe, it, expect, beforeEach, afterEach } from "vitest";
import { extractOTPFromText, safeError, SERVICE_CATEGORIES } from "../utils";

// ===== extractOTPFromText =====

describe("extractOTPFromText", () => {
  it("extracts a 6-digit code", () => {
    expect(extractOTPFromText("Your code is 123456")).toBe("123456");
  });

  it("extracts a 4-digit code when no 6-digit is present", () => {
    expect(extractOTPFromText("PIN: 7890")).toBe("7890");
  });

  it("extracts a 5-digit code", () => {
    expect(extractOTPFromText("Use 54321 to verify")).toBe("54321");
  });

  it("extracts a 7-digit code", () => {
    expect(extractOTPFromText("Code is 1234567")).toBe("1234567");
  });

  it("extracts an 8-digit code", () => {
    expect(extractOTPFromText("Code is 12345678")).toBe("12345678");
  });

  it("prefers 6-digit code over 4-digit code (patterns checked in order)", () => {
    // The 6-digit pattern is first in the list
    expect(extractOTPFromText("1234 654321")).toBe("654321");
  });

  it("extracts code from 'code: XXXX' pattern (case-insensitive)", () => {
    expect(extractOTPFromText("Code: 8765")).toBe("8765");
    expect(extractOTPFromText("CODE: 8765")).toBe("8765");
    expect(extractOTPFromText("code 8765")).toBe("8765");
  });

  it("extracts code from 'pin: XXXX' pattern (case-insensitive)", () => {
    expect(extractOTPFromText("pin: 4321")).toBe("4321");
    expect(extractOTPFromText("PIN: 4321")).toBe("4321");
  });

  it("extracts code from 'verification: XXXX' pattern (case-insensitive)", () => {
    expect(extractOTPFromText("verification: 9876")).toBe("9876");
    expect(extractOTPFromText("Verification 9876")).toBe("9876");
  });

  it("returns null when no code is found", () => {
    expect(extractOTPFromText("Hello world")).toBeNull();
  });

  it("returns null for empty string", () => {
    expect(extractOTPFromText("")).toBeNull();
  });

  it("handles code embedded in longer message", () => {
    expect(
      extractOTPFromText(
        "Welcome to our service! Your one-time verification code is 482910. It expires in 10 minutes."
      )
    ).toBe("482910");
  });

  it("does not match a 3-digit number", () => {
    // 3 digits should not be matched by any pattern
    expect(extractOTPFromText("Use 123 now")).toBeNull();
  });
});

// ===== safeError =====

describe("safeError", () => {
  const ORIG_ENV = process.env.NODE_ENV;

  afterEach(() => {
    process.env.NODE_ENV = ORIG_ENV;
  });

  it("returns error message in development", () => {
    process.env.NODE_ENV = "development";
    const err = new Error("DB connection failed");
    expect(safeError(err)).toBe("DB connection failed");
  });

  it("returns 'Unknown error' when no message and not production", () => {
    process.env.NODE_ENV = "development";
    expect(safeError({})).toBe("Unknown error");
    expect(safeError(null)).toBe("Unknown error");
    expect(safeError(undefined)).toBe("Unknown error");
  });

  it("hides the real error in production", () => {
    process.env.NODE_ENV = "production";
    const err = new Error("Sensitive internal detail");
    expect(safeError(err)).toBe("Something went wrong. Please try again.");
  });

  it("hides even null errors in production", () => {
    process.env.NODE_ENV = "production";
    expect(safeError(null)).toBe("Something went wrong. Please try again.");
  });
});

// ===== SERVICE_CATEGORIES =====

describe("SERVICE_CATEGORIES", () => {
  it("maps WhatsApp to Messaging", () => {
    expect(SERVICE_CATEGORIES["WhatsApp"]).toBe("Messaging");
  });

  it("maps Google to Tech", () => {
    expect(SERVICE_CATEGORIES["Google"]).toBe("Tech");
  });

  it("maps Facebook to Social", () => {
    expect(SERVICE_CATEGORIES["Facebook"]).toBe("Social");
  });

  it("maps Amazon to Shopping", () => {
    expect(SERVICE_CATEGORIES["Amazon"]).toBe("Shopping");
  });

  it("maps PayPal to Finance", () => {
    expect(SERVICE_CATEGORIES["PayPal"]).toBe("Finance");
  });

  it("maps Coinbase to Crypto", () => {
    expect(SERVICE_CATEGORIES["Coinbase"]).toBe("Crypto");
  });

  it("maps Netflix to Entertainment", () => {
    expect(SERVICE_CATEGORIES["Netflix"]).toBe("Entertainment");
  });

  it("maps Tinder to Dating", () => {
    expect(SERVICE_CATEGORIES["Tinder"]).toBe("Dating");
  });

  it("returns undefined for unknown service", () => {
    expect(SERVICE_CATEGORIES["UnknownService"]).toBeUndefined();
  });
});
