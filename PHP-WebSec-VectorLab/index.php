<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VulnCore-AppSec-Lab | Enterprise Security Sandbox</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- FontAwesome for professional security icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-shield-halved"></i> VulnCore-AppSec-Lab</h1>
            <span class="badge"><i class="fa-solid fa-server"></i> Environment: Active</span>
        </header>

        <p style="color: var(--text-muted); margin-bottom: 2rem;">
            Professional-grade, vulnerable-by-design application security laboratory. Built for advanced penetration testing workflows, proxy interception analysis, and vector auditing.
        </p>

        <div class="grid">
            <!-- Module 1 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-scale-balanced"></i> Module 1: Business Logic</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Exploit parameter tampering and pricing/voting logic flaws requiring backend validation bypass via Burp Suite.</p>
                </div>
                <a href="modules/module1_logic.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>

            <!-- Module 2 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-user-lock"></i> Module 2: IDOR / BAC</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Bypass functional access controls and exploit Insecure Direct Object References to access private user profiles.</p>
                </div>
                <a href="modules/module2_auth.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>

            <!-- Module 3 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-file-arrow-up"></i> Module 3: File Uploads</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Bypass weak extension blacklists and analyze weaponized PDF execution paths (`OpenAction` vectors).</p>
                </div>
                <a href="modules/module3_upload.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>

            <!-- Module 4 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-database"></i> Module 4: SQLi & XSS</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Execute UNION/Error-based SQL injection alongside Stored, Reflected, and Blind XSS attack simulations.</p>
                </div>
                <a href="modules/module4_sqli_xss.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>

            <!-- Module 5 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-network-wired"></i> Module 5: SSRF</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Bypass localhost regex filters using Hexadecimal, Octal, IPv6, and DNS aliases to query internal metadata endpoints.</p>
                </div>
                <a href="modules/module5_ssrf.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>

            <!-- Module 6 -->
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-folder-tree"></i> Module 6: LFI & Traversal</span>
                        <span class="difficulty-medium">Medium</span>
                    </div>
                    <p>Exploit dynamic template loading via recursive path traversal bypasses and wrapper exploitation.</p>
                </div>
                <a href="modules/module6_lfi.php" class="btn"><i class="fa-solid fa-terminal"></i> Launch Module</a>
            </div>
        </div>
    </div>
</body>
</html>