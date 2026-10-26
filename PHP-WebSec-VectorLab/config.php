<?php
/**
 * VulnCore-AppSec-Lab - Configuration & Database Bootstrap
 * Sets up SQLite via PDO, auto-seeds default lab data, and manages sessions.
 */

// Initialize secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Database file path
$db_file = __DIR__ . '/database.db';
$db_exists = file_exists($db_file);

try {
    // Establish PDO connection to SQLite
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // If database doesn't exist, bootstrap schema and seed default data
    if (!$db_exists) {
        $pdo->exec("
            -- Users Table (Module 2: IDOR & Auth)
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                email TEXT NOT NULL,
                credit_card TEXT NOT NULL,
                notes TEXT
            );

            -- Products / Shop Table (Module 1: Business Logic & Parameter Tampering)
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price REAL NOT NULL,
                stock INTEGER NOT NULL
            );

            -- Votes / Polls Table (Module 1)
            CREATE TABLE votes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                candidate TEXT NOT NULL,
                vote_count INTEGER NOT NULL
            );

            -- Comments / Feedback Table (Module 4: SQLi & XSS / Blind XSS)
            CREATE TABLE feedback (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                author TEXT NOT NULL,
                comment TEXT NOT NULL,
                is_admin_reviewed INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Seed Default Users
            INSERT INTO users (username, password, role, email, credit_card, notes) VALUES
            ('admin', 'admin123', 'administrator', 'admin@vulncore.internal', '4111-2222-3333-4444', 'System Administrator account. Top secret clearance.'),
            ('john_doe', 'password123', 'user', 'john@gmail.com', '5555-4444-3333-2222', 'Standard user profile. Enjoys application security.'),
            ('alice_smith', 'alice2026', 'user', 'alice@yahoo.com', '6666-7788-9900-1122', 'Marketing team lead. VIP access requested.');

            -- Seed Default Products
            INSERT INTO products (name, price, stock) VALUES
            ('Enterprise Security Audit License', 499.99, 10),
            ('Advanced Pentesting Toolkit (USB)', 149.50, 25),
            ('VulnCore-AppSec-Lab Pro Edition', 99.00, 100);

            -- Seed Default Poll Votes
            INSERT INTO votes (candidate, vote_count) VALUES
            ('Option A: Automated CI/CD Security Scanners', 142),
            ('Option B: Manual Red Team Code Review', 89),
            ('Option C: Zero Trust Network Architecture', 215);

            -- Seed Default Feedback
            INSERT INTO feedback (author, comment, is_admin_reviewed) VALUES
            ('Guest Reviewer', 'Great application structure, very clean dark mode interface!', 1);
        ");
    }
} catch (PDOException $e) {
    die("<div style='background:#f43f5e; color:#fff; padding:1rem; font-family:monospace;'>Database Connection Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

/**
 * Helper function to check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper function to get current logged in user data
 */
function current_user($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>