# Proxnum Reseller - SMS Service Reseller Platform

**Version:** 2.0.0 Complete Edition  
**License:** Commercial - Requires Valid License Key

## 📋 Overview

Proxnum Reseller is a **complete, enterprise-ready** white-label SaaS solution that allows you to resell Proxnum SMS services to your own customers. This system connects directly to the Proxnum API and provides a professional, feature-rich interface for both you (the reseller) and your clients.

## ✨ Features

### 🎯 Complete Feature Set (50+ Features)

#### Admin Panel (11 Pages)
- **Dashboard**: Real-time statistics and overview
- **Client Management**: Add, manage, and monitor clients
- **Reports & Analytics**: Revenue tracking, top clients, service popularity ⭐ NEW
- **Price Management**: Custom markup per service/country ⭐ NEW
- **Activity Logs**: Complete audit trail with IP tracking ⭐ NEW
- **License Information**: System status and verification ⭐ NEW
- **API Statistics**: Usage monitoring and error tracking ⭐ NEW
- **Transactions**: Complete financial history
- **Settings**: System configuration
- **Profile**: Admin account management ⭐ NEW

#### Client Dashboard (10 Pages)
- **Dashboard Home**: Personal statistics overview
- **Buy Numbers**: Virtual number purchase
- **Activations**: SMS code retrieval with auto-refresh ⭐ NEW
- **Rental Numbers**: Long-term number rentals ⭐ NEW
- **Transactions**: Personal financial history ⭐ NEW
- **Notifications**: Alerts and activity timeline ⭐ NEW
- **API Documentation**: Complete developer guide ⭐ NEW
- **Profile & Settings**: Account management with API keys ⭐ NEW

###  Security Features
- **License Verification**: Secure, cached verification (24h cache, 72h grace)
- **CSRF Protection**: Token-based form protection
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization
- **Password Security**: bcrypt hashing
- **Activity Logging**: Complete audit trail with IP tracking
- **API Authentication**: Bearer token support
- **Session Security**: httponly, secure flags
- **Code Encryption**: ionCube ready

###  Business Intelligence
- **Revenue Analytics**: Daily/monthly/cumulative tracking
- **Client Reports**: Top spenders, activity patterns
- **Service Analytics**: Popular services and countries
- **Growth Metrics**: Month-over-month comparison
- **API Monitoring**: Call tracking and error rates
- **Cost Analysis**: Average costs and total spending

### 🚀 Technical Features
- **MVC Architecture**: Clean, maintainable code structure
- **Responsive Design**: Works on all devices
- **AJAX Powered**: Real-time updates without page reload
- **Auto-refresh**: Smart polling for pending operations
- **Pagination**: Efficient data loading
- **Empty States**: Professional UI for all scenarios
- **API Integration**: Both consuming and providing APIs
- **Database Abstraction**: PDO-based layer

##  Pricing

- **Monthly License**: $20/month
- **Lifetime License**: $300 one-time payment

## 📦 Installation

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3+
- Apache/Nginx web server
- ionCube Loader (for encrypted files)
- cURL extension enabled
- JSON extension enabled
- PDO MySQL extension

### Installation Steps

1. **Download** the `proxnum-reseller.zip` file
2. **Upload** to your hosting root directory
3. **Extract** the zip file
4. **Navigate** to `http://yourdomain.com/install`
5. **Follow** the installation wizard:
   - Enter license key (purchase from Proxnum)
   - Enter your Proxnum API key (from your profile)
   - Configure database details
   - Set admin credentials
6. **Complete** installation and delete the `/install` folder

## 🔑 Getting Started

### Obtaining Your License Key

1. Visit [Proxnum.com](https://proxnum.com)
2. Navigate to **Reseller Licenses**
3. Purchase a monthly or lifetime license
4. Copy your license key

### Getting Your API Key

1. Login to your Proxnum account
2. Go to **Profile** → **API Keys**
3. Click **Generate New API Key**
4. Copy the API key for installation

### First Login

After installation:
- URL: `http://yourdomain.com/admin`
- Use the admin credentials you set during installation

## 🎯 Usage

### For Resellers

1. **Add Clients**: Create customer accounts from admin panel
2. **Manage Balances**: Add credits to client accounts
3. **View Transactions**: Monitor all activities
4. **Set Pricing**: Configure your markup (optional feature)
5. **Generate Reports**: Export transaction data

### For Clients

1. **Login**: Use credentials provided by reseller
2. **Add Balance**: Request balance from reseller
3. **Purchase SMS**: Buy virtual numbers for verifications
4. **View History**: Check all transactions
5. **Get Codes**: Receive SMS codes instantly

## 🔒 Security Features

- Encrypted core files (ionCube)
- Secure license validation
- SQL injection protection
- XSS prevention
- CSRF tokens
- Password hashing (bcrypt)
- Session security
- API key encryption

## 📞 Support

For technical support:
- **Email**: support@proxnum.com
- **Documentation**: [docs.proxnum.com](https://proxnum.com)
- **API Status**: Check Proxnum API dashboard

## ⚠️ Important Notes

- **License Required**: System will not work without valid license
- **API Key Required**: Must have active Proxnum account
- **Backup Regularly**: Always backup your database
- **Keep Updated**: Install updates when available
- **Do Not Modify**: Encrypted files to prevent cracking

## 📄 License Agreement

This software is licensed, not sold. By installing this software, you agree to:
- Use with a valid license key only
- Not attempt to crack or reverse engineer
- Not redistribute or resell the software itself
- Follow Proxnum terms of service

## 🔄 Updates

Updates are released regularly. To update:
1. Backup your database and files
2. Download the latest version
3. Replace files (keep `/config` folder)
4. Run update script if provided

## 🚀 Roadmap

- Mobile app integration
- Automated pricing markup
- Multi-currency support
- Advanced reporting
- Webhook notifications
- API for resellers

---

**Developed for Proxnum** | © 2026 All Rights Reserved
