interface EmailOptions {
  to: string;
  subject: string;
  text: string;
  html?: string;
}

export async function sendEmail(options: EmailOptions): Promise<boolean> {
  if (process.env.SMTP_HOST && process.env.SMTP_USER && process.env.SMTP_PASS) {
    console.log(`[EMAIL] Would send email to ${options.to}: ${options.subject}`);
    console.log(`[EMAIL] SMTP is configured but sending is not yet implemented.`);
    console.log(`[EMAIL] Configure a nodemailer transport when ready.`);
    return false;
  }

  console.log(`[EMAIL STUB] To: ${options.to}`);
  console.log(`[EMAIL STUB] Subject: ${options.subject}`);
  console.log(`[EMAIL STUB] Body: ${options.text}`);
  return false;
}

export async function sendPasswordResetEmail(email: string, token: string): Promise<boolean> {
  const resetUrl = `${process.env.APP_URL || "http://localhost:5000"}/#/forgot-password?token=${token}`;
  return sendEmail({
    to: email,
    subject: "GetOTPs - Password Reset",
    text: `Reset your password using this link: ${resetUrl}\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.`,
    html: `<p>Reset your password by clicking the link below:</p><p><a href="${resetUrl}">Reset Password</a></p><p>This link expires in 1 hour.</p><p>If you didn't request this, ignore this email.</p>`,
  });
}

export function isEmailConfigured(): boolean {
  return !!(process.env.SMTP_HOST && process.env.SMTP_USER && process.env.SMTP_PASS);
}
