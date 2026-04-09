import { Link } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { HeroScene } from "@/components/3d/HeroScene";
import { PhoneMockup } from "@/components/3d/PhoneMockup";
import { SceneErrorBoundary } from "@/components/3d/SceneErrorBoundary";
import {
  Zap, Shield, Globe, ArrowRight, Star, Lock,
  ChevronDown, ChevronUp, CheckCircle, Cpu, Key,
  Clock, RefreshCw, BarChart3, Wifi, Server, Sparkles
} from "lucide-react";
import { useState, useEffect, useRef } from "react";

/* ─── Data ─── */
const PLATFORMS = [
  { name: "WhatsApp", emoji: "💬", price: "1.92", color: "#25d366", hot: true },
  { name: "Telegram", emoji: "✈️", price: "1.32", color: "#0088cc", hot: true },
  { name: "Google", emoji: "🔍", price: "0.45", color: "#4285f4" },
  { name: "TikTok", emoji: "🎵", price: "0.33", color: "#ff0050", hot: true },
  { name: "Facebook", emoji: "👤", price: "0.40", color: "#1877f2" },
  { name: "Instagram", emoji: "📸", price: "0.45", color: "#e1306c" },
  { name: "Discord", emoji: "🎮", price: "0.30", color: "#5865f2" },
  { name: "Binance", emoji: "₿", price: "0.75", color: "#f3ba2f", hot: true },
  { name: "Twitter/X", emoji: "𝕏", price: "0.35", color: "#ffffff" },
  { name: "Amazon", emoji: "🛒", price: "0.55", color: "#ff9900" },
  { name: "Uber", emoji: "🚗", price: "0.60", color: "#000000" },
  { name: "PayPal", emoji: "💳", price: "0.70", color: "#003087" },
  { name: "Snapchat", emoji: "👻", price: "0.33", color: "#fffc00" },
  { name: "LinkedIn", emoji: "💼", price: "0.40", color: "#0a66c2" },
  { name: "Coinbase", emoji: "🪙", price: "0.75", color: "#0052ff" },
  { name: "Microsoft", emoji: "🪟", price: "0.35", color: "#737373" },
];

const COUNTRIES = [
  { flag: "🇺🇸", name: "United States", code: "+1", stock: 500, color: "from-blue-600/20" },
  { flag: "🇬🇧", name: "United Kingdom", code: "+44", stock: 120, color: "from-red-600/20" },
  { flag: "🇨🇦", name: "Canada", code: "+1", stock: 95, color: "from-red-500/20" },
  { flag: "🇦🇺", name: "Australia", code: "+61", stock: 80, color: "from-yellow-600/20" },
  { flag: "🇩🇪", name: "Germany", code: "+49", stock: 75, color: "from-gray-600/20" },
  { flag: "🇫🇷", name: "France", code: "+33", stock: 60, color: "from-blue-500/20" },
];

const STEPS = [
  { n: "01", icon: "🌍", title: "Choose Country", desc: "Select from 50+ countries with real-time number availability." },
  { n: "02", icon: "📱", title: "Pick a Service", desc: "Search from 500+ platforms — WhatsApp, Telegram, Binance & more." },
  { n: "03", icon: "💳", title: "Rent Your Number", desc: "Instant activation. Your virtual US number is live within seconds." },
  { n: "04", icon: "✅", title: "Receive Your OTP", desc: "Code arrives in your dashboard in under 5 seconds. Done." },
];

const PRICING = [
  {
    name: "Receive OTP",
    tagline: "One-time verification",
    price: "From $0.15",
    period: "per code",
    color: "from-cyan-500/20 to-cyan-600/5",
    border: "border-cyan-500/30",
    glow: "shadow-cyan-500/10",
    features: [
      "20-min number window",
      "Full refund if no SMS",
      "500+ supported services",
      "Instant code delivery",
      "No subscription needed",
    ],
    cta: "Receive OTP",
    icon: <Key className="w-5 h-5" />,
  },
  {
    name: "Rent a Number",
    tagline: "Short or long-term rental",
    price: "From $0.50",
    period: "per rental",
    popular: true,
    color: "from-violet-500/20 to-violet-600/5",
    border: "border-violet-500/40",
    glow: "shadow-violet-500/15",
    features: [
      "Keep the number longer",
      "Receive multiple SMS",
      "Full number control",
      "Works across all apps",
      "Extended rental options",
    ],
    cta: "Rent Number",
    icon: <Server className="w-5 h-5" />,
  },
  {
    name: "API / Bulk",
    tagline: "Developer & reseller access",
    price: "Custom",
    period: "volume pricing",
    color: "from-emerald-500/20 to-emerald-600/5",
    border: "border-emerald-500/30",
    glow: "shadow-emerald-500/10",
    features: [
      "REST API included",
      "Bulk number ordering",
      "Webhook callbacks",
      "Reseller dashboard",
      "Priority support",
    ],
    cta: "Talk to Us",
    icon: <BarChart3 className="w-5 h-5" />,
  },
];

