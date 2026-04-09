import { Link } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import { useTheme } from "@/contexts/ThemeContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { HeroScene } from "@/components/3d/HeroScene";
import { PhoneMockup } from "@/components/3d/PhoneMockup";
import { SceneErrorBoundary } from "@/components/3d/SceneErrorBoundary";
import {
  Moon, Sun, CheckCircle, Zap, Shield, Globe,
  ChevronDown, ChevronUp, ArrowRight, Star, Lock
} from "lucide-react";
import { useState } from "react";

const POPULAR_SERVICES = [
  { name: "WhatsApp", icon: "💬", price: "0.50", color: "from-green-500/20 to-green-600/10" },
  { name: "Google", icon: "🔍", price: "0.45", color: "from-blue-500/20 to-blue-600/10" },
  { name: "Telegram", icon: "✈️", price: "0.35", color: "from-sky-500/20 to-sky-600/10" },
  { name: "Facebook", icon: "👤", price: "0.40", color: "from-indigo-500/20 to-indigo-600/10" },
  { name: "Instagram", icon: "📸", price: "0.45", color: "from-pink-500/20 to-pink-600/10" },
  { name: "Twitter / X", icon: "𝕏", price: "0.35", color: "from-gray-500/20 to-gray-600/10" },
  { name: "TikTok", icon: "🎵", price: "0.40", color: "from-red-500/20 to-red-600/10" },
  { name: "Discord", icon: "🎮", price: "0.30", color: "from-violet-500/20 to-violet-600/10" },
  { name: "Amazon", icon: "🛒", price: "0.55", color: "from-orange-500/20 to-orange-600/10" },
  { name: "Uber", icon: "🚗", price: "0.60", color: "from-slate-500/20 to-slate-600/10" },
  { name: "PayPal", icon: "💳", price: "0.70", color: "from-blue-600/20 to-blue-700/10" },
  { name: "Coinbase", icon: "₿", price: "0.75", color: "from-yellow-500/20 to-yellow-600/10" },
];

const FAQS = [
  {
    q: "What is GetOTPs?",
    a: "GetOTPs is a virtual phone number service that lets you receive SMS verification codes for 500+ apps and websites without using your personal phone number.",
  },
  {
    q: "How does it work?",
    a: "Create an account, add balance, select a service, and get a US phone number. Use that number to receive your OTP code, which appears in your dashboard instantly.",
  },
  {
    q: "How long do I have the number?",
    a: "Each number is rented for 20 minutes. If no SMS arrives in that time, you can cancel for a full refund.",
  },
  {
    q: "What payment methods do you accept?",
    a: "We accept all major credit and debit cards via Stripe's secure checkout. All transactions are encrypted.",
  },
  {
    q: "Can I use the API?",
    a: "Yes! Every account gets an API key. You can integrate GetOTPs into your own projects using our REST API.",
  },
];

