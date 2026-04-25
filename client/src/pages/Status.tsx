import { useQuery } from "@tanstack/react-query";
import { Link } from "wouter";
import { Logo } from "@/components/Logo";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { AlertCircle, Activity, Clock3 } from "lucide-react";

type UptimeLog = {
  id: number;
  status: "up" | "degraded" | "down";
  statusCode: number;
  latencyMs: number | null;
  source: string;
  checkedAt: string;
};

type StatusResponse = {
  current: {
    status: "up" | "degraded" | "down";
    statusCode: number;
    checkedAt: string;
    latencyMs: number | null;
  };
  summary: {
    last24h: {
      totalChecks: number;
      upChecks: number;
      uptimePercent: number;
      avgLatencyMs: number | null;
    };
  };
  history: UptimeLog[];
};

function formatTime(isoTime: string): string {
  return new Date(isoTime).toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}

function statusBadge(status: UptimeLog["status"]) {
  if (status === "up") {
    return <Badge className="bg-green-500/15 text-green-600 border-green-500/30">Operational</Badge>;
  }

  if (status === "degraded") {
    return <Badge className="bg-yellow-500/15 text-yellow-600 border-yellow-500/30">Degraded</Badge>;
  }

  return <Badge variant="destructive">Down</Badge>;
}

export default function StatusPage() {
  const { data, isLoading, isError } = useQuery<StatusResponse>({
    queryKey: ["/api/status"],
    refetchInterval: 60_000,
  });

  const uptime = data?.summary.last24h.uptimePercent ?? 0;
  const averageLatency = data?.summary.last24h.avgLatencyMs ?? null;

  return (
    <div className="min-h-screen bg-background">
      <header className="border-b border-border">
        <div className="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
          <Link href="/">
            <a>
              <Logo size={28} />
            </a>
          </Link>
          <Link href="/">
            <a className="text-sm text-muted-foreground hover:text-foreground">Back to home</a>
          </Link>
        </div>
      </header>

      <main className="max-w-5xl mx-auto px-4 py-8 space-y-6">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Service Status</h1>
          <p className="text-sm text-muted-foreground mt-1">
            Live health and uptime checks for GetOTPs API.
          </p>
        </div>

        {isError && (
          <Card className="border-destructive/40">
            <CardContent className="py-6 flex items-center gap-3 text-destructive">
              <AlertCircle className="w-5 h-5" />
              <p className="text-sm">Unable to load status data right now. Please try again shortly.</p>
            </CardContent>
          </Card>
        )}

        <div className="grid md:grid-cols-3 gap-4">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm flex items-center gap-2">
                <Activity className="w-4 h-4 text-primary" />
                Current status
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <Skeleton className="h-6 w-24" />
              ) : (
                <div className="space-y-2">
                  {data ? statusBadge(data.current.status) : <Badge variant="secondary">Unknown</Badge>}
                  <p className="text-xs text-muted-foreground">
                    Last check: {data ? formatTime(data.current.checkedAt) : "n/a"}
                  </p>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm">24h uptime</CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? <Skeleton className="h-6 w-20" /> : <p className="text-2xl font-bold">{uptime.toFixed(2)}%</p>}
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm flex items-center gap-2">
                <Clock3 className="w-4 h-4 text-primary" />
                Avg latency
              </CardTitle>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <Skeleton className="h-6 w-16" />
              ) : (
                <p className="text-2xl font-bold">{averageLatency == null ? "n/a" : `${Math.round(averageLatency)}ms`}</p>
              )}
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Recent uptime checks</CardTitle>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="space-y-2">
                {[1, 2, 3, 4].map((item) => (
                  <Skeleton key={item} className="h-10 w-full" />
                ))}
              </div>
            ) : !data?.history.length ? (
              <p className="text-sm text-muted-foreground">No uptime checks recorded yet.</p>
            ) : (
              <div className="space-y-2">
                {data.history.map((log) => (
                  <div key={log.id} className="flex items-center justify-between border border-border rounded-lg px-3 py-2">
                    <div className="flex items-center gap-3">
                      {statusBadge(log.status)}
                      <span className="text-xs text-muted-foreground">HTTP {log.statusCode}</span>
                      <span className="text-xs text-muted-foreground">
                        {log.latencyMs == null ? "n/a" : `${log.latencyMs}ms`}
                      </span>
                    </div>
                    <span className="text-xs text-muted-foreground">{formatTime(log.checkedAt)}</span>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
