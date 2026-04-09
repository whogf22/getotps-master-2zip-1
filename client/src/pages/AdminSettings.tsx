import { useState, useEffect } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Settings, Save, RotateCcw } from "lucide-react";

export default function AdminSettings() {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: settings, isLoading } = useQuery<any>({
    queryKey: ["/api/admin/settings"],
  });

  const [multiplier, setMultiplier] = useState("");
  const [defaultCountry, setDefaultCountry] = useState("");

  useEffect(() => {
    if (settings) {
      setMultiplier(settings.price_multiplier || "1.5");
      setDefaultCountry(settings.default_country || "us");
    }
  }, [settings]);

  const saveMutation = useMutation({
    mutationFn: async (data: any) => {
      const res = await apiRequest("PUT", "/api/admin/settings", data);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/settings"] });
      toast({ title: "Settings saved", description: "Platform settings have been updated." });
    },
    onError: (err: any) => {
      toast({ title: "Error", description: err.message || "Failed to save settings", variant: "destructive" });
    },
  });

  const handleSave = () => {
    saveMutation.mutate({
      price_multiplier: multiplier,
      default_country: defaultCountry,
    });
  };

  const handleReset = () => {
    if (settings) {
      setMultiplier(settings.price_multiplier || "1.5");
      setDefaultCountry(settings.default_country || "us");
    }
  };

  const hasChanges =
    multiplier !== (settings?.price_multiplier || "1.5") ||
    defaultCountry !== (settings?.default_country || "us");

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-3xl">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Platform Settings</h1>
          <p className="text-sm text-muted-foreground mt-1">Configure pricing and default options</p>
        </div>

        <div className="rounded-2xl border border-border bg-card overflow-hidden">
          <div className="px-6 py-4 border-b border-border flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-primary/15 flex items-center justify-center">
              <Settings className="w-4 h-4 text-primary" />
            </div>
            <h2 className="font-semibold text-sm">Pricing Configuration</h2>
          </div>

          {isLoading ? (
            <div className="p-6 space-y-6">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-full" />
            </div>
          ) : (
            <div className="p-6 space-y-6">
              <div>
                <label className="text-sm font-medium mb-2 block">Price Multiplier</label>
                <p className="text-xs text-muted-foreground mb-3">
                  Multiplier applied to Proxnum base prices. A value of 1.5 means you charge 50% more than cost.
                </p>
                <Input
                  type="number"
                  min="1"
                  step="0.1"
                  value={multiplier}
                  onChange={(e) => setMultiplier(e.target.value)}
                  className="rounded-xl max-w-xs"
                />
                {multiplier && Number(multiplier) > 0 && (
                  <p className="text-xs text-muted-foreground mt-2">
                    Example: A $1.00 cost number will be sold for <span className="font-semibold text-primary">${(1 * Number(multiplier)).toFixed(2)}</span>
                  </p>
                )}
              </div>

              <div className="border-t border-border pt-6">
                <label className="text-sm font-medium mb-2 block">Default Country Code</label>
                <p className="text-xs text-muted-foreground mb-3">
                  Default country for number selection (e.g., "us", "187" for USA).
                </p>
                <Input
                  value={defaultCountry}
                  onChange={(e) => setDefaultCountry(e.target.value)}
                  className="rounded-xl max-w-xs"
                  placeholder="us"
                />
              </div>

              <div className="border-t border-border pt-6 flex items-center gap-3">
                <Button
                  className="rounded-xl gap-2 font-semibold"
                  disabled={!hasChanges || saveMutation.isPending}
                  onClick={handleSave}
                >
                  <Save className="w-4 h-4" />
                  {saveMutation.isPending ? "Saving..." : "Save Settings"}
                </Button>
                {hasChanges && (
                  <Button
                    variant="outline"
                    className="rounded-xl gap-2"
                    onClick={handleReset}
                  >
                    <RotateCcw className="w-4 h-4" /> Reset
                  </Button>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}
