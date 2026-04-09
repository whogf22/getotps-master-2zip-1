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
  timestamp: number;
  exiting: boolean;
}

export function LiveOTPFeed() {
  const [events, setEvents] = useState<OTPEvent[]>([]);
  const [counter, setCounter] = useState(0);

  const addEvent = useCallback(() => {
    const svc = SERVICES[Math.floor(Math.random() * SERVICES.length)];
    const country = COUNTRIES[Math.floor(Math.random() * COUNTRIES.length)];
    setCounter((c) => c + 1);
    setEvents((prev) => {
      const ne: OTPEvent = {
        id: Date.now(),
        service: svc,
        code: randomCode(),
        country,
        timestamp: Date.now(),
        exiting: false,
      };
      const updated = [ne, ...prev].slice(0, 5);
      return updated;
    });
  }, []);

  useEffect(() => {
    addEvent();
    const interval = setInterval(addEvent, 3200);
    return () => clearInterval(interval);
  }, [addEvent]);

  useEffect(() => {
    const cleanup = setInterval(() => {
      setEvents((prev) => {
        const now = Date.now();
        return prev
          .map((e) => (now - e.timestamp > 12000 ? { ...e, exiting: true } : e))
          .filter((e) => now - e.timestamp < 14000);
      });
    }, 2000);
    return () => clearInterval(cleanup);
  }, []);

  return (
    <div className="live-feed">
      <div className="live-feed-header">
        <div className="live-feed-dot" />
        <span>Live OTP Activity</span>
      </div>
      <div className="live-feed-list">
        {events.map((evt) => (
          <div key={evt.id} className={`live-feed-item ${evt.exiting ? "live-feed-exit" : "live-feed-enter"}`}>
            <div className="lfi-emoji">{evt.service.emoji}</div>
            <div className="lfi-body">
              <div className="lfi-service" style={{ color: evt.service.color }}>
                {evt.service.name}
              </div>
              <div className="lfi-code">{evt.code}</div>
            </div>
            <div className="lfi-meta">
              <span className="lfi-country">{evt.country}</span>
              <span className="lfi-status">delivered</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export function LiveActivityBar() {
  const [count, setCount] = useState(12847);

  useEffect(() => {
    const t = setInterval(() => setCount((c) => c + Math.floor(Math.random() * 3)), 2500);
    return () => clearInterval(t);
  }, []);

  return (
    <div className="activity-bar">
      <div className="ab-item">
        <div className="ab-pulse" />
        <span>System Online</span>
      </div>
      <div className="ab-divider" />
      <div className="ab-item">
        <span className="ab-stat">{count.toLocaleString()}</span>
        <span>OTPs delivered today</span>
      </div>
      <div className="ab-divider" />
      <div className="ab-item">
        <span className="ab-stat ab-stat-green">99.97%</span>
        <span>Success rate</span>
      </div>
      <div className="ab-divider" />
      <div className="ab-item">
        <span className="ab-stat">2.1s</span>
        <span>Avg delivery</span>
      </div>
    </div>
  );
}
