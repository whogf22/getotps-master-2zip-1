        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Left Section - Copyright -->
                <div class="footer-section">
                    <p class="footer-copyright">
                         <?= \Core\Helper::getSetting('site_name', 'Proxnum') ?> is your sms verification helper.
                    </p>
                </div>

                <!-- Center Section - Links -->
                <div class="footer-section footer-links">
                    <a href="<?= \Core\Helper::url('/dashboard') ?>" class="footer-link">Dashboard</a>
                    <a href="<?= \Core\Helper::url('/dashboard/activations') ?>" class="footer-link">Activations</a>
                    <a href="<?= \Core\Helper::url('/dashboard/transactions') ?>" class="footer-link">Transactions</a>
                </div>

                <!-- Right Section - Support & Docs -->
                <div class="footer-section footer-support">
                    
                    <a href="<?= \Core\Helper::url('/dashboard/support') ?>" class="footer-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Support
                    </a>
                </div>
            </div>

            <!-- Footer Bottom - Status -->
            <div class="footer-bottom">
                <div class="status-indicator">
                    <span class="status-dot"></span>
                    <span class="status-text">All systems operational</span>
                </div>
                <div class="footer-meta">
                    <span>Powered by <?= \Core\Helper::getSetting('site_name', 'SMS Service') ?> </span>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background: white;
            border-top: 1px solid var(--neutral-300);
            margin-top: auto;
            padding: 2rem 0 1.5rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 2rem;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--neutral-200);
        }

        .footer-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .footer-links {
            justify-content: center;
            gap: 1.5rem;
        }

        .footer-support {
            justify-content: flex-end;
            gap: 1.5rem;
        }

        .footer-copyright {
            font-size: 0.875rem;
            color: var(--neutral-600);
            margin: 0;
        }

        .footer-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--neutral-600);
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .footer-link:hover {
            color: var(--primary-deep);
        }

        .footer-link svg {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.25rem;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse-status 2s ease-in-out infinite;
        }

        @keyframes pulse-status {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .status-text {
            font-size: 0.813rem;
            color: var(--neutral-600);
            font-weight: 500;
        }

        .footer-meta {
            font-size: 0.813rem;
            color: var(--neutral-500);
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1.5rem;
            }

            .footer-section {
                justify-content: center;
            }

            .footer-links {
                flex-wrap: wrap;
            }

            .footer-support {
                justify-content: center;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        @media (max-width: 640px) {
            .footer-container {
                padding: 0 20px;
            }

            .footer-links {
                flex-direction: column;
                gap: 0.75rem;
            }

            .footer-link {
                font-size: 0.813rem;
            }
        }
    </style>
</body>
</html>
