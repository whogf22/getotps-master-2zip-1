import { useQuery } from "@tanstack/react-query";
import { Link } from "wouter";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Users, ShoppingCart, DollarSign, TrendingUp, ArrowRight, Wallet, Clock, UserCheck, Banknote,
} from "lucide-react";
import type { AdminStats, AdminUser, AdminTransaction } from "@/types/admin";

export default function AdminDashboard() {
  const { data: stats, isLoading: statsLoading } = useQuery<AdminStats>({
    queryKey: ["/api/admin/stats"],
  });

  const { data: users } = useQuery<AdminUser[]>({
    queryKey: ["/api/admin/users"],
  });

  const { data: transactions } = useQuery<AdminTransaction[]>({
    queryKey: ["/api/admin/transactions"],
  });

  const recentUsers = users?.slice(0, 5) || [];
  const recentTxns = transactions?.slice(0, 8) || [];

  const kpis = [
    {
      label: "Total Users",
      value: stats?.totalUsers ?? "—",
      icon: <Users className="w-5 h-5" />,
      iconBg: "bg-blue-500/15 text-blue-500",
    },
    {
      label: "Active Users (24h)",
      value: stats?.activeUsers ?? "—",
      icon: <UserCheck className="w-5 h-5" />,
      iconBg: "bg-cyan-500/15 text-cyan-500",
    },
    {
      label: "Total Orders",
      value: stats?.totalOrders ?? "—",
      icon: <ShoppingCart className="w-5 h-5" />,
      iconBg: "bg-emerald-500/15 text-emerald-500",
    },
    {
      label: "Pending Orders",
      value: stats?.pendingOrders ?? "—",
      icon: <Clock className="w-5 h-5" />,
      iconBg: "bg-orange-500/15 text-orange-500",
    },
    {
      label: "Total Revenue",
      value: stats?.revenue ? `$${stats.revenue}` : "—",
      icon: <DollarSign className="w-5 h-5" />,
      iconBg: "bg-violet-500/15 text-violet-500",
    },
    {
      label: "Today's Revenue",
      value: stats?.todayRevenue ? `$${stats.todayRevenue}` : "—",
      icon: <TrendingUp className="w-5 h-5" />,
      iconBg: "bg-green-500/15 text-green-500",
    },
    {
      label: "Total User Balances",
      value: stats?.totalBalances ? `$${stats.totalBalances}` : "—",
      icon: <Banknote className="w-5 h-5" />,
      iconBg: "bg-pink-500/15 text-pink-500",
    },
    {
      label: "Proxnum Balance",
      value: stats?.proxnumBalance ?? "—",
      icon: <Wallet className="w-5 h-5" />,
      iconBg: "bg-amber-500/15 text-amber-500",
    },
  ];

  return (
    <DashboardLayout>
      <div className="space-y-7 max-w-6xl">
        <div className="flex items-start justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
            <p className="text-sm text-muted-foreground mt-1">Platform overview and management</p>
          </div>
          <div className="flex items-center gap-2">
            <div className="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20">
              <span className="text-xs font-semibold text-amber-500">Admin</span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {kpis.map((kpi) => (
            <div
              key={kpi.label}
              className="rounded-2xl border border-border bg-card p-5 hover:border-primary/30 transition-colors"
            >
              <div className="flex items-center justify-between mb-4">
                <p className="text-xs font-medium text-muted-foreground">{kpi.label}</p>
                <div className={`w-9 h-9 rounded-xl flex items-center justify-center ${kpi.iconBg}`}>
                  {kpi.icon}
                </div>
              </div>
              {statsLoading ? (
                <Skeleton className="h-8 w-20 mb-1" />
              ) : (
                <p className="text-2xl font-bold tabular-nums">{kpi.value}</p>
              )}
            </div>
          ))}
        </div>

        <div className="grid lg:grid-cols-2 gap-6">
          <div className="rounded-2xl border border-border bg-card overflow-hidden">
            <div className="px-5 py-4 border-b border-border flex items-center justify-between">
              <div className="flex items-center gap-2">
                <div className="w-7 h-7 rounded-lg bg-blue-500/15 flex items-center justify-center">
                  <Users className="w-4 h-4 text-blue-500" />
                </div>
                <h2 className="font-semibold text-sm">Recent Users</h2>
              </div>
              <Link href="/admin/users">
                <a className="text-xs text-primary hover:text-primary/80 flex items-center gap-1 font-medium transition-colors">
                  View all <ArrowRight className="w-3.5 h-3.5" />
                </a>
              </Link>
            </div>
            <div className="p-4">
              {recentUsers.length === 0 ? (
                <p className="text-sm text-muted-foreground text-center py-8">No users yet</p>
              ) : (
                <div className="space-y-2">
                  {recentUsers.map((u) => (
                    <div key={u.id} className="flex items-center justify-between px-4 py-3 rounded-xl border border-border hover:bg-muted/30 transition-all">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-sm font-bold text-primary">
                          {u.username?.slice(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <p className="text-sm font-semibold">{u.username}</p>
                          <p className="text-xs text-muted-foreground">{u.email}</p>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-bold text-primary">${u.balance}</p>
                        <p className="text-xs text-muted-foreground">{u.role}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          <div className="rounded-2xl border border-border bg-card overflow-hidden">
            <div className="px-5 py-4 border-b border-border flex items-center justify-between">
              <div className="flex items-center gap-2">
                <div className="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                  <TrendingUp className="w-4 h-4 text-emerald-500" />
                </div>
                <h2 className="font-semibold text-sm">Recent Transactions</h2>
              </div>
            </div>
            <div className="p-4">
              {recentTxns.length === 0 ? (
                <p className="text-sm text-muted-foreground text-center py-8">No transactions yet</p>
              ) : (
                <div className="space-y-2">
                  {recentTxns.map((t) => (
                    <div key={t.id} className="flex items-center justify-between px-4 py-2.5 rounded-xl border border-border hover:bg-muted/30 transition-all">
                      <div>
                        <p className="text-xs font-semibold">{t.description}</p>
                        <p className="text-xs text-muted-foreground">{t.username} &middot; {new Date(t.createdAt).toLocaleDateString()}</p>
                      </div>
                      <span className={`text-sm font-bold tabular-nums ${t.type === "deposit" ? "text-emerald-500" : "text-red-400"}`}>
                        {t.type === "deposit" ? "+" : ""}{t.amount}
                      </span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
