import { Link } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { useAuth } from "@/contexts/AuthContext";
import {
  motion, useInView, useScroll, useTransform,
  AnimatePresence, useMotionValue, useSpring, animate
} from "framer-motion";
import {
  Zap, Shield, Globe, ArrowRight, Lock, ChevronDown,
  CheckCircle, Key, RefreshCw, Wifi, Sparkles, Copy, Check,
  MessageSquare, Phone, Star, TrendingUp, Users, Clock
} from "lucide-react";
import { useState, useEffect, useRef, useCallback } from "react";

// ─── Helpers ────────────────────────────────────────────────────────────────

function useCountUp(end: number, duration = 2) {
  const [count, setCount] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);
  const inView = useInView(ref, { once: true });
  useEffect(() => {
    if (!inView) return;
    const controls = animate(0, end, {
      duration,
      onUpdate: v => setCount(Math.floor(v)),
      ease: "easeOut",
    });
    return controls.stop;
  }, [inView, end, duration]);
  return { count, ref };
}

const prefersReducedMotion =
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const fadeUp = prefersReducedMotion
  ? {}
  : { hidden: { opacity: 0, y: 24 }, show: { opacity: 1, y: 0 } };

const staggerContainer = {
  hidden: {},
  show: { transition: { staggerChildren: 0.07 } },
};

// ─── Data ────────────────────────────────────────────────────────────────────

const SERVICES = [
  { name: "WhatsApp",   icon: "https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg",         color: "#25D366", hot: true  },
  { name: "Telegram",   icon: "https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg",    color: "#2AABEE", hot: true  },
  { name: "Google",     icon: "https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg", color: "#4285F4"            },
  { name: "TikTok",     icon: "https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg",           color: "#EE1D52", hot: true  },
  { name: "Facebook",   icon: "https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg", color: "#1877F2" },
  { name: "Instagram",  icon: "https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png",   color: "#E1306C"            },
  { name: "Discord",    icon: "https://assets-global.website-files.com/6257adef93867e50d84d30e2/636e0a6a49cf127bf92de1e2_icon_clyde_blurple_RGB.png", color: "#5865F2" },
  { name: "Binance",    icon: "https://upload.wikimedia.org/wikipedia/commons/1/12/Binance_logo.svg",     color: "#F3BA2F", hot: true  },
  { name: "Twitter/X",  icon: "https://upload.wikimedia.org/wikipedia/commons/5/57/X_logo_2023_%28white%29.png", color: "#fff"      },
  { name: "Amazon",     icon: "https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg",      color: "#FF9900"            },
  { name: "Uber",       icon: "https://upload.wikimedia.org/wikipedia/commons/c/cc/Uber_logo_2018.png",   color: "#000"               },
  { name: "PayPal",     icon: "https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg",           color: "#003087"            },
  { name: "Snapchat",   icon: "https://upload.wikimedia.org/wikipedia/en/c/c4/Snapchat_logo.svg",         color: "#FFFC00"            },
  { name: "LinkedIn",   icon: "https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png", color: "#0A66C2"    },
  { name: "Coinbase",   icon: "https://upload.wikimedia.org/wikipedia/commons/1/1a/24px-Coinbase_Wordmark_Blue.svg", color: "#0052FF" },
  { name: "Microsoft",  icon: "https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg", color: "#737373" },
];

const STATS = [
  { end: 409,  suffix: "+", label: "Services" },
  { end: 206,  suffix: "+", label: "Countries" },
  { end: 5,    suffix: "s", label: "Avg delivery" },
  { end: 99.9, suffix: "%", label: "Uptime", decimals: 1 },
];

const STEPS = [
  { n: "01", icon: Globe,        title: "Choose Country", desc: "Select from 200+ countries with live real-time inventory." },
  { n: "02", icon: Sparkles,     title: "Pick a Service", desc: "Search 400+ platforms — WhatsApp, Telegram, Binance & more." },
  { n: "03", icon: Phone,        title: "Get Your Number", desc: "Instant virtual number activation. Live within seconds." },
  { n: "04", icon: MessageSquare,title: "Receive OTP",    desc: "Code appears in your dashboard in under 5 seconds." },
];

const FEATURES = [
  { icon: Zap,       title: "< 5 Second Delivery",    desc: "OTP codes reach your dashboard nearly instantly.",         color: "#00D4C4" },
  { icon: Shield,    title: "100% Anonymous",          desc: "Your real number is never exposed or stored anywhere.",    color: "#8B5CF6" },
  { icon: RefreshCw, title: "Full Refund Guarantee",   desc: "No SMS? Your balance is automatically refunded.",          color: "#22C55E" },
  { icon: Globe,     title: "200+ Countries",          desc: "Global pool of virtual numbers across all regions.",       color: "#3B82F6" },
  { icon: Key,       title: "REST API Access",          desc: "Every account ships with a free API key, zero friction.", color: "#F59E0B" },
  { icon: Lock,      title: "Secure Wallet",            desc: "Prepaid balance. No subscriptions, no surprises.",        color: "#EF4444" },
];

