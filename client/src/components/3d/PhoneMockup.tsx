import { useEffect, useState } from "react";

const OTP_SEQUENCES = [
  { code: "847 291", service: "WhatsApp", color: "#25d366" },
  { code: "563 018", service: "Telegram", color: "#0088cc" },
  { code: "391 752", service: "Google", color: "#4285f4" },
  { code: "204 687", service: "Uber", color: "#000000" },
  { code: "719 043", service: "TikTok", color: "#ff0050" },
];

const INCOMING = [
  { id: 1, text: "+1 (555) 832-4910 is now active", delay: 0 },
  { id: 2, text: "Your code: 847 291 — WhatsApp", delay: 1200 },
  { id: 3, text: "Your code: 563 018 — Telegram", delay: 2400 },
];

export function PhoneMockup() {
  const [activeOtp, setActiveOtp] = useState(0);
  const [visible, setVisible] = useState(true);

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

  const current = OTP_SEQUENCES[activeOtp];

  return (
    <div className="phone-3d-wrap">
      {/* Glow halo behind phone */}
      <div className="phone-glow" />

      {/* Phone frame */}
      <div className="phone-frame">
        {/* Notch */}
        <div className="phone-notch" />

        {/* Status bar */}
        <div className="phone-status-bar">
          <span>9:41</span>
          <span className="flex gap-1 items-center text-[10px]">
            <svg width="12" height="8" viewBox="0 0 12 8" fill="currentColor">
              <rect x="0" y="3" width="2" height="5" rx="0.5" opacity="0.4"/>
              <rect x="3" y="2" width="2" height="6" rx="0.5" opacity="0.6"/>
              <rect x="6" y="1" width="2" height="7" rx="0.5" opacity="0.8"/>
              <rect x="9" y="0" width="2" height="8" rx="0.5"/>
            </svg>
            <svg width="14" height="8" viewBox="0 0 14 8" fill="currentColor">
              <rect x="0" y="1" width="11" height="6" rx="1" stroke="currentColor" strokeWidth="0.8" fill="none"/>
              <rect x="1.5" y="2.5" width="8" height="3" rx="0.4"/>
              <rect x="11.5" y="2.5" width="2" height="3" rx="0.5" opacity="0.5"/>
            </svg>
          </span>
        </div>

        {/* Screen content */}
        <div className="phone-screen">
          {/* Phone number badge */}
          <div className="phone-number-badge">
            <div className="pnb-flag">🇺🇸</div>
            <div>
              <div className="pnb-label">Your US Number</div>
              <div className="pnb-number">+1 (555) 832‑4910</div>
            </div>
            <div className="pnb-dot" />
          </div>

          {/* OTP display */}
          <div className="otp-display" style={{ opacity: visible ? 1 : 0, transition: "opacity 0.35s" }}>
            <div className="otp-service" style={{ color: current.color }}>
              {current.service} Verification
            </div>
            <div className="otp-digits">
              {current.code.replace(" ", "").split("").map((d, i) =>
                d === " " ? (
                  <span key={i} className="otp-sep" />
                ) : (
                  <span key={i} className="otp-digit">{d}</span>
                )
              )}
              <span className="otp-sep" />
              {current.code.split(" ")[1]?.split("").map((d, i) => (
                <span key={`b${i}`} className="otp-digit">{d}</span>
              ))}
            </div>
            <div className="otp-expire">Expires in 10:00</div>
          </div>

          {/* Incoming SMS list */}
          <div className="sms-list">
            {[
              { id: 1, text: "Number activated", sub: "+1 (555) 832-4910", icon: "📱" },
              { id: 2, text: `Code: ${current.code}`, sub: current.service, icon: "💬" },
              { id: 3, text: "New message received", sub: "2 seconds ago", icon: "✉️" },
            ].map((msg, i) => (
              <div key={msg.id} className="sms-item" style={{ animationDelay: `${i * 0.15}s` }}>
                <div className="sms-icon">{msg.icon}</div>
                <div className="sms-content">
                  <div className="sms-text">{msg.text}</div>
                  <div className="sms-sub">{msg.sub}</div>
                </div>
                {i === 1 && <div className="sms-new" />}
              </div>
            ))}
          </div>
        </div>

        {/* Home indicator */}
        <div className="phone-home-bar" />
      </div>

      {/* Floating service cards */}
      <div className="float-card float-card-1">
        <span>✓</span> WhatsApp verified
      </div>
      <div className="float-card float-card-2">
        <span style={{ color: "#0088cc" }}>✓</span> Telegram verified
      </div>
      <div className="float-card float-card-3">
        🔒 Anonymous &amp; Private
      </div>
    </div>
  );
}
