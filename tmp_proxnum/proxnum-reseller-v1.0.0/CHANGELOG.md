# Proxnum Reseller - Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-02-17 - Complete Edition

### 🎉 Major Release - Feature Complete

This is a major update that transforms the system into a complete, enterprise-ready SaaS platform with 50+ new features.

### ✨ New Admin Features
- **Reports & Analytics** - Complete business intelligence dashboard
  - Revenue by day visualization with bar charts
  - Month-over-month comparison and growth tracking
  - Top clients ranking by spending
  - Popular services and countries analytics
  - Date range filtering for custom reports
  
- **Price Management** - Flexible pricing system
  - Custom markup per service/country
  - Default markup configuration
  - Dynamic price override system
  - Bulk markup management
  
- **Activity Logs Viewer** - Complete audit trail
  - All user actions logged with timestamps
  - IP address and user agent tracking
  - Filter by user and action type
  - Error highlighting
  - Pagination support (50 per page)
  
- **License Information** - System status dashboard
  - License key and email display
  - Verification status and timestamps
  - System information (PHP, MySQL, disk space)
  - Cache expiration tracking
  
- **API Statistics** - Usage monitoring
  - API call tracking by service/country
  - Success/failure rate analysis
  - Cost tracking per endpoint
  - Error log summary
  
- **Admin Profile** - Account management
  - Update name and email
  - Change password with verification
  - Account details display
  - Activity tracking

### 🚀 New Client Features
- **Rental Numbers** - Long-term number rentals
  - Buy rental numbers for 7/14/30 days
  - View received SMS messages
  - Auto-refresh for active rentals (30s)
  - Cancel rental anytime
  - Expiration tracking
  
- **Activations Page** - Complete activation management
  - List all virtual number activations
  - Real-time status checking
  - SMS code display
  - Auto-refresh for pending (10s)
  - Cancel option for pending activations
  
- **Notifications** - Alert system
  - Low balance warnings
  - Expiring rentals alerts (24h)
  - Recent activity timeline
  - Activity icons and categorization
  
- **API Documentation** - Developer portal
  - Complete API reference
  - Endpoint documentation
  - Request/response examples
  - Authentication guide
  - Code samples and error handling
  
- **Client Profile** - Personal settings
  - Update profile information
  - Change password
  - API key generation
  - Copy to clipboard feature
  - Account statistics
  
- **Transactions Page** - Financial history
  - Personal transaction list
  - Transaction summary (credits/purchases)
  - Current balance display
  - Pagination support

### 🔧 Core Enhancements
- Added `api_token` column to users table
- Added `last_login` column for tracking
- Enhanced ProxnumApi class with rental methods:
  - `getRentalPrices()` - Fetch rental pricing
  - `buyRental()` - Purchase rental number
  - `getRentalMessages()` - Retrieve SMS messages
  - `getRentalStatus()` - Check rental status
  - `cancelRental()` - Cancel rental subscription
- Added `Helper::formatBytes()` for file size formatting
- Enhanced error handling across all controllers

###  New View Files (21 Total)
**Admin Views (11):**
1. dashboard.php - Statistics overview
2. clients.php - Client management
3. reports.php ⭐ NEW
4. price_management.php ⭐ NEW
5. activity_logs.php ⭐ NEW
6. license_info.php ⭐ NEW
7. api_stats.php ⭐ NEW
8. transactions.php ⭐ NEW
9. settings.php ⭐ NEW
10. profile.php ⭐ NEW

**Client Views (10):**
1. index.php - Dashboard home
2. buy.php - Purchase numbers
3. activations.php ⭐ NEW
4. rentals.php ⭐ NEW
5. rental_detail.php ⭐ NEW
6. transactions.php ⭐ NEW
7. notifications.php ⭐ NEW
8. api_docs.php ⭐ NEW
9. profile.php ⭐ NEW

### 🎨 UI/UX Improvements
- Updated sidebar navigation with all new pages
- Added professional charts and graphs
- Implemented empty states for all pages
- Added progress bars and percentage displays
- Modal dialogs for forms
- Color-coded badges for statuses
- Responsive tables with pagination
- Copy-to-clipboard functionality
- Auto-refresh for real-time updates
- Professional alert boxes and info panels

### 📚 Documentation Updates
- Added COMPLETE_FEATURES.md - Comprehensive feature list
- Updated FILE_STRUCTURE.md with new files
- Enhanced README.md with version 2.0 info
- Updated INSTALLATION_GUIDE.md

### 🔒 Security Enhancements
- Enhanced activity logging for all user actions
- IP tracking on all authentication events
- API key security with regeneration
- Improved CSRF protection coverage
- Session security hardening

### 📈 Statistics & Analytics
- Revenue tracking (daily/monthly/cumulative)
- Client analytics (top spenders, patterns)
- Service popularity metrics
- Geographic data analysis
- Growth metrics and comparisons
- API usage monitoring
- Error rate tracking

### 🔧 Performance
- Optimized database queries
- Added indexes for common lookups
- Efficient pagination
- AJAX for real-time updates
- Smart auto-refresh intervals
- License caching optimization

---

## [1.0.0] - 2026-02-15

### Added
- 🎉 Initial release
- ✨ Complete installer system with wizard interface
-  Secure license verification system
- 👥 Admin panel for reseller management
- 📱 Client dashboard for end users
-  Balance management system
- 🛒 Virtual number purchase integration
-  Transaction history and logging
- 🔒 Advanced security features
- 📝 Activity logging
- 🎨 Professional, modern UI design
- 📱 Responsive layout for all devices
- 🌐 Proxnum API integration
- 💳 Real-time pricing and availability

### Security
- SQL injection protection via PDO
- XSS prevention
- CSRF token protection
- Password hashing with bcrypt
- API key encryption
- Session security
- File permission protection
- License domain locking

### Developer
- MVC architecture
- Clean, documented code
- PSR-compatible structure
- Extensible design
- Helper functions library
- Database abstraction layer
- SMS notifications
- Advanced reporting
- Export functionality
- API for resellers

### [1.2.0] - Planned
- Payment gateway integration
- Automated pricing markup
- Multi-currency support
- Custom branding options
- Theme customization

### [2.0.0] - Future
- Mobile application
- Multi-language support
- Advanced analytics
- Two-factor authentication
- Webhook notifications
- White-label options

---

## Version Numbering

We use [Semantic Versioning](https://semver.org/):
- **MAJOR**: Incompatible API changes
- **MINOR**: New features, backwards compatible
- **PATCH**: Bug fixes, backwards compatible

---

## Support

- **Email**: support@proxnum.com
- **Documentation**: See INSTALLATION_GUIDE.md
- **Security**: See SECURITY_GUIDE.md
