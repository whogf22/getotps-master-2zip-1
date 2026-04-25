import { Link, useLocation } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import {
  Zap, Shield, Globe, ArrowRight, Star, Lock,
  ChevronDown, CheckCircle, Cpu, Key, RefreshCw,
  Wifi, Server, Sparkles, Eye
} from "lucide-react";
import { useState, useEffect, useRef, useMemo, useCallback } from "react";

function useReveal() {
  const ref = useRef<HTMLDivElement>(null);
  const [v, setV] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) setV(true); }, { threshold: 0.1 });
    o.observe(el);
    return () => o.disconnect();
  }, []);
  return { ref, v };
}

function Reveal({ children, className = "", delay = 0 }: { children: React.ReactNode; className?: string; delay?: number }) {
  const { ref, v } = useReveal();
  return (
    <div ref={ref} className={`reveal ${v ? "revealed" : ""} ${className}`} style={{ transitionDelay: `${delay}ms` }}>
      {children}
    </div>
  );
}

function GlowCard({ children, className = "", glowColor = "cyan" }: { children: React.ReactNode; className?: string; glowColor?: string }) {
  const ref = useRef<HTMLDivElement>(null);
  const [pos, setPos] = useState({ x: 50, y: 50 });

  const handleMove = useCallback((e: React.MouseEvent) => {
    if (!ref.current) return;
    const r = ref.current.getBoundingClientRect();
    setPos({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 });
  }, []);

  const gradColor = glowColor === "violet" ? "rgba(139,92,246,0.12)" : glowColor === "green" ? "rgba(34,197,94,0.1)" : "rgba(14,165,233,0.12)";

  return (
    <div
      ref={ref}
      className={`glow-card ${className}`}
      onMouseMove={handleMove}
      style={{ "--glow-x": `${pos.x}%`, "--glow-y": `${pos.y}%`, "--glow-color": gradColor } as React.CSSProperties}
    >
      <div className="glow-card-highlight" />
      {children}
    </div>
  );
}

const PLATFORMS = [
  { name: "WhatsApp", emoji: "💬", color: "#25d366", hot: true },
  { name: "Telegram", emoji: "✈️", color: "#0088cc", hot: true },
  { name: "Google", emoji: "🔍", color: "#4285f4" },
  { name: "TikTok", emoji: "🎵", color: "#ff0050", hot: true },
  { name: "Facebook", emoji: "👤", color: "#1877f2" },
  { name: "Instagram", emoji: "📸", color: "#e1306c" },
  { name: "Discord", emoji: "🎮", color: "#5865f2" },
  { name: "Binance", emoji: "₿", color: "#f3ba2f", hot: true },
  { name: "Twitter/X", emoji: "𝕏", color: "#ffffff" },
  { name: "Amazon", emoji: "🛒", color: "#ff9900" },
  { name: "Uber", emoji: "🚗", color: "#276ef1" },
  { name: "PayPal", emoji: "💳", color: "#003087" },
  { name: "Snapchat", emoji: "👻", color: "#fffc00" },
  { name: "LinkedIn", emoji: "💼", color: "#0a66c2" },
  { name: "Coinbase", emoji: "🪙", color: "#0052ff" },
  { name: "Microsoft", emoji: "🪟", color: "#737373" },
];

const COUNTRIES = [
  { flag: "🇺🇸", name: "United States", code: "+1", stock: 500 },
  { flag: "🇬🇧", name: "United Kingdom", code: "+44", stock: 120 },
  { flag: "🇨🇦", name: "Canada", code: "+1", stock: 95 },
  { flag: "🇦🇺", name: "Australia", code: "+61", stock: 80 },
  { flag: "🇩🇪", name: "Germany", code: "+49", stock: 75 },
  { flag: "🇫🇷", name: "France", code: "+33", stock: 60 },
];

const FAQS = [
  { q: "What is GetOTPs?", a: "GetOTPs is a virtual phone number platform that lets you rent temporary numbers to receive SMS verification codes for 500+ apps without using your real number." },
  { q: "What's the difference between Receive OTP and Rent Number?", a: "Receive OTP gives you a 20-minute window for a single verification code. Rent Number gives you longer-term control to receive multiple SMS from any app." },
  { q: "How fast are codes delivered?", a: "Codes typically appear in your dashboard within 2-5 seconds of the service sending them. Our infrastructure is optimized for real-time delivery." },
  { q: "What if no SMS arrives?", a: "Your balance is automatically and instantly refunded if no SMS is received within the rental window. Zero risk." },
  { q: "Can I use the API for automation?", a: "Yes. Every account gets a free API key. Automate number ordering, check SMS status, and build workflows using our REST API." },
  { q: "Which services are supported?", a: "We support 500+ services including WhatsApp, Telegram, Google, TikTok, Binance, Discord, Facebook, Instagram, and many more." },
];