const FAQS = [
  { q: "What is GetOTPs?",               a: "GetOTPs is a virtual phone number platform that lets you rent temporary numbers to receive SMS verification codes for 400+ apps without exposing your real number." },
  { q: "How fast are codes delivered?",  a: "OTP codes appear in your dashboard within 2–5 seconds of being sent by the service." },
  { q: "What if no SMS arrives?",        a: "Your balance is automatically and instantly refunded if no SMS arrives within the 20-minute rental window. Zero risk." },
  { q: "Can I use the API?",             a: "Yes — every account gets a free API key. Integrate GetOTPs into your automation workflows with full REST API access." },
  { q: "Which services are supported?",  a: "We support 400+ services: social (WhatsApp, Telegram, Instagram), crypto (Binance, Coinbase), tech (Google, Microsoft), and hundreds more." },
  { q: "What countries are available?",  a: "We offer numbers from 200+ countries with live inventory. US, UK, India, Russia, Brazil, and hundreds more are available." },
];

const TESTIMONIALS = [
  { text: "Got my WhatsApp OTP in literally 2 seconds. Unbelievably fast.", name: "Alex K.",    role: "Developer",        rating: 5 },
  { text: "Clean UI, instant codes, seamless experience. My go-to for all verifications.",        name: "Sam T.",     role: "Product Manager", rating: 5 },
  { text: "API integration took 10 minutes. Works flawlessly at scale.",                          name: "Jordan M.", role: "Backend Engineer", rating: 5 },
];

// ─── Nav ─────────────────────────────────────────────────────────────────────

