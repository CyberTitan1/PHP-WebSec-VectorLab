<?php
require_once '../config.php';

$message = '';
$message_type = '';
$search_results = [];
$sql_error = '';

// Handle Product Search (SQL Injection Vulnerability)
$search_query = $_GET['search'] ?? '';
if ($search_query !== '') {
    try {
        // VULNERABILITY: Unsafe string concatenation directly into SQL query for medium difficulty testing (UNION / Error-based SQLi)
        $query = "SELECT id, name, price, stock FROM products WHERE name LIKE '%" . $search_query . "%'";
        $stmt = $pdo->query($query);
        $search_results = $stmt->fetchAll();
    } catch (PDOException $e) {
        $sql_error = $e->getMessage();
    }
} else {
    // Default load all products
    $search_results = $pdo->query("SELECT id, name, price, stock FROM products")->fetchAll();
}

// Handle Feedback Submission (Stored XSS Vulnerability)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'feedback') {
    $author = trim($_POST['author'] ?? 'Anonymous');
    $comment = $_POST['comment'] ?? ''; // VULNERABILITY: Stored XSS - raw input stored and rendered without htmlspecialchars()

    $stmt = $pdo->prepare("INSERT INTO feedback (author, comment, is_admin_reviewed) VALUES (?, ?, 0)");
    try {
        $stmt->execute([$author, $comment]);
        $message = "Feedback submitted successfully into the review queue!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error submitting feedback.";
        $message_type = "error";
    }
}

// Fetch feedback list for review display
$feedbacks = $pdo->query("SELECT * FROM feedback ORDER BY id DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 4: SQL Injection & XSS - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-database"></i> Module 4: SQL Injection & XSS</h1>
            <a href="../index.php" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert" style="background: rgba(16, 185, 129, 0.1); border-color: var(--accent-green); color: var(--accent-green);">
                <i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sql_error)): ?>
            <div class="alert" style="background: rgba(244, 63, 94, 0.1); border-color: var(--accent-red); color: var(--accent-red);">
                <i class="fa-solid fa-triangle-exclamation"></i> SQL Error / Exception: <?php echo htmlspecialchars($sql_error); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-book-open"></i> Objective & Mechanics</span>
                <span class="difficulty-medium">Medium</span>
            </div>
            <p>
                This module simulates a product catalog and customer review portal. The product search feature is vulnerable to SQL Injection (enabling UNION-based data extraction). The feedback section accepts unescaped input, leading to Stored XSS payloads executing inside administrative review logs.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 2rem;">
            <!-- Product Catalog & SQLi Search -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-magnifying-glass"></i> Enterprise Product Catalog</span>
                </div>
                <p style="margin-bottom: 1rem;">Search products by keyword (SQLi testing vector).</p>

                <form method="GET" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search items..." style="flex: 1; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem; border-radius: 4px;">
                    <button type="submit" class="btn" style="padding: 0.5rem 1rem;"><i class="fa-solid search"></i> Search</button>
                </form>

                <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 250px; overflow-y: auto;">
                    <?php if (empty($search_results)): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No products found.</p>
                    <?php else: ?>
                        <?php foreach ($search_results as $prod): ?>
                            <div style="background: var(--bg-primary); padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="color: var(--text-main); display: block;"><?php echo htmlspecialchars($prod['name']); ?></strong>
                                    <span style="color: var(--accent-blue); font-family: monospace;">$<?php echo number_format($prod['price'], 2); ?></span>
                                </div>
                                <span class="badge">Stock: <?php echo $prod['stock']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Feedback & Stored XSS -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-comments"></i> Customer Feedback Form</span>
                </div>
                <p style="margin-bottom: 1rem;">Submit your review (Stored XSS vector).</p>

                <form method="POST" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <input type="hidden" name="action" value="feedback">
                    <div>
                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Author Name</label>
                        <input type="text" name="author" value="Security Researcher" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Comment / Review</label>
                        <textarea name="comment" rows="3" placeholder="Enter review or XSS payload..." required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem; border-radius: 4px; font-family: inherit;"></textarea>
                    </div>
                    <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Submit Feedback</button>
                </form>
            </div>
        </div>

        <!-- Admin Review Log Table (Stored XSS Execution Target) -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-shield-halved"></i> Administrative Review Queue (Stored XSS Output)</span>
                <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: var(--accent-red); border-color: rgba(244, 63, 94, 0.2);">Admin Log</span>
            </div>
            <p style="margin-bottom: 1rem;">Comments rendered directly in the admin review queue without sanitization:</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th style="padding: 0.75rem;">ID</th>
                            <th style="padding: 0.75rem;">Author</th>
                            <th style="padding: 0.75rem;">Comment (Raw HTML Render)</th>
                            <th style="padding: 0.75rem;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem; font-family: monospace; color: var(--accent-blue);">#<?php echo $fb['id']; ?></td>
                                <td style="padding: 0.75rem; font-weight: 500;"><?php echo htmlspecialchars($fb['author']); ?></td>
                                <!-- VULNERABILITY: Raw unescaped output rendering Stored XSS payload -->
                                <td style="padding: 0.75rem; color: var(--text-main);"><?php echo $fb['comment']; ?></td>
                                <td style="padding: 0.75rem; color: var(--text-muted); font-size: 0.8rem;"><?php echo $fb['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>