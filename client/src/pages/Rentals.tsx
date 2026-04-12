import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/contexts/AuthContext";
import { Phone, Clock, MessageSquare, X, Copy, Check, ChevronDown, ChevronUp } from "lucide-react";

function CopyButton({ text }: { text: string }) {
  const [copied, setCopied] = useState(false);
  const copy = async () => {
    await navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };
  return (
    <button onClick={copy} className="p-1 rounded text-muted-foreground hover:text-foreground transition-colors">
      {copied ? <Check className="w-3.5 h-3.5 text-green-500" /> : <Copy className="w-3.5 h-3.5" />}
    </button>
  );
}

function timeRemaining(expiresAt: string): string {
  const diff = new Date(expiresAt).getTime() - Date.now();
  if (diff <= 0) return "Expired";
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  if (days > 0) return `${days}d ${hours}h remaining`;
  const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  if (hours > 0) return `${hours}h ${mins}m remaining`;
  return `${mins}m remaining`;
}

export default function Rentals() {
  const { toast } = useToast();
  const { refreshUser } = useAuth();
  const queryClient = useQueryClient();
  const [expandedRental, setExpandedRental] = useState<number | null>(null);

  const { data: rentals, isLoading } = useQuery<any[]>({
    queryKey: ["/api/rentals"],
    refetchInterval: 30000,
  });

  const { data: messages } = useQuery<any[]>({
    queryKey: ["/api/rentals", expandedRental, "messages"],
    queryFn: async () => {
      if (!expandedRental) return [];
      const res = await apiRequest("GET", `/api/rentals/${expandedRental}/messages`);
      return res.json();
    },
    enabled: !!expandedRental,
    refetchInterval: expandedRental ? 15000 : false,
  });

  const cancelMutation = useMutation({
    mutationFn: async (rentalId: number) => {
      const res = await apiRequest("POST", `/api/rentals/${rentalId}/cancel`);
      return res.json();
    },
    onSuccess: (data: any) => {
      queryClient.invalidateQueries({ queryKey: ["/api/rentals"] });
      queryClient.invalidateQueries({ queryKey: ["/api/auth/me"] });
      refreshUser();
      toast({ title: "Rental cancelled", description: data.message });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message, variant: "destructive" });
    },
  });

  const activeRentals = rentals?.filter(r => r.status === "active") || [];
  const pastRentals = rentals?.filter(r => r.status !== "active") || [];

  const statusBadge = (status: string) => {
    switch (status) {
      case "active": return <Badge className="bg-green-500/10 text-green-500 border-green-500/20 text-xs">Active</Badge>;
      case "cancelled": return <Badge variant="secondary" className="text-xs">Cancelled</Badge>;
      case "expired": return <Badge variant="outline" className="text-xs">Expired</Badge>;
      default: return <Badge variant="secondary" className="text-xs">{status}</Badge>;
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-xl font-bold">My Rentals</h1>
            <p className="text-sm text-muted-foreground mt-0.5">Manage your long-term number rentals</p>
          </div>
          <Button size="sm" onClick={() => window.location.hash = "/buy"}>
            <Phone className="w-3.5 h-3.5 mr-1.5" />
            Rent Number
          </Button>
        </div>

        {isLoading ? (
          <div className="space-y-3">
            {[...Array(3)].map((_, i) => <Skeleton key={i} className="h-24" />)}
          </div>
        ) : activeRentals.length === 0 && pastRentals.length === 0 ? (
          <Card className="border-border">
            <CardContent className="py-12 text-center">
              <Phone className="w-10 h-10 text-muted-foreground mx-auto mb-3" />
              <p className="text-sm text-muted-foreground">No rentals yet</p>
              <p className="text-xs text-muted-foreground mt-1">Rent a number for long-term SMS receiving</p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-6">
            {activeRentals.length > 0 && (
              <div className="space-y-3">
                <h2 className="text-sm font-semibold text-muted-foreground">Active Rentals ({activeRentals.length})</h2>
                {activeRentals.map((rental: any) => (
                  <Card key={rental.id} className="border-border">
                    <CardContent className="p-4">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <Phone className="w-4 h-4 text-green-500" />
                          </div>
                          <div>
                            <p className="text-sm font-semibold">{rental.serviceName}</p>
                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                              <Clock className="w-3 h-3" />
                              {timeRemaining(rental.expiresAt)}
                            </div>
                          </div>
                        </div>
                        {statusBadge(rental.status)}
                      </div>

                      <div className="flex items-center gap-2 p-2.5 rounded-lg bg-muted/50 border border-border mb-3">
                        <span className="font-mono text-sm font-medium flex-1">{rental.phoneNumber}</span>
                        <CopyButton text={rental.phoneNumber} />
                      </div>

                      <div className="flex items-center justify-between text-xs text-muted-foreground mb-3">
                        <span>Country: {rental.country?.toUpperCase()}</span>
                        <span>{rental.days} day rental · ${rental.price}</span>
                      </div>

                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => setExpandedRental(expandedRental === rental.id ? null : rental.id)}
                          className="text-xs"
                        >
                          <MessageSquare className="w-3.5 h-3.5 mr-1" />
                          Messages
                          {expandedRental === rental.id ? <ChevronUp className="w-3 h-3 ml-1" /> : <ChevronDown className="w-3 h-3 ml-1" />}
                        </Button>
                        <Button
                          size="sm"
                          variant="destructive"
                          onClick={() => cancelMutation.mutate(rental.id)}
                          disabled={cancelMutation.isPending}
                          className="text-xs"
                        >
                          <X className="w-3.5 h-3.5 mr-1" />
                          Cancel
                        </Button>
                      </div>

                      {expandedRental === rental.id && (
                        <div className="mt-3 pt-3 border-t border-border">
                          <p className="text-xs font-semibold mb-2">Messages</p>
                          {messages && messages.length > 0 ? (
                            <div className="space-y-2 max-h-48 overflow-y-auto">
                              {messages.map((msg: any, i: number) => (
                                <div key={i} className="p-2.5 rounded-lg bg-muted/50 border border-border">
                                  <div className="flex items-center justify-between mb-1">
                                    <span className="text-xs font-medium text-primary">{msg.sender || "Unknown"}</span>
                                    <span className="text-xs text-muted-foreground">
                                      {new Date(msg.receivedAt).toLocaleTimeString()}
                                    </span>
                                  </div>
                                  <p className="text-xs">{msg.message}</p>
                                </div>
                              ))}
                            </div>
                          ) : (
                            <p className="text-xs text-muted-foreground text-center py-4">No messages received yet</p>
                          )}
                        </div>
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}

            {pastRentals.length > 0 && (
              <div className="space-y-3">
                <h2 className="text-sm font-semibold text-muted-foreground">Past Rentals ({pastRentals.length})</h2>
                {pastRentals.map((rental: any) => (
                  <Card key={rental.id} className="border-border opacity-60">
                    <CardContent className="p-4">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-lg bg-muted flex items-center justify-center">
                            <Phone className="w-4 h-4 text-muted-foreground" />
                          </div>
                          <div>
                            <p className="text-sm font-semibold">{rental.serviceName}</p>
                            <p className="text-xs text-muted-foreground font-mono">{rental.phoneNumber}</p>
                          </div>
                        </div>
                        <div className="text-right">
                          {statusBadge(rental.status)}
                          <p className="text-xs text-muted-foreground mt-1">${rental.price}</p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