function Navbar() {
  const { user } = useAuth();
  const [scrolled, setScrolled] = useState(false);
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <motion.nav
      initial={{ y: -20, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.5 }}
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled
          ? "bg-[#060D1A]/90 backdrop-blur-xl border-b border-white/5 shadow-2xl"
          : "bg-transparent"
      }`}
    >
      <div className="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">
        <a href="/#/" className="flex items-center gap-2.5 group">
          <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center shadow-lg shadow-cyan-500/30">
            <Phone className="w-4 h-4 text-white" />
          </div>
          <span className="font-bold text-white text-lg tracking-tight">GetOTPs</span>
        </a>

        <div className="hidden md:flex items-center gap-8 text-sm text-slate-400">
          <a href="#services" className="hover:text-white transition-colors">Services</a>
          <a href="#how-it-works" className="hover:text-white transition-colors">How It Works</a>
          <a href="#faq" className="hover:text-white transition-colors">FAQ</a>
          <a href="/privacy" className="hover:text-white transition-colors">Privacy</a>
        </div>

        <div className="flex items-center gap-3">
          {user ? (
            <a href="/#/dashboard">
              <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
                className="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-shadow">
                Dashboard →
              </motion.button>
            </a>
          ) : (
            <>
              <a href="/#/login" className="text-sm text-slate-400 hover:text-white transition-colors px-3 py-2">Sign in</a>
              <a href="/#/register">
                <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
                  className="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-shadow">
                  Get Started
                </motion.button>
              </a>
            </>
          )}
        </div>
      </div>
    </motion.nav>
  );
}

// ─── Hero ────────────────────────────────────────────────────────────────────

const WORDS = ["WhatsApp", "Telegram", "Binance", "Google", "Discord", "Instagram"];

function TypewriterWord() {
  const [idx, setIdx] = useState(0);
  const [display, setDisplay] = useState("");
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    if (prefersReducedMotion) { setDisplay(WORDS[0]); return; }
    const word = WORDS[idx];
    let timeout: ReturnType<typeof setTimeout>;
    if (!deleting && display.length < word.length) {
      timeout = setTimeout(() => setDisplay(word.slice(0, display.length + 1)), 80);
    } else if (!deleting && display.length === word.length) {
      timeout = setTimeout(() => setDeleting(true), 1600);
    } else if (deleting && display.length > 0) {
      timeout = setTimeout(() => setDisplay(display.slice(0, -1)), 45);
    } else {
      setDeleting(false);
      setIdx(i => (i + 1) % WORDS.length);
    }
    return () => clearTimeout(timeout);
  }, [display, deleting, idx]);

  return (
    <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#00D4C4] to-[#0099CC]">
      {display}
      <span className="animate-pulse text-[#00D4C4]">|</span>
    </span>
  );
}

function LiveOTPCard({ delay = 0, service, code, time }: { delay?: number; service: string; code: string; time: string }) {
  return (
    <motion.div
      initial={{ opacity: 0, x: 30, scale: 0.9 }}
      animate={{ opacity: 1, x: 0, scale: 1 }}
      transition={{ delay, duration: 0.5, type: "spring", stiffness: 200 }}
      className="flex items-center gap-3 bg-[#0A1628]/80 backdrop-blur-xl border border-white/10 rounded-2xl px-4 py-3 shadow-2xl w-64"
    >
      <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-[#00D4C4]/20 to-[#00D4C4]/5 border border-[#00D4C4]/30 flex items-center justify-center flex-shrink-0">
        <CheckCircle className="w-4 h-4 text-[#00D4C4]" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-slate-500 font-medium">{service} OTP</p>
        <p className="text-sm font-bold text-white tracking-widest">{code}</p>
      </div>
      <span className="text-[10px] text-slate-600 flex-shrink-0">{time}</span>
    </motion.div>
  );
}

function HeroSection() {
  const { scrollY } = useScroll();
  const y1 = useTransform(scrollY, [0, 500], [0, prefersReducedMotion ? 0 : -80]);
  const opacity = useTransform(scrollY, [0, 300], [1, 0]);

  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
      {/* Animated gradient mesh background */}
      <div className="absolute inset-0">
        <div className="absolute inset-0 bg-[#060D1A]" />
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[600px] bg-gradient-radial from-[#00D4C4]/12 via-transparent to-transparent blur-3xl" />
        <div className="absolute bottom-0 left-1/4 w-[500px] h-[400px] bg-gradient-radial from-[#6366F1]/8 via-transparent to-transparent blur-3xl" />
        <div className="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-gradient-radial from-[#0099CC]/8 via-transparent to-transparent blur-3xl" />
        {/* Grid lines */}
        <div
          className="absolute inset-0 opacity-[0.03]"
          style={{
            backgroundImage:
              "linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px)",
            backgroundSize: "60px 60px",
          }}
        />
        {/* Animated orbs */}
        <motion.div
          animate={{ scale: [1, 1.15, 1], opacity: [0.4, 0.7, 0.4] }}
          transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
          className="absolute top-1/4 left-[15%] w-64 h-64 rounded-full bg-[#00D4C4]/6 blur-3xl"
        />
        <motion.div
          animate={{ scale: [1.1, 1, 1.1], opacity: [0.3, 0.5, 0.3] }}
          transition={{ duration: 8, repeat: Infinity, ease: "easeInOut", delay: 2 }}
          className="absolute bottom-1/3 right-[12%] w-80 h-80 rounded-full bg-[#6366F1]/5 blur-3xl"
        />
      </div>

      <motion.div style={{ y: y1, opacity }} className="relative z-10 max-w-7xl mx-auto px-6 py-20">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left — copy */}
          <div className="text-left">
            {/* Badge */}
            <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }}
              className="inline-flex items-center gap-2 bg-[#00D4C4]/10 border border-[#00D4C4]/20 rounded-full px-4 py-1.5 mb-8">
              <span className="w-2 h-2 rounded-full bg-[#00D4C4] animate-pulse" />
              <span className="text-[#00D4C4] text-sm font-medium">Live · 400+ Services · 200+ Countries</span>
            </motion.div>

            {/* Headline */}
            <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
              className="text-5xl lg:text-6xl xl:text-7xl font-black text-white leading-[1.05] tracking-tight mb-4">
              Verify on
              <br />
              <TypewriterWord />
              <br />
              <span className="text-slate-400 font-semibold text-4xl lg:text-5xl xl:text-6xl">without your real number.</span>
            </motion.h1>

            <motion.p initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.35 }}
              className="text-slate-400 text-xl leading-relaxed mb-10 max-w-xl">
              Virtual phone numbers for SMS verification.
              <strong className="text-white"> No real number exposed.</strong> OTP codes in under 5 seconds.
            </motion.p>

            {/* CTAs */}
            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.45 }}
              className="flex flex-wrap gap-4 mb-12">
              <a href="/#/register">
                <motion.button
                  whileHover={{ scale: 1.03, boxShadow: "0 20px 40px rgba(0,212,196,0.35)" }}
                  whileTap={{ scale: 0.97 }}
                  className="relative group flex items-center gap-2.5 px-8 py-4 rounded-2xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold text-base shadow-lg shadow-cyan-500/30 overflow-hidden"
                >
                  {/* shimmer */}
                  <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/15 to-transparent" />
                  <Zap className="w-5 h-5" />
                  Get Number Now
                </motion.button>
              </a>
              <a href="#how-it-works">
                <motion.button
                  whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
                  className="flex items-center gap-2 px-8 py-4 rounded-2xl border border-white/10 bg-white/5 backdrop-blur text-white font-medium text-base hover:border-white/20 hover:bg-white/8 transition-all"
                >
                  See how it works
                  <ArrowRight className="w-4 h-4" />
                </motion.button>
              </a>
            </motion.div>

            {/* Trust row */}
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.6 }}
              className="flex flex-wrap gap-6 text-sm text-slate-500">
              {["No credit card", "Pay per use", "Instant refund", "Full API access"].map(t => (
                <span key={t} className="flex items-center gap-1.5">
                  <CheckCircle className="w-3.5 h-3.5 text-[#00D4C4]" />
                  {t}
                </span>
              ))}
            </motion.div>
          </div>

          {/* Right — phone mockup + live OTP cards */}
          <div className="relative flex justify-center items-center">
            <motion.div
              initial={{ opacity: 0, scale: 0.85, y: 30 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              transition={{ delay: 0.4, duration: 0.8, type: "spring", stiffness: 100 }}
              animate-repeat={{ y: [0, -12, 0] }}
              className="relative"
            >
              {/* Phone frame */}
              <motion.div
                animate={{ y: [0, -10, 0] }}
                transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
                className="relative w-64 h-[520px] bg-[#0A1628] rounded-[2.5rem] border border-white/10 shadow-2xl shadow-black/60 overflow-hidden"
              >
                {/* Screen gradient */}
                <div className="absolute inset-0 bg-gradient-to-br from-[#00D4C4]/5 to-transparent" />
                {/* Top notch */}
                <div className="absolute top-4 left-1/2 -translate-x-1/2 w-24 h-6 bg-[#060D1A] rounded-full" />
                {/* Faux UI */}
                <div className="pt-14 px-5 space-y-3">
                  <div className="flex items-center justify-between mb-5">
                    <div>
                      <p className="text-xs text-slate-500">GetOTPs Dashboard</p>
                      <p className="text-sm font-bold text-white">OTP Inbox</p>
                    </div>
                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center">
                      <span className="text-xs font-bold text-white">G</span>
                    </div>
                  </div>
                  {/* Balance pill */}
                  <div className="bg-gradient-to-r from-[#00D4C4]/15 to-[#0099CC]/10 border border-[#00D4C4]/20 rounded-2xl p-3 mb-4">
                    <p className="text-xs text-slate-500">Wallet Balance</p>
                    <p className="text-2xl font-black text-white">$24.50</p>
                  </div>
                  {/* OTP entries */}
                  {[
                    { svc: "WhatsApp", code: "847 291", status: "received", color: "#25D366" },
                    { svc: "Telegram", code: "563 018", status: "received", color: "#2AABEE" },
                    { svc: "Google",   code: "391 752", status: "received", color: "#4285F4" },
                    { svc: "Binance",  code: "••• •••", status: "waiting",  color: "#F3BA2F" },
                  ].map((item, i) => (
                    <motion.div key={i}
                      initial={{ opacity: 0, x: -10 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: 0.8 + i * 0.15 }}
                      className="flex items-center gap-2.5 bg-white/3 border border-white/5 rounded-xl px-3 py-2.5"
                    >
                      <div className="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style={{ background: item.color + "22" }}>
                        <span style={{ color: item.color }} className="text-xs font-bold">{item.svc[0]}</span>
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-[11px] text-slate-500">{item.svc}</p>
                        <p className="text-sm font-bold text-white tracking-widest">{item.code}</p>
                      </div>
                      <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${
                        item.status === "received"
                          ? "bg-green-500/15 text-green-400"
                          : "bg-yellow-500/15 text-yellow-400"
                      }`}>{item.status}</span>
                    </motion.div>
                  ))}
                </div>
              </motion.div>

              {/* Floating cards */}
              <div className="absolute -right-36 top-16 space-y-3">
                <LiveOTPCard delay={1.2} service="WhatsApp" code="847 291" time="2s ago" />
                <LiveOTPCard delay={1.5} service="Telegram" code="563 018" time="1m ago" />
              </div>
              <div className="absolute -left-44 bottom-24">
                <LiveOTPCard delay={1.8} service="Google" code="391 752" time="3m ago" />
              </div>

              {/* Glow ring */}
              <div className="absolute inset-0 rounded-[2.5rem] ring-1 ring-[#00D4C4]/15 pointer-events-none" />
              <div className="absolute -inset-4 rounded-[3rem] bg-[#00D4C4]/3 blur-2xl pointer-events-none" />
            </motion.div>
          </div>
        </div>
      </motion.div>

      {/* Scroll hint */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 1.5 }}
        className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-slate-600"
      >
        <motion.div animate={{ y: [0, 6, 0] }} transition={{ duration: 1.5, repeat: Infinity }}>
          <ChevronDown className="w-5 h-5" />
        </motion.div>
      </motion.div>
    </section>
  );
}

