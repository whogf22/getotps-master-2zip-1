import { useState, useEffect, useCallback } from "react";

const SERVICES = [
  { name: "WhatsApp", emoji: "💬", color: "#25d366" },
  { name: "Telegram", emoji: "✈️", color: "#0088cc" },
  { name: "Google", emoji: "🔍", color: "#4285f4" },
  { name: "TikTok", emoji: "🎵", color: "#ff0050" },
  { name: "Binance", emoji: "₿", color: "#f3ba2f" },
  { name: "Discord", emoji: "🎮", color: "#5865f2" },
  { name: "Instagram", emoji: "📸", color: "#e1306c" },
  { name: "Facebook", emoji: "👤", color: "#1877f2" },
  { name: "Uber", emoji: "🚗", color: "#276ef1" },
  { name: "PayPal", emoji: "💳", color: "#003087" },
  { name: "Snapchat", emoji: "👻", color: "#fffc00" },
  { name: "Coinbase", emoji: "🪙", color: "#0052ff" },
];

const COUNTRIES = ["🇺🇸", "🇬🇧", "🇨🇦", "🇩🇪", "🇫🇷", "🇦🇺"];

function randomCode() {
  return `${Math.floor(100 + Math.random() * 900)} ${Math.floor(100 + Math.random() * 900)}`;
}

interface OTPEvent {
  id: number;
  service: typeof SERVICES[0];
  code: string;
  country: string;
  ts: number;
  exiting: boolean;
}

export function LiveOTPTicker() {
  const [events, setEvents] = useState<OTPEvent[]>([]);

  const addEvent = useCallback(() => {
    const svc = SERVICES[Math.floor(Math.random() * SERVICES.length)];
    const country = COUNTRIES[Math.floor(Math.random() * COUNTRIES.length)];
    setEvents((prev) => {
      const ne: OTPEvent = { id: Date.now() + Math.random(), service: svc, code: randomCode(), country, ts: Date.now(), exiting: false };
      return [ne, ...prev].slice(0, 6);
    });
  }, []);

  useEffect(() => {
    addEvent();
    const t1 = setTimeout(addEvent, 800);
    const t2 = setTimeout(addEvent, 1600);
    const interval = setInterval(addEvent, 2800);
    return () => { clearTimeout(t1); clearTimeout(t2); clearInterval(interval); };
  }, [addEvent]);

  useEffect(() => {
    const cleanup = setInterval(() => {
      setEvents((prev) => {
        const now = Date.now();
        return prev.map(e => now - e.ts > 10000 ? { ...e, exiting: true } : e).filter(e => now - e.ts < 12000);
      });
    }, 2000);
    return () => clearInterval(cleanup);
  }, []);

  return (
    <div className="otp-ticker">
      <div className="otp-ticker-header">
        <div className="otp-ticker-pulse" />
        <span>Live Verifications</span>
      </div>
      <div className="otp-ticker-list">
        {events.map((evt) => (
          <div key={evt.id} className={`otp-tick ${evt.exiting ? "otp-tick-exit" : "otp-tick-enter"}`}>
            <span className="otp-tick-emoji">{evt.service.emoji}</span>
            <span className="otp-tick-name" style={{ color: evt.service.color }}>{evt.service.name}</span>
            <span className="otp-tick-code">{evt.code}</span>
            <span className="otp-tick-flag">{evt.country}</span>
            <span className="otp-tick-badge">delivered</span>
          </div>
        ))}
      </div>
    </div>
  );
}

export function NetworkStats() {
  const [count, setCount] = useState(14291);
  const [rate, setRate] = useState(99.97);

  useEffect(() => {
    const t = setInterval(() => {
      setCount(c => c + Math.floor(1 + Math.random() * 4));
    }, 2200);
    return () => clearInterval(t);
  }, []);

  return (
    <div className="net-stats">
      <div className="net-stat">
        <div className="net-stat-live" />
        <span className="net-stat-label">Network</span>
        <span className="net-stat-value net-stat-green">Online</span>
      </div>
      <div className="net-stat-sep" />
      <div className="net-stat">
        <span className="net-stat-label">Delivered today</span>
        <span className="net-stat-value">{count.toLocaleString()}</span>
      </div>
      <div className="net-stat-sep" />
      <div className="net-stat">
        <span className="net-stat-label">Success</span>
        <span className="net-stat-value net-stat-green">{rate}%</span>
      </div>
      <div className="net-stat-sep" />
      <div className="net-stat">
        <span className="net-stat-label">Avg speed</span>
        <span className="net-stat-value">2.1s</span>
      </div>
    </div>
  );
}