function DashboardPanel() {
  const [tab, setTab] = useState(0);
  const [tick, setTick] = useState(0);
  useEffect(() => { const t = setInterval(() => setTick(x => x + 1), 3000); return () => clearInterval(t); }, []);
  const tabs = ["OTP Inbox", "Rentals", "History"];

  return (
    <div className="dash-panel">
      <div className="dash-chrome">
        <div className="dash-dots"><span /><span /><span /></div>
        <div className="dash-url">getotps.online/dashboard</div>
      </div>
      <div className="dash-header">
        <div className="dash-logo"><div className="dash-logo-dot" /><span>GetOTPs</span><span className="dash-pro">Pro</span></div>
        <div className="dash-bal"><span className="dash-bal-l">Wallet</span><span className="dash-bal-v">Active</span></div>
      </div>
      <div className="dash-tabs">
        {tabs.map((t, i) => (
          <button key={t} className={`dash-tab ${tab === i ? "dash-tab-on" : ""}`} onClick={() => setTab(i)}>
            {t}{i === 0 && <span className="dash-tab-live" />}
          </button>
        ))}
      </div>
      <div className="dash-body">
        {tab === 0 && (
          <>
            {[
              { svc: "WhatsApp", code: "847 291", time: "2s ago", st: "received", cl: "#25d366" },
              { svc: "Telegram", code: "563 018", time: "1m ago", st: "received", cl: "#0088cc" },
              { svc: "Google", code: "391 752", time: "3m ago", st: "received", cl: "#4285f4" },
            ].map((r, i) => (
              <div key={i} className="dash-row">
                <span className="dash-row-svc" style={{ color: r.cl }}>{r.svc}</span>
                <span className="dash-row-code">{r.code}</span>
                <span className="dash-row-time">{r.time}</span>
                <span className="dash-row-st dash-st-ok">{r.st}</span>
              </div>
            ))}
            {tick % 2 === 0 && (
              <div className="dash-row dash-row-live">
                <span className="dash-row-svc" style={{ color: "#f3ba2f" }}>Binance</span>
                <span className="dash-row-code"><span className="dash-blink">●</span> Waiting…</span>
                <span className="dash-row-time">now</span>
                <span className="dash-row-st dash-st-wait">pending</span>
              </div>
            )}
          </>
        )}
        {tab === 1 && (
          <>
            {[
              { num: "+1 (555) 832-4910", svc: "WhatsApp", exp: "18:42", on: true },
              { num: "+1 (555) 217-0381", svc: "Binance", exp: "04:12", on: true },
              { num: "+44 7911 123456", svc: "Telegram", exp: "Expired", on: false },
            ].map((r, i) => (
              <div key={i} className={`dash-row ${!r.on ? "dash-row-dim" : ""}`}>
                <span className="dash-row-num">{r.num}</span>
                <span className="dash-row-svc" style={{ color: "#22d3ee", fontSize: "10px" }}>{r.svc}</span>
                <span className={`dash-row-time ${r.on ? "text-emerald-400" : ""}`}>{r.on ? `⏱ ${r.exp}` : r.exp}</span>
                <span className={`dash-row-st ${r.on ? "dash-st-ok" : "dash-st-exp"}`}>{r.on ? "active" : "expired"}</span>
              </div>
            ))}
          </>
        )}
        {tab === 2 && (
          <>
            {[
              { svc: "WhatsApp", type: "OTP", amt: "Completed", time: "14:22" },
              { svc: "Telegram", type: "Rental", amt: "Completed", time: "13:08" },
              { svc: "TikTok", type: "Refund", amt: "Refunded", time: "12:05" },
            ].map((r, i) => (
              <div key={i} className="dash-row">
                <span className="dash-row-svc" style={{ color: "#22d3ee" }}>{r.svc}</span>
                <span className="dash-row-code" style={{ fontSize: "10px", opacity: 0.5 }}>{r.type}</span>
                <span className="dash-row-time">{r.time}</span>
                <span className={r.amt === "Refunded" ? "text-emerald-400 text-[11px] font-bold" : "text-cyan-400/60 text-[11px] font-bold"}>{r.amt}</span>
              </div>
            ))}
          </>
        )}
      </div>
    </div>
  );
}

