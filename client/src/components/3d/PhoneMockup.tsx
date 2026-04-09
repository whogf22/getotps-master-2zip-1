import { useEffect, useState } from "react";

const OTP_SEQUENCES = [
  { code: "847 291", service: "WhatsApp", color: "#25d366", emoji: "💬" },
  { code: "563 018", service: "Telegram", color: "#0088cc", emoji: "✈️" },
  { code: "391 752", service: "Google", color: "#4285f4", emoji: "🔍" },
  { code: "204 687", service: "Binance", color: "#f3ba2f", emoji: "₿" },
  { code: "719 043", service: "TikTok", color: "#ff0050", emoji: "🎵" },
];

export function PhoneMockup() {
  const [activeOtp, setActiveOtp] = useState(0);
  const [visible, setVisible] = useState(true);
  const [time, setTime] = useState("9:41");

  useEffect(() => {
    const interval = setInterval(() => {
      setVisible(false);
      setTimeout(() => {
        setActiveOtp((p) => (p + 1) % OTP_SEQUENCES.length);
        setVisible(true);
      }, 350);
    }, 2800);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    const t = setInterval(() => {
      const now = new Date();
      setTime(`${now.getHours()}:${String(now.getMinutes()).padStart(2, "0")}`);
    }, 30000);
    return () => clearInterval(t);
  }, []);

  const current = OTP_SEQUENCES[activeOtp];
  const digits = current.code.replace(" ", "").split("");
  const part1 = digits.slice(0, 3);
  const part2 = digits.slice(3, 6);

  return (
    <div className="phone-3d-wrap">
      <div className="phone-glow" />
      <div className="phone-frame">
        {/* Dynamic island */}
        <div className="phone-notch" />

        {/* Status bar */}
        <div className="phone-status-bar">
          <span>{time}</span>
          <span className="flex gap-1 items-center">
            <svg width="14" height="9" viewBox="0 0 14 9" fill="currentColor">
              <rect x="0" y="4" width="2" height="5" rx="0.5" opacity="0.3"/>
              <rect x="3" y="3" width="2" height="6" rx="0.5" opacity="0.5"/>
              <rect x="6" y="1.5" width="2" height="7.5" rx="0.5" opacity="0.7"/>
              <rect x="9" y="0" width="2" height="9" rx="0.5"/>
            </svg>
            <svg width="16" height="8" viewBox="0 0 16 8" fill="currentColor">
              <rect x="0.5" y="1" width="12" height="6" rx="1.5" stroke="currentColor" strokeWidth="0.8" fill="none"/>
              <rect x="2" y="2.5" width="8" height="3" rx="0.5"/>
              <rect x="13" y="2.5" width="2" height="3" rx="0.5" opacity="0.4"/>
            </svg>
          </span>
        </div>

        <div className="phone-screen">
          {/* App header */}
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "8px" }}>
            <div style={{ fontSize: "13px", fontWeight: 800, color: "#fff", letterSpacing: "-0.02em" }}>GetOTPs</div>
            <div style={{ display: "flex", alignItems: "center", gap: "5px", fontSize: "10px", color: "rgba(255,255,255,0.4)" }}>
              <div style={{ width: "6px", height: "6px", borderRadius: "50%", background: "#22c55e", boxShadow: "0 0 6px #22c55e" }} />
              Connected
            </div>
          </div>

          {/* Number badge */}
          <div className="phone-number-badge">
            <div className="pnb-flag">🇺🇸</div>
            <div>
              <div className="pnb-label">Active Number</div>
              <div className="pnb-number">+1 (555) 832‑4910</div>
            </div>
            <div className="pnb-dot" />
          </div>

          {/* OTP panel */}
          <div className="otp-display" style={{ opacity: visible ? 1 : 0, transition: "opacity 0.35s" }}>
            <div style={{ display: "flex", alignItems: "center", gap: "6px", justifyContent: "center", marginBottom: "8px" }}>
              <span style={{ fontSize: "14px" }}>{current.emoji}</span>
              <div className="otp-service" style={{ color: current.color, margin: 0 }}>
                {current.service}
              </div>
            </div>
            <div className="otp-digits">
              {part1.map((d, i) => (
                <span key={i} className="otp-digit">{d}</span>
              ))}
              <span className="otp-sep" />
              {part2.map((d, i) => (
                <span key={`b${i}`} className="otp-digit">{d}</span>
              ))}
            </div>
            <div className="otp-expire">Tap to copy · Expires in 09:58</div>
          </div>

          {/* Recent SMS */}
          <div style={{ fontSize: "9px", fontWeight: 700, color: "rgba(255,255,255,0.25)", textTransform: "uppercase", letterSpacing: "0.1em", marginBottom: "5px" }}>
            Recent
          </div>
          <div className="sms-list">
            {[
              { emoji: current.emoji, text: `Code: ${current.code}`, sub: current.service, icon: "💬", isNew: true },
              { emoji: "🔍", text: "Code: 391 752", sub: "Google · 3m ago", icon: "✉️", isNew: false },
              { emoji: "✈️", text: "Code: 563 018", sub: "Telegram · 1m ago", icon: "✉️", isNew: false },
            ].map((msg, i) => (
              <div key={i} className="sms-item" style={{ animationDelay: `${i * 0.1}s` }}>
                <div className="sms-icon">{msg.emoji}</div>
                <div className="sms-content">
                  <div className="sms-text">{msg.text}</div>
                  <div className="sms-sub">{msg.sub}</div>
                </div>
                {msg.isNew && <div className="sms-new" />}
              </div>
            ))}
          </div>
        </div>

        <div className="phone-home-bar" />
      </div>

      {/* Floating badges */}
      <div className="float-card float-card-1">
        <span>✅</span> Delivered in 2s
      </div>
      <div className="float-card float-card-2">
        <span style={{ color: "#22d3ee" }}>🔒</span> Anonymous
      </div>
      <div className="float-card float-card-3">
        <span style={{ color: "#22c55e" }}>✓</span> Instant Delivery
      </div>
    </div>
  );
}
