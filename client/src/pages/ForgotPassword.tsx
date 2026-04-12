import { useState } from "react";
import { Link } from "wouter";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { apiRequest } from "@/lib/queryClient";
import { ArrowLeft, Mail, KeyRound } from "lucide-react";

export default function ForgotPassword() {
  const { toast } = useToast();
  const [email, setEmail] = useState("");
  const [token, setToken] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [step, setStep] = useState<"request" | "reset" | "done">("request");
  const [loading, setLoading] = useState(false);
  const [devToken, setDevToken] = useState<string | null>(null);

  const handleRequestReset = async () => {
    if (!email) {
      toast({ title: "Error", description: "Please enter your email", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      const res = await apiRequest("POST", "/api/auth/forgot-password", { email });
      const data = await res.json();
      toast({ title: "Check your email", description: data.message });
      if (data.resetToken) {
        setDevToken(data.resetToken);
        setToken(data.resetToken);
      }
      setStep("reset");
    } catch (err: any) {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  const handleResetPassword = async () => {
    if (!token) {
      toast({ title: "Error", description: "Please enter the reset token", variant: "destructive" });
      return;
    }
    if (newPassword.length < 8) {
      toast({ title: "Error", description: "Password must be at least 8 characters", variant: "destructive" });
      return;
    }
    if (newPassword !== confirmPassword) {
      toast({ title: "Error", description: "Passwords don't match", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      const res = await apiRequest("POST", "/api/auth/reset-password", { token, newPassword });
      const data = await res.json();
      toast({ title: "Success", description: data.message });
      setStep("done");
    } catch (err: any) {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <div className="w-full max-w-md space-y-4">
        <div className="text-center space-y-1">
          <h1 className="text-2xl font-bold">GetOTPs</h1>
          <p className="text-sm text-muted-foreground">Reset your password</p>
        </div>

        <Card className="border-border">
          {step === "request" && (
            <>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-semibold flex items-center gap-2">
                  <Mail className="w-4 h-4 text-primary" />
                  Forgot Password
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <p className="text-xs text-muted-foreground">
                  Enter your email address and we'll send you a reset link.
                </p>
                <div className="space-y-1.5">
                  <Label className="text-xs">Email</Label>
                  <Input
                    type="email"
                    value={email}
                    onChange={e => setEmail(e.target.value)}
                    placeholder="you@example.com"
                    className="h-9 text-sm"
                  />
                </div>
                <Button onClick={handleRequestReset} disabled={loading} className="w-full">
                  {loading ? "Sending..." : "Send Reset Link"}
                </Button>
              </CardContent>
            </>
          )}

          {step === "reset" && (
            <>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-semibold flex items-center gap-2">
                  <KeyRound className="w-4 h-4 text-primary" />
                  Reset Password
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {devToken && (
                  <div className="p-2.5 rounded-lg bg-yellow-500/10 border border-yellow-500/20">
                    <p className="text-xs text-yellow-700 dark:text-yellow-400 font-medium">Dev Mode: Token auto-filled</p>
                  </div>
                )}
                <div className="space-y-1.5">
                  <Label className="text-xs">Reset Token</Label>
                  <Input
                    value={token}
                    onChange={e => setToken(e.target.value)}
                    placeholder="Paste your reset token"
                    className="h-9 text-sm font-mono"
                  />
                </div>
                <div className="space-y-1.5">
                  <Label className="text-xs">New Password</Label>
                  <Input
                    type="password"
                    value={newPassword}
                    onChange={e => setNewPassword(e.target.value)}
                    placeholder="Min. 8 characters"
                    className="h-9 text-sm"
                  />
                </div>
                <div className="space-y-1.5">
                  <Label className="text-xs">Confirm Password</Label>
                  <Input
                    type="password"
                    value={confirmPassword}
                    onChange={e => setConfirmPassword(e.target.value)}
                    placeholder="Repeat password"
                    className="h-9 text-sm"
                  />
                </div>
                <Button onClick={handleResetPassword} disabled={loading} className="w-full">
                  {loading ? "Resetting..." : "Reset Password"}
                </Button>
              </CardContent>
            </>
          )}

          {step === "done" && (
            <CardContent className="py-8 text-center space-y-3">
              <div className="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center mx-auto">
                <KeyRound className="w-5 h-5 text-green-500" />
              </div>
              <p className="text-sm font-semibold">Password Reset Successfully</p>
              <p className="text-xs text-muted-foreground">You can now log in with your new password.</p>
              <Link href="/login">
                <Button size="sm" className="mt-2">Go to Login</Button>
              </Link>
            </CardContent>
          )}
        </Card>

        <div className="text-center">
          <Link href="/login" className="text-xs text-muted-foreground hover:text-foreground inline-flex items-center gap-1">
            <ArrowLeft className="w-3 h-3" />
            Back to Login
          </Link>
        </div>
      </div>
    </div>
  );
}
