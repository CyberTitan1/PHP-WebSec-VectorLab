<?php
require_once '../config.php';

$message = '';
$message_type = '';

// Handle Checkout Action (Parameter Tampering)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $product_id = $_POST['product_id'] ?? 1;
    $quantity = $_POST['quantity'] ?? 1;
    
    // VULNERABILITY: Backend blindly trusts client-supplied price parameter without recalculating from database!
    $submitted_price = $_POST['price'] ?? 0.00;

    // Fetch product name for confirmation message
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $prod = $stmt->fetch();
    $prod_name = $prod ? $prod['name'] : 'Custom Item';

    $total_cost = $submitted_price * $quantity;

    if ($total_cost <= 1.00) {
        $message = "SUCCESS: Order placed for [{$prod_name}] x {$quantity}! Total Charged: \${$total_cost}. Parameter tampering successful!";
        $message_type = "success";
    } else {
        $message = "Order completed for [{$prod_name}] x {$quantity}. Total Charged: \${$total_cost}. (Tip: Intercept with Burp Suite to modify the price parameter!)";
        $message_type = "info";
    }
}

// Handle Voting Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $vote_id = $_POST['vote_id'] ?? 1;
    
    $stmt = $pdo->prepare("UPDATE votes SET vote_count = vote_count + 1 WHERE id = ?");
    try {
        $stmt->execute([$vote_id]);
        $message = "Vote recorded successfully for Poll ID #{$vote_id}!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error recording vote: Invalid Poll ID.";
        $message_type = "error";
    }
}

// Fetch products and votes for display
$products = $pdo->query("SELECT * FROM products")->fetchAll();
$votes = $pdo->query("SELECT * FROM votes")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 1: Business Logic & Parameter Tampering - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-scale-balanced"></i> Module 1: Business Logic & Parameter Tampering</h1>
            <a href="../index.php" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert" style="background: <?php echo $message_type === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(56, 189, 248, 0.1)'; ?>; border-color: <?php echo $message_type === 'success' ? 'var(--accent-green)' : 'var(--accent-blue)'; ?>; color: <?php echo $message_type === 'success' ? 'var(--accent-green)' : 'var(--accent-blue)'; ?>;">
                <i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-book-open"></i> Objective & Mechanics</span>
                <span class="difficulty-medium">Medium</span>
            </div>
            <p>
                This portal handles high-value enterprise software licenses and voting pools. The backend application assumes users will never modify hidden form fields or POST parameters during checkout. Use <strong>Burp Suite Proxy</strong> to intercept the checkout request, alter the price parameter to <code>0.01</code>, and bypass standard pricing checks.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 2fr 1fr;">
            <!-- E-Commerce Checkout Portal -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-cart-shopping"></i> Enterprise Licensing Checkout</span>
                </div>
                <p>Select a product to purchase. Notice how prices are displayed in the form.</p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($products as $p): ?>
                        <div style="background: var(--bg-primary); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--text-main); display: block;"><?php echo htmlspecialchars($p['name']); ?></strong>
                                <span style="color: var(--accent-blue); font-family: monospace; font-size: 1.1rem;">$<?php echo number_format($p['price'], 2); ?></span>
                                <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 1rem;">Stock: <?php echo $p['stock']; ?></span>
                            </div>
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="action" value="checkout">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="price" value="<?php echo $p['price']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="10" style="width: 50px; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.4rem; border-radius: 4px; text-align: center;">
                                <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fa-solid fa-bag-shopping"></i> Buy</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Voting Pool Portal -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-square-poll-vertical"></i> Strategy Poll</span>
                </div>
                <p>Vote for next quarter's security framework priority.</p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($votes as $v): ?>
                        <div style="background: var(--bg-primary); padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                <span><?php echo htmlspecialchars($v['candidate']); ?></span>
                                <span style="color: var(--accent-yellow); font-weight: bold;"><?php echo $v['vote_count']; ?> votes</span>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="vote">
                                <input type="hidden" name="vote_id" value="<?php echo $v['id']; ?>">
                                <button type="submit" class="btn" style="width: 100%; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.8rem; padding: 0.3rem;"><i class="fa-solid fa-check"></i> Vote ID #<?php echo $v['id']; ?></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>