const TRUST = [
  { icon: <Zap className="w-5 h-5 text-yellow-400" />, bg: "bg-yellow-400/10", title: "< 5 Second Delivery", desc: "OTP codes arrive in your dashboard nearly instantly after the service sends them." },
  { icon: <Shield className="w-5 h-5 text-cyan-400" />, bg: "bg-cyan-400/10", title: "100% Anonymous", desc: "Your real phone number is never stored, shared, or used. Complete privacy." },
  { icon: <RefreshCw className="w-5 h-5 text-emerald-400" />, bg: "bg-emerald-400/10", title: "Full Refund Guarantee", desc: "If no SMS arrives within the rental window, your balance is instantly returned." },
  { icon: <Globe className="w-5 h-5 text-violet-400" />, bg: "bg-violet-400/10", title: "50+ Countries", desc: "Choose from a global pool of virtual numbers across major markets." },
  { icon: <Cpu className="w-5 h-5 text-orange-400" />, bg: "bg-orange-400/10", title: "REST API Access", desc: "Every account includes a free API key for building automations and integrations." },
  { icon: <Lock className="w-5 h-5 text-pink-400" />, bg: "bg-pink-400/10", title: "Secure Balance", desc: "Prepaid wallet model. No subscriptions, no chargebacks, no surprise fees." },
];

const REVIEWS = [
  { text: "Got my WhatsApp OTP in literally 2 seconds. Unbelievably fast.", name: "Alex K.", role: "Developer", stars: 5 },
  { text: "Clean UI, instant codes, fair pricing. My absolute go-to for all account verifications.", name: "Sam T.", role: "Product Manager", stars: 5 },
  { text: "API integration took 10 minutes. Works flawlessly in my automation scripts.", name: "Jordan M.", role: "Backend Engineer", stars: 5 },
];

const FAQS = [
  { q: "What is GetOTPs?", a: "GetOTPs is a virtual phone number platform that lets you rent temporary US and international numbers to receive SMS verification codes (OTPs) for 500+ apps — without using your real phone number." },
  { q: "What's the difference between Receive OTP and Rent Number?", a: "Receive OTP gives you a 20-minute window to get a single verification code for one specific service. Rent Number gives you longer-term control of a number to receive multiple SMS from any app." },
  { q: "How fast are the codes delivered?", a: "Codes typically appear in your dashboard within 5 seconds of the service sending them. Our infrastructure is built for real-time SMS forwarding." },
  { q: "What happens if no SMS arrives?", a: "If no SMS is received within your rental window, your balance is automatically and instantly refunded. No questions asked." },
  { q: "Do you support Binance, Telegram, and WhatsApp?", a: "Yes. We support all three and hundreds more. WhatsApp and Telegram have high-demand numbers that sell out fast — we restock continuously." },
  { q: "Can I use the API for bulk operations?", a: "Absolutely. Every account gets a free API key. You can automate number ordering, check SMS status, and build workflows using our REST API." },
];

/* ─── Animated counter ─── */
function Counter({ target, suffix = "" }: { target: string; suffix?: string }) {
  return <span className="text-3xl md:text-4xl font-black text-cyan-400 tabular-nums">{target}{suffix}</span>;
}

