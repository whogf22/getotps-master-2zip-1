import { Link } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { HeroScene } from "@/components/3d/HeroScene";
import { PhoneMockup } from "@/components/3d/PhoneMockup";
import { SceneErrorBoundary } from "@/components/3d/SceneErrorBoundary";
import { LiveOTPFeed, LiveActivityBar } from "@/components/3d/LiveOTPFeed";
import {
  Zap, Shield, Globe, ArrowRight, Star, Lock,
  ChevronDown, CheckCircle, Cpu, Key, RefreshCw,
  BarChart3, Wifi, Server, Sparkles, Eye, Clock
} from "lucide-react";
import { useState, useEffect, useRef, useCallback } from "react";

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
  { name: "Uber", emoji: "🚗", price: "0.60", color: "#276ef1" },
  { name: "PayPal", emoji: "💳", price: "0.70", color: "#003087" },
  { name: "Snapchat", emoji: "👻", price: "0.33", color: "#fffc00" },
  { name: "LinkedIn", emoji: "💼", price: "0.40", color: "#0a66c2" },
  { name: "Coinbase", emoji: "🪙", price: "0.75", color: "#0052ff" },
  { name: "Microsoft", emoji: "🪟", price: "0.35", color: "#737373" },
];

const COUNTRIES = [
  { flag: "🇺🇸", name: "United States", code: "+1", stock: 500 },
  { flag: "🇬🇧", name: "United Kingdom", code: "+44", stock: 120 },
  { flag: "🇨🇦", name: "Canada", code: "+1", stock: 95 },
  { flag: "🇦🇺", name: "Australia", code: "+61", stock: 80 },
  { flag: "🇩🇪", name: "Germany", code: "+49", stock: 75 },
  { flag: "🇫🇷", name: "France", code: "+33", stock: 60 },
];

const TRUST = [
  { icon: <Zap className="w-5 h-5 text-yellow-400" />, bg: "bg-yellow-400/10", title: "< 5 Second Delivery", desc: "OTP codes reach your dashboard nearly instantly after the service sends them." },
  { icon: <Shield className="w-5 h-5 text-cyan-400" />, bg: "bg-cyan-400/10", title: "100% Anonymous", desc: "Your real phone number is never stored, shared, or exposed. Total privacy." },
  { icon: <RefreshCw className="w-5 h-5 text-emerald-400" />, bg: "bg-emerald-400/10", title: "Full Refund Guarantee", desc: "No SMS arrives? Your balance is instantly returned. No questions." },
  { icon: <Globe className="w-5 h-5 text-violet-400" />, bg: "bg-violet-400/10", title: "50+ Countries", desc: "Global pool of virtual numbers across every major market worldwide." },
  { icon: <Cpu className="w-5 h-5 text-orange-400" />, bg: "bg-orange-400/10", title: "REST API Access", desc: "Every account gets a free API key. Automate at scale." },
  { icon: <Lock className="w-5 h-5 text-pink-400" />, bg: "bg-pink-400/10", title: "Secure Wallet", desc: "Prepaid balance. No subscriptions, no surprise fees. You control spending." },
];

const FAQS = [
  { q: "What is GetOTPs?", a: "GetOTPs is a virtual phone number platform that lets you rent temporary numbers to receive SMS verification codes for 500+ apps without using your real number." },
  { q: "What's the difference between Receive OTP and Rent Number?", a: "Receive OTP gives you a 20-minute window for a single verification code. Rent Number gives you longer-term control to receive multiple SMS from any app." },
  { q: "How fast are codes delivered?", a: "Codes typically appear in your dashboard within 2-5 seconds of the service sending them. Our infrastructure is optimized for real-time delivery." },
  { q: "What if no SMS arrives?", a: "Your balance is automatically and instantly refunded if no SMS is received within the rental window. Zero risk." },
  { q: "Can I use the API for automation?", a: "Yes. Every account gets a free API key. Automate number ordering, check SMS status, and build workflows using our REST API." },
  { q: "Which services are supported?", a: "We support 500+ services including WhatsApp, Telegram, Google, TikTok, Binance, Discord, Facebook, Instagram, and many more." },
];

