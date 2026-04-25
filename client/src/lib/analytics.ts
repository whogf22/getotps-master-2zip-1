declare global {
  interface Window {
    plausible?: (eventName: string, options?: { props?: Record<string, string | number | boolean> }) => void;
  }
}

export function trackEvent(
  eventName: "register" | "topup" | "first_otp",
  props?: Record<string, string | number | boolean>,
) {
  if (typeof window === "undefined" || typeof window.plausible !== "function") {
    return;
  }
  window.plausible(eventName, props ? { props } : undefined);
}
