<?php
require_once '../config.php';

// Ensure uploads directory exists
$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$message = '';
$message_type = '';

// Handle File Upload Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $original_name = $file['name'];
    $tmp_name = $file['tmp_name'];
    
    // Extract file extension
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // VULNERABILITY / WEAK BLACKLIST: Blocks standard '.php', but misses alternate executable PHP extensions (.phtml, .phar, .php5)
    $blacklisted_extensions = ['php3', 'php4', 'pht'];

    if (in_array($file_ext, $blacklisted_extensions)) {
        $message = "Upload rejected: Direct '.php' extension files are blocked by corporate security policy.";
        $message_type = "error";
    } else {
        // Sanitize base filename to prevent directory traversal in filename
        $safe_name = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($original_name));
        $target_path = $upload_dir . $safe_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $message = "Document successfully uploaded to /uploads/" . htmlspecialchars($safe_name) . "!";
            $message_type = "success";
        } else {
            $message = "Error moving uploaded file to destination server directory.";
            $message_type = "error";
        }
    }
}

// Scan uploads directory for listed files
$uploaded_files = [];
if (is_dir($upload_dir)) {
    $scan = scandir($upload_dir);
    foreach ($scan as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploaded_files[] = [
                'name' => $f,
                'size' => filesize($upload_dir . $f),
                'time' => date("Y-m-d H:i:s", filemtime($upload_dir . $f))
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 3: Unrestricted File Uploads & PDF Execution - VulnCore-AppSec-Lab</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-file-arrow-up"></i> Module 3: Unrestricted File Uploads</h1>
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
                This module simulates a corporate invoice and document portal. The file upload backend implements a naive blacklist checking for standard <code>.php</code> extensions. Attempt to upload a PHP web shell using alternate executable extensions (such as <code>.phtml</code> or <code>.phar</code>) or weaponized PDF templates to achieve remote code execution in the <code>/uploads/</code> directory.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr;">
            <!-- Upload Form Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Document / Invoice</span>
                </div>
                <p style="margin-bottom: 1.5rem;">Accepted formats: PDF, PNG, JPG, Docx. (Restricted: Direct `.php` files).</p>

                <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Select File from System</label>
                        <input type="file" name="document" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-main); padding: 0.75rem; border-radius: 6px;">
                    </div>
                    <button type="submit" class="btn" style="margin-top: 0.5rem;"><i class="fa-solid fa-upload"></i> Upload to Server</button>
                </form>
            </div>

            <!-- Uploaded Staging Directory Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-folder-open"></i> Staging Directory (`/uploads/`)</span>
                    <span class="badge"><?php echo count($uploaded_files); ?> files</span>
                </div>
                <p style="margin-bottom: 1rem;">Files successfully stored in the public web root:</p>

                <?php if (empty($uploaded_files)): ?>
                    <p style="color: var(--text-muted); font-style: italic; font-size: 0.9rem;">No documents uploaded yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 250px; overflow-y: auto;">
                        <?php foreach ($uploaded_files as $file): ?>
                            <div style="background: var(--bg-primary); padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <a href="../uploads/<?php echo urlencode($file['name']); ?>" target="_blank" style="color: var(--accent-blue); text-decoration: none; font-weight: 500; font-family: monospace; display: block;"><?php echo htmlspecialchars($file['name']); ?></a>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $file['size']; ?> bytes | <?php echo $file['time']; ?></span>
                                </div>
                                <a href="../uploads/<?php echo urlencode($file['name']); ?>" target="_blank" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-main);"><i class="fa-solid fa-arrow-up-right-from-square"></i> View</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>