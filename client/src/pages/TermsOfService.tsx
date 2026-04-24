import { Link } from "wouter";
import { Logo } from "@/components/Logo";

const LAST_UPDATED = "April 24, 2026";

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <section className="space-y-3">
      <h2 className="text-xl font-semibold text-foreground">{title}</h2>
      <div className="space-y-3 text-sm leading-6 text-muted-foreground">{children}</div>
    </section>
  );
}

export default function TermsOfService() {
  return (
    <div className="min-h-screen bg-background">
      <header className="border-b border-border bg-card/60 backdrop-blur">
        <div className="mx-auto flex h-16 max-w-4xl items-center justify-between px-4">
          <Link href="/">
            <a className="inline-flex items-center">
              <Logo size={28} />
            </a>
          </Link>
          <Link href="/">
            <a className="text-sm font-medium text-primary hover:text-primary/80">Back to homepage</a>
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-4xl px-4 py-10">
        <article className="space-y-8 rounded-2xl border border-border bg-card p-6 md:p-8">
          <header className="space-y-2">
            <h1 className="text-3xl font-bold text-foreground">Terms of Service</h1>
            <p className="text-sm text-muted-foreground">Last updated: {LAST_UPDATED}</p>
            <p className="text-sm text-muted-foreground">
              Placeholder copy for legal review. Replace with counsel-approved terms before production launch.
            </p>
          </header>

          <Section title="1. Acceptance of Terms">
            <p>
              By using GetOTPs, you agree to these Terms of Service and all applicable laws. If you do not agree, do
              not use the platform.
            </p>
          </Section>

          <Section title="2. Services Provided">
            <p>
              GetOTPs provides temporary and rental virtual phone numbers for receiving SMS verification messages. Service
              availability varies by country, carrier, and third-party provider capacity.
            </p>
          </Section>

          <Section title="3. Account Responsibilities">
            <p>
              You are responsible for maintaining the security of your account credentials and for all activity under your
              account. You must provide accurate registration information.
            </p>
          </Section>

          <Section title="4. Acceptable Use">
            <p>
              You may not use the platform for unlawful activity, fraud, abuse, harassment, or circumvention of third-party
              platform policies. We may suspend or terminate accounts that violate these terms.
            </p>
          </Section>

          <Section title="5. Pricing and Payments">
            <p>
              Services are billed on a prepaid balance basis unless otherwise stated. Pricing may change at any time and is
              displayed before purchase. Taxes and compliance obligations remain your responsibility.
            </p>
          </Section>

          <Section title="6. Refunds">
            <p>
              Refund eligibility is governed by the Refund Policy. In general, failed deliveries where no SMS is received
              during the active window may qualify for automatic or manual refund review.
            </p>
          </Section>

          <Section title="7. Service Availability">
            <p>
              We strive for high uptime but do not guarantee uninterrupted service. Maintenance, provider outages, and
              network issues may impact availability.
            </p>
          </Section>

          <Section title="8. Limitation of Liability">
            <p>
              To the maximum extent permitted by law, GetOTPs is not liable for indirect, incidental, special, or consequential
              damages arising from use of the platform.
            </p>
          </Section>

          <Section title="9. Changes to Terms">
            <p>
              We may update these terms from time to time. Continued use after updates constitutes acceptance of the revised
              terms.
            </p>
          </Section>
        </article>
      </main>
    </div>
  );
}
