# Email/SMTP Setup for Existing Installation

## Quick Fix - Add SMTP to Your Database

Since you already have a working database, just run this one SQL file to add email support:

### Option 1: Using MySQL Command Line
```bash
C:\xampp\mysql\bin\mysql.exe -u root proxnum_reseller < install\add_email_smtp.sql
```

### Option 2: Using phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your `proxnum_reseller` database
3. Click the "Import" tab
4. Choose file: `install/add_email_smtp.sql`
5. Click "Go"

## What This Adds

✅ **Email Templates Table**
- Stores customizable email templates
- Includes 5 default templates (welcome, low_balance, activation_completed, admin_new_user, password_reset)

✅ **12 SMTP Settings**
- `mail_smtp_enabled` - Enable/disable SMTP
- `mail_smtp_host` - Your SMTP server
- `mail_smtp_port` - SMTP port (default: 587)
- `mail_smtp_username` - SMTP username
- `mail_smtp_password` - SMTP password  
- `mail_smtp_encryption` - Encryption type (tls/ssl)
- `mail_smtp_auth` - Enable authentication
- `mail_from_address` - Sender email
- `mail_from_name` - Sender name
- `mail_signup_enabled` - Send welcome emails
- `mail_low_balance_enabled` - Send low balance alerts
- `mail_activation_enabled` - Send activation notifications

## After Import

1. **Go to Admin Dashboard → Settings**
2. **Scroll to "Email/SMTP Configuration"**
3. **Configure your SMTP settings:**
   - Enable SMTP
   - Enter your SMTP host (e.g., smtp.gmail.com)
   - Enter port (587 for TLS, 465 for SSL)
   - Enter username and password
   - Set your from address and name

4. **Click "Send Test Email"** to verify everything works

## For New Installations

The main [install/schema.sql](install/schema.sql) now includes email/SMTP support by default. No additional steps needed.

## Troubleshooting

### Still Getting Database Errors?

1. **Verify the migration ran:**
   ```sql
   SELECT * FROM settings WHERE `key` LIKE 'mail_%';
   ```
   You should see 12 email settings.

2. **Check email_templates table exists:**
   ```sql
   SHOW TABLES LIKE 'email_templates';
   ```

3. **Clear your browser cache** and reload the admin settings page

### Test Email Not Working?

- Make sure SMTP is enabled (`mail_smtp_enabled` = 1)
- Verify your SMTP credentials are correct
- Check that your email provider allows SMTP access
- For Gmail: Enable "Less secure app access" or use App Password
- Check PHP error logs: `C:\xampp\htdocs\pxnme\proxnum-reseller\error.log`

## Email Features

Once configured, the system will automatically send:
- ✉️ Welcome emails when new users sign up
- ⚠️ Low balance alerts when balance drops below threshold
- ✅ Activation confirmation emails with SMS codes
- 👤 Admin notifications for new registrations

All emails use customizable templates from the `email_templates` table.
