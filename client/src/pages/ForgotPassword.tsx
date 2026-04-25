import { useState } from "react";
import { Link } from "wouter";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default function ForgotPassword() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!email) {
      toast({ title: "Missing email", description: "Please provide your email address.", variant: "destructive" });
      return;
    }

    setLoading(true);
    try {
      await apiRequest("POST", "/api/auth/forgot-password", { email });
      toast({
        title: "Check your inbox",
        description: "If an account exists, a reset link has been sent.",
      });
    } catch (err: any) {
      toast({
        title: "Request failed",
        description: err.message || "Could not submit reset request.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-6">
      <div className="w-full max-w-md border border-border rounded-2xl p-6 space-y-5">
        <div className="text-center space-y-3">
          <div className="flex justify-center">
            <Logo size={34} />
          </div>
          <h1 className="text-xl font-semibold">Forgot your password?</h1>
          <p className="text-sm text-muted-foreground">
            Enter your account email and we will send a secure password reset link.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-1.5">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="you@example.com"
              data-testid="input-forgot-email"
            />
          </div>

          <Button type="submit" className="w-full" disabled={loading} data-testid="button-submit-forgot-password">
            {loading ? "Sending link..." : "Send reset link"}
          </Button>
        </form>

        <p className="text-sm text-center text-muted-foreground">
          Remembered your password?{" "}
          <Link href="/login">
            <a className="text-primary hover:underline">Back to sign in</a>
          </Link>
        </p>
      </div>
    </div>
  );
}
