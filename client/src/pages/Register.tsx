import { useState } from "react";
import { useAuth } from "@/contexts/AuthContext";
import { motion } from "framer-motion";
import { Eye, EyeOff, ArrowRight, CheckCircle, Phone, Zap } from "lucide-react";
import { useToast } from "@/hooks/use-toast";

export default function Register() {
  const [username, setUsername] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const { register } = useAuth();
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !email || !password) {
      toast({ title: "Error", description: "Please fill in all fields", variant: "destructive" });
      return;
    }
    if (password.length < 6) {
      toast({ title: "Error", description: "Password must be at least 6 characters", variant: "destructive" });
      return;
    }
    setLoading(true);
    try {
      await register(username, email, password);
      window.location.hash = "/dashboard";
    } catch (err: any) {
      toast({ title: "Registration failed", description: err.message || "Something went wrong", variant: "destructive" });
    } finally {
      setLoading(false);
    }
  };

  const strength = password.length === 0 ? 0 : password.length < 6 ? 1 : password.length < 10 ? 2 : 3;
  const strengthColors = ["", "#EF4444", "#F59E0B", "#22C55E"];
  const strengthLabels = ["", "Weak", "Good", "Strong"];

  return (
    <div className="min-h-screen bg-[#060D1A] flex" style={{ fontFamily: "'Inter','General Sans',system-ui,sans-serif" }}>
      {/* Left panel */}
      <div className="hidden lg:flex lg:w-1/2 relative overflow-hidden p-12 flex-col justify-between">
        <div className="absolute inset-0 bg-gradient-to-br from-[#00D4C4]/8 via-transparent to-[#6366F1]/5" />
        <div className="absolute top-1/3 left-1/2 -translate-x-1/2 w-80 h-80 bg-[#00D4C4]/8 rounded-full blur-3xl" />
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
        <div className="relative space-y-6">
          <h2 className="text-4xl font-black text-white leading-tight">Start verifying<br /><span className="text-[#00D4C4]">anonymously.</span></h2>
          <p className="text-slate-400">Join thousands who protect their real phone number with GetOTPs.</p>
          <ul className="space-y-3">
            {["Free to join — no credit card", "Pay only when you use", "Instant OTP codes", "200+ countries covered"].map((item, i) => (
              <li key={i} className="flex items-center gap-3 text-slate-300 text-sm">
                <CheckCircle className="w-4 h-4 text-[#00D4C4] flex-shrink-0" />
                {item}
              </li>
            ))}
          </ul>
        </div>
        <div className="relative text-xs text-slate-700">© {new Date().getFullYear()} GetOTPs. All rights reserved.</div>
      </div>

      {/* Right panel */}
      <div className="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
        <div className="w-full max-w-md">
          <div className="lg:hidden flex items-center gap-2.5 mb-8">
            <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center">
              <Phone className="w-4 h-4 text-white" />
            </div>
            <span className="font-bold text-white text-lg">GetOTPs</span>
          </div>

          <motion.div initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.5 }}>
            <h1 className="text-3xl font-black text-white mb-2">Create account</h1>
            <p className="text-slate-500 text-sm mb-8">Get started with GetOTPs for free</p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-400 mb-1.5">Username</label>
                <input type="text" value={username} onChange={e => setUsername(e.target.value)}
                  placeholder="cooluser" data-testid="input-username"
                  className="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-600 px-4 text-sm focus:outline-none focus:border-[#00D4C4]/50 focus:ring-1 focus:ring-[#00D4C4]/30 transition-all" />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-400 mb-1.5">Email</label>
                <input type="email" value={email} onChange={e => setEmail(e.target.value)}
                  placeholder="you@example.com" data-testid="input-email"
                  className="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-600 px-4 text-sm focus:outline-none focus:border-[#00D4C4]/50 focus:ring-1 focus:ring-[#00D4C4]/30 transition-all" />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-400 mb-1.5">Password</label>
                <div className="relative">
                  <input type={showPassword ? "text" : "password"} value={password} onChange={e => setPassword(e.target.value)}
                    placeholder="Min. 6 characters" data-testid="input-password"
                    className="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-600 px-4 pr-12 text-sm focus:outline-none focus:border-[#00D4C4]/50 focus:ring-1 focus:ring-[#00D4C4]/30 transition-all" />
                  <button type="button" onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors p-1">
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
                {password.length > 0 && (
                  <div className="mt-2 flex items-center gap-2">
                    <div className="flex-1 h-1 rounded-full bg-white/10 overflow-hidden">
                      <div className="h-full rounded-full transition-all duration-300"
                        style={{ width: `${(strength / 3) * 100}%`, background: strengthColors[strength] }} />
                    </div>
                    <span className="text-xs" style={{ color: strengthColors[strength] }}>{strengthLabels[strength]}</span>
                  </div>
                )}
              </div>

              <p className="text-xs text-slate-600">
                By creating an account, you agree to our{" "}
                <a href="/terms" className="text-slate-500 hover:text-[#00D4C4] transition-colors">Terms of Service</a>
                {" "}and{" "}
                <a href="/privacy" className="text-slate-500 hover:text-[#00D4C4] transition-colors">Privacy Policy</a>.
              </p>

              <motion.button type="submit" disabled={loading}
                whileHover={{ scale: 1.01, boxShadow: "0 16px 40px rgba(0,212,196,0.3)" }}
                whileTap={{ scale: 0.98 }}
                className="relative group w-full h-12 rounded-xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold shadow-lg shadow-cyan-500/25 overflow-hidden flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/15 to-transparent" />
                {loading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  <>Create Account <ArrowRight className="w-4 h-4" /></>
                )}
              </motion.button>
            </form>

            <p className="text-center text-sm text-slate-600 mt-6">
              Already have an account?{" "}
              <a href="/#/login" className="text-[#00D4C4] hover:text-white transition-colors font-medium">Sign in</a>
            </p>
          </motion.div>
        </div>
      </div>
    </div>
  );
}
