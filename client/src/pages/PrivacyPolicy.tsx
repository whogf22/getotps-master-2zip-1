import { Link } from "wouter";
import { Logo } from "@/components/Logo";

export default function PrivacyPolicy() {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <header className="border-b border-border">
        <div className="mx-auto max-w-4xl px-4 py-4 flex items-center justify-between">
          <Link href="/">
            <a>
              <Logo size={28} />
            </a>
          </Link>
          <Link href="/">
            <a className="text-sm text-primary hover:underline">Back to Home</a>
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-4xl px-4 py-10 space-y-8">
        <div>
          <h1 className="text-3xl font-bold">Privacy Policy</h1>
          <p className="text-sm text-muted-foreground mt-2">
            Last updated: {new Date().toISOString().slice(0, 10)}
          </p>
        </div>

        <section className="space-y-3">
          <h2 className="text-xl font-semibold">Overview</h2>
          <p className="text-sm text-muted-foreground leading-7">
            This placeholder policy explains how GetOTPs may collect, use, and protect account,
            transaction, and service usage data. Replace this content with legal counsel-approved text
            before production launch.
          </p>
        </section>

        <section className="space-y-3">
          <h2 className="text-xl font-semibold">Data We Collect</h2>
          <ul className="list-disc pl-6 text-sm text-muted-foreground leading-7 space-y-1">
            <li>Account details such as username and email.</li>
            <li>Order and rental metadata required to provide OTP services.</li>
            <li>Payment and transaction records needed for billing and refunds.</li>
          </ul>
        </section>

        <section className="space-y-3">
          <h2 className="text-xl font-semibold">How We Use Data</h2>
          <p className="text-sm text-muted-foreground leading-7">
            Placeholder: we use data to operate the platform, prevent abuse, handle support requests,
            and satisfy legal obligations. Add explicit retention timelines and lawful bases here.
          </p>
        </section>
      </main>
    </div>
  );
}
