import { randomUUID } from "crypto";

type SendEmailArgs = {
  to: string;
  subject: string;
  html: string;
  text?: string;
};

const RESEND_API_URL = "https://api.resend.com/emails";

function sanitizeHost(value: string): string {
  return value.replace(/\/+$/, "");
}

function getAppBaseUrl(): string {
  return sanitizeHost(process.env.APP_BASE_URL || "http://localhost:5000");
}

function getFromEmail(): string {
  return process.env.EMAIL_FROM || "GetOTPs <no-reply@getotps.online>";
}

async function sendWithResend({ to, subject, html, text }: SendEmailArgs): Promise<void> {
  const apiKey = process.env.RESEND_API_KEY;
  if (!apiKey) {
    throw new Error("RESEND_API_KEY is not configured");
  }

  const response = await fetch(RESEND_API_URL, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${apiKey}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      from: getFromEmail(),
      to: [to],
      subject,
      html,
      text,
    }),
  });

  if (!response.ok) {
    const errorBody = await response.text();
    throw new Error(`Resend error ${response.status}: ${errorBody}`);
  }
}

function getVerificationLink(token: string): string {
  return `${getAppBaseUrl()}/verify-email?token=${encodeURIComponent(token)}`;
}

function getResetPasswordLink(token: string): string {
  return `${getAppBaseUrl()}/reset-password?token=${encodeURIComponent(token)}`;
}

export function createOpaqueToken(): string {
  return randomUUID() + randomUUID();
}

export async function sendVerificationEmail(args: { email: string; username: string; token: string }): Promise<void> {
  const link = getVerificationLink(args.token);
  await sendWithResend({
    to: args.email,
    subject: "Verify your GetOTPs email",
    text: `Hi ${args.username}, verify your email by opening this link: ${link}`,
    html: `
      <div style="font-family:Arial,sans-serif;line-height:1.5;color:#111">
        <h2>Verify your email</h2>
        <p>Hi ${args.username},</p>
        <p>Welcome to GetOTPs. Please verify your email to secure your account.</p>
        <p><a href="${link}">Verify Email</a></p>
        <p>This link expires in 24 hours.</p>
      </div>
    `,
  });
}

export async function sendPasswordResetEmail(args: { email: string; username: string; token: string }): Promise<void> {
  const link = getResetPasswordLink(args.token);
  await sendWithResend({
    to: args.email,
    subject: "Reset your GetOTPs password",
    text: `Hi ${args.username}, reset your password using this link: ${link}`,
    html: `
      <div style="font-family:Arial,sans-serif;line-height:1.5;color:#111">
        <h2>Reset your password</h2>
        <p>Hi ${args.username},</p>
        <p>We received a request to reset your password.</p>
        <p><a href="${link}">Reset Password</a></p>
        <p>This link expires in 30 minutes. If you did not request this, you can ignore this email.</p>
      </div>
    `,
  });
}
