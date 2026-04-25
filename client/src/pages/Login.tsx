import { useState } from "react";
import { Link } from "wouter";
import { useAuth } from "@/contexts/AuthContext";
import { motion, AnimatePresence } from "framer-motion";
import { Eye, EyeOff, ArrowRight, CheckCircle, Phone, Zap } from "lucide-react";
import { useToast } from "@/hooks/use-toast";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) {
      toast({ title: "Error", description: "Please fill in all fields", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      await login(email, password);
      window.location.hash = "/dashboard";
    } catch (err: any) {
      toast({ title: "Login failed", description: err.message || "Invalid credentials", variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#060D1A] flex" style={{ fontFamily: "'Inter','General Sans',system-ui,sans-serif" }}>
      {/* Left panel */}
      <div className="hidden lg:flex lg:w-1/2 relative overflow-hidden p-12 flex-col justify-between">
        {/* Background */}
        <div className="absolute inset-0 bg-gradient-to-br from-[#00D4C4]/10 via-transparent to-[#6366F1]/5" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#00D4C4]/8 rounded-full blur-3xl" />
        <div className="absolute inset-0 opacity-[0.03]" style={{
          backgroundImage: "linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px)",
          backgroundSize: "40px 40px"
        }} />

        <div className="relative">
          <a href="/#/" className="flex items-center gap-2.5">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center shadow-lg shadow-cyan-500/30">
              <Phone className="w-5 h-5 text-white" />
            </div>
            <span className="font-bold text-white text-xl">GetOTPs</span>
          </a>
        </div>

        <div className="relative space-y-8">
          <div>
            <motion.h2 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
              className="text-4xl font-black text-white mb-3 leading-tight">
              Privacy-first<br />SMS verification
            </motion.h2>
            <motion.p initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }}
              className="text-slate-400 text-base leading-relaxed">
              Keep your real number private. Verify any app with disposable numbers in seconds.
            </motion.p>
          </div>

          <motion.ul initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.4 }}
            className="space-y-3">
            {["400+ supported services", "Instant OTP delivery", "No personal data required", "Full refund if no SMS arrives"].map((item, i) => (
              <motion.li key={i} initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.5 + i * 0.08 }}
                className="flex items-center gap-3 text-slate-300 text-sm">
                <CheckCircle className="w-4 h-4 text-[#00D4C4] flex-shrink-0" />
                {item}
              </motion.li>
            ))}
          </motion.ul>

          {/* Fake OTP preview */}
          <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.7 }}
            className="bg-[#0A1628]/80 border border-white/8 rounded-2xl p-4 backdrop-blur">
            <p className="text-xs text-slate-600 mb-3 font-medium">Latest OTPs</p>
            <div className="space-y-2">
              {[["WhatsApp", "847 291", "#25D366"], ["Telegram", "563 018", "#2AABEE"]].map(([svc, code, c]) => (
                <div key={svc} className="flex items-center gap-3">
                  <div className="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style={{ background: c + "22", color: c }}>{svc[0]}</div>
                  <span className="text-xs text-slate-500">{svc}</span>
                  <span className="ml-auto text-sm font-bold text-white tracking-widest">{code}</span>
                  <span className="text-[10px] px-1.5 py-0.5 rounded-full bg-green-500/15 text-green-400">received</span>
                </div>
              ))}
            </div>
          </motion.div>
        </div>

        <div className="relative text-xs text-slate-700">
          © {new Date().getFullYear()} GetOTPs. All rights reserved.
        </div>
      </div>

      {/* Right panel — form */}
      <div className="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
        <div className="w-full max-w-md">
          {/* Mobile logo */}
          <div className="lg:hidden flex items-center gap-2.5 mb-8">
            <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center">
              <Phone className="w-4 h-4 text-white" />
            </div>
            <span className="font-bold text-white text-lg">GetOTPs</span>
          </div>

          <motion.div initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.5 }}>
            <h1 className="text-3xl font-black text-white mb-2">Welcome back</h1>
            <p className="text-slate-500 text-sm mb-8">Sign in to your GetOTPs account</p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-400 mb-1.5">Email</label>
                <input
                  type="email" value={email} onChange={e => setEmail(e.target.value)}
                  placeholder="you@example.com"
                  data-testid="input-email"
                  className="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-600 px-4 text-sm focus:outline-none focus:border-[#00D4C4]/50 focus:ring-1 focus:ring-[#00D4C4]/30 transition-all"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-400 mb-1.5">Password</label>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"} value={password} onChange={e => setPassword(e.target.value)}
                    placeholder="••••••••"
                    data-testid="input-password"
                    className="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-600 px-4 pr-12 text-sm focus:outline-none focus:border-[#00D4C4]/50 focus:ring-1 focus:ring-[#00D4C4]/30 transition-all"
                  />
                  <button type="button" onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors p-1">
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <motion.button
                type="submit" disabled={loading}
                whileHover={{ scale: 1.01, boxShadow: "0 16px 40px rgba(0,212,196,0.3)" }}
                whileTap={{ scale: 0.98 }}
                className="relative group w-full h-12 rounded-xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold shadow-lg shadow-cyan-500/25 overflow-hidden flex items-center justify-center gap-2 mt-2 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/15 to-transparent" />
                {loading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  <>Sign In <ArrowRight className="w-4 h-4" /></>
                )}
              </motion.button>
            </form>

            <p className="text-center text-sm text-slate-600 mt-6">
              Don't have an account?{" "}
              <a href="/#/register" className="text-[#00D4C4] hover:text-white transition-colors font-medium">
                Create one free
              </a>
            </p>
          </motion.div>
        </div>
      </div>
    </div>
  );
}
