import { motion } from "framer-motion";
import { Phone, ArrowRight, Home } from "lucide-react";

export default function NotFound() {
  return (
    <div className="min-h-screen bg-[#060D1A] flex flex-col items-center justify-center px-6 text-white"
      style={{ fontFamily: "'Inter','General Sans',system-ui,sans-serif" }}>

      {/* Background */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#00D4C4]/5 rounded-full blur-3xl" />
        <div className="absolute inset-0 opacity-[0.025]" style={{
          backgroundImage: "linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px)",
          backgroundSize: "40px 40px"
        }} />
      </div>

      <div className="relative text-center space-y-8 max-w-md">
        {/* Animated 404 */}
        <motion.div
          initial={{ scale: 0.5, opacity: 0 }} animate={{ scale: 1, opacity: 1 }}
          transition={{ type: "spring", stiffness: 200, damping: 20 }}
        >
          <div className="relative inline-block">
            <motion.span
              animate={{ y: [0, -8, 0] }} transition={{ duration: 3, repeat: Infinity, ease: "easeInOut" }}
              className="block text-[120px] lg:text-[160px] font-black leading-none bg-gradient-to-br from-[#00D4C4] via-[#0099CC] to-[#6366F1] bg-clip-text text-transparent"
            >
              404
            </motion.span>
            {/* Glow */}
            <div className="absolute inset-0 text-[120px] lg:text-[160px] font-black leading-none text-[#00D4C4]/10 blur-2xl">404</div>
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }}>
          <h1 className="text-2xl font-bold text-white mb-3">Page not found</h1>
          <p className="text-slate-500 text-base leading-relaxed">
            The page you're looking for doesn't exist or has been moved.
          </p>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.45 }}
          className="flex flex-col sm:flex-row gap-3 justify-center">
          <a href="/#/">
            <motion.button whileHover={{ scale: 1.03, boxShadow: "0 16px 40px rgba(0,212,196,0.3)" }} whileTap={{ scale: 0.97 }}
              className="relative group flex items-center gap-2 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#00D4C4] to-[#0099CC] text-white font-bold shadow-lg shadow-cyan-500/25 overflow-hidden">
              <span className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/15 to-transparent" />
              <Home className="w-4 h-4" />
              Back to Home
            </motion.button>
          </a>
          <a href="/#/register">
            <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
              className="flex items-center gap-2 px-7 py-3.5 rounded-xl border border-white/10 bg-white/5 text-white font-medium hover:border-white/20 transition-all">
              Get Started <ArrowRight className="w-4 h-4" />
            </motion.button>
          </a>
        </motion.div>

        {/* Logo */}
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.6 }}>
          <a href="/#/" className="inline-flex items-center gap-2 text-slate-700 hover:text-slate-500 transition-colors text-sm">
            <div className="w-6 h-6 rounded-lg bg-gradient-to-br from-[#00D4C4] to-[#0099CC] flex items-center justify-center">
              <Phone className="w-3 h-3 text-white" />
            </div>
            GetOTPs
          </a>
        </motion.div>
      </div>
    </div>
  );
}
