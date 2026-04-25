import { Link } from "wouter";
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
 const ref = useRef(null);
 const \[v, setV\] = useState(false);
 useEffect(() => {
 const el = ref.current;
 if (!el) return;
 const o = new IntersectionObserver((\[e\]) => { if (e.isIntersecting) setV(true); }, { threshold: 0.1 });
 o.observe(el);
 return () => o.disconnect();
 }, \[\]);
 return { ref, v };
}

function Reveal({ children, className = "", delay = 0 }: { children: React.ReactNode; className?: string; delay?: number }) {
 const { ref, v } = useReveal();
 return (


{children}


 );
}

function GlowCard({ children, className = "", glowColor = "cyan" }: { children: React.ReactNode; className?: string; glowColor?: string }) {
 const ref = useRef(null);
 const \[pos, setPos\] = useState({ x: 50, y: 50 });

 const handleMove = useCallback((e: React.MouseEvent) => {
 if (!ref.current) return;
 const r = ref.current.getBoundingClientRect();
 setPos({ x: ((e.clientX - r.left) / r.width) \* 100, y: ((e.clientY - r.top) / r.height) \* 100 });
 }, \[\]);

 const gradColor = glowColor === "violet" ? "rgba(139,92,246,0.12)" : glowColor === "green" ? "rgba(34,197,94,0.1)" : "rgba(14,165,233,0.12)";

 return (


{children}


);
}

const PLATFORMS = \[\
{ name: "WhatsApp", emoji: "💬", color: "#25d366", hot: true },\
{ name: "Telegram", emoji: "✈️", color: "#0088cc", hot: true },\
{ name: "Google", emoji: "🔍", color: "#4285f4" },\
{ name: "TikTok", emoji: "🎵", color: "#ff0050", hot: true },\
{ name: "Facebook", emoji: "👤", color: "#1877f2" },\
{ name: "Instagram", emoji: "📸", color: "#e1306c" },\
{ name: "Discord", emoji: "🎮", color: "#5865f2" },\
{ name: "Binance", emoji: "₿", color: "#f3ba2f", hot: true },\
{ name: "Twitter/X", emoji: "𝕏", color: "#ffffff" },\
{ name: "Amazon", emoji: "🛒", color: "#ff9900" },\
{ name: "Uber", emoji: "🚗", color: "#276ef1" },\
{ name: "PayPal", emoji: "💳", color: "#003087" },\
{ name: "Snapchat", emoji: "👻", color: "#fffc00" },\
{ name: "LinkedIn", emoji: "💼", color: "#0a66c2" },\
{ name: "Coinbase", emoji: "🪙", color: "#0052ff" },\
{ name: "Microsoft", emoji: "🪟", color: "#737373" },\
\];

const COUNTRIES = \[\
{ flag: "🇺🇸", name: "United States", code: "+1", },\
{ flag: "🇬🇧", name: "United Kingdom", code: "+44", },\
{ flag: "🇨🇦", name: "Canada", code: "+1", },\
{ flag: "🇦🇺", name: "Australia", code: "+61", },\
{ flag: "🇩🇪", name: "Germany", code: "+49", },\
{ flag: "🇫🇷", name: "France", code: "+33", },\
\];

const FAQS = \[\
{ q: "What is GetOTPs?", a: "GetOTPs is a virtual phone number platform that lets you rent temporary numbers to receive SMS verification codes for 400+ apps without using your real number." },\
{ q: "What's the difference between Receive OTP and Rent Number?", a: "Receive OTP gives you a 20-minute window for a single verification code. Rent Number gives you longer-term control to receive multiple SMS from any app." },\
{ q: "How fast are codes delivered?", a: "Codes typically appear in your dashboard within 2-5 seconds of the service sending them. Our infrastructure is optimized for real-time delivery." },\
{ q: "What if no SMS arrives?", a: "Your balance is automatically and instantly refunded if no SMS is received within the rental window. Zero risk." },\
{ q: "Can I use the API for automation?", a: "Yes. Every account gets a free API key. Automate number ordering, check SMS status, and build workflows using our REST API." },\
{ q: "Which services are supported?", a: "We support 400+ services including WhatsApp, Telegram, Google, TikTok, Binance, Discord, Facebook, Instagram, and many more." },\
\];

