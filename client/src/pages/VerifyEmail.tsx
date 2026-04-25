import { useEffect, useState } from "react";
import { Link, useLocation } from "wouter";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { apiRequest } from "@/lib/queryClient";

export default function VerifyEmail() {
  const [location] = useLocation();
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [message, setMessage] = useState("Preparing verification...");

  useEffect(() => {
    const token = new URLSearchParams(window.location.search).get("token");
    if (!token) {
      setStatus("error");
      setMessage("Missing verification token.");
      return;
    }

    const run = async () => {
      setStatus("loading");
      try {
        const response = await apiRequest("POST", "/api/auth/verify-email", { token });
        const data = await response.json();
        setStatus("success");
        setMessage(data.message || "Email verified successfully.");
      } catch (err: any) {
        setStatus("error");
        setMessage(err.message || "Verification failed.");
      }
    };

    void run();
  }, [location]);

  return (
    <div className="min-h-screen bg-background flex items-center justify-center p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <CardTitle>Verify your email</CardTitle>
          <CardDescription>Confirm your account ownership to continue.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <p
            className={`text-sm ${
              status === "success" ? "text-green-500" : status === "error" ? "text-red-500" : "text-muted-foreground"
            }`}
          >
            {status === "loading" ? "Verifying token..." : message}
          </p>
          <div className="flex flex-col gap-2">
            <Link href="/login">
              <Button className="w-full" variant={status === "success" ? "default" : "outline"}>
                Go to login
              </Button>
            </Link>
            <Link href="/">
              <Button className="w-full" variant="ghost">
                Back to home
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
