<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
            text-align: center;
            padding: 20px;
        }
        
        .error-container {
            max-width: 500px;
        }
        
        h1 {
            font-size: 120px;
            margin: 0;
            text-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        h2 {
            font-size: 32px;
            margin: 20px 0;
        }
        
        p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        a {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for doesn't exist or has been moved.</p>
        <a href="<?= \Core\Helper::url('/dashboard') ?>">Go Home</a>
    </div>
</body>
</html>
