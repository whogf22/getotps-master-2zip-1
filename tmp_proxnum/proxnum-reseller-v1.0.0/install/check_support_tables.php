<?php
/**
 * Database Check Script
 * Run this to verify if support_tickets tables exist
 * Access via: http://localhost/pxnme/proxnum-reseller/install/check_support_tables.php
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h1>Support Tickets Tables Check</h1>";
    echo "<style>body{font-family:sans-serif;margin:40px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;margin:20px 0;} td,th{border:1px solid #ddd;padding:8px;} th{background:#f4f4f4;}</style>";
    
    // Check if support_tickets table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'support_tickets'");
    $ticketsExists = $stmt->rowCount() > 0;
    
    // Check if support_replies table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'support_replies'");
    $repliesExists = $stmt->rowCount() > 0;
    
    echo "<h2>Table Status:</h2>";
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th></tr>";
    echo "<tr><td>support_tickets</td><td class='" . ($ticketsExists ? "success'>✓ EXISTS" : "error'>✗ MISSING") . "</td></tr>";
    echo "<tr><td>support_replies</td><td class='" . ($repliesExists ? "success'>✓ EXISTS" : "error'>✗ MISSING") . "</td></tr>";
    echo "</table>";
    
    if (!$ticketsExists || !$repliesExists) {
        echo "<div class='error'>";
        echo "<h3>⚠️ Tables are missing!</h3>";
        echo "<p>You need to run the migration SQL to create these tables.</p>";
        echo "<p><strong>Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Select your database: <strong>" . htmlspecialchars(DB_NAME) . "</strong></li>";
        echo "<li>Click on the 'SQL' tab</li>";
        echo "<li>Copy the contents of <code>migration_support_tickets.sql</code></li>";
        echo "<li>Paste into the SQL text area and click 'Go'</li>";
        echo "</ol>";
        echo "<p>Or read <code>SUPPORT_TICKETS_FIX.md</code> for more options.</p>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✓ All tables exist!</h3>";
        
        // Get table info
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM support_tickets");
        $ticketCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM support_replies");
        $replyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<p>Current data:</p>";
        echo "<ul>";
        echo "<li>Tickets: <strong>$ticketCount</strong></li>";
        echo "<li>Replies: <strong>$replyCount</strong></li>";
        echo "</ul>";
        echo "<p>Support tickets system should be working properly now!</p>";
        echo "</div>";
        
        // Show table structure
        echo "<h3>Table Structure:</h3>";
        
        echo "<h4>support_tickets columns:</h4>";
        $stmt = $pdo->query("DESCRIBE support_tickets");
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>support_replies columns:</h4>";
        $stmt = $pdo->query("DESCRIBE support_replies");
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>Database Connection Error</h3>";
    echo "<p>Could not connect to database. Please check your database configuration.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Script location: " . __FILE__ . "</small></p>";
?>