const PRICING = [
  {
    name: "Receive OTP", tagline: "One-time verification",
    price: "From $0.15", period: "per code", icon: <Key className="w-5 h-5" />,
    features: ["20-min number window", "Full refund if no SMS", "500+ supported services", "Instant code delivery", "No subscription"],
  },
  {
    name: "Rent a Number", tagline: "Extended rental", popular: true,
    price: "From $0.50", period: "per rental", icon: <Server className="w-5 h-5" />,
    features: ["Keep the number longer", "Receive multiple SMS", "Full number control", "Works across all apps", "Extended options"],
  },
  {
    name: "API / Bulk", tagline: "Developer access",
    price: "Custom", period: "volume pricing", icon: <BarChart3 className="w-5 h-5" />,
    features: ["REST API included", "Bulk ordering", "Webhook callbacks", "Reseller dashboard", "Priority support"],
  },
];

function useScrollReveal() {
  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([e]) => { if (e.isIntersecting) setVisible(true); }, { threshold: 0.12 });
    obs.observe(el);
    return () => obs.disconnect();
  }, []);
  return { ref, visible };
}

function RevealSection({ children, className = "", delay = 0 }: { children: React.ReactNode; className?: string; delay?: number }) {
  const { ref, visible } = useScrollReveal();
  return (
    <div ref={ref} className={`reveal-section ${visible ? "revealed" : ""} ${className}`} style={{ transitionDelay: `${delay}ms` }}>
      {children}
    </div>
  );
}

function DashboardPreview() {
  const [tab, setTab] = useState(0);
  const tabs = ["OTP Inbox", "Active Rentals", "History"];
  const [tick, setTick] = useState(0);
  useEffect(() => { const t = setInterval(() => setTick(x => x + 1), 3000); return () => clearInterval(t); }, []);

  return (
    <div className="dashboard-preview">
      <div className="dp-header">
        <div className="dp-logo"><div className="dp-logo-dot" /><span>GetOTPs</span><span className="dp-badge">Pro</span></div>
        <div className="dp-balance"><span className="dp-balance-label">Balance</span><span className="dp-balance-amount">$24.80</span></div>
      </div>
      <div className="dp-tabs">
        {tabs.map((t, i) => (
          <button key={t} className={`dp-tab ${tab === i ? "dp-tab-active" : ""}`} onClick={() => setTab(i)}>
            {t}{i === 0 && <span className="dp-tab-dot" />}
          </button>
        ))}
      </div>
      <div className="dp-content">
        {tab === 0 && (
          <div className="dp-list">
            {[
              { svc: "WhatsApp", code: "847 291", time: "2s ago", st: "received", cl: "#25d366" },
              { svc: "Telegram", code: "563 018", time: "1m ago", st: "received", cl: "#0088cc" },
              { svc: "Google", code: "391 752", time: "3m ago", st: "received", cl: "#4285f4" },
            ].map((r, i) => (
              <div key={i} className="dp-item">
                <div className="dp-item-service" style={{ color: r.cl }}>{r.svc}</div>
                <div className="dp-item-code">{r.code}</div>
                <div className="dp-item-meta"><span className="dp-item-time">{r.time}</span><span className="dp-item-status dp-status-ok">{r.st}</span></div>
              </div>
            ))}
            {tick % 2 === 0 && (
              <div className="dp-item dp-item-live">
                <div className="dp-item-service" style={{ color: "#f3ba2f" }}>Binance</div>
                <div className="dp-item-code"><span className="dp-code-blink">●</span> Waiting…</div>
                <div className="dp-item-meta"><span className="dp-item-time">now</span><span className="dp-item-status dp-status-wait">pending</span></div>
              </div>
            )}
          </div>
        )}
        {tab === 1 && (
          <div className="dp-list">
            {[
              { num: "+1 (555) 832-4910", svc: "WhatsApp", exp: "18:42", active: true },
              { num: "+1 (555) 217-0381", svc: "Binance", exp: "04:12", active: true },
              { num: "+44 7911 123456", svc: "Telegram", exp: "Expired", active: false },
            ].map((r, i) => (
              <div key={i} className={`dp-item ${!r.active ? "dp-item-expired" : ""}`}>
                <div className="dp-item-number">{r.num}</div>
                <div className="dp-item-service" style={{ color: "#22d3ee" }}>{r.svc}</div>
                <div className="dp-item-meta">
                  <span className={r.active ? "text-emerald-400 text-[10px]" : "text-white/30 text-[10px]"}>{r.active ? `⏱ ${r.exp}` : r.exp}</span>
                  <span className={`dp-item-status ${r.active ? "dp-status-ok" : "dp-status-exp"}`}>{r.active ? "active" : "expired"}</span>
                </div>
              </div>
            ))}
          </div>
        )}
        {tab === 2 && (
          <div className="dp-list">
            {[
              { svc: "WhatsApp", type: "OTP", amt: "-$1.92", time: "14:22" },
              { svc: "Telegram", type: "Rental", amt: "-$0.50", time: "13:08" },
              { svc: "Google", type: "OTP", amt: "-$0.45", time: "Yesterday" },
              { svc: "TikTok", type: "Refund", amt: "+$0.33", time: "Yesterday" },
            ].map((r, i) => (
              <div key={i} className="dp-item">
                <div className="dp-item-service" style={{ color: "#22d3ee" }}>{r.svc}</div>
                <div className="dp-item-code" style={{ fontSize: "11px", opacity: 0.6 }}>{r.type}</div>
                <div className="dp-item-meta"><span className="dp-item-time">{r.time}</span><span className={r.amt.startsWith("+") ? "dp-item-credit" : "dp-item-debit"}>{r.amt}</span></div>
              </div>
            ))}
          </div>
        )}
      </div>
      <div className="dp-footer">
        <button className="dp-action-btn">+ New OTP</button>
        <button className="dp-action-btn dp-action-secondary">Rent Number</button>
      </div>
    </div>
  );
}

