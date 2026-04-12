import { useState } from "react";
import { Link } from "wouter";
import { useAuth } from "@/contexts/AuthContext";
import { useTheme } from "@/contexts/ThemeContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { Moon, Sun, Eye, EyeOff, ArrowRight, CheckCircle } from "lucide-react";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) {
      toast({ title: "Error", description: "Please fill in all fields", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      await login(email, password);
      window.location.hash = "/dashboard";
    } catch (err: any) {
      toast({ title: "Login failed", description: err.message || "Invalid credentials", variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* Left panel — dark brand side */}
      <div className="hidden lg:flex lg:w-[45%] bg-[hsl(222,47%,6%)] relative overflow-hidden flex-col justify-between p-12">
        <div className="absolute inset-0 hero-grid opacity-50" />
        <div className="absolute top-1/3 left-1/2 -translate-x-1/2 w-96 h-96 bg-primary/20 rounded-full blur-[120px] pointer-events-none" />
        <div className="relative">
          <Link href="/">
            <Logo size={30} />
          </Link>
        </div>
        <div className="relative space-y-8">
          <div>
            <h2 className="text-3xl font-bold text-white mb-3">Privacy-first SMS verification</h2>
            <p className="text-white/50 leading-relaxed">
              Keep your real number private. Verify any app or service with disposable US numbers in seconds.
            </p>
          </div>
          <div className="space-y-3">
            {[
              "500+ supported services",
              "Instant OTP delivery",
              "No personal data required",
              "Full refund if no SMS arrives",
            ].map(item => (
              <div key={item} className="flex items-center gap-3 text-white/70 text-sm">
                <CheckCircle className="w-4 h-4 text-primary shrink-0" />
                {item}
              </div>
            ))}
          </div>
        </div>
        <p className="relative text-white/20 text-xs">© 2025 GetOTPs. All rights reserved.</p>
      </div>

      {/* Right panel — form */}
      <div className="flex-1 flex flex-col bg-background">
        {/* Top bar */}
        <div className="flex items-center justify-between p-5 lg:p-8">
          <Link href="/" className="lg:hidden">
            <Logo size={26} />
          </Link>
          <div className="ml-auto flex items-center gap-3">
            <button
              onClick={toggleTheme}
              className="p-2.5 rounded-xl hover:bg-accent transition-colors text-muted-foreground"
            >
              {theme === "dark" ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
            </button>
            <Link href="/register">
              <Button variant="outline" size="sm" className="rounded-xl">Create account</Button>
            </Link>
          </div>
        </div>

        {/* Form area */}
        <div className="flex-1 flex items-center justify-center px-6 py-12">
          <div className="w-full max-w-sm">
            <div className="mb-8">
              <h1 className="text-2xl font-bold mb-2">Welcome back</h1>
              <p className="text-muted-foreground text-sm">Sign in to your GetOTPs account</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-1.5">
                <Label htmlFor="email" className="text-sm font-medium">Email</Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="you@example.com"
                  value={email}
                  onChange={e => setEmail(e.target.value)}
                  autoComplete="email"
                  data-testid="input-email"
                  className="h-11 rounded-xl"
                />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="password" className="text-sm font-medium">Password</Label>
                <div className="relative">
                  <Input
                    id="password"
                    type={showPassword ? "text" : "password"}
                    placeholder="••••••••"
                    value={password}
                    onChange={e => setPassword(e.target.value)}
                    autoComplete="current-password"
                    data-testid="input-password"
                    className="h-11 rounded-xl pr-11"
                  />
                  <button
                    type="button"
                    className="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <Button
                type="submit"
                className="w-full h-11 rounded-xl font-semibold gap-2 mt-2"
                disabled={loading}
                data-testid="button-submit-login"
              >
                {loading ? "Signing in..." : (
                  <>Sign In <ArrowRight className="w-4 h-4" /></>
                )}
              </Button>
            </form>

            <div className="mt-4 text-center">
              <Link href="/forgot-password">
                <a className="text-xs text-muted-foreground hover:text-primary transition-colors">Forgot password?</a>
              </Link>
            </div>
            <p className="mt-3 text-center text-sm text-muted-foreground">
              Don't have an account?{" "}
              <Link href="/register">
                <a className="text-primary hover:text-primary/80 font-semibold transition-colors">Create one free</a>
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
