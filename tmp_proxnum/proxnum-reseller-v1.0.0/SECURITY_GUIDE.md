# Proxnum Reseller - Security Hardening Guide

## 🔒 Essential Security Measures

### 1. SSL/TLS Certificate (CRITICAL)

**Install SSL Certificate:**
```bash
# Using Let's Encrypt (Free)
sudo certmod --apache -d yourdomain.com
```

**Force HTTPS in .htaccess:**
```apache
# Uncomment these lines in .htaccess
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. File Permissions

**Recommended Permissions:**
```bash
# Files
find . -type f -exec chmod 644 {} \;

# Directories
find . -type d -exec chmod 755 {} \;

# Specific folders (writable)
chmod 775 cache logs
chmod 755 config

# Protect config files
chmod 644 config/*.php

# Protect htaccess
chmod 644 .htaccess
```

### 3. Protect Sensitive Directories

**config/.htaccess:**
```apache
Order deny,allow
Deny from all
```

**logs/.htaccess:**
```apache
Order deny,allow
Deny from all
```

**cache/.htaccess:**
```apache
Order deny,allow
Deny from all
```

### 4. Database Security

**Secure Database User:**
```sql
-- Create dedicated user
CREATE USER 'proxnum_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only needed permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON proxnum_db.* TO 'proxnum_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;
```

**Use Strong Passwords:**
- Minimum 16 characters
- Mix of letters, numbers, symbols
- Change regularly

### 5. Protect Against Common Attacks

**SQL Injection:**
-  Already protected (PDO prepared statements)
- Never use raw SQL with user input

**XSS (Cross-Site Scripting):**
-  Already protected (htmlspecialchars on output)
- Validate all inputs

**CSRF (Cross-Site Request Forgery):**
-  Already protected (CSRF tokens)
- All forms include tokens

**Directory Traversal:**
-  Protected by .htaccess
- Sanitized file paths

### 6. PHP Security Settings

**php.ini or .htaccess:**
```ini
# Hide PHP version
expose_php = Off

# Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

# File upload limits
upload_max_filesize = 10M
post_max_size = 10M

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

# Error handling (production)
display_errors = Off
log_errors = On
error_log = /path/to/logs/error.log
```

### 7. API Key Security

**Protect Your API Keys:**
```php
// Never commit config files
// config/app.php contains sensitive data

// Encrypt sensitive data
$encrypted = Helper::encrypt($apiKey);

// Use environment variables (recommended)
define('PROXNUM_API_KEY', getenv('PROXNUM_API_KEY'));
```

### 8. Rate Limiting

**Protect Against Brute Force:**

Add to login controller:
```php
// Track failed attempts
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Limit attempts
if ($_SESSION['login_attempts'] >= 5) {
    $this->json(['success' => false, 'message' => 'Too many attempts. Try again in 15 minutes']);
}
```

### 9. Regular Backups

**Automated Backup Script:**
```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"

# Database backup
mysqldump -u username -p'password' database_name > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /path/to/site

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

**Schedule with cron:**
```cron
# Daily at 2 AM
0 2 * * * /path/to/backup.sh
```

### 10. Monitoring & Logging

**Monitor Suspicious Activity:**
```php
// Log failed logins
Helper::logActivity('login_failed', 'Failed login from IP: ' . Helper::getClientIp());

// Log admin actions
Helper::logActivity('client_added', 'Admin added new client');

// Monitor unusual patterns
// - Multiple failed logins
// - Unusual purchase amounts
// - Strange activation patterns
```

### 11. Two-Factor Authentication (Future)

**Planned Feature:**
- Google Authenticator
- SMS verification
- Email verification

### 12. IP Whitelisting (Optional)

**Restrict Admin Access:**
```apache
# In .htaccess
<FilesMatch "admin">
    Order deny,allow
    Deny from all
    Allow from 1.2.3.4
    Allow from 5.6.7.8
</FilesMatch>
```

### 13. Security Headers

**Add to .htaccess:**
```apache
# Already included:
Header set X-XSS-Protection "1; mode=block"
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

### 14. Keep Software Updated

**Update Checklist:**
- [ ] PHP version
- [ ] MySQL version
- [ ] Apache/Nginx version
- [ ] Proxnum Reseller software
- [ ] Operating system

### 15. License Security

**License Verification:**
- Verified every 24 hours
- Cannot be bypassed
- Domain-locked
- Encrypted storage

**If License Compromised:**
1. Contact support immediately
2. Generate new license
3. Update installation
4. Monitor for unauthorized use

---

## 🚨 Security Incident Response

### If Hacked:

1. **Immediate Actions:**
   - Take site offline
   - Change all passwords
   - Review access logs
   - Check file modifications

2. **Investigation:**
   - Check logs/error.log
   - Review activity_logs table
   - Identify entry point
   - Document everything

3. **Recovery:**
   - Restore from clean backup
   - Apply security patches
   - Update all credentials
   - Monitor closely

4. **Prevention:**
   - Implement additional security
   - Update software
   - Review permissions
   - Enable monitoring

### Report Issues

Security issues? Contact:
- **Email**: security@proxnum.com
- **Priority**: High
- **Response**: Within 24 hours

---

##  Security Checklist

**Before Going Live:**

- [ ] SSL certificate installed
- [ ] HTTPS enforced
- [ ] Strong passwords set
- [ ] File permissions correct
- [ ] Installation folder deleted
- [ ] Database user has minimal permissions
- [ ] Error display disabled (production)
- [ ] Logging enabled
- [ ] Backups configured
- [ ] .htaccess protecting sensitive folders
- [ ] Security headers enabled
- [ ] API keys secured
- [ ] License verified
- [ ] Tested on staging first

**Monthly Maintenance:**

- [ ] Review security logs
- [ ] Check for updates
- [ ] Test backups
- [ ] Review user accounts
- [ ] Change important passwords
- [ ] Monitor disk space
- [ ] Check SSL certificate expiry

---

**Remember**: Security is an ongoing process, not a one-time setup!
