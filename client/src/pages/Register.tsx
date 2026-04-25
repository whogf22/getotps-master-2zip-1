import { useState } from "react";
import { Link } from "wouter";
import { useAuth } from "@/contexts/AuthContext";
import { useTheme } from "@/contexts/ThemeContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { Moon, Sun, Eye, EyeOff } from "lucide-react";
import { HCaptchaField } from "@/components/HCaptchaField";
import zxcvbn from "zxcvbn";

export default function Register() {
  const [username, setUsername] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [acceptedTerms, setAcceptedTerms] = useState(false);
  const [loading, setLoading] = useState(false);
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);
  const [captchaResetSignal, setCaptchaResetSignal] = useState(0);
  const captchaRequired = Boolean(import.meta.env.VITE_HCAPTCHA_SITE_KEY);
  const { register } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const { toast } = useToast();
  const passwordStrength = password ? zxcvbn(password) : null;
  const passwordScore = passwordStrength?.score ?? 0;
  const passwordLabel = ["Very weak", "Weak", "Fair", "Strong", "Excellent"][passwordScore];
  const passwordColor = [
    "bg-red-500",
    "bg-orange-500",
    "bg-yellow-500",
    "bg-emerald-500",
    "bg-green-500",
  ][passwordScore];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !email || !password) {
      toast({ title: "Error", description: "Please fill in all fields", variant: "destructive" });
      return;
    }
    if (password.length < 8) {
      toast({ title: "Error", description: "Password must be at least 8 characters", variant: "destructive" });
      return;
    }
    if (!acceptedTerms) {
      toast({ title: "Error", description: "You must agree to Terms & Privacy before creating an account", variant: "destructive" });
      return;
    }
    if (captchaRequired && !captchaToken) {
      toast({ title: "Verification required", description: "Please complete the hCaptcha challenge", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      await register(username, email, password, captchaToken || undefined, acceptedTerms);
      window.location.assign("/verify-email");
    } catch (err: any) {
      toast({ title: "Registration failed", description: err.message || "Something went wrong", variant: "destructive" });
      setCaptchaToken(null);
      setCaptchaResetSignal((current) => current + 1);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background flex flex-col">
      <header className="flex items-center justify-between p-4 border-b border-border">
        <Link href="/"><a><Logo size={26} /></a></Link>
        <button onClick={toggleTheme} className="p-2 rounded-lg hover:bg-accent transition-colors">
          {theme === "dark" ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
        </button>
      </header>

      <div className="flex-1 flex items-center justify-center p-4">
        <Card className="w-full max-w-sm border-border shadow-lg">
          <CardHeader className="text-center pb-4">
            <div className="flex justify-center mb-3">
              <Logo size={40} showText={false} />
            </div>
            <CardTitle className="text-xl">Create account</CardTitle>
            <CardDescription className="text-sm">Get started with GetOTPs for free</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-1.5">
                <Label htmlFor="username" className="text-sm">Username</Label>
                <Input
                  id="username"
                  type="text"
                  placeholder="johndoe"
                  value={username}
                  onChange={e => setUsername(e.target.value)}
                  autoComplete="username"
                  data-testid="input-username"
                  className="h-9"
                />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="email" className="text-sm">Email</Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="you@example.com"
                  value={email}
                  onChange={e => setEmail(e.target.value)}
                  autoComplete="email"
                  data-testid="input-email"
                  className="h-9"
                />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="password" className="text-sm">Password</Label>
                <div className="relative">
                  <Input
                    id="password"
                    type={showPassword ? "text" : "password"}
                    placeholder="Min. 8 characters"
                    value={password}
                    onChange={e => setPassword(e.target.value)}
                    autoComplete="new-password"
                    data-testid="input-password"
                    className="h-9 pr-10"
                  />
                  <button
                    type="button"
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
                {password.length > 0 && (
                  <div className="mt-2 space-y-1">
                    <div className="h-1.5 rounded-full bg-muted overflow-hidden">
                      <div
                        className={`h-full transition-all ${passwordColor}`}
                        style={{ width: `${((passwordScore + 1) / 5) * 100}%` }}
                      />
                    </div>
                    <p className="text-xs text-muted-foreground">
                      Password strength: <span className="font-medium">{passwordLabel}</span>
                    </p>
                  </div>
                )}
              </div>
              <div className="flex items-start gap-2 rounded-md border border-border p-3">
                <Checkbox
                  id="accept-terms"
                  checked={acceptedTerms}
                  onCheckedChange={(checked) => setAcceptedTerms(Boolean(checked))}
                  className="mt-0.5"
                  data-testid="checkbox-accept-terms"
                />
                <Label htmlFor="accept-terms" className="text-xs leading-relaxed text-muted-foreground">
                  I agree to the{" "}
                  <Link href="/terms">
                    <a className="text-primary hover:underline">Terms of Service</a>
                  </Link>{" "}
                  and{" "}
                  <Link href="/privacy">
                    <a className="text-primary hover:underline">Privacy Policy</a>
                  </Link>
                  .
                </Label>
              </div>
              <Button type="submit" className="w-full" disabled={loading} data-testid="button-submit-register">
                {loading ? "Creating account..." : "Create Account"}
              </Button>

              <div className="pt-1">
                <HCaptchaField onTokenChange={setCaptchaToken} resetSignal={captchaResetSignal} />
              </div>
            </form>

            <div className="mt-5 text-center">
              <p className="text-xs text-muted-foreground">
                Already have an account?{" "}
                <Link href="/login"><a className="text-primary hover:underline font-medium">Sign in</a></Link>
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
