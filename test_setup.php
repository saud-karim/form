<?php
/**
 * Contact Form Setup Test
 * 
 * This file helps verify that your server configuration
 * is compatible with the contact form requirements.
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Setup Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test { margin: 15px 0; padding: 10px; border-radius: 5px; }
        .pass { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .fail { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        .status { font-weight: bold; }
        code { background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Contact Form Setup Test</h1>
    <p>This page checks if your server meets the requirements for the contact form.</p>

    <h2>PHP Configuration</h2>
    
    <?php
    // PHP Version Check
    $phpVersion = phpversion();
    $minPhpVersion = '7.4';
    if (version_compare($phpVersion, $minPhpVersion, '>=')) {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> PHP Version: ' . $phpVersion . ' (Required: ' . $minPhpVersion . '+)</div>';
    } else {
        echo '<div class="test fail"><span class="status">✗ FAIL:</span> PHP Version: ' . $phpVersion . ' (Required: ' . $minPhpVersion . '+)</div>';
    }

    // File Info Extension
    if (extension_loaded('fileinfo')) {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> FileInfo extension is loaded</div>';
    } else {
        echo '<div class="test fail"><span class="status">✗ FAIL:</span> FileInfo extension is required for MIME type detection</div>';
    }

    // Mail Function
    if (function_exists('mail')) {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> Mail function is available</div>';
    } else {
        echo '<div class="test fail"><span class="status">✗ FAIL:</span> Mail function is not available</div>';
    }

    // File Uploads
    if (ini_get('file_uploads')) {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> File uploads are enabled</div>';
    } else {
        echo '<div class="test fail"><span class="status">✗ FAIL:</span> File uploads are disabled</div>';
    }
    ?>

    <h2>Upload Configuration</h2>
    
    <?php
    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');
    $maxFileUploads = ini_get('max_file_uploads');
    $memoryLimit = ini_get('memory_limit');

    echo '<div class="test info"><span class="status">INFO:</span> Maximum file upload size: <code>' . $uploadMaxFilesize . '</code></div>';
    echo '<div class="test info"><span class="status">INFO:</span> Maximum POST size: <code>' . $postMaxSize . '</code></div>';
    echo '<div class="test info"><span class="status">INFO:</span> Maximum file uploads: <code>' . $maxFileUploads . '</code></div>';
    echo '<div class="test info"><span class="status">INFO:</span> Memory limit: <code>' . $memoryLimit . '</code></div>';

    // Check if upload size is adequate
    $uploadBytes = (int)$uploadMaxFilesize * 1024 * 1024;
    if ($uploadBytes >= 5 * 1024 * 1024) { // 5MB
        echo '<div class="test pass"><span class="status">✓ PASS:</span> Upload size is adequate for image files</div>';
    } else {
        echo '<div class="test warning"><span class="status">⚠ WARNING:</span> Upload size may be too small for large images. Consider increasing upload_max_filesize</div>';
    }
    ?>

    <h2>Directory Permissions</h2>
    
    <?php
    $uploadDir = 'temp_uploads/';
    
    // Check if upload directory exists
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo '<div class="test pass"><span class="status">✓ PASS:</span> Created upload directory: <code>' . $uploadDir . '</code></div>';
        } else {
            echo '<div class="test fail"><span class="status">✗ FAIL:</span> Could not create upload directory: <code>' . $uploadDir . '</code></div>';
        }
    } else {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> Upload directory exists: <code>' . $uploadDir . '</code></div>';
    }

    // Check write permissions
    if (is_writable($uploadDir)) {
        echo '<div class="test pass"><span class="status">✓ PASS:</span> Upload directory is writable</div>';
    } else {
        echo '<div class="test fail"><span class="status">✗ FAIL:</span> Upload directory is not writable. Set permissions to 755</div>';
    }
    ?>

    <h2>Mail Configuration</h2>
    
    <?php
    $smtpHost = ini_get('SMTP');
    $smtpPort = ini_get('smtp_port');
    $sendmailFrom = ini_get('sendmail_from');
    $sendmailPath = ini_get('sendmail_path');

    if (!empty($smtpHost)) {
        echo '<div class="test info"><span class="status">INFO:</span> SMTP Host: <code>' . $smtpHost . '</code></div>';
        echo '<div class="test info"><span class="status">INFO:</span> SMTP Port: <code>' . $smtpPort . '</code></div>';
    }

    if (!empty($sendmailFrom)) {
        echo '<div class="test info"><span class="status">INFO:</span> Sendmail From: <code>' . $sendmailFrom . '</code></div>';
    }

    if (!empty($sendmailPath)) {
        echo '<div class="test info"><span class="status">INFO:</span> Sendmail Path: <code>' . $sendmailPath . '</code></div>';
    }

    if (empty($smtpHost) && empty($sendmailPath)) {
        echo '<div class="test warning"><span class="status">⚠ WARNING:</span> No mail configuration detected. You may need to configure SMTP or sendmail</div>';
    }
    ?>

    <h2>Security Headers</h2>
    
    <?php
    $headers = getallheaders();
    $securityHeaders = ['X-Content-Type-Options', 'X-Frame-Options', 'X-XSS-Protection'];
    
    foreach ($securityHeaders as $header) {
        if (isset($headers[$header])) {
            echo '<div class="test pass"><span class="status">✓ PASS:</span> ' . $header . ' header is set</div>';
        } else {
            echo '<div class="test warning"><span class="status">⚠ INFO:</span> ' . $header . ' header not detected (will be set by .htaccess)</div>';
        }
    }
    ?>

    <h2>Test Form</h2>
    <div class="test info">
        <p><span class="status">INFO:</span> If all tests above pass, you can test the form functionality:</p>
        <ol>
            <li>Update the email configuration in <code>send_email.php</code></li>
            <li>Open <code>index.html</code> in your browser</li>
            <li>Fill out the form with test data</li>
            <li>Upload two test images</li>
            <li>Submit and check for success/error messages</li>
        </ol>
    </div>

    <h2>Next Steps</h2>
    <div class="test info">
        <p><span class="status">TODO:</span> Configuration steps:</p>
        <ol>
            <li>Edit <code>send_email.php</code> and update the email configuration</li>
            <li>Test the form with real email addresses</li>
            <li>For production: disable error display and enable error logging</li>
            <li>Consider using PHPMailer for better email delivery (run <code>composer install</code>)</li>
        </ol>
    </div>

    <hr>
    <p><small>Test completed on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
</body>
</html> 