// ─── Stats ───────────────────────────────────────────────────────────────────

function StatItem({ end, suffix, label, decimals = 0 }: { end: number; suffix: string; label: string; decimals?: number }) {
  const { count, ref } = useCountUp(end, 2);
  return (
    <div className="text-center">
      <p className="text-4xl font-black text-white mb-1">
        <span ref={ref}>{decimals ? count.toFixed(decimals) : count}</span>
        <span className="text-[#00D4C4]">{suffix}</span>
      </p>
      <p className="text-sm text-slate-500 font-medium">{label}</p>
    </div>
  );
}

function StatsBar() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true });
  return (
    <section ref={ref} className="relative border-y border-white/5 bg-white/[0.02] backdrop-blur">
      <div className="max-w-4xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
        {STATS.map((s, i) => (
          <motion.div key={i}
            initial={{ opacity: 0, y: 16 }} animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ delay: i * 0.1, duration: 0.5 }}
          >
            <StatItem {...s} />
          </motion.div>
        ))}
      </div>
    </section>
  );
}

// ─── Services ────────────────────────────────────────────────────────────────

function ServiceCard({ name, icon, color, hot }: typeof SERVICES[0]) {
  const [imgErr, setImgErr] = useState(false);

  return (
    <motion.a href="/#/register"
      variants={fadeUp}
      whileHover={{ y: -4, scale: 1.02 }}
      whileTap={{ scale: 0.97 }}
      className="group relative flex flex-col items-center gap-2.5 p-4 rounded-2xl border border-white/6 bg-white/[0.03] hover:border-white/15 hover:bg-white/[0.06] cursor-pointer transition-all duration-300 text-center overflow-hidden"
      style={{ "--hover-glow": color + "20" } as any}
    >
      {hot && (
        <span className="absolute top-2 right-2 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/20">
          HOT
        </span>
      )}
      {/* Glow on hover */}
      <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"
        style={{ background: `radial-gradient(circle at 50% 30%, ${color}18, transparent 70%)` }} />
      <div className="relative w-10 h-10 flex items-center justify-center flex-shrink-0">
        {!imgErr ? (
          <img src={icon} alt={name} className="w-9 h-9 object-contain drop-shadow-md"
            onError={() => setImgErr(true)} />
        ) : (
          <div className="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm"
            style={{ background: color + "33" }}>
            {name[0]}
          </div>
        )}
        {/* Micro-bounce on hover */}
        <motion.div className="absolute inset-0" whileHover={{ scale: [1, 1.2, 1] }} transition={{ duration: 0.3 }} />
      </div>
      <p className="text-xs font-semibold text-slate-300 group-hover:text-white transition-colors leading-tight">{name}</p>
    </motion.a>
  );
}

