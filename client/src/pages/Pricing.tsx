import { useMemo } from "react";
import { Link, useLocation } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { Logo } from "@/components/Logo";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ArrowRight } from "lucide-react";

type ServiceRow = {
  id: number;
  name: string;
  slug: string;
  category: string | null;
  price: string;
  isActive?: number;
};

const CATEGORY_ORDER = [
  "Messaging",
  "Social",
  "Tech",
  "Finance",
  "Crypto",
  "Shopping",
  "Food",
  "Transport",
  "Travel",
  "Entertainment",
  "Dating",
  "Other",
];

export default function Pricing() {
  const [, navigate] = useLocation();
  const { data: services = [] } = useQuery<ServiceRow[]>({
    queryKey: ["/api/services"],
  });

  const grouped = useMemo(() => {
    const map = new Map<string, ServiceRow[]>();

    for (const service of services) {
      const category = service.category || "Other";
      const list = map.get(category) ?? [];
      list.push(service);
      map.set(category, list);
    }

    return Array.from(map.entries())
      .sort(([a], [b]) => {
        const aIndex = CATEGORY_ORDER.includes(a) ? CATEGORY_ORDER.indexOf(a) : 999;
        const bIndex = CATEGORY_ORDER.includes(b) ? CATEGORY_ORDER.indexOf(b) : 999;
        return aIndex - bIndex;
      })
      .map(([category, list]) => ({
        category,
        items: list.sort((a, b) => parseFloat(a.price) - parseFloat(b.price) || a.name.localeCompare(b.name)),
      }));
  }, [services]);

  return (
    <div className="min-h-screen bg-background">
      <header className="border-b border-border">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
          <button onClick={() => navigate("/")} className="text-left">
            <Logo size={28} />
          </button>
          <div className="flex items-center gap-2">
            <Link href="/login">
              <Button variant="ghost" size="sm">Sign in</Button>
            </Link>
            <Link href="/register">
              <Button size="sm">Get started</Button>
            </Link>
          </div>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-4 py-10 space-y-8">
        <div className="space-y-2">
          <h1 className="text-3xl font-bold">Pricing</h1>
          <p className="text-muted-foreground">
            Live per-service rates. Final checkout price may vary by country availability and provider-side constraints.
          </p>
        </div>

        {grouped.length === 0 ? (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No pricing data is available yet.
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-6">
            {grouped.map((section) => (
              <Card key={section.category}>
                <CardHeader className="pb-2">
                  <CardTitle className="text-lg">{section.category}</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                      <thead>
                        <tr className="border-b border-border text-left text-muted-foreground">
                          <th className="py-2 pr-3 font-medium">Service</th>
                          <th className="py-2 pr-3 font-medium">Slug</th>
                          <th className="py-2 font-medium text-right">Rate (USD)</th>
                        </tr>
                      </thead>
                      <tbody>
                        {section.items.map((service) => (
                          <tr key={service.id} className="border-b border-border/60 last:border-0">
                            <td className="py-2 pr-3 font-medium">{service.name}</td>
                            <td className="py-2 pr-3 text-muted-foreground">{service.slug}</td>
                            <td className="py-2 text-right font-semibold">${parseFloat(service.price).toFixed(2)}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        <div className="pt-2">
          <Link href="/register">
            <Button size="lg" className="gap-2">
              Start verifying now
              <ArrowRight className="w-4 h-4" />
            </Button>
          </Link>
        </div>
      </main>
    </div>
  );
}
