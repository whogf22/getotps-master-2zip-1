import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Users, Search, Plus, X, DollarSign } from "lucide-react";

export default function AdminUsers() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState("");
  const [balanceModal, setBalanceModal] = useState<any>(null);
  const [balanceAmount, setBalanceAmount] = useState("");
  const [balanceDesc, setBalanceDesc] = useState("");

  const { data: users, isLoading } = useQuery<any[]>({
    queryKey: ["/api/admin/users"],
  });

  const addBalanceMutation = useMutation({
    mutationFn: async ({ userId, amount, description }: { userId: number; amount: string; description: string }) => {
      const res = await apiRequest("POST", `/api/admin/users/${userId}/add-balance`, { amount, description });
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/users"] });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/stats"] });
      setBalanceModal(null);
      setBalanceAmount("");
      setBalanceDesc("");
      toast({ title: "Balance updated", description: "User balance has been credited." });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message || "Failed to add balance", variant: "destructive" });
    },
  });

  const filtered = users?.filter((u: any) =>
    u.username?.toLowerCase().includes(search.toLowerCase()) ||
    u.email?.toLowerCase().includes(search.toLowerCase())
  ) || [];

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-5xl">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Users Management</h1>
          <p className="text-sm text-muted-foreground mt-1">View and manage all registered users</p>
        </div>

        <div className="flex items-center gap-3">
          <div className="relative flex-1 max-w-md">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              placeholder="Search by username or email..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-9 rounded-xl"
            />
          </div>
          <div className="px-3 py-1.5 rounded-lg bg-muted text-xs font-medium text-muted-foreground">
            {filtered.length} user{filtered.length !== 1 ? "s" : ""}
          </div>
        </div>

        <div className="rounded-2xl border border-border bg-card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border bg-muted/30">
                  <th className="text-left px-5 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">User</th>
                  <th className="text-left px-5 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Email</th>
                  <th className="text-left px-5 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Role</th>
                  <th className="text-right px-5 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Balance</th>
                  <th className="text-right px-5 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                  Array.from({ length: 5 }).map((_, i) => (
                    <tr key={i} className="border-b border-border">
                      <td className="px-5 py-4"><Skeleton className="h-5 w-32" /></td>
                      <td className="px-5 py-4"><Skeleton className="h-5 w-40" /></td>
                      <td className="px-5 py-4"><Skeleton className="h-5 w-16" /></td>
                      <td className="px-5 py-4"><Skeleton className="h-5 w-20 ml-auto" /></td>
                      <td className="px-5 py-4"><Skeleton className="h-8 w-24 ml-auto" /></td>
                    </tr>
                  ))
                ) : filtered.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-5 py-12 text-center">
                      <Users className="w-10 h-10 text-muted-foreground mx-auto mb-3" />
                      <p className="text-sm font-medium text-muted-foreground">No users found</p>
                    </td>
                  </tr>
                ) : (
                  filtered.map((u: any) => (
                    <tr key={u.id} className="border-b border-border hover:bg-muted/20 transition-colors">
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-xs font-bold text-primary">
                            {u.username?.slice(0, 2).toUpperCase()}
                          </div>
                          <span className="text-sm font-semibold">{u.username}</span>
                        </div>
                      </td>
                      <td className="px-5 py-4 text-sm text-muted-foreground">{u.email}</td>
                      <td className="px-5 py-4">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          u.role === "admin"
                            ? "bg-amber-500/15 text-amber-500 border border-amber-500/20"
                            : "bg-muted text-muted-foreground"
                        }`}>
                          {u.role}
                        </span>
                      </td>
                      <td className="px-5 py-4 text-right">
                        <span className="text-sm font-bold text-primary tabular-nums">${u.balance}</span>
                      </td>
                      <td className="px-5 py-4 text-right">
                        <Button
                          size="sm"
                          variant="outline"
                          className="h-8 text-xs rounded-lg gap-1.5"
                          onClick={() => setBalanceModal(u)}
                        >
                          <Plus className="w-3.5 h-3.5" /> Add Balance
                        </Button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {balanceModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="fixed inset-0 bg-black/70 backdrop-blur-sm" onClick={() => setBalanceModal(null)} />
          <div className="relative w-full max-w-md mx-4 rounded-2xl border border-border bg-card p-6 shadow-xl">
            <button
              onClick={() => setBalanceModal(null)}
              className="absolute top-4 right-4 p-1.5 rounded-lg hover:bg-muted transition-colors"
            >
              <X className="w-4 h-4" />
            </button>
            <div className="flex items-center gap-3 mb-5">
              <div className="w-10 h-10 rounded-xl bg-primary/15 flex items-center justify-center">
                <DollarSign className="w-5 h-5 text-primary" />
              </div>
              <div>
                <h3 className="font-semibold">Add Balance</h3>
                <p className="text-xs text-muted-foreground">
                  Credit funds to <span className="font-medium text-foreground">{balanceModal.username}</span>
                </p>
              </div>
            </div>
            <div className="space-y-4">
              <div>
                <label className="text-xs font-medium text-muted-foreground mb-1.5 block">Amount (USD)</label>
                <Input
                  type="number"
                  min="0.01"
                  step="0.01"
                  placeholder="10.00"
                  value={balanceAmount}
                  onChange={(e) => setBalanceAmount(e.target.value)}
                  className="rounded-xl"
                />
              </div>
              <div>
                <label className="text-xs font-medium text-muted-foreground mb-1.5 block">Description (optional)</label>
                <Input
                  placeholder="e.g. Manual top-up"
                  value={balanceDesc}
                  onChange={(e) => setBalanceDesc(e.target.value)}
                  className="rounded-xl"
                />
              </div>
              <Button
                className="w-full rounded-xl font-semibold"
                disabled={!balanceAmount || Number(balanceAmount) <= 0 || addBalanceMutation.isPending}
                onClick={() => addBalanceMutation.mutate({
                  userId: balanceModal.id,
                  amount: balanceAmount,
                  description: balanceDesc,
                })}
              >
                {addBalanceMutation.isPending ? "Processing..." : `Add $${balanceAmount || "0.00"}`}
              </Button>
            </div>
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}
