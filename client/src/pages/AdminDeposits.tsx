import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Wallet, Check, X, Clock, AlertCircle } from "lucide-react";

export default function AdminDeposits() {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: deposits, isLoading } = useQuery<any[]>({
    queryKey: ["/api/admin/crypto/pending"],
  });

  const confirmMutation = useMutation({
    mutationFn: async (id: number) => {
      const res = await apiRequest("POST", `/api/admin/crypto/${id}/confirm`);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/crypto/pending"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/stats"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/users"] });
      toast({ title: "Deposit confirmed", description: "Balance has been credited to the user." });
    },
    onError: (err: any) => {
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
      toast({ title: "Deposit rejected" });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message || "Failed to reject", variant: "destructive" });
    },
  });

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-5xl">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Pending Deposits</h1>
          <p className="text-sm text-muted-foreground mt-1">Review and confirm crypto deposit requests</p>
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
              <p className="text-sm font-medium mb-1">No pending deposits</p>
              <p className="text-xs text-muted-foreground">All crypto deposits have been processed</p>
            </div>
          ) : (
            <div className="divide-y divide-border">
              {deposits.map((d: any) => (
                <div key={d.id} className="p-5 hover:bg-muted/20 transition-colors">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-start gap-4">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${
                        d.status === "confirming"
                          ? "bg-amber-500/15"
                          : "bg-blue-500/15"
                      }`}>
                        {d.status === "confirming" ? (
                          <Clock className="w-5 h-5 text-amber-500" />
                        ) : (
                          <AlertCircle className="w-5 h-5 text-blue-500" />
                        )}
                      </div>
                      <div className="space-y-1.5">
                        <div className="flex items-center gap-2">
                          <p className="text-sm font-semibold">{d.username}</p>
                          <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                            d.status === "confirming"
                              ? "bg-amber-500/15 text-amber-500"
                              : "bg-blue-500/15 text-blue-500"
                          }`}>
                            {d.status}
                          </span>
                        </div>
                        <p className="text-xs text-muted-foreground">{d.email}</p>
                        <div className="flex items-center gap-4 text-xs">
                          <span className="font-semibold text-primary text-base">${d.amount}</span>
                          <span className="text-muted-foreground">{d.currency?.toUpperCase()}</span>
                          <span className="text-muted-foreground">{d.network}</span>
                        </div>
                        {d.txHash && (
                          <p className="text-xs font-mono text-muted-foreground break-all">
                            TX: {d.txHash}
                          </p>
                        )}
                        <p className="text-xs text-muted-foreground">
                          {new Date(d.createdAt).toLocaleString()}
                        </p>
                      </div>
                    </div>

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
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}
