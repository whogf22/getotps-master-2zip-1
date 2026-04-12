import { useState } from "react";
import { Link, useLocation } from "wouter";
import { useAuth } from "@/contexts/AuthContext";
import { useTheme } from "@/contexts/ThemeContext";
import { Logo } from "./Logo";
import { Button } from "@/components/ui/button";
import {
  LayoutDashboard,
  ShoppingCart,
  Phone,
  History,
  CreditCard,
  BookOpen,
  User,
  LogOut,
  Moon,
  Sun,
  Menu,
  ChevronLeft,
  Plus,
  Shield,
  Users,
  Wallet,
  Settings,
} from "lucide-react";

const navItems = [
  { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { href: "/buy", label: "Buy Number", icon: ShoppingCart },
  { href: "/active", label: "Active Numbers", icon: Phone },
  { href: "/rentals", label: "Rentals", icon: CreditCard },
  { href: "/history", label: "Order History", icon: History },
  { href: "/funds", label: "Add Funds", icon: Wallet },
  { href: "/api-docs", label: "API Docs", icon: BookOpen },
  { href: "/profile", label: "Profile", icon: User },
];

const adminNavItems = [
  { href: "/admin", label: "Admin Dashboard", icon: Shield },
  { href: "/admin/users", label: "Users", icon: Users },
  { href: "/admin/deposits", label: "Pending Deposits", icon: Wallet },
  { href: "/admin/settings", label: "Settings", icon: Settings },
];

interface DashboardLayoutProps {
  children: React.ReactNode;
}

export function DashboardLayout({ children }: DashboardLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const { user, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const [location] = useLocation();

  const handleLogout = async () => {
    await logout();
    window.location.hash = "/";
  };

  const SidebarContent = () => (
    <div className="flex flex-col h-full">
      {/* Logo + collapse */}
      <div className="flex items-center justify-between px-4 h-16 border-b border-sidebar-border shrink-0">
        <Logo showText={!collapsed} size={28} />
        <button
          onClick={() => setCollapsed(!collapsed)}
          className="hidden md:flex items-center justify-center w-7 h-7 rounded-lg text-sidebar-foreground/50 hover:text-sidebar-foreground hover:bg-sidebar-accent transition-colors"
          data-testid="button-collapse-sidebar"
        >
          <ChevronLeft className={`w-4 h-4 transition-transform duration-200 ${collapsed ? "rotate-180" : ""}`} />
        </button>
      </div>

      {/* Balance card */}
      <div className={`mx-3 mt-4 mb-2 rounded-xl border border-primary/25 bg-gradient-to-br from-primary/15 to-primary/5 overflow-hidden`}>
        {collapsed ? (
          <div className="p-3 text-center">
            <span className="text-primary font-bold text-sm">${user?.balance || "0.00"}</span>
          </div>
        ) : (
          <div className="p-4">
            <p className="text-xs text-sidebar-foreground/50 font-medium uppercase tracking-wider mb-1">Balance</p>
            <p className="text-2xl font-bold text-primary" data-testid="text-balance">${user?.balance || "0.00"}</p>
            <Link href="/funds">
              <a className="mt-3 flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg bg-primary/20 hover:bg-primary/30 text-primary text-xs font-semibold transition-colors" data-testid="button-sidebar-add-funds">
                <Plus className="w-3.5 h-3.5" /> Add Funds
              </a>
            </Link>
          </div>
        )}
      </div>

      {/* Nav */}
      <nav className="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto" data-testid="nav-sidebar">
        {navItems.map(({ href, label, icon: Icon }) => {
          const active = location === href;
          return (
            <Link key={href} href={href}>
              <a
                className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer select-none
                  ${active
                    ? "bg-primary text-primary-foreground shadow-sm"
                    : "text-sidebar-foreground/70 hover:text-sidebar-foreground hover:bg-sidebar-accent"
                  }
                  ${collapsed ? "justify-center" : ""}
                `}
                onClick={() => setMobileOpen(false)}
                data-testid={`nav-link-${href.replace("/", "")}`}
                title={collapsed ? label : undefined}
              >
                <Icon className={`w-4 h-4 shrink-0 ${active ? "text-primary-foreground" : ""}`} />
                {!collapsed && <span>{label}</span>}
              </a>
            </Link>
          );
        })}

        {user?.role === "admin" && (
          <>
            <div className={`my-3 mx-3 border-t border-sidebar-border ${collapsed ? "mx-1" : ""}`} />
            {!collapsed && (
              <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/30">Admin</p>
            )}
            {adminNavItems.map(({ href, label, icon: Icon }) => {
              const active = href === "/admin" ? location === "/admin" : location.startsWith(href);
              return (
                <Link key={href} href={href}>
                  <a
                    className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer select-none
                      ${active
                        ? "bg-amber-500/20 text-amber-400 shadow-sm"
                        : "text-sidebar-foreground/70 hover:text-sidebar-foreground hover:bg-sidebar-accent"
                      }
                      ${collapsed ? "justify-center" : ""}
                    `}
                    onClick={() => setMobileOpen(false)}
                    title={collapsed ? label : undefined}
                  >
                    <Icon className={`w-4 h-4 shrink-0 ${active ? "text-amber-400" : ""}`} />
                    {!collapsed && <span>{label}</span>}
                  </a>
                </Link>
              );
            })}
          </>
        )}
      </nav>

      {/* Footer actions */}
      <div className="p-3 border-t border-sidebar-border space-y-1 shrink-0">
        <button
          onClick={toggleTheme}
          className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-sidebar-foreground/70 hover:text-sidebar-foreground hover:bg-sidebar-accent w-full transition-colors ${collapsed ? "justify-center" : ""}`}
          data-testid="button-toggle-theme"
        >
          {theme === "dark" ? <Sun className="w-4 h-4 shrink-0" /> : <Moon className="w-4 h-4 shrink-0" />}
          {!collapsed && <span>{theme === "dark" ? "Light Mode" : "Dark Mode"}</span>}
        </button>
        <button
          onClick={handleLogout}
          className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 w-full transition-colors ${collapsed ? "justify-center" : ""}`}
          data-testid="button-logout"
        >
          <LogOut className="w-4 h-4 shrink-0" />
          {!collapsed && <span>Sign Out</span>}
        </button>
      </div>
    </div>
  );

  return (
    <div className="flex h-screen overflow-hidden bg-background">
      {/* Desktop Sidebar */}
      <aside
        className={`hidden md:flex flex-col bg-sidebar border-r border-sidebar-border transition-all duration-200 shrink-0 ${collapsed ? "w-[64px]" : "w-[220px]"}`}
      >
        <SidebarContent />
      </aside>

      {/* Mobile Sidebar Overlay */}
      {mobileOpen && (
        <div className="md:hidden fixed inset-0 z-50 flex">
          <div className="fixed inset-0 bg-black/70 backdrop-blur-sm" onClick={() => setMobileOpen(false)} />
          <aside className="relative w-[220px] bg-sidebar border-r border-sidebar-border flex flex-col z-10">
            <SidebarContent />
          </aside>
        </div>
      )}

      {/* Main Content */}
      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        {/* Top bar */}
        <header className="flex items-center justify-between h-16 px-5 border-b border-border bg-background/95 backdrop-blur-sm shrink-0">
          <div className="flex items-center gap-3">
            <button
              className="md:hidden p-2 rounded-xl hover:bg-accent transition-colors"
              onClick={() => setMobileOpen(!mobileOpen)}
              data-testid="button-mobile-menu"
            >
              <Menu className="w-5 h-5" />
            </button>
            <div className="hidden sm:block">
              <p className="text-sm text-muted-foreground">
                Welcome back, <span className="text-foreground font-semibold">{user?.username}</span>
              </p>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <div className="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-primary/10 border border-primary/20">
              <span className="text-xs text-muted-foreground">Balance</span>
              <span className="text-sm font-bold text-primary" data-testid="text-header-balance">${user?.balance || "0.00"}</span>
            </div>
            <Link href="/funds">
              <Button size="sm" className="rounded-xl text-xs font-semibold gap-1.5" data-testid="button-add-funds">
                <Plus className="w-3.5 h-3.5" /> Add Funds
              </Button>
            </Link>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-auto p-5 md:p-7">
          {children}
        </main>
      </div>
    </div>
  );
}
