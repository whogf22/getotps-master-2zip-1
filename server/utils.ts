/**
 * Extracts an OTP/verification code from an SMS message body.
 * Returns the first numeric code found, or null if none found.
 */
export function extractOTPFromText(text: string): string | null {
  const patterns = [
    /\b(\d{6})\b/,
    /\b(\d{4})\b/,
    /\b(\d{5})\b/,
    /\b(\d{7,8})\b/,
    /code[:\s]+(\d{4,8})/i,
    /pin[:\s]+(\d{4,8})/i,
    /verification[:\s]+(\d{4,8})/i,
  ];
  for (const p of patterns) {
    const match = text.match(p);
    if (match) return match[1];
  }
  return null;
}

/**
 * Returns a safe error message that hides internal details in production.
 */
export function safeError(err: any): string {
  if (process.env.NODE_ENV === "production") {
    return "Something went wrong. Please try again.";
  }
  return err?.message || "Unknown error";
}

/**
 * Service category mapping used during Proxnum service sync.
 */
export const SERVICE_CATEGORIES: Record<string, string> = {
  WhatsApp: "Messaging", Whatsapp: "Messaging", Telegram: "Messaging", Discord: "Messaging", Signal: "Messaging",
  Viber: "Messaging", LINE: "Messaging", WeChat: "Messaging", KakaoTalk: "Messaging",
  Google: "Tech", Microsoft: "Tech", Apple: "Tech", AWS: "Tech", GitHub: "Tech", Anthropic: "Tech",
  Facebook: "Social", Instagram: "Social", Twitter: "Social", TikTok: "Social",
  Snapchat: "Social", LinkedIn: "Social", Reddit: "Social", Pinterest: "Social",
  Amazon: "Shopping", eBay: "Shopping", Walmart: "Shopping", BestBuy: "Shopping",
  Uber: "Transport", Lyft: "Transport", DoorDash: "Food", Grubhub: "Food", UberEats: "Food",
  Airbnb: "Travel", Booking: "Travel",
  PayPal: "Finance", CashApp: "Finance", Venmo: "Finance", Chime: "Finance", Zelle: "Finance",
  Coinbase: "Crypto", Binance: "Crypto", Kraken: "Crypto",
  Netflix: "Entertainment", Spotify: "Entertainment", Hulu: "Entertainment", Disney: "Entertainment",
  Bumble: "Dating", Tinder: "Dating", Hinge: "Dating", Badoo: "Dating",
};
