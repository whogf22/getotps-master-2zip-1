<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successfully Unsubscribed</title>
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
            text-align: center;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        h1 {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .email {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
            font-weight: 500;
            color: #333;
        }
        
        .resubscribe-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .resubscribe-box h3 {
            color: #0056b3;
            margin-bottom: 10px;
            font-size: 18px;
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
            margin: 10px 5px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <h1>You've Been Unsubscribed</h1>
        
        <p>We've successfully unsubscribed this email address from bulk emails:</p>
        
        <div class="email">
            <?= htmlspecialchars($user['email']) ?>
        </div>
        
        <p>You won't receive promotional emails, newsletters, or general announcements anymore.</p>
        
        <p><strong>Don't worry!</strong> You'll still receive important emails like transaction receipts, security alerts, and account notifications.</p>
        
        <div class="resubscribe-box">
            <h3>Changed your mind?</h3>
            <p>You can re-subscribe anytime to start receiving our emails again.</p>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="resubscribe">
                <button type="submit" class="btn btn-primary">
                    Re-subscribe to Emails
                </button>
            </form>
        </div>
        
        <a href="/" class="btn btn-secondary">
            Return to Homepage
        </a>
    </div>
</body>
</html>