function DashboardPanel() {
const \[tab, setTab\] = useState(0);
const \[tick, setTick\] = useState(0);
useEffect(() => { const t = setInterval(() => setTick(x => x + 1), 3000); return () => clearInterval(t); }, \[\]);
const tabs = \["OTP Inbox", "Rentals", "History"\];

return (


getotps.online/dashboard

GetOTPsPro

WalletActive

{tabs.map((t, i) => (
setTab(i)}>
{t}{i === 0 && }

))}


{tab === 0 && (
<>
{\[\
{ svc: "WhatsApp", code: "847 291", time: "2s ago", st: "received", cl: "#25d366" },\
{ svc: "Telegram", code: "563 018", time: "1m ago", st: "received", cl: "#0088cc" },\
{ svc: "Google", code: "391 752", time: "3m ago", st: "received", cl: "#4285f4" },\
\].map((r, i) => (


{r.svc}{r.code}{r.time}{r.st}

))}
{tick % 2 === 0 && (


Binance● Waiting…nowpending

)}

)}
{tab === 1 && (
<>
{\[\
{ num: "+1 (555) 832-4910", svc: "WhatsApp", exp: "18:42", on: true },\
{ num: "+1 (555) 217-0381", svc: "Binance", exp: "04:12", on: true },\
{ num: "+44 7911 123456", svc: "Telegram", exp: "Expired", on: false },\
\].map((r, i) => (


{r.num}{r.svc}{r.on ? \`⏱ ${r.exp}\` : r.exp}{r.on ? "active" : "expired"}

))}

)}
{tab === 2 && (
<>
{\[\
{ svc: "WhatsApp", type: "OTP", amt: "Completed", time: "14:22" },\
{ svc: "Telegram", type: "Rental", amt: "Completed", time: "13:08" },\
{ svc: "TikTok", type: "Refund", amt: "Refunded", time: "12:05" },\
\].map((r, i) => (


{r.svc}{r.type}{r.time}{r.amt}

))}

)}


);
}

export default function Landing() {
const { user } = useAuth();
const \[openFaq, setOpenFaq\] = useState(null);
const \[scrolled, setScrolled\] = useState(false);
const \[mx, setMx\] = useState(0);
const \[my, setMy\] = useState(0);

useEffect(() => {
const fn = () => setScrolled(window.scrollY > 30);
window.addEventListener("scroll", fn, { passive: true });
return () => window.removeEventListener("scroll", fn);
}, \[\]);

useEffect(() => {
const fn = (e: MouseEvent) => {
setMx((e.clientX / window.innerWidth - 0.5) \* 2);
setMy((e.clientY / window.innerHeight - 0.5) \* 2);
};
window.addEventListener("mousemove", fn, { passive: true });
return () => window.removeEventListener("mousemove", fn);
}, \[\]);

const go = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
const { data: services } = useQuery({ queryKey: \["/api/public/services"\] });
const serviceCount = services?.length ? `${Math.floor(services.length / 50) * 50}+` : "400+";
  const countryCount = "200+";
  const plats = services?.length ? services.slice(0, 16) : PLATFORMS;

return (


{/\\* ════════ NAV ════════ \*/}


{/\\* ════════ HERO ════════ \*/}


Live Network · {countryCount} Countries · {serviceCount} Services


# Receive OTPs.Rent Numbers.Activate Instantly.

Virtual phone numbers for SMS verification.
**No real number exposed. Codes in under 5 seconds.**

Get Number Now  go("platforms")}>View Services

{\[\
{ icon: , t: "< 5s delivery", c: "text-yellow-400" },\
{ icon: , t: "100% anonymous", c: "text-cyan-400" },\
{ icon: , t: "Full refund", c: "text-emerald-400" },\
{ icon: , t: "99.9% uptime", c: "text-violet-400" },\
\].map(p =>

{p.icon}{p.t}

)}


{/\\* ════════ TWO SERVICES ════════ \*/}


### Receive OTP

Get a temporary number, trigger the SMS, receive the code. 20-minute window. Perfect for one-time verification.

Start Now

### Rent a Number

Full control of a virtual number for longer periods. Receive multiple SMS, hold for reuse, test integrations.

Rent Now

{/\\* ════════ HOW IT WORKS ════════ \*/}


How It Works

## Get Verified in Under 2 Minutes

Four simple steps. No technical knowledge needed.

{\[\
{ n: "01", emoji: "🌍", title: "Choose Country", desc: "Select from 200+ countries with live number availability." },\
{ n: "02", emoji: "📱", title: "Pick a Service", desc: `Search ${serviceCount} platforms — WhatsApp, Telegram, Binance & more." },\
{ n: "03", emoji: "💳", title: "Get Your Number", desc: "Instant activation. Virtual number live within seconds." },\
{ n: "04", emoji: "✅", title: "Receive OTP", desc: "Code arrives in your dashboard in under 5 seconds." },\
\].map((s, i) => (


{s.emoji}

{s.n}

### {s.title}

{s.desc}

))}


{/\\* ════════ PLATFORMS ════════ \*/}


{serviceCount} Services

## Every Platform. One Dashboard.

Messaging, crypto, social, delivery — if it sends SMS, we cover it.

{plats.map((svc: any, i: number) => (


{svc.emoji \|\| "📱"}

{svc.name}

{svc.hot &&

🔥

}

))}


Browse All {serviceCount} Services

{/\\* ════════ COUNTRIES ════════ \*/}


Global Coverage

## Numbers Available Worldwide

Real-time inventory from {countryCount} countries.

{COUNTRIES.map((c, i) => (


{c.flag}

{c.name}

{c.code}

{c.stock}

in stock



))}


{/\\* ════════ DASHBOARD PREVIEW ════════ \*/}


Live Dashboard

## Your Control Center

Track every OTP, manage rentals, and monitor your balance in one premium interface.

{\["Real-time OTP inbox with live updates", "Active rental timer with countdown", "Full order history & refund tracking", "Instant balance top-up", "REST API key & webhook config"\].map(f => (
 - {f}

))}


Open Dashboard

{/\\* ════════ TRUST ════════ \*/}


Built for Trust

## Security & Reliability First

{\[\
{ icon: , bg: "bg-yellow-400/10", title: "< 5 Second Delivery", desc: "OTP codes reach your dashboard nearly instantly." },\
{ icon: , bg: "bg-cyan-400/10", title: "100% Anonymous", desc: "Your real number is never exposed or stored." },\
{ icon: , bg: "bg-emerald-400/10", title: "Full Refund Guarantee", desc: "No SMS? Instant automatic balance refund." },\
{ icon: , bg: "bg-violet-400/10", title: `${countryCount} Countries`, desc: "Global pool of virtual numbers worldwide." },\
{ icon: , bg: "bg-orange-400/10", title: "REST API Access", desc: "Every account gets a free API key." },\
{ icon: , bg: "bg-pink-400/10", title: "Secure Wallet", desc: "Prepaid balance. No subscriptions, no surprises." },\
\].map((t, i) => (


{t.icon}

### {t.title}

{t.desc}

))}


{/\\* ════════ TESTIMONIALS ════════ \*/}


Testimonials

## Trusted Worldwide

{\[\
{ text: "Got my WhatsApp OTP in literally 2 seconds. Unbelievably fast.", name: "Alex K.", role: "Developer" },\
{ text: "Clean UI, instant codes, seamless experience. My go-to for all verifications.", name: "Sam T.", role: "Product Manager" },\
{ text: "API integration took 10 minutes. Works flawlessly at scale.", name: "Jordan M.", role: "Backend Engineer" },\
\].map((r, i) => (


{\[1,2,3,4,5\].map(s => )}

"{r.text}"

{r.name\[0\]}

{r.name}

{r.role}

))}


{/\\* ════════ FAQ ════════ \*/}


FAQ

## Common Questions

{FAQS.map((faq, i) => (


setOpenFaq(openFaq === i ? null : i)}>
{faq.q}

{openFaq === i &&

{faq.a}

}


))}


{/\\* ════════ FINAL CTA ════════ \*/}


No credit card required

## Your Private Number.  Ready in Seconds.

Join thousands verifying accounts without exposing their real number.

Create Free Account  go("platforms")}>Browse Services

No credit card · Pay per use · Cancel anytime

{/* ════════ FOOTER ════════ */}
<footer className="border-t border-white/10 mt-16 py-10 text-center text-sm text-slate-500">
  <div className="flex flex-wrap justify-center gap-6 mb-4">
    <a href="/privacy" className="hover:text-white transition-colors">Privacy Policy</a>
    <a href="/terms" className="hover:text-white transition-colors">Terms of Service</a>
    <a href="/#/api-docs" className="hover:text-white transition-colors">API Docs</a>
    <a href="mailto:support@getotps.online" className="hover:text-white transition-colors">Support</a>
  </div>
  <p>© {new Date().getFullYear()} GetOTPs · Operated by CARDXC LLC</p>
</footer>



);
}