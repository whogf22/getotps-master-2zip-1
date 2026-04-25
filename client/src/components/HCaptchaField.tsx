import HCaptcha from "@hcaptcha/react-hcaptcha";

interface HCaptchaFieldProps {
  onTokenChange: (token: string | null) => void;
  resetSignal?: number;
}

export function HCaptchaField({ onTokenChange, resetSignal = 0 }: HCaptchaFieldProps) {
  const siteKey = import.meta.env.VITE_HCAPTCHA_SITE_KEY as string | undefined;

  if (!siteKey) {
    return (
      <p className="text-xs text-amber-500" data-testid="hcaptcha-not-configured">
        hCaptcha is not configured for this environment.
      </p>
    );
  }

  return (
    <HCaptcha
      key={resetSignal}
      sitekey={siteKey}
      onVerify={(token) => onTokenChange(token)}
      onExpire={() => onTokenChange(null)}
      onError={() => onTokenChange(null)}
    />
  );
}