export default function Landing() {
  const { user } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const [openFaq, setOpenFaq] = useState<number | null>(null);

  const { data: services } = useQuery<any[]>({
    queryKey: ["/api/services"],
  });

  return (
    <div className="min-h-screen bg-[#060d1a] text-white overflow-x-hidden">

      {/* ── Navbar ── */}
      <header className="sticky top-0 z-50 border-b border-white/10 bg-[#060d1a]/90 backdrop-blur-xl">
        <div className="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
          <Logo size={30} />
          <nav className="hidden md:flex items-center gap-8 text-sm font-medium">
            <button
              onClick={() => document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' })}
              className="text-white/50 hover:text-white transition-colors"
            >
              Services
            </button>
            <button
              onClick={() => document.getElementById('how-it-works')?.scrollIntoView({ behavior: 'smooth' })}
              className="text-white/50 hover:text-white transition-colors"
            >
              How It Works
            </button>
            <button
              onClick={() => document.getElementById('faq')?.scrollIntoView({ behavior: 'smooth' })}
              className="text-white/50 hover:text-white transition-colors"
            >
              FAQ
            </button>
          </nav>
          <div className="flex items-center gap-2">
            <button
              onClick={toggleTheme}
              className="p-2.5 rounded-xl hover:bg-white/10 transition-colors text-white/50 hover:text-white"
              data-testid="button-theme-toggle"
            >
              {theme === "dark" ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
            </button>
            {user ? (
              <Link href="/dashboard">
                <Button size="sm" className="rounded-xl font-semibold bg-primary hover:bg-primary/90" data-testid="button-go-dashboard">
                  Dashboard <ArrowRight className="w-3.5 h-3.5 ml-1" />
                </Button>
              </Link>
            ) : (
              <>
                <Link href="/login">
                  <Button variant="ghost" size="sm" className="rounded-xl font-medium text-white/70 hover:text-white hover:bg-white/10" data-testid="button-login">
                    Sign In
                  </Button>
                </Link>
                <Link href="/register">
                  <Button size="sm" className="rounded-xl font-semibold px-5 bg-primary hover:bg-primary/90" data-testid="button-register">
                    Get Started
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>
      </header>

      {/* ── 3D Hero Section ── */}
      <section className="relative min-h-screen flex items-center overflow-hidden">
        {/* 3D Background Canvas */}
        <SceneErrorBoundary fallback={<div className="absolute inset-0 bg-[#060d1a] hero-grid opacity-30" />}>
          <HeroScene />
        </SceneErrorBoundary>

        {/* Hero Content — overlaid on top of 3D */}
        <div className="relative z-10 w-full max-w-6xl mx-auto px-4 py-20">
          <div className="max-w-3xl">
            {/* Badge */}
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-primary/40 bg-primary/10 text-primary text-sm font-medium mb-8">
              <span className="w-2 h-2 rounded-full bg-primary animate-pulse" />
              500+ Services · Instant Delivery · From $0.15
            </div>

            {/* Headline */}
            <h1 className="text-5xl md:text-7xl font-bold tracking-tight text-white mb-6 leading-[1.1]">
              Verify Anything<br />
              <span className="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-[hsl(185,72%,55%)] to-blue-400">
                Without Exposure
              </span>
            </h1>

            <p className="text-lg md:text-xl text-white/55 max-w-xl mb-10 leading-relaxed">
              Disposable US phone numbers for SMS verification. No personal data ever exposed.
              Codes arrive in <span className="text-white/90 font-semibold">under 5 seconds</span>.
            </p>

            <div className="flex flex-col sm:flex-row gap-4">
              <Link href="/register">
                <Button
                  size="lg"
                  className="rounded-2xl px-10 h-14 text-base font-semibold glow-primary-sm hover:glow-primary transition-all bg-gradient-to-r from-[hsl(185,72%,38%)] to-[hsl(185,72%,48%)] hover:from-[hsl(185,72%,42%)] hover:to-[hsl(185,72%,52%)]"
                  data-testid="button-hero-register"
                >
                  Start Free <ArrowRight className="w-5 h-5 ml-2" />
                </Button>
              </Link>
              <button
                onClick={() => document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' })}
                className="inline-flex items-center justify-center gap-2 px-10 h-14 rounded-2xl border border-white/20 text-white/75 hover:text-white hover:border-white/40 transition-all text-base font-medium glass-dark"
                data-testid="button-hero-pricing"
              >
                Explore Services
              </button>
            </div>

            {/* Trust row */}
            <div className="mt-12 flex flex-wrap gap-5 text-white/35 text-sm">
              {[
                { icon: <Lock className="w-3.5 h-3.5" />, text: "Zero personal data" },
                { icon: <Zap className="w-3.5 h-3.5" />, text: "< 5s delivery" },
                { icon: <CheckCircle className="w-3.5 h-3.5" />, text: "Full refund guarantee" },
              ].map(t => (
                <div key={t.text} className="flex items-center gap-1.5">
                  <span className="text-primary/70">{t.icon}</span>
                  {t.text}
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2 text-white/25 text-xs">
          <span>Scroll to explore</span>
          <div className="w-5 h-8 rounded-full border border-white/20 flex items-start justify-center p-1">
            <div className="w-1 h-2 rounded-full bg-white/40 animate-bounce" />
          </div>
        </div>
      </section>

      {/* ── Stats Bar ── */}
      <section className="border-y border-white/10 bg-[#070f1f]">
        <div className="max-w-5xl mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4">
            {[
              { value: "500+", label: "Services" },
              { value: "99.9%", label: "Uptime" },
              { value: "< 5s", label: "Avg Delivery" },
              { value: "$0.15", label: "Starting From" },
            ].map((stat, i) => (
              <div key={stat.label} className={`py-8 px-6 text-center ${i < 3 ? "border-r border-white/10" : ""}`}>
                <p className="text-3xl font-bold text-cyan-400 tabular-nums">{stat.value}</p>
                <p className="text-xs text-white/35 mt-1 font-medium uppercase tracking-wider">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── 3D Phone + Feature showcase ── */}
      <section className="py-24 relative overflow-hidden bg-[#060d1a]">
        <div className="absolute inset-0 hero-grid opacity-30" />
        <div className="max-w-6xl mx-auto px-4 relative">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            {/* CSS 3D Phone Mockup */}
            <div className="h-[580px] relative flex items-center justify-center">
              <PhoneMockup />
              <div className="absolute bottom-0 left-1/2 -translate-x-1/2 w-48 h-12 bg-primary/20 rounded-full blur-3xl" />
            </div>

            {/* Feature copy */}
            <div className="space-y-8">
              <div>
                <p className="text-primary text-sm font-semibold uppercase tracking-widest mb-3">Why GetOTPs</p>
                <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
                  Built for<br />Privacy & Speed
                </h2>
                <p className="text-white/45 leading-relaxed">
                  Everything you need for frictionless SMS verification,
                  without ever compromising your real identity.
                </p>
              </div>
              <div className="space-y-5">
                {[
                  {
                    icon: <Zap className="w-5 h-5 text-yellow-400" />,
                    bg: "bg-yellow-400/10",
                    title: "Instant Delivery",
                    desc: "Codes reach your dashboard in under 5 seconds. No polling delays.",
                  },
                  {
                    icon: <Shield className="w-5 h-5 text-cyan-400" />,
                    bg: "bg-cyan-400/10",
                    title: "100% Anonymous",
                    desc: "Disposable numbers. Your real number is never exposed or stored.",
                  },
                  {
                    icon: <Globe className="w-5 h-5 text-violet-400" />,
                    bg: "bg-violet-400/10",
                    title: "Developer REST API",
                    desc: "Full API access with every account. Automate verifications at scale.",
                  },
                ].map(f => (
                  <div key={f.title} className="flex gap-4">
                    <div className={`w-10 h-10 rounded-xl ${f.bg} flex items-center justify-center shrink-0 mt-0.5`}>
                      {f.icon}
                    </div>
                    <div>
                      <h3 className="font-semibold text-white mb-0.5">{f.title}</h3>
                      <p className="text-sm text-white/45 leading-relaxed">{f.desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── How It Works ── */}
      <section id="how-it-works" className="py-24 bg-[#070f1f] border-y border-white/10">
        <div className="max-w-5xl mx-auto px-4">
          <div className="text-center mb-14">
            <p className="text-primary text-sm font-semibold uppercase tracking-widest mb-3">Simple Process</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white mb-3">Get your OTP in 2 minutes</h2>
            <p className="text-white/40 max-w-md mx-auto">Three steps and you're verified — without your real number.</p>
          </div>
          <div className="grid md:grid-cols-3 gap-6 relative">
            {/* Connector */}
            <div className="hidden md:block absolute top-12 left-[calc(33%+1.5rem)] right-[calc(33%+1.5rem)] h-px bg-gradient-to-r from-primary/30 via-primary/60 to-primary/30" />

            {[
              { step: "01", icon: "👤", title: "Create Account", desc: "Register free in 30 seconds. Zero credit card required.", grad: "from-cyan-500/20 to-cyan-600/5" },
              { step: "02", icon: "💳", title: "Add Balance", desc: "Top up from $5 via Stripe's secure checkout.", grad: "from-violet-500/20 to-violet-600/5" },
              { step: "03", icon: "📱", title: "Receive OTP", desc: "Pick a service, grab a US number, get your code.", grad: "from-emerald-500/20 to-emerald-600/5" },
            ].map(item => (
              <div
                key={item.step}
                className="relative rounded-2xl border border-white/10 bg-gradient-to-br from-white/[0.04] to-transparent p-8 hover:border-primary/40 transition-all hover:shadow-xl hover:shadow-primary/5 group"
              >
                <div className={`w-14 h-14 rounded-2xl bg-gradient-to-br ${item.grad} flex items-center justify-center text-2xl mb-5 group-hover:scale-110 transition-transform border border-white/10`}>
                  {item.icon}
                </div>
                <div className="absolute top-6 right-6 text-5xl font-bold text-white/[0.04] font-mono select-none">{item.step}</div>
                <h3 className="font-bold text-lg text-white mb-2">{item.title}</h3>
                <p className="text-sm text-white/40 leading-relaxed">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Popular Services ── */}
      <section id="services" className="py-24 bg-[#060d1a]">
        <div className="max-w-5xl mx-auto px-4">
          <div className="text-center mb-14">
            <p className="text-primary text-sm font-semibold uppercase tracking-widest mb-3">500+ Supported</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white mb-3">Popular Services</h2>
            <p className="text-white/40 max-w-md mx-auto">Verify accounts across all major platforms — social, tech, finance, and more.</p>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            {(services?.length ? services : POPULAR_SERVICES).slice(0, 12).map((svc: any, idx: number) => (
              <Link href="/register" key={idx}>
                <div className={`rounded-2xl border border-white/10 bg-gradient-to-br ${svc.color || "from-primary/10 to-primary/5"} hover:border-primary/50 hover:shadow-lg hover:shadow-primary/10 transition-all cursor-pointer group p-4 text-center`}>
                  <div className="text-3xl mb-2.5 group-hover:scale-110 transition-transform inline-block">{svc.icon}</div>
                  <p className="text-xs font-semibold truncate mb-1 text-white/80">{svc.name}</p>
                  <p className="text-xs text-cyan-400 font-bold">${svc.price}</p>
                </div>
              </Link>
            ))}
          </div>
          <div className="text-center mt-8">
            <Link href="/register">
              <Button variant="outline" className="rounded-xl px-8 gap-2 border-white/20 text-white/70 hover:text-white hover:border-white/40">
                See All 500+ Services <ArrowRight className="w-4 h-4" />
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* ── Social Proof ── */}
      <section className="py-20 bg-[#070f1f] border-y border-white/10">
        <div className="max-w-5xl mx-auto px-4">
          <p className="text-center text-white/30 text-sm font-medium mb-10 uppercase tracking-widest">Trusted by developers and privacy-conscious users worldwide</p>
          <div className="grid md:grid-cols-3 gap-4">
            {[
              { text: "Super fast and reliable. Got my WhatsApp OTP in literally 2 seconds. Will use again.", name: "Alex K.", role: "Developer" },
              { text: "Finally a service that just works. Clean UI, instant codes, fair pricing. My go-to for all verifications.", name: "Sam T.", role: "Product Manager" },
              { text: "API integration was seamless. Works perfectly in automation scripts with zero issues.", name: "Jordan M.", role: "Backend Engineer" },
            ].map(r => (
              <div key={r.name} className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 hover:border-white/20 transition-all">
                <div className="flex gap-0.5 mb-3">
                  {[1,2,3,4,5].map(s => <Star key={s} className="w-4 h-4 fill-yellow-400 text-yellow-400" />)}
                </div>
                <p className="text-sm text-white/50 leading-relaxed mb-4">"{r.text}"</p>
                <div>
                  <p className="text-sm font-semibold text-white">{r.name}</p>
                  <p className="text-xs text-white/30">{r.role}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── FAQ ── */}
      <section id="faq" className="py-24 max-w-3xl mx-auto px-4">
        <div className="text-center mb-14">
          <p className="text-primary text-sm font-semibold uppercase tracking-widest mb-3">FAQ</p>
          <h2 className="text-3xl md:text-4xl font-bold text-white">Common Questions</h2>
        </div>
        <div className="space-y-3">
          {FAQS.map((faq, i) => (
            <div
              key={i}
              className={`rounded-2xl border transition-all ${openFaq === i ? "border-primary/50 bg-primary/5" : "border-white/10 bg-white/[0.02] hover:border-white/20"}`}
            >
              <button
                className="flex items-center justify-between w-full p-5 text-left gap-4"
                onClick={() => setOpenFaq(openFaq === i ? null : i)}
                data-testid={`button-faq-${i}`}
              >
                <span className="font-semibold text-sm text-white/85">{faq.q}</span>
                <div className={`shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-all ${openFaq === i ? "bg-primary text-white" : "bg-white/10 text-white/40"}`}>
                  {openFaq === i ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
                </div>
              </button>
              {openFaq === i && (
                <div className="px-5 pb-5 text-sm text-white/45 leading-relaxed">{faq.a}</div>
              )}
            </div>
          ))}
        </div>
      </section>

      {/* ── CTA ── */}
      <section className="py-4 px-4 pb-24">
        <div className="max-w-4xl mx-auto rounded-3xl overflow-hidden relative border border-white/10 p-12 md:p-16 text-center bg-[#070f1f]">
          <div className="absolute inset-0 hero-grid opacity-40" />
          <div className="absolute -top-20 left-1/2 -translate-x-1/2 w-96 h-80 bg-primary/15 rounded-full blur-[100px] pointer-events-none" />
          <div className="absolute -bottom-10 right-10 w-64 h-64 bg-violet-500/10 rounded-full blur-[80px] pointer-events-none" />
          <div className="relative">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-primary/30 bg-primary/10 text-primary text-sm font-medium mb-6">
              <span className="w-2 h-2 rounded-full bg-primary animate-pulse" />
              Free to get started · No card required
            </div>
            <h2 className="text-3xl md:text-5xl font-bold text-white mb-4">
              Ready to Protect<br />Your Privacy?
            </h2>
            <p className="text-white/45 text-lg mb-8 max-w-lg mx-auto">
              Join thousands of users verifying accounts without exposing their real phone number.
            </p>
            <Link href="/register">
              <Button
                size="lg"
                className="rounded-2xl px-12 h-14 text-base font-semibold glow-primary-sm hover:glow-primary transition-all bg-gradient-to-r from-[hsl(185,72%,38%)] to-[hsl(185,72%,48%)]"
                data-testid="button-cta-register"
              >
                Create Free Account <ArrowRight className="w-5 h-5 ml-2" />
              </Button>
            </Link>
            <p className="mt-5 text-white/20 text-sm">No credit card required · Cancel anytime</p>
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer className="border-t border-white/10 py-10">
        <div className="max-w-5xl mx-auto px-4">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
            <Logo size={26} />
            <div className="flex gap-8 text-sm text-white/35">
              <button onClick={() => document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' })} className="hover:text-white transition-colors">Services</button>
              <button onClick={() => document.getElementById('how-it-works')?.scrollIntoView({ behavior: 'smooth' })} className="hover:text-white transition-colors">How It Works</button>
              <button onClick={() => document.getElementById('faq')?.scrollIntoView({ behavior: 'smooth' })} className="hover:text-white transition-colors">FAQ</button>
              <Link href="/api-docs" className="hover:text-white transition-colors">API</Link>
            </div>
          </div>
          <div className="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-white/20">
            <p>© 2025 GetOTPs. All rights reserved.</p>
            <div className="flex gap-5">
              <a href="#" className="hover:text-white/50 transition-colors">Privacy Policy</a>
              <a href="#" className="hover:text-white/50 transition-colors">Terms of Service</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
