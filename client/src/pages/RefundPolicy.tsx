import { Link } from "wouter";
import { Logo } from "@/components/Logo";

export default function RefundPolicy() {
  return (
    <main className="min-h-screen bg-background text-foreground">
      <div className="max-w-4xl mx-auto px-6 py-10">
        <div className="flex items-center justify-between mb-8">
          <Link href="/">
            <a className="inline-flex items-center gap-2">
              <Logo size={28} />
            </a>
          </Link>
          <Link href="/">
            <a className="text-sm text-primary hover:underline">Back to home</a>
          </Link>
        </div>

        <article className="space-y-8">
          <header>
            <h1 className="text-3xl font-bold mb-2">Refund Policy</h1>
            <p className="text-sm text-muted-foreground">
              Placeholder policy copy for legal review. Last updated: April 24, 2026.
            </p>
          </header>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold">1. OTP no-message refunds</h2>
            <p className="text-sm leading-7 text-muted-foreground">
              If an OTP rental expires without any SMS received, GetOTPs automatically credits your wallet
              balance for the full paid amount. Credits are applied to your account ledger and appear in
              transaction history.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold">2. User-cancelled orders</h2>
            <p className="text-sm leading-7 text-muted-foreground">
              Orders that remain pending may be eligible for cancellation and refund based on provider
              constraints. Once a code is delivered or an activation is consumed, the order is non-refundable.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold">3. Rental products</h2>
            <p className="text-sm leading-7 text-muted-foreground">
              Time-based rental products are non-refundable after activation unless required by law or when
              technical delivery failure is confirmed by platform logs.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold">4. Deposit disputes</h2>
            <p className="text-sm leading-7 text-muted-foreground">
              For top-up/deposit disputes, contact support with transaction references, wallet hash, and
              account email. Fraudulent or duplicated dispute activity may lead to account restrictions.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold">5. Contact</h2>
            <p className="text-sm leading-7 text-muted-foreground">
              Send refund inquiries to support@getotps.online. Final response times and outcomes are subject
              to compliance checks and provider confirmation data.
            </p>
          </section>
        </article>
      </div>
    </main>
  );
}
