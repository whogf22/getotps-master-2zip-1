import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Link } from "wouter";
import { useAuth } from "@/contexts/AuthContext";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { DollarSign, ShoppingCart, Phone, MessageSquare, ArrowRight, Zap, TrendingUp } from "lucide-react";

const QUICK_SERVICES = [
  { name: "WhatsApp", icon: "💬", slug: "whatsapp", color: "from-green-500/15 to-green-600/5 border-green-500/20" },
  { name: "Telegram", icon: "✈️", slug: "telegram", color: "from-sky-500/15 to-sky-600/5 border-sky-500/20" },
  { name: "Google", icon: "🔍", slug: "google", color: "from-blue-500/15 to-blue-600/5 border-blue-500/20" },
  { name: "Discord", icon: "🎮", slug: "discord", color: "from-violet-500/15 to-violet-600/5 border-violet-500/20" },
];

function StatusBadge({ status }: { status: string }) {
  const configs: Record<string, { cls: string; dot: string }> = {
    waiting:   { cls: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400", dot: "bg-yellow-500 animate-pulse" },
    received:  { cls: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400", dot: "bg-green-500" },
    completed: { cls: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400", dot: "bg-blue-500" },
    cancelled: { cls: "bg-gray-100 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400", dot: "bg-gray-400" },
    expired:   { cls: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400", dot: "bg-red-500" },
  };
  const cfg = configs[status] || configs.expired;
  return (
    <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium ${cfg.cls}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`} />
      {status.charAt(0).toUpperCase() + status.slice(1)}
    </span>
  );
}

export default function Dashboard() {
  const { user, refreshUser } = useAuth();
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: orders, isLoading: ordersLoading } = useQuery<any[]>({
    queryKey: ["/api/orders"],
  });

  const { data: services } = useQuery<any[]>({
    queryKey: ["/api/services"],
  });

  const buyMutation = useMutation({
    mutationFn: async (serviceId: number) => {
      const res = await apiRequest("POST", "/api/orders", { serviceId });
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/orders"] });
      queryClient.invalidateQueries({ queryKey: ["/api/auth/me"] });
      refreshUser();
      toast({ title: "Number acquired!", description: "Your number is ready. Go to Active Numbers to see it." });
    },
    onError: (err: any) => {
      toast({ title: "Failed", description: err.message || "Could not buy number", variant: "destructive" });
    },
  });

  const handleQuickBuy = (slug: string) => {
    const svc = services?.find((s: any) => s.slug === slug);
    if (!svc) return toast({ title: "Service not found", variant: "destructive" });
    buyMutation.mutate(svc.id);
  };

  const recentOrders = orders?.slice(0, 5) || [];
  const activeCount = orders?.filter((o: any) => o.status === "waiting" || o.status === "received").length || 0;
  const smsReceived = orders?.filter((o: any) => o.status === "received" || o.status === "completed").length || 0;

  const kpis = [
    {
      label: "Balance",
      value: `$${user?.balance || "0.00"}`,
      icon: <DollarSign className="w-5 h-5" />,
      iconBg: "bg-primary/15 text-primary",
      testId: "kpi-balance",
      sub: "Available to spend",
    },
    {
      label: "Active Numbers",
      value: activeCount,
      icon: <Phone className="w-5 h-5" />,
      iconBg: "bg-emerald-500/15 text-emerald-500",
      testId: "kpi-active",
      sub: "Currently rented",
    },
    {
      label: "Total Orders",
      value: orders?.length || 0,
      icon: <ShoppingCart className="w-5 h-5" />,
      iconBg: "bg-blue-500/15 text-blue-500",
      testId: "kpi-orders",
      sub: "All time orders",
    },
    {
      label: "SMS Received",
      value: smsReceived,
      icon: <MessageSquare className="w-5 h-5" />,
      iconBg: "bg-violet-500/15 text-violet-500",
      testId: "kpi-sms",
      sub: "Codes received",
    },
  ];

  return (
    <DashboardLayout>
      <div className="space-y-7 max-w-5xl">

        {/* Header */}
        <div className="flex items-start justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p className="text-sm text-muted-foreground mt-1">Your account overview</p>
          </div>
          <Link href="/buy">
            <Button className="rounded-xl gap-2 font-semibold">
              <Zap className="w-4 h-4" /> Buy Number
            </Button>
          </Link>
        </div>

        {/* KPI Cards */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {kpis.map(kpi => (
            <div
              key={kpi.label}
              className="rounded-2xl border border-border bg-card p-5 hover:border-primary/30 transition-colors"
              data-testid={kpi.testId}
            >
              <div className="flex items-center justify-between mb-4">
                <p className="text-xs font-medium text-muted-foreground">{kpi.label}</p>
                <div className={`w-9 h-9 rounded-xl flex items-center justify-center ${kpi.iconBg}`}>
                  {kpi.icon}
                </div>
              </div>
              {ordersLoading ? (
                <Skeleton className="h-8 w-20 mb-1" />
              ) : (
                <p className="text-2xl font-bold tabular-nums">{kpi.value}</p>
              )}
              <p className="text-xs text-muted-foreground mt-1">{kpi.sub}</p>
            </div>
          ))}
        </div>

        <div className="grid lg:grid-cols-3 gap-6">
          {/* Quick Buy */}
          <div className="rounded-2xl border border-border bg-card overflow-hidden">
            <div className="px-5 py-4 border-b border-border flex items-center gap-2">
              <div className="w-7 h-7 rounded-lg bg-primary/15 flex items-center justify-center">
                <Zap className="w-4 h-4 text-primary" />
              </div>
              <h2 className="font-semibold text-sm">Quick Buy</h2>
            </div>
            <div className="p-4 space-y-2">
              {QUICK_SERVICES.map(svc => {
                const serviceData = services?.find((s: any) => s.slug === svc.slug);
                return (
                  <div
                    key={svc.slug}
                    className={`flex items-center justify-between p-3 rounded-xl border bg-gradient-to-r ${svc.color} transition-all hover:shadow-sm`}
                  >
                    <div className="flex items-center gap-3">
                      <span className="text-xl">{svc.icon}</span>
                      <div>
                        <p className="text-sm font-semibold">{svc.name}</p>
                        <p className="text-xs text-primary font-medium">${serviceData?.price || "..."}</p>
                      </div>
                    </div>
                    <Button
                      size="sm"
                      variant="outline"
                      className="h-8 text-xs rounded-lg font-semibold"
                      onClick={() => handleQuickBuy(svc.slug)}
                      disabled={buyMutation.isPending}
                      data-testid={`button-quickbuy-${svc.slug}`}
                    >
                      Buy
                    </Button>
                  </div>
                );
              })}
              <Link href="/buy">
                <a className="flex items-center justify-center gap-1.5 w-full py-3 text-xs text-primary hover:text-primary/80 font-semibold transition-colors mt-1">
                  Browse all 500+ services <ArrowRight className="w-3.5 h-3.5" />
                </a>
              </Link>
            </div>
          </div>

          {/* Recent Orders */}
          <div className="rounded-2xl border border-border bg-card overflow-hidden lg:col-span-2">
            <div className="px-5 py-4 border-b border-border flex items-center justify-between">
              <div className="flex items-center gap-2">
                <div className="w-7 h-7 rounded-lg bg-blue-500/15 flex items-center justify-center">
                  <TrendingUp className="w-4 h-4 text-blue-500" />
                </div>
                <h2 className="font-semibold text-sm">Recent Orders</h2>
              </div>
              <Link href="/history">
                <a className="text-xs text-primary hover:text-primary/80 flex items-center gap-1 font-medium transition-colors">
                  View all <ArrowRight className="w-3.5 h-3.5" />
                </a>
              </Link>
            </div>
            <div className="p-4">
              {ordersLoading ? (
                <div className="space-y-3">
                  {[1, 2, 3].map(i => <Skeleton key={i} className="h-14 w-full rounded-xl" />)}
                </div>
              ) : recentOrders.length === 0 ? (
                <div className="text-center py-12">
                  <div className="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-4">
                    <ShoppingCart className="w-7 h-7 text-muted-foreground" />
                  </div>
                  <p className="text-sm font-medium mb-1">No orders yet</p>
                  <p className="text-xs text-muted-foreground mb-5">Buy your first number to get started</p>
                  <Link href="/buy">
                    <Button size="sm" className="rounded-xl">Buy Your First Number</Button>
                  </Link>
                </div>
              ) : (
                <div className="space-y-2">
                  {recentOrders.map((order: any) => (
                    <div
                      key={order.id}
                      className="flex items-center justify-between px-4 py-3 rounded-xl border border-border hover:border-border/80 hover:bg-muted/30 transition-all"
                      data-testid={`row-order-${order.id}`}
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-muted flex items-center justify-center text-lg shrink-0">
                          {order.service?.icon || "📱"}
                        </div>
                        <div>
                          <p className="text-sm font-semibold">{order.service?.name || "Unknown"}</p>
                          <p className="text-xs text-muted-foreground font-mono">{order.phoneNumber}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-3">
                        <StatusBadge status={order.status} />
                        <span className="text-xs font-semibold text-muted-foreground">${order.price}</span>
                      </div>
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