/* ─── Dashboard preview mockup ─── */
function DashboardPreview() {
  const [activeTab, setActiveTab] = useState(0);
  const tabs = ["OTP Inbox", "Active Rentals", "Order History"];
  const [tick, setTick] = useState(0);
  useEffect(() => {
    const t = setInterval(() => setTick(x => x + 1), 3000);
    return () => clearInterval(t);
  }, []);

  const otpItems = [
    { service: "WhatsApp", code: "847 291", time: "2s ago", status: "received", color: "#25d366" },
    { service: "Telegram", code: "563 018", time: "1m ago", status: "received", color: "#0088cc" },
    { service: "Google", code: "391 752", time: "3m ago", status: "received", color: "#4285f4" },
    { service: "TikTok", code: "204 687", time: "5m ago", status: "expired", color: "#ff0050" },
  ];

  const rentalItems = [
    { number: "+1 (555) 832-4910", service: "WhatsApp", expires: "18:42", status: "active" },
    { number: "+1 (555) 217-0381", service: "Binance", expires: "04:12", status: "active" },
    { number: "+44 7911 123456", service: "Telegram", expires: "Expired", status: "expired" },
  ];

  return (
    <div className="dashboard-preview">
      {/* Header */}
      <div className="dp-header">
        <div className="dp-logo">
          <div className="dp-logo-dot" />
          <span>GetOTPs</span>
          <span className="dp-badge">Pro</span>
        </div>
        <div className="dp-balance">
          <span className="dp-balance-label">Balance</span>
          <span className="dp-balance-amount">$24.80</span>
        </div>
      </div>

      {/* Tabs */}
      <div className="dp-tabs">
        {tabs.map((t, i) => (
          <button key={t} className={`dp-tab ${activeTab === i ? "dp-tab-active" : ""}`} onClick={() => setActiveTab(i)}>
            {t}
            {i === 0 && <span className="dp-tab-dot" />}
          </button>
        ))}
      </div>

      {/* Content */}
      <div className="dp-content">
        {activeTab === 0 && (
          <div className="dp-list">
            {otpItems.map((item, i) => (
              <div key={i} className={`dp-item ${item.status === "expired" ? "dp-item-expired" : ""}`}>
                <div className="dp-item-service" style={{ color: item.color }}>{item.service}</div>
                <div className="dp-item-code">{item.code}</div>
                <div className="dp-item-meta">
                  <span className="dp-item-time">{item.time}</span>
                  <span className={`dp-item-status ${item.status === "received" ? "dp-status-ok" : "dp-status-exp"}`}>
                    {item.status}
                  </span>
                </div>
              </div>
            ))}
            {/* Live entry flicker */}
            {tick % 2 === 0 && (
              <div className="dp-item dp-item-live">
                <div className="dp-item-service" style={{ color: "#f3ba2f" }}>Binance</div>
                <div className="dp-item-code">
                  <span className="dp-code-blink">●</span> Waiting…
                </div>
                <div className="dp-item-meta">
                  <span className="dp-item-time">now</span>
                  <span className="dp-item-status dp-status-wait">pending</span>
                </div>
              </div>
            )}
          </div>
        )}
        {activeTab === 1 && (
          <div className="dp-list">
            {rentalItems.map((item, i) => (
              <div key={i} className={`dp-item ${item.status === "expired" ? "dp-item-expired" : ""}`}>
                <div className="dp-item-number">{item.number}</div>
                <div className="dp-item-service" style={{ color: "#22d3ee" }}>{item.service}</div>
                <div className="dp-item-meta">
                  <span className={`dp-item-expires ${item.status === "active" ? "text-emerald-400" : "text-white/30"}`}>
                    {item.status === "active" ? `⏱ ${item.expires}` : item.expires}
                  </span>
                  <span className={`dp-item-status ${item.status === "active" ? "dp-status-ok" : "dp-status-exp"}`}>
                    {item.status}
                  </span>
                </div>
              </div>
            ))}
          </div>
        )}
        {activeTab === 2 && (
          <div className="dp-list">
            {[
              { service: "WhatsApp", type: "OTP", amount: "-$1.92", time: "Today 14:22", ok: true },
              { service: "Telegram", type: "Rental", amount: "-$0.50", time: "Today 13:08", ok: true },
              { service: "Google", type: "OTP", amount: "-$0.45", time: "Yesterday", ok: true },
              { service: "TikTok", type: "OTP (refund)", amount: "+$0.33", time: "Yesterday", ok: false },
            ].map((item, i) => (
              <div key={i} className="dp-item">
                <div className="dp-item-service" style={{ color: "#22d3ee" }}>{item.service}</div>
                <div className="dp-item-code" style={{ fontSize: "11px", opacity: 0.6 }}>{item.type}</div>
                <div className="dp-item-meta">
                  <span className="dp-item-time">{item.time}</span>
                  <span className={item.ok ? "dp-item-debit" : "dp-item-credit"}>{item.amount}</span>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Footer action row */}
      <div className="dp-footer">
        <button className="dp-action-btn">+ New OTP</button>
        <button className="dp-action-btn dp-action-secondary">Rent Number</button>
      </div>
    </div>
  );
}

/* ─── Main component ─── */
export default function Landing() {
  const { user } = useAuth();
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const fn = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", fn);
    return () => window.removeEventListener("scroll", fn);
  }, []);

  const scrollTo = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });

  const { data: services } = useQuery<any[]>({ queryKey: ["/api/services"] });

  const displayServices = services?.length ? services.slice(0, 16) : PLATFORMS;

  return (
    <div className="min-h-screen bg-[#040c18] text-white overflow-x-hidden">

      {/* ─── NAVBAR ─── */}
      <header className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${scrolled ? "bg-[#040c18]/95 backdrop-blur-xl border-b border-white/8 shadow-2xl shadow-black/40" : "bg-transparent"}`}>
        <div className="max-w-7xl mx-auto px-5 h-[70px] flex items-center justify-between">
          <Logo size={32} />
          <nav className="hidden md:flex items-center gap-8 text-sm font-medium">
            {[
              ["Services", "platforms"],
              ["How It Works", "how-it-works"],
              ["Pricing", "pricing"],
              ["FAQ", "faq"],
            ].map(([label, id]) => (
              <button key={id} onClick={() => scrollTo(id)} className="text-white/50 hover:text-white transition-colors">
                {label}
              </button>
            ))}
          </nav>
          <div className="flex items-center gap-2">
            {user ? (
              <Link href="/dashboard">
                <Button size="sm" className="rounded-xl font-semibold bg-primary hover:bg-primary/90 gap-1.5" data-testid="button-go-dashboard">
                  Dashboard <ArrowRight className="w-3.5 h-3.5" />
                </Button>
              </Link>
            ) : (
              <>
                <Link href="/login">
                  <Button variant="ghost" size="sm" className="rounded-xl font-medium text-white/60 hover:text-white hover:bg-white/8" data-testid="button-login">
                    Sign In
                  </Button>
                </Link>
                <Link href="/register">
                  <Button size="sm" className="rounded-xl font-semibold px-5 bg-gradient-to-r from-[hsl(185,72%,38%)] to-[hsl(185,72%,50%)] hover:from-[hsl(185,72%,42%)] hover:to-[hsl(185,72%,54%)] shadow-lg shadow-cyan-500/20" data-testid="button-register">
                    Get Started Free
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>
      </header>

      {/* ─── HERO ─── */}
      <section className="relative min-h-screen flex items-center pt-[70px] overflow-hidden">
        {/* 3D canvas bg */}
        <SceneErrorBoundary fallback={<div className="absolute inset-0 hero-grid opacity-20" />}>
          <HeroScene />
        </SceneErrorBoundary>

        {/* Gradient overlays */}
        <div className="absolute inset-0 bg-gradient-to-b from-[#040c18]/30 via-transparent to-[#040c18] pointer-events-none z-[1]" />
        <div className="absolute inset-0 bg-gradient-to-r from-[#040c18]/80 via-[#040c18]/20 to-transparent pointer-events-none z-[1]" />

        <div className="relative z-10 w-full max-w-7xl mx-auto px-5 py-20 grid lg:grid-cols-2 gap-12 items-center">
          {/* Left: copy */}
          <div>
            {/* Badge */}
            <div className="hero-badge mb-8">
              <span className="hero-badge-dot" />
              <span>500+ Services · 50+ Countries · From $0.15</span>
            </div>

            <h1 className="text-5xl md:text-6xl xl:text-[72px] font-black tracking-tight text-white leading-[1.05] mb-6">
              Rent Numbers.<br />
              <span className="hero-gradient-text">Receive OTPs.</span><br />
              <span className="text-white/80">Activate Faster.</span>
            </h1>

            <p className="text-lg md:text-xl text-white/50 max-w-lg mb-10 leading-relaxed">
              Instant virtual phone numbers for SMS verification on WhatsApp, Telegram, Google, TikTok, Binance & 500+ more.
              <span className="text-white/80 font-semibold"> No real number. No exposure.</span>
            </p>

            {/* Dual CTA */}
            <div className="flex flex-col sm:flex-row gap-3 mb-10">
              <Link href="/register">
                <button className="hero-cta-primary" data-testid="button-hero-register">
                  Get Number Now
                  <ArrowRight className="w-5 h-5" />
                </button>
              </Link>
              <button className="hero-cta-secondary" onClick={() => scrollTo("platforms")}>
                View Services
              </button>
            </div>

            {/* Trust pills */}
            <div className="flex flex-wrap gap-3">
              {[
                { icon: <Zap className="w-3.5 h-3.5 text-yellow-400" />, text: "< 5s delivery" },
                { icon: <Shield className="w-3.5 h-3.5 text-cyan-400" />, text: "100% anonymous" },
                { icon: <RefreshCw className="w-3.5 h-3.5 text-emerald-400" />, text: "Full refund guarantee" },
                { icon: <Wifi className="w-3.5 h-3.5 text-violet-400" />, text: "99.9% uptime" },
              ].map(p => (
                <div key={p.text} className="trust-pill">
                  {p.icon}
                  <span>{p.text}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Right: phone mockup */}
          <div className="hidden lg:flex items-center justify-center relative">
            {/* Floating service badges */}
            <div className="absolute top-8 -left-4 hero-float-card z-20" style={{ animationDelay: "0s" }}>
              <span>💬</span>
              <div>
                <div className="text-xs font-bold text-white">WhatsApp OTP</div>
                <div className="text-[10px] text-emerald-400">Delivered in 2s ✓</div>
              </div>
            </div>
            <div className="absolute bottom-20 -left-8 hero-float-card z-20" style={{ animationDelay: "0.8s" }}>
              <span>✈️</span>
              <div>
                <div className="text-xs font-bold text-white">Telegram</div>
                <div className="text-[10px] text-emerald-400">Code: 563 018 ✓</div>
              </div>
            </div>
            <div className="absolute top-16 -right-6 hero-float-card z-20" style={{ animationDelay: "1.5s" }}>
              <span>₿</span>
              <div>
                <div className="text-xs font-bold text-white">Binance</div>
                <div className="text-[10px] text-cyan-400">Number active ⚡</div>
              </div>
            </div>
            <div className="absolute bottom-10 -right-4 hero-float-card z-20" style={{ animationDelay: "2.2s" }}>
              <span>🎵</span>
              <div>
                <div className="text-xs font-bold text-white">TikTok</div>
                <div className="text-[10px] text-violet-400">From $0.33 →</div>
              </div>
            </div>

            <PhoneMockup />
          </div>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2 text-white/20 text-xs">
          <div className="w-5 h-8 rounded-full border border-white/15 flex items-start justify-center p-1">
            <div className="w-1 h-2 rounded-full bg-white/30 animate-bounce" />
          </div>
        </div>
      </section>

      {/* ─── TWO MAIN SERVICES ─── */}
      <section className="relative z-10 -mt-10 pb-8 px-5">
        <div className="max-w-5xl mx-auto grid md:grid-cols-2 gap-5">
          {/* Receive OTP */}
          <div className="service-split-card service-card-otp">
            <div className="ssc-icon">🔑</div>
            <div className="ssc-body">
              <h3>Receive OTP</h3>
              <p>Get a temporary number, trigger the SMS, receive the code in seconds. Perfect for one-time account verification.</p>
              <div className="ssc-price">From <strong>$0.15</strong> / code</div>
            </div>
            <Link href="/register">
              <button className="ssc-btn">Start Now <ArrowRight className="w-4 h-4" /></button>
            </Link>
          </div>

          {/* Rent Number */}
          <div className="service-split-card service-card-rent">
            <div className="ssc-icon">📲</div>
            <div className="ssc-body">
              <h3>Rent a Number</h3>
              <p>Get full control of a virtual number for longer. Receive multiple SMS, test integrations, or hold it for reuse.</p>
              <div className="ssc-price">From <strong>$0.50</strong> / rental</div>
            </div>
            <Link href="/register">
              <button className="ssc-btn ssc-btn-rent">Rent Now <ArrowRight className="w-4 h-4" /></button>
            </Link>
          </div>
        </div>
      </section>

      {/* ─── STATS BAR ─── */}
      <section className="py-2 border-y border-white/8 bg-[#060e1c]">
        <div className="max-w-5xl mx-auto px-5">
          <div className="grid grid-cols-2 md:grid-cols-4">
            {[
              { value: "500+", label: "Supported Services" },
              { value: "50+", label: "Countries" },
              { value: "99.9%", label: "Uptime" },
              { value: "< 5s", label: "Average Delivery" },
            ].map((s, i) => (
              <div key={s.label} className={`py-7 px-5 text-center ${i < 3 ? "md:border-r border-white/8" : ""}`}>
                <Counter target={s.value} />
                <p className="text-[11px] text-white/30 mt-1.5 uppercase tracking-widest font-medium">{s.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── HOW IT WORKS ─── */}
      <section id="how-it-works" className="py-28 bg-[#040c18]">
        <div className="max-w-6xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">Simple Process</p>
            <h2 className="section-title">Get Your OTP in Under 2 Minutes</h2>
            <p className="section-subtitle">Four steps. No technical knowledge required.</p>
          </div>

          <div className="grid md:grid-cols-4 gap-5 relative">
            {/* connector line */}
            <div className="hidden md:block absolute top-[52px] left-[calc(12.5%+28px)] right-[calc(12.5%+28px)] h-px bg-gradient-to-r from-transparent via-cyan-500/30 to-transparent" />

            {STEPS.map((step, i) => (
              <div key={step.n} className="step-card group">
                <div className="step-icon-wrap">
                  <span className="text-2xl">{step.icon}</span>
                  <div className="step-num">{step.n}</div>
                </div>
                <h3 className="text-base font-bold text-white mt-5 mb-2">{step.title}</h3>
                <p className="text-sm text-white/40 leading-relaxed">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── SUPPORTED PLATFORMS ─── */}
      <section id="platforms" className="py-28 bg-[#060e1c] border-y border-white/8">
        <div className="max-w-6xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">500+ Supported</p>
            <h2 className="section-title">Works with Every Platform</h2>
            <p className="section-subtitle">From messaging apps to crypto exchanges — if it sends SMS, we cover it.</p>
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-8">
            {displayServices.map((svc: any, i: number) => (
              <Link href="/register" key={i}>
                <div className="platform-card group">
                  <div className="platform-icon">{svc.emoji || svc.icon || "📱"}</div>
                  <div className="platform-name">{svc.name}</div>
                  <div className="platform-price">${parseFloat(svc.price || "0.33").toFixed(2)}</div>
                  {(svc.hot) && <div className="platform-hot">🔥</div>}
                </div>
              </Link>
            ))}
          </div>

          <div className="text-center">
            <Link href="/register">
              <button className="outline-btn">
                Browse All 500+ Services <ArrowRight className="w-4 h-4" />
              </button>
            </Link>
          </div>
        </div>
      </section>

      {/* ─── COUNTRY AVAILABILITY ─── */}
      <section className="py-28 bg-[#040c18]">
        <div className="max-w-6xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">Global Coverage</p>
            <h2 className="section-title">Numbers Available Worldwide</h2>
            <p className="section-subtitle">Real-time inventory from 50+ countries. US numbers always in stock.</p>
          </div>

          <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            {COUNTRIES.map(c => (
              <div key={c.name} className={`country-card bg-gradient-to-br ${c.color} to-transparent`}>
                <div className="country-flag">{c.flag}</div>
                <div className="country-info">
                  <div className="country-name">{c.name}</div>
                  <div className="country-code">{c.code}</div>
                </div>
                <div className="country-stock">
                  <div className="stock-num">{c.stock}</div>
                  <div className="stock-label">in stock</div>
                </div>
                <div className="country-live" />
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── DASHBOARD PREVIEW ─── */}
      <section className="py-28 bg-[#060e1c] border-y border-white/8 overflow-hidden">
        <div className="max-w-6xl mx-auto px-5">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <p className="section-eyebrow">Live Dashboard</p>
              <h2 className="section-title text-left">Your Control Center</h2>
              <p className="section-subtitle text-left mb-8">Track every OTP, manage active rentals, and monitor your balance — all in one clean dashboard.</p>
              <ul className="space-y-4">
                {[
                  { icon: <CheckCircle className="w-4.5 h-4.5 text-cyan-400" />, t: "Real-time OTP inbox" },
                  { icon: <CheckCircle className="w-4.5 h-4.5 text-cyan-400" />, t: "Active rental timer" },
                  { icon: <CheckCircle className="w-4.5 h-4.5 text-cyan-400" />, t: "Full order history" },
                  { icon: <CheckCircle className="w-4.5 h-4.5 text-cyan-400" />, t: "Instant balance top-up" },
                  { icon: <CheckCircle className="w-4.5 h-4.5 text-cyan-400" />, t: "REST API key included" },
                ].map(item => (
                  <li key={item.t} className="flex items-center gap-3 text-white/70 text-sm">
                    {item.icon}
                    {item.t}
                  </li>
                ))}
              </ul>
              <div className="mt-8">
                <Link href="/register">
                  <button className="hero-cta-primary">
                    Open Dashboard <ArrowRight className="w-4 h-4" />
                  </button>
                </Link>
              </div>
            </div>

            <div className="relative flex items-center justify-center">
              <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/10 via-violet-500/5 to-transparent rounded-3xl blur-3xl" />
              <DashboardPreview />
            </div>
          </div>
        </div>
      </section>

      {/* ─── PRICING ─── */}
      <section id="pricing" className="py-28 bg-[#040c18]">
        <div className="max-w-6xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">Transparent Pricing</p>
            <h2 className="section-title">Pay Only for What You Use</h2>
            <p className="section-subtitle">No subscriptions. No hidden fees. Prepaid wallet, spend as you go.</p>
          </div>

          <div className="grid md:grid-cols-3 gap-5">
            {PRICING.map(plan => (
              <div key={plan.name} className={`pricing-card bg-gradient-to-br ${plan.color} border ${plan.border} shadow-2xl ${plan.glow} ${plan.popular ? "pricing-card-featured" : ""}`}>
                {plan.popular && <div className="pricing-popular-badge">Most Popular</div>}
                <div className="pricing-icon">{plan.icon}</div>
                <h3 className="text-xl font-bold text-white mb-1">{plan.name}</h3>
                <p className="text-sm text-white/40 mb-5">{plan.tagline}</p>
                <div className="pricing-amount">
                  <span className="pricing-price">{plan.price}</span>
                  <span className="pricing-period">/ {plan.period}</span>
                </div>
                <ul className="space-y-3 my-7">
                  {plan.features.map(f => (
                    <li key={f} className="flex items-center gap-2.5 text-sm text-white/60">
                      <CheckCircle className="w-4 h-4 text-cyan-400 shrink-0" />
                      {f}
                    </li>
                  ))}
                </ul>
                <Link href="/register">
                  <button className={`w-full pricing-btn ${plan.popular ? "pricing-btn-featured" : ""}`}>
                    {plan.cta} <ArrowRight className="w-4 h-4" />
                  </button>
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── TRUST & SECURITY ─── */}
      <section className="py-28 bg-[#060e1c] border-y border-white/8">
        <div className="max-w-6xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">Built for Trust</p>
            <h2 className="section-title">Security & Reliability First</h2>
            <p className="section-subtitle">Every feature is designed to protect your privacy and guarantee delivery.</p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {TRUST.map(t => (
              <div key={t.title} className="trust-card group">
                <div className={`trust-card-icon ${t.bg}`}>{t.icon}</div>
                <h3 className="font-bold text-white mb-2">{t.title}</h3>
                <p className="text-sm text-white/40 leading-relaxed">{t.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── SOCIAL PROOF ─── */}
      <section className="py-28 bg-[#040c18]">
        <div className="max-w-5xl mx-auto px-5">
          <div className="text-center mb-16">
            <p className="section-eyebrow">Testimonials</p>
            <h2 className="section-title">Trusted Worldwide</h2>
          </div>
          <div className="grid md:grid-cols-3 gap-5">
            {REVIEWS.map(r => (
              <div key={r.name} className="review-card group">
                <div className="flex gap-0.5 mb-3">
                  {[1,2,3,4,5].map(s => <Star key={s} className="w-4 h-4 fill-yellow-400 text-yellow-400" />)}
                </div>
                <p className="text-sm text-white/50 leading-relaxed mb-5 flex-1">"{r.text}"</p>
                <div className="flex items-center gap-3">
                  <div className="review-avatar">{r.name[0]}</div>
                  <div>
                    <p className="text-sm font-semibold text-white">{r.name}</p>
                    <p className="text-xs text-white/30">{r.role}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── FAQ ─── */}
      <section id="faq" className="py-28 bg-[#060e1c] border-t border-white/8">
        <div className="max-w-3xl mx-auto px-5">
          <div className="text-center mb-14">
            <p className="section-eyebrow">FAQ</p>
            <h2 className="section-title">Common Questions</h2>
          </div>
          <div className="space-y-3">
            {FAQS.map((faq, i) => (
              <div key={i} className={`faq-item ${openFaq === i ? "faq-item-open" : ""}`}>
                <button
                  className="flex items-center justify-between w-full p-5 text-left gap-4"
                  onClick={() => setOpenFaq(openFaq === i ? null : i)}
                  data-testid={`button-faq-${i}`}
                >
                  <span className="font-semibold text-sm text-white/85">{faq.q}</span>
                  <div className={`faq-chevron ${openFaq === i ? "faq-chevron-open" : ""}`}>
                    <ChevronDown className="w-3.5 h-3.5" />
                  </div>
                </button>
                {openFaq === i && (
                  <div className="px-5 pb-5 text-sm text-white/45 leading-relaxed">{faq.a}</div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── FINAL CTA ─── */}
      <section className="py-8 px-5 pb-24 bg-[#040c18]">
        <div className="max-w-5xl mx-auto">
          <div className="cta-block">
            <div className="cta-glow-top" />
            <div className="cta-glow-bottom" />
            <div className="absolute inset-0 hero-grid opacity-20 rounded-3xl" />
            <div className="relative">
              <div className="hero-badge mb-6">
                <Sparkles className="w-3.5 h-3.5" />
                <span>Free to start — No credit card required</span>
              </div>
              <h2 className="text-3xl md:text-5xl font-black text-white mb-5 leading-tight">
                Your Private Number.<br />
                <span className="hero-gradient-text">Ready in Seconds.</span>
              </h2>
              <p className="text-white/45 text-lg mb-10 max-w-lg mx-auto">
                Join thousands of developers and privacy-conscious users verifying accounts without ever exposing their real number.
              </p>
              <div className="flex flex-col sm:flex-row gap-3 justify-center">
                <Link href="/register">
                  <button className="hero-cta-primary" data-testid="button-cta-register">
                    Create Free Account <ArrowRight className="w-5 h-5" />
                  </button>
                </Link>
                <button className="hero-cta-secondary" onClick={() => scrollTo("platforms")}>
                  Browse Services
                </button>
              </div>
              <p className="mt-6 text-white/20 text-sm">No credit card required · Pay per use · Cancel anytime</p>
            </div>
          </div>
        </div>
      </section>

      {/* ─── FOOTER ─── */}
      <footer className="border-t border-white/8 bg-[#040c18] py-12">
        <div className="max-w-6xl mx-auto px-5">
          <div className="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-10">
            <div>
              <Logo size={28} />
              <p className="text-xs text-white/30 mt-3 leading-relaxed max-w-[180px]">
                Instant virtual numbers for OTP verification & number rental worldwide.
              </p>
            </div>
            <div>
              <p className="text-xs font-semibold text-white/50 uppercase tracking-widest mb-4">Product</p>
              <div className="space-y-2.5">
                {[["Receive OTP", "platforms"], ["Rent a Number", "pricing"], ["API Access", "pricing"]].map(([l, id]) => (
                  <button key={l} onClick={() => scrollTo(id)} className="block text-sm text-white/35 hover:text-white/70 transition-colors">{l}</button>
                ))}
              </div>
            </div>
            <div>
              <p className="text-xs font-semibold text-white/50 uppercase tracking-widest mb-4">Resources</p>
              <div className="space-y-2.5">
                {[["How It Works", "how-it-works"], ["Pricing", "pricing"], ["FAQ", "faq"]].map(([l, id]) => (
                  <button key={l} onClick={() => scrollTo(id)} className="block text-sm text-white/35 hover:text-white/70 transition-colors">{l}</button>
                ))}
              </div>
            </div>
            <div>
              <p className="text-xs font-semibold text-white/50 uppercase tracking-widest mb-4">Legal</p>
              <div className="space-y-2.5">
                {["Privacy Policy", "Terms of Service", "Refund Policy"].map(l => (
                  <a key={l} href="#" className="block text-sm text-white/35 hover:text-white/70 transition-colors">{l}</a>
                ))}
              </div>
            </div>
          </div>
          <div className="border-t border-white/8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/20">
            <p>© {new Date().getFullYear()} GetOTPs. All rights reserved.</p>
            <p>Built for speed, privacy, and scale.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