export default function Landing() {
  const { user } = useAuth();
  const [, navigate] = useLocation();
  const { data: services } = useQuery<any[]>({ queryKey: ["/api/services"] });
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [scrolled, setScrolled] = useState(false);
  const [mx, setMx] = useState(0);
  const [my, setMy] = useState(0);

  useEffect(() => {
    const fn = () => setScrolled(window.scrollY > 30);
    window.addEventListener("scroll", fn, { passive: true });
    return () => window.removeEventListener("scroll", fn);
  }, []);

  useEffect(() => {
    const fn = (e: MouseEvent) => {
      setMx((e.clientX / window.innerWidth - 0.5) * 2);
      setMy((e.clientY / window.innerHeight - 0.5) * 2);
    };
    window.addEventListener("mousemove", fn, { passive: true });
    return () => window.removeEventListener("mousemove", fn);
  }, []);

  const go = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
  const plats = services?.length ? services.slice(0, 16) : PLATFORMS;
  const topPricing = useMemo(() => {
    if (!services || services.length === 0) return [];
    return [...services]
      .sort((a, b) => Number(a.price) - Number(b.price))
      .slice(0, 6)
      .map((service) => ({
        id: service.id,
        name: service.name,
        price: Number(service.price).toFixed(2),
        category: service.category || "General",
      }));
  }, [services]);

  return (
    <div className="landing">

      {/* ════════ NAV ════════ */}
      <header className={`l-nav ${scrolled ? "l-nav-solid" : ""}`}>
        <div className="l-nav-inner">
          <Logo size={30} />
          <nav className="l-nav-links">
            <Link href="/services"><a className="l-nav-link">Services</a></Link>
            {[["How It Works", "how-it-works"], ["FAQ", "faq"]].map(([label, id]) => (
              <button key={id} onClick={() => go(id)} className="l-nav-link">{label}</button>
            ))}
          </nav>
          <div className="l-nav-actions">
            {user ? (
              <Link href="/dashboard"><Button size="sm" className="l-cta-btn">Dashboard <ArrowRight className="w-3.5 h-3.5" /></Button></Link>
            ) : (
              <>
                <Link href="/login"><button className="l-nav-link">Sign In</button></Link>
                <Link href="/register"><button className="l-cta-btn">Get Started Free</button></Link>
              </>
            )}
          </div>
        </div>
      </header>

      {/* ════════ HERO ════════ */}
      <section className="l-hero">
        <div className="absolute inset-0 hero-fallback-bg" />

        <div className="l-hero-gradient-l" />
        <div className="l-hero-gradient-b" />
        <div className="l-hero-vignette" />

        <div className="l-hero-content">
          <div className="l-hero-left" style={{ transform: `translate(${mx * -4}px, ${my * -3}px)` }}>
            <div className="l-badge">
              <span className="l-badge-dot" />
              Live Network · 50+ Countries · 500+ Services
            </div>

            <h1 className="l-hero-h1">
              <span className="l-h1-line">Receive OTPs.</span>
              <span className="l-h1-gradient">Rent Numbers.</span>
              <span className="l-h1-dim">Activate Instantly.</span>
            </h1>

            <p className="l-hero-sub">
              Virtual phone numbers for SMS verification.
              <strong> No real number exposed. Codes in under 5 seconds.</strong>
            </p>

            <div className="l-hero-ctas">
              <Link href="/register"><button className="l-btn-primary">Get Number Now <ArrowRight className="w-5 h-5" /></button></Link>
                  <button className="l-btn-glass" onClick={() => navigate("/services")}>View Services</button>
            </div>

            <div className="l-hero-pills">
              {[
                { icon: <Zap className="w-3.5 h-3.5" />, t: "< 5s delivery", c: "text-yellow-400" },
                { icon: <Shield className="w-3.5 h-3.5" />, t: "100% anonymous", c: "text-cyan-400" },
                { icon: <RefreshCw className="w-3.5 h-3.5" />, t: "Full refund", c: "text-emerald-400" },
                { icon: <Wifi className="w-3.5 h-3.5" />, t: "99.9% uptime", c: "text-violet-400" },
              ].map(p => <div key={p.t} className="l-pill"><span className={p.c}>{p.icon}</span>{p.t}</div>)}
            </div>
          </div>

          <div className="l-hero-right" style={{ transform: `translate(${mx * 6}px, ${my * 4}px)` }}>
            <DashboardPanel />
          </div>
        </div>

        <div className="l-scroll-cue"><div className="l-scroll-dot" /></div>
      </section>

      {/* ════════ TWO SERVICES ════════ */}
      <section className="l-section">
        <div className="l-container grid md:grid-cols-2 gap-6">
          <Reveal>
            <GlowCard className="l-svc-card">
              <div className="l-svc-glow l-svc-glow-cyan" />
              <div className="l-svc-inner">
                <div className="l-svc-icon-wrap l-svc-icon-cyan"><Key className="w-6 h-6" /></div>
                <h3 className="l-svc-title">Receive OTP</h3>
                <p className="l-svc-desc">Get a temporary number, trigger the SMS, receive the code. 20-minute window. Perfect for one-time verification.</p>
                <Link href="/register"><button className="l-svc-btn l-svc-btn-cyan">Start Now <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </GlowCard>
          </Reveal>
          <Reveal delay={120}>
            <GlowCard className="l-svc-card" glowColor="violet">
              <div className="l-svc-glow l-svc-glow-violet" />
              <div className="l-svc-inner">
                <div className="l-svc-icon-wrap l-svc-icon-violet"><Server className="w-6 h-6" /></div>
                <h3 className="l-svc-title">Rent a Number</h3>
                <p className="l-svc-desc">Full control of a virtual number for longer periods. Receive multiple SMS, hold for reuse, test integrations.</p>
                <Link href="/register"><button className="l-svc-btn l-svc-btn-violet">Rent Now <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </GlowCard>
          </Reveal>
        </div>
      </section>

      {/* ════════ HOW IT WORKS ════════ */}
      <section id="how-it-works" className="l-section l-section-glow">
        <div className="l-container">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">How It Works</span><h2 className="l-h2">Get Verified in Under 2 Minutes</h2><p className="l-h2-sub">Four simple steps. No technical knowledge needed.</p></div></Reveal>
          <div className="grid md:grid-cols-4 gap-5 relative">
            <div className="l-step-line" />
            {[
              { n: "01", emoji: "🌍", title: "Choose Country", desc: "Select from 50+ countries with live number availability." },
              { n: "02", emoji: "📱", title: "Pick a Service", desc: "Search 500+ platforms — WhatsApp, Telegram, Binance & more." },
              { n: "03", emoji: "💳", title: "Get Your Number", desc: "Instant activation. Virtual number live within seconds." },
              { n: "04", emoji: "✅", title: "Receive OTP", desc: "Code arrives in your dashboard in under 5 seconds." },
            ].map((s, i) => (
              <Reveal key={s.n} delay={i * 100}>
                <GlowCard className="l-step">
                  <div className="l-step-icon"><span>{s.emoji}</span><div className="l-step-num">{s.n}</div></div>
                  <h3 className="l-step-title">{s.title}</h3>
                  <p className="l-step-desc">{s.desc}</p>
                </GlowCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ════════ PLATFORMS ════════ */}
      <section id="platforms" className="l-section">
        <div className="l-container">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">500+ Services</span><h2 className="l-h2">Every Platform. One Dashboard.</h2><p className="l-h2-sub">Messaging, crypto, social, delivery — if it sends SMS, we cover it.</p></div></Reveal>
          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-10">
            {plats.map((svc: any, i: number) => (
              <Reveal key={i} delay={i * 25}>
                <Link href="/register">
                  <GlowCard className="l-plat">
                    <div className="l-plat-icon">{svc.emoji || "📱"}</div>
                    <div className="l-plat-name">{svc.name}</div>
                    {svc.hot && <div className="l-plat-hot">🔥</div>}
                  </GlowCard>
                </Link>
              </Reveal>
            ))}
          </div>
          <Reveal><div className="text-center"><Link href="/services"><button className="l-btn-outline">Browse All 500+ Services <ArrowRight className="w-4 h-4" /></button></Link></div></Reveal>
        </div>
      </section>

      {/* ════════ PRICING ════════ */}
      <section className="l-section">
        <div className="l-container">
          <Reveal>
            <div className="l-section-head">
              <span className="l-eyebrow">Pricing</span>
              <h2 className="l-h2">Transparent Pay-As-You-Go Rates</h2>
              <p className="l-h2-sub">Live service rates from our current inventory. No subscription required.</p>
            </div>
          </Reveal>
          <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3 mb-8">
            {topPricing.map((service, index) => (
              <Reveal key={service.id} delay={index * 50}>
                <GlowCard className="p-5">
                  <p className="text-xs text-cyan-300/70 uppercase tracking-wide mb-1">{service.category}</p>
                  <h3 className="text-lg font-semibold text-white">{service.name}</h3>
                  <p className="text-sm text-white/35 mt-1">Starting from</p>
                  <p className="text-2xl font-bold text-cyan-300 mt-2">${service.price}</p>
                </GlowCard>
              </Reveal>
            ))}
          </div>
          <Reveal>
            <div className="text-center">
              <button className="l-btn-primary" onClick={() => navigate("/pricing")}>
                View full pricing table <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          </Reveal>
        </div>
      </section>

      {/* ════════ COUNTRIES ════════ */}
      <section className="l-section l-section-glow">
        <div className="l-container">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">Global Coverage</span><h2 className="l-h2">Numbers Available Worldwide</h2><p className="l-h2-sub">Real-time inventory from 50+ countries.</p></div></Reveal>
          <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            {COUNTRIES.map((c, i) => (
              <Reveal key={c.name} delay={i * 60}>
                <GlowCard className="l-country">
                  <div className="l-country-flag">{c.flag}</div>
                  <div className="l-country-info"><div className="l-country-name">{c.name}</div><div className="l-country-code">{c.code}</div></div>
                  <div className="l-country-stock"><div className="l-country-num">{c.stock}</div><div className="l-country-label">in stock</div></div>
                  <div className="l-country-live" />
                </GlowCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ════════ DASHBOARD PREVIEW ════════ */}
      <section className="l-section">
        <div className="l-container">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <Reveal>
              <div>
                <span className="l-eyebrow">Live Dashboard</span>
                <h2 className="l-h2 text-left">Your Control Center</h2>
                <p className="l-h2-sub text-left mx-0 mb-8">Track every OTP, manage rentals, and monitor your balance in one premium interface.</p>
                <ul className="l-feature-list">
                  {["Real-time OTP inbox with live updates", "Active rental timer with countdown", "Full order history & refund tracking", "Instant balance top-up", "REST API key & webhook config"].map(f => (
                    <li key={f}><CheckCircle className="w-4 h-4 text-cyan-400 shrink-0" />{f}</li>
                  ))}
                </ul>
                <Link href="/register"><button className="l-btn-primary mt-2">Open Dashboard <ArrowRight className="w-4 h-4" /></button></Link>
              </div>
            </Reveal>
            <Reveal delay={150}>
              <div className="l-dash-wrap">
                <div className="l-dash-glow" />
                <DashboardPanel />
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      {/* ════════ TRUST ════════ */}
      <section className="l-section">
        <div className="l-container">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">Built for Trust</span><h2 className="l-h2">Security & Reliability First</h2></div></Reveal>
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {[
              { icon: <Zap className="w-5 h-5 text-yellow-400" />, bg: "bg-yellow-400/10", title: "< 5 Second Delivery", desc: "OTP codes reach your dashboard nearly instantly." },
              { icon: <Shield className="w-5 h-5 text-cyan-400" />, bg: "bg-cyan-400/10", title: "100% Anonymous", desc: "Your real number is never exposed or stored." },
              { icon: <RefreshCw className="w-5 h-5 text-emerald-400" />, bg: "bg-emerald-400/10", title: "Full Refund Guarantee", desc: "No SMS? Instant automatic balance refund." },
              { icon: <Globe className="w-5 h-5 text-violet-400" />, bg: "bg-violet-400/10", title: "50+ Countries", desc: "Global pool of virtual numbers worldwide." },
              { icon: <Cpu className="w-5 h-5 text-orange-400" />, bg: "bg-orange-400/10", title: "REST API Access", desc: "Every account gets a free API key." },
              { icon: <Lock className="w-5 h-5 text-pink-400" />, bg: "bg-pink-400/10", title: "Secure Wallet", desc: "Prepaid balance. No subscriptions, no surprises." },
            ].map((t, i) => (
              <Reveal key={t.title} delay={i * 60}>
                <GlowCard className="l-trust">
                  <div className={`l-trust-icon ${t.bg}`}>{t.icon}</div>
                  <h3 className="l-trust-title">{t.title}</h3>
                  <p className="l-trust-desc">{t.desc}</p>
                </GlowCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ════════ TESTIMONIALS ════════ */}
      <section className="l-section l-section-glow">
        <div className="l-container max-w-5xl">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">Testimonials</span><h2 className="l-h2">Trusted Worldwide</h2></div></Reveal>
          <div className="grid md:grid-cols-3 gap-5">
            {[
              { text: "Got my WhatsApp OTP in literally 2 seconds. Unbelievably fast.", name: "Alex K.", role: "Developer" },
              { text: "Clean UI, instant codes, seamless experience. My go-to for all verifications.", name: "Sam T.", role: "Product Manager" },
              { text: "API integration took 10 minutes. Works flawlessly at scale.", name: "Jordan M.", role: "Backend Engineer" },
            ].map((r, i) => (
              <Reveal key={r.name} delay={i * 80}>
                <GlowCard className="l-review">
                  <div className="flex gap-0.5 mb-3">{[1,2,3,4,5].map(s => <Star key={s} className="w-3.5 h-3.5 fill-yellow-400 text-yellow-400" />)}</div>
                  <p className="l-review-text">"{r.text}"</p>
                  <div className="l-review-author"><div className="l-review-avatar">{r.name[0]}</div><div><p className="l-review-name">{r.name}</p><p className="l-review-role">{r.role}</p></div></div>
                </GlowCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ════════ FAQ ════════ */}
      <section id="faq" className="l-section">
        <div className="l-container max-w-3xl">
          <Reveal><div className="l-section-head"><span className="l-eyebrow">FAQ</span><h2 className="l-h2">Common Questions</h2></div></Reveal>
          <div className="space-y-3">
            {FAQS.map((faq, i) => (
              <Reveal key={i} delay={i * 40}>
                <div className={`l-faq ${openFaq === i ? "l-faq-open" : ""}`}>
                  <button className="l-faq-q" onClick={() => setOpenFaq(openFaq === i ? null : i)}>
                    <span>{faq.q}</span>
                    <div className={`l-faq-chevron ${openFaq === i ? "l-faq-chevron-open" : ""}`}><ChevronDown className="w-4 h-4" /></div>
                  </button>
                  {openFaq === i && <div className="l-faq-a">{faq.a}</div>}
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ════════ FINAL CTA ════════ */}
      <section className="l-section pb-24">
        <div className="l-container max-w-5xl">
          <Reveal>
            <div className="l-final-cta">
              <div className="l-final-glow-1" />
              <div className="l-final-glow-2" />
              <div className="l-final-grid" />
              <div className="l-final-inner">
                <div className="l-badge"><Sparkles className="w-3.5 h-3.5" /> No credit card required</div>
                <h2 className="l-final-h2">Your Private Number.<br /><span className="l-h1-gradient">Ready in Seconds.</span></h2>
                <p className="l-final-sub">Join thousands verifying accounts without exposing their real number.</p>
                <div className="l-hero-ctas justify-center">
                  <Link href="/register"><button className="l-btn-primary">Create Free Account <ArrowRight className="w-5 h-5" /></button></Link>
                  <button className="l-btn-glass" onClick={() => navigate("/services")}>Browse Services</button>
                </div>
                <p className="l-final-note">No credit card · Pay per use · Cancel anytime</p>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      {/* ════════ FOOTER ════════ */}
      <footer className="l-footer">
        <div className="l-container">
          <div className="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-10">
            <div><Logo size={26} /><p className="l-footer-desc">Virtual numbers for OTP verification & number rental worldwide.</p></div>
            <div>
              <p className="l-footer-heading">Product</p>
              <Link href="/services"><a className="l-footer-link">Receive OTP</a></Link>
              <Link href="/services"><a className="l-footer-link">Rent Number</a></Link>
              <button onClick={() => go("how-it-works")} className="l-footer-link">API</button>
            </div>
            <div><p className="l-footer-heading">Resources</p>{[["How It Works", "how-it-works"], ["FAQ", "faq"]].map(([l, id]) => <button key={l} onClick={() => go(id)} className="l-footer-link">{l}</button>)}</div>
            <div>
              <p className="l-footer-heading">Legal</p>
              <button onClick={() => navigate("/privacy")} className="l-footer-link">Privacy Policy</button>
              <button onClick={() => navigate("/terms")} className="l-footer-link">Terms of Service</button>
              <button onClick={() => navigate("/refund")} className="l-footer-link">Refund Policy</button>
            </div>
          </div>
          <div className="l-footer-bottom">
            <p>&copy; {new Date().getFullYear()} GetOTPs. All rights reserved.</p>
            <p>Built for speed, privacy, and scale.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
