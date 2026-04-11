import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Wallet, Check, X, Clock, AlertCircle, Shield, CheckCircle2 } from "lucide-react";
import type { AdminDeposit } from "@/types/admin";

type ViewMode = "pending" | "all";

export default function AdminDeposits() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [viewMode, setViewMode] = useState<ViewMode>("pending");

  const { data: pendingDeposits, isLoading: pendingLoading } = useQuery<AdminDeposit[]>({
    queryKey: ["/api/admin/crypto/pending"],
  });

  const { data: allDeposits, isLoading: allLoading } = useQuery<AdminDeposit[]>({
    queryKey: ["/api/admin/crypto/all"],
    enabled: viewMode === "all",
  });

  const deposits = viewMode === "pending" ? pendingDeposits : allDeposits;
  const isLoading = viewMode === "pending" ? pendingLoading : allLoading;

  const confirmMutation = useMutation({
    mutationFn: async (id: number) => {
      const res = await apiRequest("POST", `/api/admin/crypto/${id}/confirm`);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/crypto/pending"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/crypto/all"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/stats"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/users"] });
      toast({ title: "Deposit confirmed", description: "Balance has been credited to the user." });
    },
    onError: (err: Error) => {
      toast({ title: "Error", description: err.message || "Failed to confirm", variant: "destructive" });
    },
  });

  const rejectMutation = useMutation({
    mutationFn: async (id: number) => {
      const res = await apiRequest("POST", `/api/admin/crypto/${id}/reject`);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/crypto/pending"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/crypto/all"] });
      toast({ title: "Deposit rejected" });
    },
    onError: (err: Error) => {
      toast({ title: "Error", description: err.message || "Failed to reject", variant: "destructive" });
    },
  });

  function getStatusIcon(status: string, circleTransferId?: string | null) {
    if (status === "completed" && circleTransferId) {
      return <Shield className="w-5 h-5 text-blue-500" />;
    }
    if (status === "completed") {
      return <CheckCircle2 className="w-5 h-5 text-emerald-500" />;
    }
    if (status === "confirming") {
      return <Clock className="w-5 h-5 text-amber-500" />;
    }
    if (status === "rejected" || status === "expired") {
      return <X className="w-5 h-5 text-red-500" />;
    }
    return <AlertCircle className="w-5 h-5 text-blue-500" />;
  }

  function getStatusBg(status: string, circleTransferId?: string | null) {
    if (status === "completed" && circleTransferId) return "bg-blue-500/15";
    if (status === "completed") return "bg-emerald-500/15";
    if (status === "confirming") return "bg-amber-500/15";
    if (status === "rejected" || status === "expired") return "bg-red-500/15";
    return "bg-blue-500/15";
  }

  function getStatusBadge(status: string, circleTransferId?: string | null) {
    if (status === "completed" && circleTransferId) {
      return { bg: "bg-blue-500/15 text-blue-500", label: "Circle (auto)" };
    }
    if (status === "completed") {
      return { bg: "bg-emerald-500/15 text-emerald-500", label: "completed" };
    }
    if (status === "confirming") {
      return { bg: "bg-amber-500/15 text-amber-500", label: "confirming" };
    }
    if (status === "rejected") {
      return { bg: "bg-red-500/15 text-red-500", label: "rejected" };
    }
    if (status === "expired") {
      return { bg: "bg-red-500/15 text-red-400", label: "expired" };
    }
    return { bg: "bg-blue-500/15 text-blue-500", label: status };
  }

  const isPending = (status: string) => status === "pending" || status === "confirming";

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-5xl">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">Deposits</h1>
            <p className="text-sm text-muted-foreground mt-1">Review crypto deposit requests and Circle auto-deposits</p>
          </div>
          <div className="flex gap-1 bg-muted rounded-lg p-1">
            <button
              onClick={() => setViewMode("pending")}
              className={`px-3 py-1.5 rounded-md text-xs font-semibold transition-all ${
                viewMode === "pending"
                  ? "bg-background text-foreground shadow-sm"
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              Pending
              {pendingDeposits && pendingDeposits.length > 0 && (
                <span className="ml-1.5 bg-amber-500/20 text-amber-500 px-1.5 py-0.5 rounded-full text-[10px]">
                  {pendingDeposits.length}
                </span>
              )}
            </button>
            <button
              onClick={() => setViewMode("all")}
              className={`px-3 py-1.5 rounded-md text-xs font-semibold transition-all ${
                viewMode === "all"
                  ? "bg-background text-foreground shadow-sm"
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              All Deposits
            </button>
          </div>
        </div>

        <div className="rounded-2xl border border-border bg-card overflow-hidden">
          {isLoading ? (
            <div className="p-6 space-y-4">
              {[1, 2, 3].map(i => <Skeleton key={i} className="h-20 w-full rounded-xl" />)}
            </div>
          ) : !deposits || deposits.length === 0 ? (
            <div className="text-center py-16">
              <div className="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-4">
                <Wallet className="w-7 h-7 text-muted-foreground" />
              </div>
              <p className="text-sm font-medium mb-1">
                {viewMode === "pending" ? "No pending deposits" : "No deposits yet"}
              </p>
              <p className="text-xs text-muted-foreground">
                {viewMode === "pending"
                  ? "All crypto deposits have been processed"
                  : "No deposit history to display"}
              </p>
            </div>
          ) : (
            <div className="divide-y divide-border">
              {deposits.map((d) => {
                const badge = getStatusBadge(d.status, d.circleTransferId);
                return (
                  <div key={d.id} className="p-5 hover:bg-muted/20 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                      <div className="flex items-start gap-4">
                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${getStatusBg(d.status, d.circleTransferId)}`}>
                          {getStatusIcon(d.status, d.circleTransferId)}
                        </div>
                        <div className="space-y-1.5">
                          <div className="flex items-center gap-2">
                            <p className="text-sm font-semibold">{d.username}</p>
                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${badge.bg}`}>
                              {badge.label}
                            </span>
                          </div>
                          <p className="text-xs text-muted-foreground">{d.email}</p>
                          <div className="flex items-center gap-4 text-xs">
                            <span className="font-semibold text-primary text-base">${d.amount}</span>
                            <span className="text-muted-foreground">{d.currency?.toUpperCase()}</span>
                            {d.cryptoAmount && <span className="text-muted-foreground">{d.cryptoAmount} {d.currency}</span>}
                          </div>
                          {d.txHash && (
                            <p className="text-xs font-mono text-muted-foreground break-all">
                              TX: {d.txHash}
                            </p>
                          )}
                          {d.circleTransferId && (
                            <p className="text-xs font-mono text-muted-foreground break-all">
                              Circle ID: {d.circleTransferId}
                            </p>
                          )}
                          <p className="text-xs text-muted-foreground">
                            {new Date(d.createdAt).toLocaleString()}
                          </p>
                        </div>
                      </div>

                      {isPending(d.status) && (
                        <div className="flex items-center gap-2 sm:flex-col">
                          <Button
                            size="sm"
                            className="rounded-lg gap-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700"
                            disabled={confirmMutation.isPending || rejectMutation.isPending}
                            onClick={() => confirmMutation.mutate(d.id)}
                          >
                            <Check className="w-3.5 h-3.5" /> Confirm
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            className="rounded-lg gap-1.5 text-xs font-semibold text-red-400 hover:text-red-300 border-red-400/30 hover:bg-red-500/10"
                            disabled={confirmMutation.isPending || rejectMutation.isPending}
                            onClick={() => rejectMutation.mutate(d.id)}
                          >
                            <X className="w-3.5 h-3.5" /> Reject
                          </Button>
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}