function ServicesSection() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-100px" });
  const { data: apiServices } = useQuery<any[]>({ queryKey: ["/api/public/services"] });
  const displayServices = SERVICES; // Use curated list with real icons for landing

  return (
    <section id="services" ref={ref} className="py-28 px-6">
      <div className="max-w-7xl mx-auto">
        <motion.div className="text-center mb-16"
          initial={{ opacity: 0, y: 24 }} animate={inView ? { opacity: 1, y: 0 } : {}} transition={{ duration: 0.6 }}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">Every platform</span>
          <h2 className="text-4xl lg:text-5xl font-black text-white mb-5">
            One dashboard.<br />
            <span className="text-slate-400 font-semibold text-3xl lg:text-4xl">
              {apiServices?.length ? `${Math.floor(apiServices.length / 50) * 50}+` : "400+"} services covered.
            </span>
          </h2>
          <p className="text-slate-500 text-lg max-w-xl mx-auto">
            Messaging, crypto, social, delivery — if it sends SMS, we cover it.
          </p>
        </motion.div>

        <motion.div
          variants={staggerContainer}
          initial="hidden"
          animate={inView ? "show" : "hidden"}
          className="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3"
        >
          {displayServices.map((s, i) => (
            <ServiceCard key={i} {...s} />
          ))}
        </motion.div>

        <motion.div
          initial={{ opacity: 0 }} animate={inView ? { opacity: 1 } : {}} transition={{ delay: 0.8 }}
          className="text-center mt-10"
        >
          <a href="/#/register"
            className="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#00D4C4] transition-colors font-medium group">
            Browse all {apiServices?.length ?? "400+"} services
            <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </a>
        </motion.div>
      </div>
    </section>
  );
}

// ─── How it Works ────────────────────────────────────────────────────────────

