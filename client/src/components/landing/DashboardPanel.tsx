import { useEffect, useState } from "react";

export default function DashboardPanel() {
  const [tab, setTab] = useState(0);
  const [tick, setTick] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => setTick((current) => current + 1), 3000);
    return () => clearInterval(timer);
  }, []);

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
        {tabs.map((tabName, index) => (
          <button key={tabName} className={`dash-tab ${tab === index ? "dash-tab-on" : ""}`} onClick={() => setTab(index)}>
            {tabName}{index === 0 && <span className="dash-tab-live" />}
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
            ].map((row, index) => (
              <div key={index} className="dash-row">
                <span className="dash-row-svc" style={{ color: row.cl }}>{row.svc}</span>
                <span className="dash-row-code">{row.code}</span>
                <span className="dash-row-time">{row.time}</span>
                <span className="dash-row-st dash-st-ok">{row.st}</span>
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
            ].map((row, index) => (
              <div key={index} className={`dash-row ${!row.on ? "dash-row-dim" : ""}`}>
                <span className="dash-row-num">{row.num}</span>
                <span className="dash-row-svc" style={{ color: "#22d3ee", fontSize: "10px" }}>{row.svc}</span>
                <span className={`dash-row-time ${row.on ? "text-emerald-400" : ""}`}>{row.on ? `⏱ ${row.exp}` : row.exp}</span>
                <span className={`dash-row-st ${row.on ? "dash-st-ok" : "dash-st-exp"}`}>{row.on ? "active" : "expired"}</span>
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
            ].map((row, index) => (
              <div key={index} className="dash-row">
                <span className="dash-row-svc" style={{ color: "#22d3ee" }}>{row.svc}</span>
                <span className="dash-row-code" style={{ fontSize: "10px", opacity: 0.5 }}>{row.type}</span>
                <span className="dash-row-time">{row.time}</span>
                <span className={row.amt === "Refunded" ? "text-emerald-400 text-[11px] font-bold" : "text-cyan-400/60 text-[11px] font-bold"}>{row.amt}</span>
              </div>
            ))}
          </>
        )}
      </div>
    </div>
  );
}
