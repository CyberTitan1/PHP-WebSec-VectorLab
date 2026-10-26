<?php
require_once '../config.php';

$message = '';
$message_type = '';

// Handle Login Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $message = "Logged in successfully as " . htmlspecialchars($user['username']) . "!";
        $message_type = "success";
    } else {
        $message = "Invalid username or password. (Try john_doe / password123)";
        $message_type = "error";
    }
}

// Handle Logout Action
if (isset($_GET['logout'])) {
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role']);
    header("Location: module2_auth.php");
    exit;
}

// Handle Profile Update Action (IDOR Vulnerability on Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $target_user_id = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? 1);
    $new_email = trim($_POST['email'] ?? '');
    $new_notes = trim($_POST['notes'] ?? '');
    $new_cc = trim($_POST['credit_card'] ?? '');

    // VULNERABILITY: Backend trusts the submitted user_id parameter without verifying authorization/ownership!
    $stmt = $pdo->prepare("UPDATE users SET email = ?, notes = ?, credit_card = ? WHERE id = ?");
    try {
        $stmt->execute([$new_email, $new_notes, $new_cc, $target_user_id]);
        $message = "Profile successfully updated for User ID #{$target_user_id}!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error updating profile.";
        $message_type = "error";
    }
}

// Determine which user profile to view
// VULNERABILITY: Defaults to session user ID, but accepts any user_id via GET parameter without session authorization check!
$view_user_id = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? 1);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$view_user_id]);
$profile = $stmt->fetch();

// Fallback if invalid ID requested
if (!$profile) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = 1");
    $stmt->execute();
    $profile = $stmt->fetch();
    $view_user_id = $profile['id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 2: Broken Access Control & IDOR - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-user-lock"></i> Module 2: Broken Access Control & IDOR</h1>
            <a href="../index.php" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert" style="background: <?php echo $message_type === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)'; ?>; border-color: <?php echo $message_type === 'success' ? 'var(--accent-green)' : 'var(--accent-red)'; ?>; color: <?php echo $message_type === 'success' ? 'var(--accent-green)' : 'var(--accent-red)'; ?>;">
                <i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-book-open"></i> Objective & Mechanics</span>
                <span class="difficulty-medium">Medium</span>
            </div>
            <p>
                This module simulates a corporate employee directory and profile dashboard. When logged in as a standard user (e.g., <code>john_doe</code> / <code>password123</code>), view your profile URL (<code>?user_id=2</code>). Attempt to change the <code>user_id</code> parameter in the URL to <code>1</code> to inspect and modify the administrator's private profile, email, and financial credit card information.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Authentication Panel / Navigation -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-right-to-bracket"></i> Authentication</span>
                </div>
                <?php if (is_logged_in()): ?>
                    <p style="margin-bottom: 1rem;">Logged in as: <strong style="color: var(--accent-blue);"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                    <p style="font-size: 0.85rem; margin-bottom: 1.5rem;">Role: <span class="badge"><?php echo htmlspecialchars($_SESSION['role']); ?></span></p>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="module2_auth.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="btn" style="background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-user"></i> My Profile (?user_id=<?php echo $_SESSION['user_id']; ?>)</a>
                        <a href="module2_auth.php?user_id=1" class="btn" style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); color: var(--accent-red);"><i class="fa-solid fa-user-shield"></i> Target Admin (?user_id=1)</a>
                        <a href="module2_auth.php?logout=1" class="btn" style="background: var(--border-color); color: var(--text-main); margin-top: 1rem;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <p style="font-size: 0.9rem; margin-bottom: 1rem;">Log in with a standard account to test access controls.</p>
                    <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="hidden" name="action" value="login">
                        <div>
                            <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Username</label>
                            <input type="text" name="username" value="john_doe" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Password</label>
                            <input type="password" name="password" value="password123" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem; border-radius: 4px;">
                        </div>
                        <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
                    </form>
                    <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                        Default accounts:<br>
                        - <code>admin / admin123</code> (ID: 1)<br>
                        - <code>john_doe / password123</code> (ID: 2)<br>
                        - <code>alice_smith / alice2026</code> (ID: 3)
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Viewer & Editor Form -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-id-card"></i> User Profile Dashboard (Viewing ID: #<?php echo $view_user_id; ?>)</span>
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: var(--accent-yellow); border-color: rgba(245, 158, 11, 0.2);"><?php echo htmlspecialchars($profile['role']); ?></span>
                </div>
                <p style="margin-bottom: 1.5rem;">Reviewing sensitive account details and financial records.</p>

                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="user_id" value="<?php echo $profile['id']; ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($profile['username']); ?>" disabled style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.6rem; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Email Address</label>
                            <input type="text" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.6rem; border-radius: 6px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Credit Card / Financial Record</label>
                        <input type="text" name="credit_card" value="<?php echo htmlspecialchars($profile['credit_card']); ?>" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--accent-yellow); font-family: monospace; padding: 0.6rem; border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Private Notes / Clearance Data</label>
                        <textarea name="notes" rows="3" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.6rem; border-radius: 6px; font-family: inherit;"><?php echo htmlspecialchars($profile['notes']); ?></textarea>
                    </div>

                    <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Update Profile Data</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>