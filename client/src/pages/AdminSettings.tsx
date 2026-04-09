import { useState, useEffect } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { apiRequest } from "@/lib/queryClient";
import { useToast } from "@/hooks/use-toast";
import { Settings, Save, RotateCcw, Plus, X, Search, Sliders } from "lucide-react";
import type { AdminSettings as AdminSettingsType, ServiceItem } from "@/types/admin";

export default function AdminSettings() {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: settings, isLoading } = useQuery<AdminSettingsType>({
    queryKey: ["/api/admin/settings"],
  });

  const { data: services } = useQuery<ServiceItem[]>({
    queryKey: ["/api/services"],
  });

  const [multiplier, setMultiplier] = useState("");
  const [defaultCountry, setDefaultCountry] = useState("");
  const [svcMultipliers, setSvcMultipliers] = useState<Record<string, string>>({});
  const [newSlug, setNewSlug] = useState("");
  const [newMultiplier, setNewMultiplier] = useState("");
  const [svcSearch, setSvcSearch] = useState("");

  useEffect(() => {
    if (settings) {
      setMultiplier(settings.price_multiplier || "1.5");
      setDefaultCountry(settings.default_country || "us");
      setSvcMultipliers(settings.service_multipliers || {});
    }
  }, [settings]);

  const saveMutation = useMutation({
    mutationFn: async (data: Partial<AdminSettingsType>) => {
      const res = await apiRequest("PUT", "/api/admin/settings", data);
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["/api/admin/settings"] });
      toast({ title: "Settings saved", description: "Platform settings have been updated." });
    },
    onError: (err: Error) => {
      toast({ title: "Error", description: err.message || "Failed to save settings", variant: "destructive" });
    },
  });

  const handleSave = () => {
    saveMutation.mutate({
      price_multiplier: multiplier,
      default_country: defaultCountry,
      service_multipliers: svcMultipliers,
    });
  };

  const handleReset = () => {
    if (settings) {
      setMultiplier(settings.price_multiplier || "1.5");
      setDefaultCountry(settings.default_country || "us");
      setSvcMultipliers(settings.service_multipliers || {});
    }
  };

  const addServiceMultiplier = () => {
    if (!newSlug.trim() || !newMultiplier.trim()) return;
    setSvcMultipliers(prev => ({ ...prev, [newSlug.trim()]: newMultiplier.trim() }));
    setNewSlug("");
    setNewMultiplier("");
  };

  const removeServiceMultiplier = (slug: string) => {
    setSvcMultipliers(prev => {
      const next = { ...prev };
      next[slug] = "";
      return next;
    });
  };

  const origMultipliers = settings?.service_multipliers || {};
  const hasChanges =
    multiplier !== (settings?.price_multiplier || "1.5") ||
    defaultCountry !== (settings?.default_country || "us") ||
    JSON.stringify(svcMultipliers) !== JSON.stringify(origMultipliers);

  const activeMultipliers = Object.entries(svcMultipliers).filter(([, v]) => v && v !== "0" && v !== "");

  const filteredServices = services?.filter((s) =>
    s.name.toLowerCase().includes(svcSearch.toLowerCase()) ||
    s.slug.toLowerCase().includes(svcSearch.toLowerCase())
  ).slice(0, 8) || [];

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
            <h2 className="font-semibold text-sm">Global Configuration</h2>
          </div>

          {isLoading ? (
            <div className="p-6 space-y-6">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-full" />
            </div>
          ) : (
            <div className="p-6 space-y-6">
              <div>
                <label className="text-sm font-medium mb-2 block">Default Price Multiplier</label>
                <p className="text-xs text-muted-foreground mb-3">
                  Global multiplier applied to Proxnum base prices. A value of 1.5 means 50% markup over cost.
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
            </div>
          )}
        </div>

        <div className="rounded-2xl border border-border bg-card overflow-hidden">
          <div className="px-6 py-4 border-b border-border flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-violet-500/15 flex items-center justify-center">
              <Sliders className="w-4 h-4 text-violet-500" />
            </div>
            <div>
              <h2 className="font-semibold text-sm">Per-Service Price Multipliers</h2>
              <p className="text-xs text-muted-foreground">Override the global multiplier for specific services</p>
            </div>
          </div>

          <div className="p-6 space-y-4">
            {activeMultipliers.length > 0 && (
              <div className="space-y-2">
                {activeMultipliers.map(([slug, val]) => {
                  const svc = services?.find((s) => s.slug === slug);
                  return (
                    <div key={slug} className="flex items-center gap-3 px-4 py-3 rounded-xl border border-border bg-muted/20">
                      <div className="flex-1">
                        <p className="text-sm font-semibold">{svc?.name || slug}</p>
                        <p className="text-xs text-muted-foreground font-mono">{slug}</p>
                      </div>
                      <Input
                        type="number"
                        min="0.1"
                        step="0.1"
                        value={val}
                        onChange={(e) => setSvcMultipliers(prev => ({ ...prev, [slug]: e.target.value }))}
                        className="w-24 rounded-lg text-center"
                      />
                      <span className="text-xs text-muted-foreground">x</span>
                      <button
                        onClick={() => removeServiceMultiplier(slug)}
                        className="p-1.5 rounded-lg hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors"
                      >
                        <X className="w-4 h-4" />
                      </button>
                    </div>
                  );
                })}
              </div>
            )}

            <div className="border-t border-border pt-4">
              <p className="text-xs font-medium text-muted-foreground mb-3">Add service override</p>
              <div className="relative mb-3">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  placeholder="Search services..."
                  value={svcSearch}
                  onChange={(e) => setSvcSearch(e.target.value)}
                  className="pl-9 rounded-xl"
                />
              </div>
              {svcSearch && filteredServices.length > 0 && (
                <div className="space-y-1 mb-3 max-h-48 overflow-y-auto">
                  {filteredServices.map((s) => (
                    <button
                      key={s.slug}
                      className="w-full text-left px-3 py-2 rounded-lg hover:bg-muted/50 text-sm flex items-center justify-between transition-colors"
                      onClick={() => {
                        setNewSlug(s.slug);
                        setSvcSearch("");
                      }}
                    >
                      <span className="font-medium">{s.name}</span>
                      <span className="text-xs text-muted-foreground font-mono">{s.slug}</span>
                    </button>
                  ))}
                </div>
              )}
              <div className="flex items-center gap-2">
                <Input
                  placeholder="Service slug"
                  value={newSlug}
                  onChange={(e) => setNewSlug(e.target.value)}
                  className="rounded-xl flex-1"
                />
                <Input
                  type="number"
                  min="0.1"
                  step="0.1"
                  placeholder="2.0"
                  value={newMultiplier}
                  onChange={(e) => setNewMultiplier(e.target.value)}
                  className="rounded-xl w-24"
                />
                <Button
                  size="sm"
                  variant="outline"
                  className="rounded-lg gap-1 shrink-0"
                  disabled={!newSlug.trim() || !newMultiplier.trim()}
                  onClick={addServiceMultiplier}
                >
                  <Plus className="w-3.5 h-3.5" /> Add
                </Button>
              </div>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <Button
            className="rounded-xl gap-2 font-semibold"
            disabled={!hasChanges || saveMutation.isPending}
            onClick={handleSave}
          >
            <Save className="w-4 h-4" />
            {saveMutation.isPending ? "Saving..." : "Save All Settings"}
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
    </DashboardLayout>
  );
}
