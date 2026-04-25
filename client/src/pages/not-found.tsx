import { Link } from "wouter";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { Compass, Home, LogIn } from "lucide-react";

export default function NotFound() {
  return (
    <div className="min-h-screen w-full bg-[hsl(222,47%,6%)] text-white flex items-center justify-center px-4">
      <div className="w-full max-w-2xl rounded-2xl border border-white/10 bg-white/5 p-8 sm:p-10 backdrop-blur-sm">
        <div className="mb-6 flex items-center justify-between">
          <Logo size={30} />
          <span className="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs text-cyan-200">
            <Compass className="h-3.5 w-3.5" />
            Route Not Found
          </span>
        </div>

        <p className="text-sm text-cyan-200/80 mb-2">Error 404</p>
        <h1 className="text-3xl sm:text-4xl font-bold mb-3">This page doesn't exist.</h1>
        <p className="text-sm sm:text-base text-white/70 mb-8">
          The link may be outdated, or the page has moved. Use the shortcuts below to continue.
        </p>

        <div className="flex flex-wrap gap-3">
          <Link href="/">
            <Button className="gap-2">
              <Home className="h-4 w-4" />
              Back to Home
            </Button>
          </Link>
          <Link href="/login">
            <Button variant="outline" className="gap-2 border-white/25 text-white hover:bg-white/10">
              <LogIn className="h-4 w-4" />
              Sign In
            </Button>
          </Link>
        </div>
      </div>
    </div>
  );
}