export default function Landing() {
  const { user } = useAuth();
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [scrolled, setScrolled] = useState(false);
  const [mousePos, setMousePos] = useState({ x: 0, y: 0 });

  useEffect(() => {
    const fn = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", fn);
    return () => window.removeEventListener("scroll", fn);
  }, []);

  useEffect(() => {
    const fn = (e: MouseEvent) => {
      setMousePos({ x: (e.clientX / window.innerWidth - 0.5) * 2, y: (e.clientY / window.innerHeight - 0.5) * 2 });
    };
    window.addEventListener("mousemove", fn);
    return () => window.removeEventListener("mousemove", fn);
  }, []);

  const scrollTo = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
  const { data: services } = useQuery<any[]>({ queryKey: ["/api/services"] });
  const displayPlatforms = services?.length ? services.slice(0, 16) : PLATFORMS;

  return (
    <div className="min-h-screen bg-[#030810] text-white overflow-x-hidden">

      {/* ─── NAVBAR ─── */}
      <header className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${scrolled ? "nav-scrolled" : "bg-transparent"}`}>
        <div className="max-w-7xl mx-auto px-5 h-[72px] flex items-center justify-between">
          <Logo size={32} />
          <nav className="hidden md:flex items-center gap-8 text-[13px] font-medium">
            {[["Services", "platforms"], ["How It Works", "how-it-works"], ["Pricing", "pricing"], ["FAQ", "faq"]].map(([label, id]) => (
              <button key={id} onClick={() => scrollTo(id)} className="text-white/40 hover:text-white transition-colors duration-300">{label}</button>
            ))}
          </nav>
          <div className="flex items-center gap-2">
            {user ? (
              <Link href="/dashboard">
                <Button size="sm" className="rounded-xl font-semibold bg-primary hover:bg-primary/90 gap-1.5">Dashboard <ArrowRight className="w-3.5 h-3.5" /></Button>
              </Link>
            ) : (
              <>
                <Link href="/login"><Button variant="ghost" size="sm" className="rounded-xl font-medium text-white/50 hover:text-white hover:bg-white/6">Sign In</Button></Link>
                <Link href="/register"><button className="nav-cta-btn">Get Started Free</button></Link>
              </>
            )}
          </div>
        </div>
      </header>

      {/* ─── HERO — Immersive 3D environment ─── */}
      <section className="relative min-h-screen flex flex-col justify-center pt-[72px] overflow-hidden">
        {/* 3D canvas */}
        <SceneErrorBoundary fallback={<div className="absolute inset-0 hero-fallback-bg" />}>
          <HeroScene />
        </SceneErrorBoundary>

        {/* Cinematic gradients */}
        <div className="absolute inset-0 bg-gradient-to-b from-[#030810]/50 via-transparent to-[#030810] pointer-events-none z-[1]" />
        <div className="absolute inset-0 bg-gradient-to-r from-[#030810]/90 via-[#030810]/30 to-transparent pointer-events-none z-[1]" />
        <div className="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-[#030810] to-transparent pointer-events-none z-[1]" />

        {/* Hero content — asymmetric, dramatic */}
        <div className="relative z-10 w-full max-w-7xl mx-auto px-5 flex-1 flex items-center">
          <div className="grid lg:grid-cols-[1.15fr_0.85fr] gap-8 w-full items-center">
            {/* Left column: copy */}
            <div className="max-w-2xl" style={{ transform: `translate(${mousePos.x * -3}px, ${mousePos.y * -2}px)`, transition: "transform 0.3s ease-out" }}>
              <div className="hero-badge mb-7">
                <span className="hero-badge-dot" />
                <span>Live Infrastructure · 50+ Countries · 500+ Services</span>
              </div>

              <h1 className="text-[clamp(40px,6vw,76px)] font-black tracking-[-0.03em] leading-[1.02] mb-6">
                <span className="text-white">Receive OTPs.</span><br />
                <span className="hero-gradient-text">Rent Numbers.</span><br />
                <span className="text-white/70">Activate Instantly.</span>
              </h1>

              <p className="text-[17px] text-white/40 max-w-lg mb-10 leading-[1.7]">
                Instant virtual phone numbers for SMS verification on every platform.
                <span className="text-white/75 font-semibold"> No real number exposed. No data stored. Codes in under 5 seconds.</span>
              </p>

              <div className="flex flex-col sm:flex-row gap-3 mb-8">
                <Link href="/register"><button className="hero-cta-primary">Get Number Now <ArrowRight className="w-5 h-5" /></button></Link>
                <button className="hero-cta-secondary" onClick={() => scrollTo("platforms")}>View Services</button>
              </div>

              <div className="flex flex-wrap gap-2.5">
                {[
                  { icon: <Zap className="w-3.5 h-3.5 text-yellow-400" />, t: "< 5s delivery" },
                  { icon: <Shield className="w-3.5 h-3.5 text-cyan-400" />, t: "100% anonymous" },
                  { icon: <RefreshCw className="w-3.5 h-3.5 text-emerald-400" />, t: "Refund guarantee" },
                  { icon: <Wifi className="w-3.5 h-3.5 text-violet-400" />, t: "99.9% uptime" },
                ].map(p => <div key={p.t} className="trust-pill">{p.icon}<span>{p.t}</span></div>)}
              </div>
            </div>

            {/* Right column: phone + live feed */}
            <div className="hidden lg:block relative" style={{ transform: `translate(${mousePos.x * 5}px, ${mousePos.y * 3}px)`, transition: "transform 0.4s ease-out" }}>
              <div className="relative flex items-center justify-center">
                {/* Depth glow layers */}
                <div className="absolute inset-0 -m-12 bg-gradient-to-br from-cyan-500/8 via-transparent to-violet-500/5 rounded-[40px] blur-3xl" />

                <PhoneMockup />

                {/* Floating hero cards */}
                <div className="absolute top-4 -left-16 hero-float-card z-20" style={{ animationDelay: "0s" }}>
                  <span>💬</span>
                  <div>
                    <div className="text-[11px] font-bold text-white">WhatsApp OTP</div>
                    <div className="text-[10px] text-emerald-400">Delivered in 2s ✓</div>
                  </div>
                </div>
                <div className="absolute bottom-24 -left-20 hero-float-card z-20" style={{ animationDelay: "1s" }}>
                  <span>₿</span>
                  <div>
                    <div className="text-[11px] font-bold text-white">Binance</div>
                    <div className="text-[10px] text-cyan-400">Number active ⚡</div>
                  </div>
                </div>
                <div className="absolute top-20 -right-12 hero-float-card z-20" style={{ animationDelay: "2s" }}>
                  <span>✈️</span>
                  <div>
                    <div className="text-[11px] font-bold text-white">Telegram</div>
                    <div className="text-[10px] text-emerald-400">563 018 ✓</div>
                  </div>
                </div>
                <div className="absolute bottom-8 -right-16 hero-float-card z-20" style={{ animationDelay: "2.8s" }}>
                  <span>🎵</span>
                  <div>
                    <div className="text-[11px] font-bold text-white">TikTok</div>
                    <div className="text-[10px] text-violet-400">$0.33 →</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Scroll pulse */}
        <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-10">
          <div className="scroll-indicator" />
        </div>
      </section>

      {/* ─── LIVE ACTIVITY BAR ─── */}
      <LiveActivityBar />

      {/* ─── TWO SERVICES GLASS PANELS ─── */}
      <section className="relative z-10 py-6 px-5">
        <div className="max-w-5xl mx-auto grid md:grid-cols-2 gap-5">
          <RevealSection>
            <div className="glass-service-card gsc-otp">
              <div className="gsc-glow gsc-glow-cyan" />
              <div className="gsc-content">
                <div className="gsc-icon">🔑</div>
                <div className="gsc-body">
                  <h3>Receive OTP</h3>
                  <p>Get a temporary number, trigger the SMS, receive the code instantly. Perfect for one-time account verification.</p>
                  <div className="gsc-price">From <strong>$0.15</strong> / code</div>
                </div>
                <Link href="/register"><button className="gsc-btn">Start Now <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </div>
          </RevealSection>
          <RevealSection delay={100}>
            <div className="glass-service-card gsc-rent">
              <div className="gsc-glow gsc-glow-violet" />
              <div className="gsc-content">
                <div className="gsc-icon">📲</div>
                <div className="gsc-body">
                  <h3>Rent a Number</h3>
                  <p>Full control of a virtual number for longer periods. Receive multiple SMS, test integrations, hold for reuse.</p>
                  <div className="gsc-price">From <strong>$0.50</strong> / rental</div>
                </div>
                <Link href="/register"><button className="gsc-btn gsc-btn-violet">Rent Now <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </div>
          </RevealSection>
        </div>
      </section>

      {/* ─── HOW IT WORKS ─── */}
      <section id="how-it-works" className="py-28 relative">
        <div className="section-glow-bg" />
        <div className="max-w-6xl mx-auto px-5 relative z-2">
          <RevealSection>
            <div className="text-center mb-16">
              <p className="section-eyebrow">How It Works</p>
              <h2 className="section-title">Get Verified in Under 2 Minutes</h2>
              <p className="section-subtitle">Four simple steps. No technical knowledge needed.</p>
            </div>
          </RevealSection>

          <div className="grid md:grid-cols-4 gap-5 relative">
            <div className="hidden md:block absolute top-[52px] left-[12.5%] right-[12.5%] h-px">
              <div className="connector-line" />
            </div>
            {[
              { n: "01", icon: "🌍", title: "Choose Country", desc: "Select from 50+ countries with live number availability." },
              { n: "02", icon: "📱", title: "Pick a Service", desc: "Search 500+ platforms — WhatsApp, Telegram, Binance & more." },
              { n: "03", icon: "💳", title: "Get Your Number", desc: "Instant activation. Virtual number live within seconds." },
              { n: "04", icon: "✅", title: "Receive OTP", desc: "Code arrives in your dashboard in under 5 seconds." },
            ].map((step, i) => (
              <RevealSection key={step.n} delay={i * 80}>
                <div className="step-card group">
                  <div className="step-icon-wrap">
                    <span className="text-2xl">{step.icon}</span>
                    <div className="step-num">{step.n}</div>
                  </div>
                  <h3 className="text-[15px] font-bold text-white mt-5 mb-2">{step.title}</h3>
                  <p className="text-[13px] text-white/35 leading-relaxed">{step.desc}</p>
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── SUPPORTED PLATFORMS ─── */}
      <section id="platforms" className="py-28 relative border-t border-white/5">
        <div className="section-glow-bg" style={{ background: "radial-gradient(ellipse at 30% 50%, rgba(129,140,248,0.04), transparent 60%)" }} />
        <div className="max-w-6xl mx-auto px-5 relative z-2">
          <RevealSection>
            <div className="text-center mb-16">
              <p className="section-eyebrow">500+ Services Supported</p>
              <h2 className="section-title">Every Platform. One Dashboard.</h2>
              <p className="section-subtitle">From messaging apps to crypto exchanges — if it sends SMS, we cover it.</p>
            </div>
          </RevealSection>

          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-8">
            {displayPlatforms.map((svc: any, i: number) => (
              <RevealSection key={i} delay={i * 30}>
                <Link href="/register">
                  <div className="platform-card group">
                    <div className="platform-icon">{svc.emoji || "📱"}</div>
                    <div className="platform-name">{svc.name}</div>
                    <div className="platform-price">${parseFloat(svc.price || "0.33").toFixed(2)}</div>
                    {svc.hot && <div className="platform-hot">🔥</div>}
                  </div>
                </Link>
              </RevealSection>
            ))}
          </div>
          <div className="text-center"><Link href="/register"><button className="outline-btn">Browse All 500+ Services <ArrowRight className="w-4 h-4" /></button></Link></div>
        </div>
      </section>

      {/* ─── COUNTRY AVAILABILITY ─── */}
      <section className="py-28 relative">
        <div className="max-w-6xl mx-auto px-5">
          <RevealSection>
            <div className="text-center mb-16">
              <p className="section-eyebrow">Global Coverage</p>
              <h2 className="section-title">Numbers Available Worldwide</h2>
              <p className="section-subtitle">Real-time inventory from 50+ countries. US numbers always in stock.</p>
            </div>
          </RevealSection>
          <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            {COUNTRIES.map((c, i) => (
              <RevealSection key={c.name} delay={i * 60}>
                <div className="country-card">
                  <div className="country-flag">{c.flag}</div>
                  <div className="country-info"><div className="country-name">{c.name}</div><div className="country-code">{c.code}</div></div>
                  <div className="country-stock"><div className="stock-num">{c.stock}</div><div className="stock-label">in stock</div></div>
                  <div className="country-live" />
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── DASHBOARD PREVIEW ─── */}
      <section className="py-28 relative border-y border-white/5 overflow-hidden">
        <div className="section-glow-bg" style={{ background: "radial-gradient(ellipse at 70% 50%, rgba(14,165,233,0.05), transparent 60%)" }} />
        <div className="max-w-6xl mx-auto px-5 relative z-2">
          <div className="grid lg:grid-cols-2 gap-14 items-center">
            <RevealSection>
              <div>
                <p className="section-eyebrow">Live Dashboard</p>
                <h2 className="section-title text-left">Your Control Center</h2>
                <p className="text-[15px] text-white/35 leading-relaxed mb-8 max-w-md">
                  Track every OTP, manage active rentals, and monitor your balance — all in one premium dashboard.
                </p>
                <ul className="space-y-3.5 mb-8">
                  {["Real-time OTP inbox with live updates", "Active rental timer with countdown", "Full order history & refund tracking", "Instant Stripe balance top-up", "REST API key & webhook config"].map(t => (
                    <li key={t} className="flex items-center gap-3 text-white/55 text-[13.5px]">
                      <CheckCircle className="w-4 h-4 text-cyan-400 shrink-0" />{t}
                    </li>
                  ))}
                </ul>
                <Link href="/register"><button className="hero-cta-primary">Open Dashboard <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </RevealSection>
            <RevealSection delay={150}>
              <div className="relative">
                <div className="absolute inset-0 -m-8 bg-gradient-to-br from-cyan-500/8 via-violet-500/4 to-transparent rounded-3xl blur-3xl" />
                <DashboardPreview />
              </div>
            </RevealSection>
          </div>
        </div>
      </section>

      {/* ─── PRICING ─── */}
      <section id="pricing" className="py-28 relative">
        <div className="max-w-6xl mx-auto px-5">
          <RevealSection>
            <div className="text-center mb-16">
              <p className="section-eyebrow">Transparent Pricing</p>
              <h2 className="section-title">Pay Only for What You Use</h2>
              <p className="section-subtitle">No subscriptions. No hidden fees. Prepaid wallet, spend as you go.</p>
            </div>
          </RevealSection>
          <div className="grid md:grid-cols-3 gap-5">
            {PRICING.map((plan, i) => (
              <RevealSection key={plan.name} delay={i * 80}>
                <div className={`pricing-card ${plan.popular ? "pricing-card-featured" : ""}`}>
                  {plan.popular && <div className="pricing-popular-badge">Most Popular</div>}
                  <div className="pricing-icon">{plan.icon}</div>
                  <h3 className="text-lg font-bold text-white mb-1">{plan.name}</h3>
                  <p className="text-[13px] text-white/35 mb-5">{plan.tagline}</p>
                  <div className="mb-1"><span className="text-[28px] font-black text-white">{plan.price}</span><span className="text-[12px] text-white/30 ml-1.5">/ {plan.period}</span></div>
                  <ul className="space-y-2.5 my-6">{plan.features.map(f => <li key={f} className="flex items-center gap-2.5 text-[13px] text-white/50"><CheckCircle className="w-3.5 h-3.5 text-cyan-400 shrink-0" />{f}</li>)}</ul>
                  <Link href="/register"><button className={`w-full pricing-btn ${plan.popular ? "pricing-btn-featured" : ""}`}>{plan.name === "API / Bulk" ? "Contact Us" : plan.name === "Rent a Number" ? "Rent Now" : "Get OTP"} <ArrowRight className="w-4 h-4" /></button></Link>
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── TRUST ─── */}
      <section className="py-28 relative border-y border-white/5">
        <div className="max-w-6xl mx-auto px-5">
          <RevealSection>
            <div className="text-center mb-16">
              <p className="section-eyebrow">Built for Trust</p>
              <h2 className="section-title">Security & Reliability First</h2>
            </div>
          </RevealSection>
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {TRUST.map((t, i) => (
              <RevealSection key={t.title} delay={i * 60}>
                <div className="trust-card group">
                  <div className={`trust-card-icon ${t.bg}`}>{t.icon}</div>
                  <h3 className="font-bold text-white mb-2 text-[15px]">{t.title}</h3>
                  <p className="text-[13px] text-white/35 leading-relaxed">{t.desc}</p>
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── TESTIMONIALS ─── */}
      <section className="py-28 relative">
        <div className="max-w-5xl mx-auto px-5">
          <RevealSection><div className="text-center mb-16"><p className="section-eyebrow">Testimonials</p><h2 className="section-title">Trusted Worldwide</h2></div></RevealSection>
          <div className="grid md:grid-cols-3 gap-5">
            {[
              { text: "Got my WhatsApp OTP in literally 2 seconds. Unbelievably fast. This is the real deal.", name: "Alex K.", role: "Developer" },
              { text: "Clean UI, instant codes, fair pricing. My absolute go-to for all account verifications.", name: "Sam T.", role: "Product Manager" },
              { text: "API integration took 10 minutes. Works flawlessly in my automation scripts at scale.", name: "Jordan M.", role: "Backend Engineer" },
            ].map((r, i) => (
              <RevealSection key={r.name} delay={i * 80}>
                <div className="review-card">
                  <div className="flex gap-0.5 mb-3">{[1,2,3,4,5].map(s => <Star key={s} className="w-3.5 h-3.5 fill-yellow-400 text-yellow-400" />)}</div>
                  <p className="text-[13px] text-white/45 leading-relaxed mb-5 flex-1">"{r.text}"</p>
                  <div className="flex items-center gap-3"><div className="review-avatar">{r.name[0]}</div><div><p className="text-[13px] font-semibold text-white">{r.name}</p><p className="text-[11px] text-white/30">{r.role}</p></div></div>
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── FAQ ─── */}
      <section id="faq" className="py-28 border-t border-white/5">
        <div className="max-w-3xl mx-auto px-5">
          <RevealSection><div className="text-center mb-14"><p className="section-eyebrow">FAQ</p><h2 className="section-title">Common Questions</h2></div></RevealSection>
          <div className="space-y-3">
            {FAQS.map((faq, i) => (
              <RevealSection key={i} delay={i * 40}>
                <div className={`faq-item ${openFaq === i ? "faq-item-open" : ""}`}>
                  <button className="flex items-center justify-between w-full p-5 text-left gap-4" onClick={() => setOpenFaq(openFaq === i ? null : i)}>
                    <span className="font-semibold text-[14px] text-white/80">{faq.q}</span>
                    <div className={`faq-chevron ${openFaq === i ? "faq-chevron-open" : ""}`}><ChevronDown className="w-3.5 h-3.5" /></div>
                  </button>
                  {openFaq === i && <div className="px-5 pb-5 text-[13px] text-white/40 leading-relaxed">{faq.a}</div>}
                </div>
              </RevealSection>
            ))}
          </div>
        </div>
      </section>

      {/* ─── FINAL CTA ─── */}
      <section className="py-8 px-5 pb-24">
        <RevealSection>
          <div className="max-w-5xl mx-auto">
            <div className="cta-block">
              <div className="cta-glow-top" />
              <div className="cta-glow-bottom" />
              <div className="absolute inset-0 hero-grid opacity-15 rounded-3xl" />
              <div className="relative">
                <div className="hero-badge mb-6"><Sparkles className="w-3.5 h-3.5" /><span>Free to start — No credit card required</span></div>
                <h2 className="text-3xl md:text-5xl font-black text-white mb-5 leading-tight">
                  Your Private Number.<br /><span className="hero-gradient-text">Ready in Seconds.</span>
                </h2>
                <p className="text-white/40 text-lg mb-10 max-w-lg mx-auto">Join thousands verifying accounts without exposing their real number.</p>
                <div className="flex flex-col sm:flex-row gap-3 justify-center">
                  <Link href="/register"><button className="hero-cta-primary">Create Free Account <ArrowRight className="w-5 h-5" /></button></Link>
                  <button className="hero-cta-secondary" onClick={() => scrollTo("platforms")}>Browse Services</button>
                </div>
                <p className="mt-6 text-white/15 text-sm">No credit card · Pay per use · Cancel anytime</p>
              </div>
            </div>
          </div>
        </RevealSection>
      </section>

      {/* ─── FOOTER ─── */}
      <footer className="border-t border-white/5 bg-[#020710] py-12">
        <div className="max-w-6xl mx-auto px-5">
          <div className="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-10">
            <div><Logo size={28} /><p className="text-[11px] text-white/25 mt-3 leading-relaxed max-w-[180px]">Instant virtual numbers for OTP verification & number rental worldwide.</p></div>
            <div><p className="text-[11px] font-semibold text-white/40 uppercase tracking-widest mb-4">Product</p><div className="space-y-2.5">{[["Receive OTP", "platforms"], ["Rent Number", "pricing"], ["API", "pricing"]].map(([l, id]) => <button key={l} onClick={() => scrollTo(id)} className="block text-[13px] text-white/30 hover:text-white/60 transition-colors">{l}</button>)}</div></div>
            <div><p className="text-[11px] font-semibold text-white/40 uppercase tracking-widest mb-4">Resources</p><div className="space-y-2.5">{[["How It Works", "how-it-works"], ["Pricing", "pricing"], ["FAQ", "faq"]].map(([l, id]) => <button key={l} onClick={() => scrollTo(id)} className="block text-[13px] text-white/30 hover:text-white/60 transition-colors">{l}</button>)}</div></div>
            <div><p className="text-[11px] font-semibold text-white/40 uppercase tracking-widest mb-4">Legal</p><div className="space-y-2.5">{["Privacy Policy", "Terms of Service", "Refund Policy"].map(l => <a key={l} href="#" className="block text-[13px] text-white/30 hover:text-white/60 transition-colors">{l}</a>)}</div></div>
          </div>
          <div className="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-white/15">
            <p>&copy; {new Date().getFullYear()} GetOTPs. All rights reserved.</p>
            <p>Built for speed, privacy, and scale.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
