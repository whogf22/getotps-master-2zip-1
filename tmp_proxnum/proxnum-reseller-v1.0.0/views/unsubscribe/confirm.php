<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Email Preferences</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        .icon {
            text-align: center;
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        
        .info-box p {
            color: #666;
            line-height: 1.6;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .warning-box p {
            color: #856404;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .user-email {
            text-align: center;
            font-size: 18px;
            color: #667eea;
            font-weight: 500;
            margin: 20px 0;
        }
        
        ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        ul li {
            margin: 8px 0;
            color: #666;
            line-height: 1.5;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✉️</div>
        <h1>Unsubscribe from Emails</h1>
        <div class="subtitle">Manage your email preferences</div>
        
        <div class="user-email">
            <?= htmlspecialchars($user['email']) ?>
        </div>
        
        <div class="info-box">
            <strong>What happens if you unsubscribe?</strong>
            <ul>
                <li>You won't receive bulk announcements and newsletters</li>
                <li>You won't receive promotional offers</li>
                <li>You won't receive general updates</li>
            </ul>
        </div>
        
        <div class="warning-box">
            <p><strong>⚠️ Important:</strong> You will still receive important emails such as:</p>
            <ul>
                <li>Account security notifications</li>
                <li>Transaction receipts and invoices</li>
                <li>Password reset emails</li>
                <li>Low balance alerts</li>
                <li>Service activation confirmations</li>
            </ul>
        </div>
        
        <form method="POST" onsubmit="return confirm('Are you sure you want to unsubscribe from bulk emails?');">
            <input type="hidden" name="action" value="unsubscribe">
            
            <div class="button-group">
                <button type="submit" class="btn btn-danger">
                    Unsubscribe
                </button>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
