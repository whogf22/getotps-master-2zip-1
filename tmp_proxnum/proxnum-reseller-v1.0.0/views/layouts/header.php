<?php
// Get base path for the application  
$basePath = \Core\Helper::url('');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - <?= \Core\Helper::getSetting('site_name', 'Proxnum Reseller') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-deep: #0a2540;
            --primary-soft: #1a3b5d;
            --accent-gold: #c9a03d;
            --accent-teal: #1e7e6c;
            --neutral-100: #f8fafc;
            --neutral-200: #eef2f6;
            --neutral-300: #e2e8f0;
            --neutral-400: #cbd5e1;
            --neutral-600: #475569;
            --neutral-700: #334155;
            --neutral-800: #1e293b;
            --neutral-900: #0f172a;
            --success-light: #e3f9ee;
            --success-dark: #0b7e55;
            --warning-light: #fff3d4;
            --warning-dark: #b45b0a;
            --danger-light: #fee9e7;
            --danger-dark: #b91c1c;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-2xl: 32px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background: linear-gradient(135deg, var(--neutral-100) 0%, var(--neutral-200) 100%);
            color: var(--neutral-900);
            line-height: 1.6;
            min-height: 100vh;
        }

        .layout {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--neutral-100) 0%, var(--neutral-200) 100%);
        }

        /* Top Navigation Bar - Modern Clean Design */
        .navbar {
            background: white;
            border-bottom: 1px solid var(--neutral-300);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .navbar-container {
            display: flex;
            align-items: center;
            padding: 0 40px;
            max-width: 1400px;
            margin: 0 auto;
            height: 80px;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-deep);
            padding: 0;
            margin-right: 40px;
            white-space: nowrap;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-decoration: none;
        }

        .navbar-brand span {
            font-size: 0.75rem;
            font-weight: 500;
            display: block;
            color: var(--neutral-600);
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 4px;
            flex: 1;
            align-items: center;
        }

        .navbar-menu > li {
            position: relative;
        }

        .navbar-menu > li > a {
            display: flex;
            align-items: center;
            padding: 0.5rem 1.2rem;
            color: var(--neutral-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            white-space: nowrap;
            border-radius: var(--radius-lg);
            gap: 0.4rem;
        }

        .navbar-menu > li > a:hover {
            color: var(--primary-deep);
            background: var(--neutral-200);
        }

        .navbar-menu > li > a.active {
            color: var(--primary-deep);
            background: var(--neutral-200);
            font-weight: 600;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: white;
            min-width: 240px;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s ease;
            z-index: 1001;
            border: 1px solid var(--neutral-300);
            overflow: hidden;
        }

        .navbar-menu > li:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu li {
            list-style: none;
        }

        .dropdown-menu li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.2rem;
            color: var(--neutral-600);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--neutral-200);
        }

        .dropdown-menu li:last-child a {
            border-bottom: none;
        }

        .dropdown-menu li a:hover {
            background: var(--neutral-100);
            color: var(--primary-deep);
            padding-left: 1.5rem;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .balance-badge {
            background: linear-gradient(135deg, var(--accent-teal) 0%, #166653 100%);
            padding: 0.6rem 1.2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
            box-shadow: 0 4px 12px rgba(30, 126, 108, 0.2);
            border: none;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--neutral-700);
            padding: 0.4rem 1rem;
            border-radius: 60px;
            background: var(--neutral-200);
            border: 1px solid var(--neutral-300);
        }

        .logout-btn {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            background: var(--danger-dark);
            border-radius: 60px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(185, 28, 28, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .logout-btn:hover {
            background: #991b1b;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(185, 28, 28, 0.3);
        }

        .main-content {
            padding: 2rem 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-deep);
            letter-spacing: -0.01em;
        }

        /* Dashboard Stats */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: var(--radius-xl);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            border: 1px solid var(--neutral-300);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-soft) 100%);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.1);
            border-color: var(--accent-teal);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: var(--neutral-200);
            color: var(--primary-soft);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--neutral-900);
            line-height: 1.2;
            margin-bottom: 0.1rem;
        }

        .stat-info p {
            font-size: 0.85rem;
            color: var(--neutral-600);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        /* Dashboard Panels */
        .dashboard-panel {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            margin-bottom: 2rem;
            border: 1px solid var(--neutral-300);
            overflow: hidden;
        }

        .panel-header {
            padding: 1.5rem 2rem;
            background: var(--neutral-100);
            border-bottom: 1px solid var(--neutral-300);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-deep);
        }

        .panel-body {
            padding: 2rem;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 1rem 1rem;
            background: var(--neutral-100);
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--neutral-600);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 1px solid var(--neutral-300);
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--neutral-200);
            color: var(--neutral-700);
            font-size: 0.95rem;
        }

        .data-table tbody tr:hover {
            background: var(--neutral-100);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.8rem;
            border-radius: 60px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-active, .badge-completed, .badge-credit, .badge-success {
            background: var(--success-light);
            color: var(--success-dark);
        }

        .badge-pending, .badge-warning {
            background: var(--warning-light);
            color: var(--warning-dark);
        }

        .badge-inactive, .badge-cancelled, .badge-expired {
            background: var(--neutral-200);
            color: var(--neutral-600);
        }

        .badge-suspended, .badge-debit, .badge-danger {
            background: var(--danger-light);
            color: var(--danger-dark);
        }

        .badge-purchase {
            background: #e6f0ff;
            color: #1e40af;
        }

        /* Buttons */
        .btn {
            background: linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-soft) 100%);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 60px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 37, 64, 0.15);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10, 37, 64, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-link {
            color: var(--accent-teal);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-link:hover {
            color: var(--primary-soft);
            gap: 0.5rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--neutral-600);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            color: var(--neutral-900);
            background: white;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(201, 160, 61, 0.1);
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid transparent;
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger-dark);
            border-color: rgba(185, 28, 28, 0.1);
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success-dark);
            border-color: rgba(11, 126, 85, 0.1);
        }

        .alert-warning {
            background: var(--warning-light);
            color: var(--warning-dark);
            border-color: rgba(180, 91, 10, 0.1);
        }

        .alert-info {
            background: #e6f0ff;
            color: #1e40af;
            border-color: rgba(30, 64, 175, 0.1);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--neutral-600);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--neutral-800);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        /* Table Responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-lg);
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: var(--neutral-200);
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--neutral-400);
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--neutral-600);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: 1px solid var(--neutral-300);
            color: var(--neutral-600);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            margin-left: auto;
            border-radius: var(--radius-lg);
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            background: var(--neutral-200);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .navbar-container {
                padding: 0 20px;
                flex-wrap: wrap;
                height: auto;
                min-height: 70px;
            }

            .navbar-brand {
                margin-right: 20px;
                font-size: 1.3rem;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .navbar-menu {
                display: none;
                flex-direction: column;
                width: 100%;
                gap: 0;
                background: white;
                padding: 1rem 0;
                border-top: 1px solid var(--neutral-300);
                margin-top: 0.5rem;
            }

            .navbar-menu.active {
                display: flex;
            }

            .navbar-menu > li {
                width: 100%;
            }

            .navbar-menu > li > a {
                padding: 0.8rem 1.5rem;
                border-radius: 0;
            }

            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: var(--neutral-100);
                border-radius: 0;
                display: none;
                border: none;
                margin: 0.5rem 0;
            }

            .navbar-menu > li.active .dropdown-menu {
                display: block;
            }

            .dropdown-menu li a {
                padding: 0.8rem 1.5rem 0.8rem 2.5rem;
            }

            .navbar-right {
                width: 100%;
                justify-content: flex-end;
                padding: 1rem 0;
                border-top: 1px solid var(--neutral-300);
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem 1rem;
            }

            .page-header h2 {
                font-size: 1.75rem;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .panel-header {
                padding: 1.2rem 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .panel-body {
                padding: 1.5rem;
            }

            .data-table {
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .navbar-brand span {
                font-size: 0.7rem;
            }

            .balance-badge {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .user-info {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }

            .logout-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
        
        /* Demo Mode Banner */
        .demo-banner {
            display: flex;
            justify-content: center;
            padding: 0.75rem 1rem;
            position: sticky;
            top: 80px;
            z-index: 999;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .demo-banner-content {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .demo-banner-icon {
            font-size: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .demo-banner-text {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .demo-banner-subtext {
            font-weight: 400;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .demo-banner-cta {
            background: white;
            color: #ee5a6f;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .demo-banner-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .demo-banner {
                padding: 0.5rem 0.75rem;
                top: 70px;
            }
            
            .demo-banner-content {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.6rem 1.2rem;
                border-radius: 30px;
            }
            
            .demo-banner-text {
                font-size: 0.85rem;
            }
            
            .demo-banner-subtext {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Top Navigation -->
        <nav class="navbar">
            <div class="navbar-container">
                <a href="<?= $basePath ?>" class="navbar-brand">
                    <?= \Core\Helper::getSetting('site_name', 'Proxnum Reseller') ?>
                    <span><?= $_SESSION['user_role'] === 'admin' ? 'Administrator' : 'SMS Verification' ?></span>
                </a>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                    ☰
                </button>
                
                <ul class="navbar-menu">
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <!-- Admin Navigation -->
                        <li><a href="<?= $basePath ?>/admin">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                            </svg>
                            Dashboard
                        </a></li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Clients
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/admin/clients">👥 All clients</a></li>
                                <li><a href="<?= $basePath ?>/admin/activationsHistory">📱 Activations history</a></li>
                                <li><a href="<?= $basePath ?>/admin/reports">📈 Reports</a></li>
                                <li><a href="<?= $basePath ?>/admin/activityLogs">📋 Activity logs</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                                </svg>
                                Finance
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/admin/revenue"> Revenue</a></li>
                                <li><a href="<?= $basePath ?>/admin/priceManagement">💲 Price management</a></li>
                                <li><a href="<?= $basePath ?>/admin/transactions"> Transactions</a></li>
                                <li><a href="<?= $basePath ?>/admin/paymentGateways">💳 Payment gateways</a></li>
                                <li><a href="<?= $basePath ?>/admin/paymentVerifications"> Payment verifications</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H5.78a1.65 1.65 0 0 0-1.51 1 1.65 1.65 0 0 0 .33 1.82l.87.87A10 10 0 0 0 9.3 20.4c1.2.44 2.5.6 3.8.6s2.6-.16 3.8-.6a10 10 0 0 0 3.83-2.53l.87-.87z"></path>
                                </svg>
                                System
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/admin/settings">⚙️ Settings</a></li>
                                <li><a href="<?= $basePath ?>/admin/updates">🔄 Updates</a></li>
                                <li><a href="<?= $basePath ?>/admin/apiStats">📡 API statistics</a></li>
                                <li><a href="<?= $basePath ?>/admin/systemHealth">🏥 System health</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                                Support
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/admin/support">🎫 Tickets</a></li>
                                <li><a href="<?= $basePath ?>/admin/announcements">📢 Updates</a></li>
                                <li><a href="<?= $basePath ?>/admin/emailTemplates">📧 Email templates</a></li>
                                <li><a href="<?= $basePath ?>/admin/sendEmail">📤 Send Email</a></li>
                            </ul>
                        </li>
                        
                    <?php else: ?>
                        <!-- Client Navigation -->
                        <li><a href="<?= $basePath ?>/dashboard">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                            </svg>
                            Dashboard
                        </a></li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                </svg>
                                Numbers
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/dashboard/buy">🛒 Buy number</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/activations">📱 My activations</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/favorites">⭐ Favorites</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/history">📜 History</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                                </svg>
                                Wallet
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/dashboard/wallet">💳 Add funds</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/transactions"> Transactions</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/invoices">🧾 Invoices</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Profile
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/dashboard/profile">⚙️ Settings</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/notifications">🔔 Notifications</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                                Support
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= $basePath ?>/dashboard/support">🎫 Tickets</a></li>
                                <li><a href="<?= $basePath ?>/dashboard/announcements">📢 Updates</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="navbar-right">
                    <?php if ($_SESSION['user_role'] !== 'admin'): 
                        $headerUser = \Core\Database::getInstance()->fetch('SELECT balance FROM users WHERE id = ?', [$_SESSION['user_id']]);
                    ?>
                    <span class="balance-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                        </svg>
                        <?= \Core\Helper::money($headerUser['balance'] ?? 0) ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= $basePath ?>/admin/profile" class="user-info">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                    <?php else: ?>
                        <div class="user-info">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= $basePath ?>/auth/logout" class="logout-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </nav>
        
        <!-- Demo Mode Banner -->
        <?php if (\Core\Helper::isDemo()): ?>
        <div class="demo-banner">
            <div class="demo-banner-content">
                <span class="demo-banner-icon">🎭</span>
                <div>
                    <div class="demo-banner-text">You're using a DEMO account</div>
                    <div class="demo-banner-subtext">Some features are disabled. Purchase a license for full access.</div>
                </div>
                <a href="<?= str_replace('/proxnum-reseller', '', Helper::url('')) ?>/license/plans" class="demo-banner-cta">Get License</a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="main-content">
            <?php if (isset($title)): ?>
            <div class="page-header">
                <h2><?= $title ?></h2>
            </div>
            <?php endif; ?>
            
            <script>
            // Mobile Menu Toggle
            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('mobileMenuToggle');
                const menu = document.querySelector('.navbar-menu');
                const dropdowns = document.querySelectorAll('.navbar-menu > li');
                
                if (toggle && menu) {
                    toggle.addEventListener('click', function() {
                        menu.classList.toggle('active');
                    });
                }
                
                // Mobile dropdown toggle
                dropdowns.forEach(function(item) {
                    const link = item.querySelector('a');
                    const dropdown = item.querySelector('.dropdown-menu');
                    
                    if (dropdown && link) {
                        link.addEventListener('click', function(e) {
                            if (window.innerWidth <= 1024) {
                                e.preventDefault();
                                item.classList.toggle('active');
                            }
                        });
                    }
                });
                
                // Auto-wrap tables in responsive container
                const tables = document.querySelectorAll('.data-table');
                tables.forEach(function(table) {
                    if (!table.parentElement.classList.contains('table-responsive')) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'table-responsive';
                        table.parentNode.insertBefore(wrapper, table);
                        wrapper.appendChild(table);
                    }
                });
            });
            </script>