function HowItWorks() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-80px" });

  return (
    <section id="how-it-works" ref={ref}
      className="py-28 px-6 bg-gradient-to-b from-transparent via-[#00D4C4]/3 to-transparent">
      <div className="max-w-5xl mx-auto">
        <motion.div className="text-center mb-20"
          initial={{ opacity: 0, y: 24 }} animate={inView ? { opacity: 1, y: 0 } : {}}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">Simple process</span>
          <h2 className="text-4xl lg:text-5xl font-black text-white mb-4">Get verified in <span className="text-[#00D4C4]">under 2 minutes</span></h2>
          <p className="text-slate-400 text-lg">Four steps. No technical knowledge needed.</p>
        </motion.div>

        <div className="grid md:grid-cols-4 gap-6 relative">
          {/* Connector line */}
          <div className="hidden md:block absolute top-10 left-[12.5%] right-[12.5%] h-px">
            <motion.div
              initial={{ scaleX: 0 }}
              animate={inView ? { scaleX: 1 } : {}}
              transition={{ delay: 0.4, duration: 1, ease: "easeOut" }}
              className="h-full bg-gradient-to-r from-[#00D4C4]/30 via-[#00D4C4]/50 to-[#00D4C4]/30 origin-left"
            />
          </div>

          {STEPS.map((step, i) => (
            <motion.div key={i}
              initial={{ opacity: 0, y: 30 }}
              animate={inView ? { opacity: 1, y: 0 } : {}}
              transition={{ delay: 0.2 + i * 0.15, duration: 0.5 }}
              className="relative flex flex-col items-center text-center"
            >
              <motion.div
                whileHover={{ scale: 1.08, y: -2 }}
                className="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#00D4C4]/15 to-[#0099CC]/10 border border-[#00D4C4]/25 flex items-center justify-center mb-5 shadow-lg shadow-cyan-500/10 relative z-10"
              >
                <step.icon className="w-8 h-8 text-[#00D4C4]" />
              </motion.div>
              <span className="text-xs font-black text-[#00D4C4]/60 mb-2 tracking-widest">{step.n}</span>
              <h3 className="font-bold text-white text-base mb-2">{step.title}</h3>
              <p className="text-slate-500 text-sm leading-relaxed">{step.desc}</p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}

// ─── Features ────────────────────────────────────────────────────────────────

function FeatureCard({ icon: Icon, title, desc, color }: typeof FEATURES[0]) {
  return (
    <motion.div
      variants={fadeUp}
      whileHover={{ y: -3 }}
      className="group relative p-6 rounded-2xl border border-white/6 bg-white/[0.02] hover:border-white/12 hover:bg-white/[0.04] transition-all duration-300 overflow-hidden"
    >
      <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity"
        style={{ background: `radial-gradient(circle at 20% 20%, ${color}10, transparent 60%)` }} />
      <div className="relative w-12 h-12 rounded-xl mb-4 flex items-center justify-center"
        style={{ background: color + "18", border: `1px solid ${color}30` }}>
        <Icon className="w-6 h-6" style={{ color }} />
      </div>
      <h3 className="font-bold text-white text-base mb-2">{title}</h3>
      <p className="text-slate-500 text-sm leading-relaxed">{desc}</p>
    </motion.div>
  );
}

function FeaturesSection() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-80px" });

  return (
    <section ref={ref} className="py-28 px-6">
      <div className="max-w-7xl mx-auto">
        <motion.div className="text-center mb-16"
          initial={{ opacity: 0, y: 24 }} animate={inView ? { opacity: 1, y: 0 } : {}}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">Built for trust</span>
          <h2 className="text-4xl lg:text-5xl font-black text-white mb-4">Security &amp; reliability first</h2>
          <p className="text-slate-400 text-lg max-w-lg mx-auto">Every feature designed to keep you anonymous and your verifications instant.</p>
        </motion.div>
        <motion.div variants={staggerContainer} initial="hidden" animate={inView ? "show" : "hidden"}
          className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
          {FEATURES.map((f, i) => <FeatureCard key={i} {...f} />)}
        </motion.div>
      </div>
    </section>
  );
}

// ─── Dashboard preview ───────────────────────────────────────────────────────

function DashboardPreview() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-100px" });

  return (
    <section ref={ref} className="py-28 px-6">
      <div className="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
        <motion.div
          initial={{ opacity: 0, x: -30 }} animate={inView ? { opacity: 1, x: 0 } : {}} transition={{ duration: 0.6 }}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">Live dashboard</span>
          <h2 className="text-4xl lg:text-5xl font-black text-white mb-6">Your control center</h2>
          <p className="text-slate-400 text-lg mb-8 leading-relaxed">
            Track every OTP, manage rentals, and monitor your balance in one premium interface. Real-time updates, zero delays.
          </p>
          <ul className="space-y-4 mb-10">
            {[
              "Real-time OTP inbox with live updates",
              "Active rental timer with countdown",
              "Full order history & refund tracking",
              "REST API key & webhook config",
            ].map((item, i) => (
              <motion.li key={i} initial={{ opacity: 0, x: -10 }} animate={inView ? { opacity: 1, x: 0 } : {}}
                transition={{ delay: 0.2 + i * 0.1 }} className="flex items-center gap-3 text-slate-300">
                <CheckCircle className="w-5 h-5 text-[#00D4C4] flex-shrink-0" />
                {item}
              </motion.li>
            ))}
          </ul>
          <a href="/#/register">
            <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.97 }}
              className="flex items-center gap-2 px-7 py-3.5 rounded-2xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold shadow-lg shadow-cyan-500/25">
              Open Dashboard <ArrowRight className="w-4 h-4" />
            </motion.button>
          </a>
        </motion.div>

        {/* Dashboard mockup wide */}
        <motion.div
          initial={{ opacity: 0, x: 30 }} animate={inView ? { opacity: 1, x: 0 } : {}} transition={{ duration: 0.6, delay: 0.2 }}>
          <div className="relative rounded-2xl border border-white/8 bg-[#0A1628] overflow-hidden shadow-2xl shadow-black/60">
            {/* Header bar */}
            <div className="flex items-center gap-2 px-4 py-3 border-b border-white/5">
              <div className="w-2.5 h-2.5 rounded-full bg-red-500/70" />
              <div className="w-2.5 h-2.5 rounded-full bg-yellow-500/70" />
              <div className="w-2.5 h-2.5 rounded-full bg-green-500/70" />
              <span className="ml-3 text-xs text-slate-600 font-mono">getotps.online/dashboard</span>
            </div>
            <div className="p-5 space-y-3">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <p className="text-xs text-slate-600">GetOTPs Pro</p>
                  <p className="text-base font-bold text-white">OTP Inbox</p>
                </div>
                <div className="text-right">
                  <p className="text-xs text-slate-600">Balance</p>
                  <p className="text-lg font-black text-[#00D4C4]">$24.50</p>
                </div>
              </div>
              {[
                { svc: "WhatsApp", code: "847 291", t: "2s ago",  status: "received", c: "#25D366" },
                { svc: "Telegram", code: "563 018", t: "1m ago",  status: "received", c: "#2AABEE" },
                { svc: "Google",   code: "391 752", t: "3m ago",  status: "received", c: "#4285F4" },
                { svc: "Binance",  code: "••• •••", t: "now",     status: "waiting",  c: "#F3BA2F" },
              ].map((item, i) => (
                <motion.div key={i}
                  initial={{ opacity: 0, y: 8 }} animate={inView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.4 + i * 0.1 }}
                  className="flex items-center gap-3 bg-white/3 border border-white/5 rounded-xl px-4 py-3"
                >
                  <div className="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm"
                    style={{ background: item.c + "22", color: item.c }}>{item.svc[0]}</div>
                  <div className="flex-1">
                    <p className="text-xs text-slate-500">{item.svc}</p>
                    <p className="text-sm font-bold text-white tracking-widest">{item.code}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-[10px] text-slate-600">{item.t}</p>
                    <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${
                      item.status === "received" ? "bg-green-500/15 text-green-400" : "bg-yellow-500/15 text-yellow-400 animate-pulse"
                    }`}>{item.status}</span>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
          <div className="absolute -inset-4 rounded-3xl bg-[#00D4C4]/3 blur-3xl pointer-events-none" />
        </motion.div>
      </div>
    </section>
  );
}

// ─── Testimonials ────────────────────────────────────────────────────────────

function TestimonialsSection() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-80px" });

  return (
    <section ref={ref} className="py-28 px-6 bg-gradient-to-b from-transparent via-white/[0.015] to-transparent">
      <div className="max-w-5xl mx-auto">
        <motion.div className="text-center mb-16"
          initial={{ opacity: 0, y: 24 }} animate={inView ? { opacity: 1, y: 0 } : {}}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">Trusted worldwide</span>
          <h2 className="text-4xl font-black text-white">Loved by developers</h2>
        </motion.div>
        <motion.div variants={staggerContainer} initial="hidden" animate={inView ? "show" : "hidden"}
          className="grid md:grid-cols-3 gap-6">
          {TESTIMONIALS.map((t, i) => (
            <motion.div key={i} variants={fadeUp}
              whileHover={{ y: -4 }}
              className="p-6 rounded-2xl border border-white/6 bg-white/[0.025] hover:border-white/10 transition-all relative group overflow-hidden">
              <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-gradient-to-br from-[#00D4C4]/5 to-transparent" />
              <div className="flex mb-4">
                {Array(t.rating).fill(0).map((_, j) => (
                  <Star key={j} className="w-4 h-4 fill-[#00D4C4] text-[#00D4C4]" />
                ))}
              </div>
              <p className="text-slate-300 text-sm leading-relaxed mb-5">"{t.text}"</p>
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center text-white font-bold text-sm">
                  {t.name[0]}
                </div>
                <div>
                  <p className="text-sm font-semibold text-white">{t.name}</p>
                  <p className="text-xs text-slate-500">{t.role}</p>
                </div>
              </div>
            </motion.div>
          ))}
        </motion.div>
      </div>
    </section>
  );
}

// ─── FAQ ─────────────────────────────────────────────────────────────────────

function FAQSection() {
  const [open, setOpen] = useState<number | null>(0);
  const ref = useRef(null);
  const inView = useInView(ref, { once: true, margin: "-80px" });

  return (
    <section id="faq" ref={ref} className="py-28 px-6">
      <div className="max-w-3xl mx-auto">
        <motion.div className="text-center mb-16"
          initial={{ opacity: 0, y: 24 }} animate={inView ? { opacity: 1, y: 0 } : {}}>
          <span className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4 block">FAQ</span>
          <h2 className="text-4xl font-black text-white">Common questions</h2>
        </motion.div>
        <div className="space-y-3">
          {FAQS.map((faq, i) => (
            <motion.div key={i}
              initial={{ opacity: 0, y: 16 }} animate={inView ? { opacity: 1, y: 0 } : {}}
              transition={{ delay: i * 0.08 }}
              className="rounded-2xl border border-white/6 bg-white/[0.02] overflow-hidden"
            >
              <button
                onClick={() => setOpen(open === i ? null : i)}
                className="w-full flex items-center justify-between px-6 py-5 text-left hover:bg-white/[0.03] transition-colors"
              >
                <span className="font-semibold text-white text-base">{faq.q}</span>
                <motion.div animate={{ rotate: open === i ? 180 : 0 }} transition={{ duration: 0.25 }}>
                  <ChevronDown className="w-5 h-5 text-slate-500 flex-shrink-0" />
                </motion.div>
              </button>
              <AnimatePresence initial={false}>
                {open === i && (
                  <motion.div
                    key="content"
                    initial={{ height: 0, opacity: 0 }}
                    animate={{ height: "auto", opacity: 1 }}
                    exit={{ height: 0, opacity: 0 }}
                    transition={{ duration: 0.25, ease: "easeInOut" }}
                  >
                    <div className="px-6 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/5 pt-4">
                      {faq.a}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}

// ─── CTA Banner ──────────────────────────────────────────────────────────────

function CTABanner() {
  const ref = useRef(null);
  const inView = useInView(ref, { once: true });

  return (
    <section ref={ref} className="py-24 px-6">
      <motion.div
        initial={{ opacity: 0, y: 30 }} animate={inView ? { opacity: 1, y: 0 } : {}}
        className="max-w-4xl mx-auto relative"
      >
        <div className="relative rounded-3xl border border-[#00D4C4]/20 bg-gradient-to-br from-[#00D4C4]/8 via-[#0099CC]/5 to-transparent p-12 text-center overflow-hidden">
          {/* Background glow */}
          <div className="absolute inset-0 bg-gradient-to-br from-[#00D4C4]/5 to-[#6366F1]/3 rounded-3xl" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-48 bg-[#00D4C4]/10 blur-3xl" />

          <div className="relative">
            <p className="text-xs font-bold tracking-widest text-[#00D4C4] uppercase mb-4">No credit card required</p>
            <h2 className="text-4xl lg:text-5xl font-black text-white mb-4">
              Your private number.<br />
              <span className="text-[#00D4C4]">Ready in seconds.</span>
            </h2>
            <p className="text-slate-400 text-lg mb-10 max-w-xl mx-auto">
              Join thousands verifying accounts without exposing their real number.
            </p>
            <div className="flex flex-wrap gap-4 justify-center">
              <a href="/#/register">
                <motion.button
                  whileHover={{ scale: 1.04, boxShadow: "0 24px 50px rgba(0,212,196,0.4)" }}
                  whileTap={{ scale: 0.97 }}
                  className="relative group flex items-center gap-2.5 px-9 py-4 rounded-2xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold text-base shadow-lg shadow-cyan-500/30 overflow-hidden"
                >
                  <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/20 to-transparent" />
                  <Zap className="w-5 h-5" />
                  Create Free Account
                </motion.button>
              </a>
              <a href="#services">
                <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
                  className="flex items-center gap-2 px-8 py-4 rounded-2xl border border-white/10 bg-white/5 text-white font-medium hover:border-white/20 transition-all">
                  Browse Services
                </motion.button>
              </a>
            </div>
            <p className="text-slate-600 text-sm mt-6">No credit card · Pay per use · Cancel anytime</p>
          </div>
        </div>
      </motion.div>
    </section>
  );
}

// ─── Footer ──────────────────────────────────────────────────────────────────

function Footer() {
  return (
    <footer className="border-t border-white/5 px-6 py-12">
      <div className="max-w-7xl mx-auto">
        <div className="grid md:grid-cols-4 gap-10 mb-10">
          <div className="md:col-span-2">
            <div className="flex items-center gap-2.5 mb-4">
              <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center">
                <Phone className="w-4 h-4 text-white" />
              </div>
              <span className="font-bold text-white text-lg">GetOTPs</span>
            </div>
            <p className="text-slate-500 text-sm leading-relaxed max-w-xs mb-4">
              Virtual phone numbers for SMS verification. Keep your real number private.
            </p>
            <p className="text-xs text-slate-700">© {new Date().getFullYear()} GetOTPs · Operated by CARDXC LLC</p>
          </div>

          <div>
            <p className="text-white text-sm font-semibold mb-4">Product</p>
            <ul className="space-y-2.5">
              {[["How it works", "#how-it-works"], ["Services", "#services"], ["API Docs", "/#/api-docs"], ["Dashboard", "/#/dashboard"]].map(([label, href]) => (
                <li key={label}><a href={href} className="text-slate-500 hover:text-white text-sm transition-colors">{label}</a></li>
              ))}
            </ul>
          </div>

          <div>
            <p className="text-white text-sm font-semibold mb-4">Legal</p>
            <ul className="space-y-2.5">
              {[["Privacy Policy", "/privacy"], ["Terms of Service", "/terms"], ["Refund Policy", "/terms#refunds"], ["Contact", "mailto:support@getotps.online"]].map(([label, href]) => (
                <li key={label}><a href={href} className="text-slate-500 hover:text-white text-sm transition-colors">{label}</a></li>
              ))}
            </ul>
          </div>
        </div>

        <div className="border-t border-white/5 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
            <span className="text-xs text-slate-500">All systems operational</span>
          </div>
          <div className="flex items-center gap-6 text-xs text-slate-600">
            <a href="/privacy" className="hover:text-slate-400 transition-colors">Privacy</a>
            <a href="/terms" className="hover:text-slate-400 transition-colors">Terms</a>
            <a href="mailto:support@getotps.online" className="hover:text-slate-400 transition-colors">support@getotps.online</a>
          </div>
        </div>
      </div>
    </footer>
  );
}

// ─── Root ────────────────────────────────────────────────────────────────────

export default function Landing() {
  const { user } = useAuth();

  useEffect(() => {
    if (user) { window.location.hash = "/dashboard"; }
  }, [user]);

  return (
    <div className="min-h-screen bg-[#060D1A] text-white" style={{ fontFamily: "'Inter', 'General Sans', system-ui, sans-serif" }}>
      <Navbar />
      <HeroSection />
      <StatsBar />
      <ServicesSection />
      <HowItWorks />
      <FeaturesSection />
      <DashboardPreview />
      <TestimonialsSection />
      <FAQSection />
      <CTABanner />
      <Footer />
    </div>
  );
}
