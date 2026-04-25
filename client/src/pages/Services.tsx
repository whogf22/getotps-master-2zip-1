import { useMemo, useState } from "react";
import { Link } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { Logo } from "@/components/Logo";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Search, ArrowRight } from "lucide-react";

interface ServiceItem {
  id: number;
  name: string;
  slug: string;
  price: string;
  category: string | null;
  isActive: number;
}

interface CountryItem {
  id?: number;
  code?: string;
  name: string;
}

type PriceRange = "all" | "under-1" | "1-2" | "2-plus";

export default function Services() {
  const [search, setSearch] = useState("");
  const [countryFilter, setCountryFilter] = useState("all");
  const [priceFilter, setPriceFilter] = useState<PriceRange>("all");

  const { data: services = [], isLoading } = useQuery<ServiceItem[]>({
    queryKey: ["/api/services"],
  });
  const { data: countries = [] } = useQuery<CountryItem[]>({
    queryKey: ["/api/countries"],
  });

  const normalizedCountries = useMemo(() => {
    return countries
      .map((country) => ({
        code: (country.code || country.id?.toString() || "").toLowerCase(),
        name: country.name,
      }))
      .filter((country) => country.code && country.name)
      .sort((a, b) => a.name.localeCompare(b.name));
  }, [countries]);

  const filteredServices = useMemo(() => {
    return services.filter((service) => {
      const matchesSearch =
        search.trim().length === 0 ||
        service.name.toLowerCase().includes(search.toLowerCase()) ||
        (service.category || "").toLowerCase().includes(search.toLowerCase());

      const servicePrice = Number(service.price);
      const matchesPrice =
        priceFilter === "all" ||
        (priceFilter === "under-1" && servicePrice < 1) ||
        (priceFilter === "1-2" && servicePrice >= 1 && servicePrice < 2) ||
        (priceFilter === "2-plus" && servicePrice >= 2);

      // Current backend service list is global; country filter is UX-level for future per-country pricing.
      const matchesCountry = countryFilter === "all" || normalizedCountries.some((country) => country.code === countryFilter);

      return matchesSearch && matchesPrice && matchesCountry;
    });
  }, [countryFilter, normalizedCountries, priceFilter, search, services]);

  return (
    <div className="min-h-screen bg-[#020810] text-white">
      <header className="border-b border-white/10 bg-black/20 backdrop-blur">
        <div className="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
          <Link href="/">
            <a><Logo size={28} /></a>
          </Link>
          <div className="flex gap-2">
            <Link href="/pricing"><Button variant="outline" className="border-white/20 text-white hover:bg-white/10">Pricing</Button></Link>
            <Link href="/register"><Button className="bg-cyan-500 hover:bg-cyan-400 text-black font-semibold">Get Started</Button></Link>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-10">
        <div className="mb-8">
          <h1 className="text-3xl sm:text-4xl font-bold">Supported Services</h1>
          <p className="text-white/60 mt-2">Search and filter supported OTP services by country and price range.</p>
        </div>

        <div className="grid gap-3 md:grid-cols-3 mb-8">
          <div className="relative md:col-span-1">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-white/50" />
            <Input
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Search service name or category"
              className="pl-9 bg-white/5 border-white/15 text-white placeholder:text-white/45"
            />
          </div>

          <select
            value={countryFilter}
            onChange={(event) => setCountryFilter(event.target.value)}
            className="h-10 rounded-md bg-white/5 border border-white/15 px-3 text-sm text-white"
          >
            <option value="all">All countries</option>
            {normalizedCountries.map((country) => (
              <option key={country.code} value={country.code}>{country.name}</option>
            ))}
          </select>

          <select
            value={priceFilter}
            onChange={(event) => setPriceFilter(event.target.value as PriceRange)}
            className="h-10 rounded-md bg-white/5 border border-white/15 px-3 text-sm text-white"
          >
            <option value="all">All prices</option>
            <option value="under-1">Under $1.00</option>
            <option value="1-2">$1.00 - $1.99</option>
            <option value="2-plus">$2.00+</option>
          </select>
        </div>

        <p className="text-sm text-white/55 mb-4">{filteredServices.length} services found</p>

        {isLoading ? (
          <div className="text-white/60">Loading services...</div>
        ) : filteredServices.length === 0 ? (
          <div className="rounded-xl border border-white/10 bg-white/5 p-6 text-white/70">No services match the selected filters.</div>
        ) : (
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {filteredServices.map((service) => (
              <div key={service.id} className="rounded-xl border border-white/10 bg-white/5 p-4">
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <h2 className="text-lg font-semibold">{service.name}</h2>
                    <p className="text-xs text-cyan-200/70 uppercase tracking-wide mt-1">{service.category || "General"}</p>
                  </div>
                  <p className="text-cyan-300 font-bold">${Number(service.price).toFixed(2)}</p>
                </div>
                <div className="mt-4 flex justify-end">
                  <Link href="/register">
                    <a className="inline-flex items-center gap-1 text-sm text-cyan-200 hover:text-cyan-100">
                      Start now <ArrowRight className="w-3.5 h-3.5" />
                    </a>
                  </Link>
                </div>
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
