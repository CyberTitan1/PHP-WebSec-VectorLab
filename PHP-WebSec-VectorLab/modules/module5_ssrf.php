<?php
require_once '../config.php';

$response_output = '';
$error_message = '';
$target_url = $_POST['url'] ?? '';

// Handle URL Check / Fetch Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($target_url)) {
    
    // VULNERABILITY / MEDIUM FILTER: Naive regex blacklist blocking literal "127.0.0.1" and "localhost"
    // Bypass vectors: Hexadecimal (http://0x7f000001/), Octal, IPv6 ([::1]), or DNS rebinding / aliases (http://lvh.me)
    if (preg_match('/(127\.0\.0\.1|localhost)/i', $target_url)) {
        $error_message = "Security Filter Block: Access to standard loopback addresses (127.0.0.1 / localhost) is restricted!";
    } else {
        // Configure stream context with timeout to prevent hanging on internal ports
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 4,
                'header' => "User-Agent: VulnCore-HealthChecker/2.0\r\n"
            ]
        ];
        $context = stream_context_create($options);

        // VULNERABILITY: Server-Side Request Forgery (SSRF) execution point
        $level_error_reporting = error_reporting(0);
        $response_output = file_get_contents($target_url, false, $context);
        error_reporting($level_error_reporting);

        if ($response_output === false) {
            $error_message = "Network Error: Could not connect to target URL or request timed out.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 5: Server-Side Request Forgery (SSRF) - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-network-wired"></i> Module 5: SSRF with Bypasses</h1>
            <a href="../index.php" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="alert" style="background: rgba(244, 63, 94, 0.1); border-color: var(--accent-red); color: var(--accent-red);">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-book-open"></i> Objective & Mechanics</span>
                <span class="difficulty-medium">Medium</span>
            </div>
            <p>
                This module simulates a remote server health and status checking tool. The application fetches content from user-supplied URLs on behalf of the server. A basic regex filter attempts to block standard loopback addresses (<code>127.0.0.1</code> and <code>localhost</code>). Bypass this filter using alternative IP address encodings (Hexadecimal <code>0x7f000001</code>, Octal, IPv6 <code>[::1]</code>, or DNS aliases like <code>lvh.me</code>) to access internal ports or simulated cloud metadata endpoints.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr;">
            <!-- URL Fetcher Form Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-globe"></i> Remote URL Health Checker</span>
                </div>
                <p style="margin-bottom: 1.5rem;">Enter a target URL for the server to ping and retrieve header banners.</p>

                <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Target URL / Endpoint</label>
                        <input type="text" name="url" value="<?php echo htmlspecialchars($target_url ?: 'http://lvh.me/'); ?>" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.75rem; border-radius: 6px; font-family: monospace;">
                    </div>
                    <button type="submit" class="btn"><i class="fa-solid fa-bolt"></i> Fetch URL Content</button>
                </form>

                <div style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted);">
                    <strong>Filter Rule:</strong> Blocks strings containing <code>127.0.0.1</code> or <code>localhost</code>.<br>
                    <strong>Hint:</strong> Try <code>http://lvh.me/</code> or <code>http://0x7f000001/</code>.
                </div>
            </div>

            <!-- Response Viewer Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-terminal"></i> Server Response Output</span>
                    <span class="badge">HTTP Fetcher</span>
                </div>
                <p style="margin-bottom: 1rem;">Raw body response returned by backend request:</p>

                <div style="background: var(--bg-primary); padding: 1rem; border-radius: 6px; border: 1px solid var(--border-color); min-height: 200px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; color: var(--accent-blue);">
                    <?php if (!empty($response_output)): ?>
                        <pre style="white-space: pre-wrap; word-break: break-all;"><?php echo htmlspecialchars($response_output); ?></pre>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-style: italic;">Awaiting request output...</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>