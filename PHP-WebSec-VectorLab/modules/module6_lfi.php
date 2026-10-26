<?php
require_once '../config.php';

$message = '';
$message_type = '';

// Default requested page/template
$page = $_GET['page'] ?? 'home';

// VULNERABILITY / MEDIUM FILTER: Strips '../' once, allowing recursive traversal bypass (e.g., '....//' becomes '../')
$filtered_page = str_replace('../', '', $page);

// Define allowed base directory for templates
$template_dir = __DIR__ . '/../templates/';
if (!file_exists($template_dir)) {
    mkdir($template_dir, 0755, true);
    // Seed default template files
    file_put_contents($template_dir . 'home.php', '<div style="color:var(--text-main);"><h3><i class="fa-solid fa-house"></i> Welcome to the Corporate Portal</h3><p>Select a preferred language pack or view system documentation.</p></div>');
    file_put_contents($template_dir . 'about.php', '<div style="color:var(--text-main);"><h3><i class="fa-solid fa-circle-info"></i> About VulnCore Enterprise</h3><p>Secure global infrastructure and application monitoring solutions.</p></div>');
}

// Construct target file path
// VULNERABILITY: Dynamic file inclusion without strict whitelisting
$target_file = $template_dir . $filtered_page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 6: Local File Inclusion (LFI) & Path Traversal - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-folder-tree"></i> Module 6: LFI & Path Traversal</h1>
            <a href="../index.php" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-book-open"></i> Objective & Mechanics</span>
                <span class="difficulty-medium">Medium</span>
            </div>
            <p>
                This module simulates a dynamic template loader and language switcher. The application includes files dynamically via the <code>page</code> query parameter. A basic security filter strips out <code>../</code> sequences once. Bypass this filter using recursive traversal patterns (such as <code>....//</code>) or exploit PHP stream wrappers (like <code>php://filter</code>) to read sensitive server configuration files.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Navigation / Template Switcher Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-language"></i> Template Selector</span>
                </div>
                <p style="margin-bottom: 1.5rem;">Switch between available portal views:</p>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="module6_lfi.php?page=home.php" class="btn" style="background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); justify-content: flex-start;"><i class="fa-solid fa-house"></i> Home Template</a>
                    <a href="module6_lfi.php?page=about.php" class="btn" style="background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); justify-content: flex-start;"><i class="fa-solid fa-circle-info"></i> About Template</a>
                </div>

                <div style="margin-top: 2rem; font-size: 0.8rem; color: var(--text-muted);">
                    <strong>Filter Rule:</strong> Removes <code>../</code> strings once.<br>
                    <strong>Hint:</strong> Try recursive payloads like <code>....//....//database.db</code> or PHP base64 filters.
                </div>
            </div>

            <!-- Dynamic Content Viewer Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-file-code"></i> Dynamic Template Output (Param: <code>?page=<?php echo htmlspecialchars($page); ?></code>)</span>
                    <span class="badge">LFI Target</span>
                </div>
                <p style="margin-bottom: 1rem; font-size: 0.85rem; color: var(--text-muted);">Rendered inclusion result:</p>

                <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: 6px; border: 1px solid var(--border-color); min-height: 220px;">
                    <?php
                    // Execute dynamic inclusion with error suppression for clean lab UI
                    $level_error_reporting = error_reporting(0);
                    if (!@include($target_file)) {
                        echo '<div style="color: var(--accent-red); font-family: monospace;"><i class="fa-solid fa-triangle-exclamation"></i> Warning: Failed to include requested template resource [" . htmlspecialchars($target_file) . "].</div>';
                    }
                    error_reporting($level_error_reporting);
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>