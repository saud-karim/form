<?php
/**
 * Email Contact Form Handler with File Attachments
 * 
 * This script processes form submissions, validates input,
 * handles file uploads, and sends emails with attachments.
 */

// Prevent any output before JSON response
ob_start();

// Set content type to JSON for API responses
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors to prevent HTML output
ini_set('log_errors', 1);

// Configuration - Update these values according to your setup
$config = [
    'recipient_email' => 'recipient@example.com',    // Change this to your email
    'recipient_name' => 'Contact Form Handler',
    'sender_email' => 'noreply@yourdomain.com',      // Change this to your domain
    'sender_name' => 'Driver License Registration System',
    'subject' => 'New Driver License Registration',
    'max_file_size' => 5 * 1024 * 1024,             // 5MB max file size
    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
    'upload_dir' => 'temp_uploads/'                   // Temporary upload directory
];

/**
 * Sanitize input data to prevent injection attacks
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate uploaded file
 */
function validateFile($file, $allowedTypes, $maxSize) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    return true;
}

/**
 * Create multipart email with attachments
 */
function createEmailWithAttachments($to, $subject, $message, $attachments, $fromEmail, $fromName) {
    // Generate boundary
    $boundary = md5(time());
    
    // Headers
    $headers = [];
    $headers[] = "From: {$fromName} <{$fromEmail}>";
    $headers[] = "Reply-To: {$fromEmail}";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    // Email body
    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $message . "\r\n\r\n";
    
    // Add attachments
    foreach ($attachments as $attachment) {
        if (file_exists($attachment['path'])) {
            $fileContent = chunk_split(base64_encode(file_get_contents($attachment['path'])));
            
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: {$attachment['type']}; name=\"{$attachment['name']}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
            $body .= $fileContent . "\r\n";
        }
    }
    
    $body .= "--{$boundary}--";
    
    // Send email
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Clean up temporary files
 */
function cleanupFiles($files) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

/**
 * Send JSON response and exit
 */
function sendJsonResponse($success, $message) {
    // Clear any previous output
    ob_clean();
    
    // Ensure we're sending JSON
    header('Content-Type: application/json');
    
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    
    exit;
}

/**
 * Handle fatal errors and return JSON
 */
function handleFatalError() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
        sendJsonResponse(false, 'A server error occurred. Please try again later.');
    }
}

// Register fatal error handler
register_shutdown_function('handleFatalError');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJsonResponse(false, 'Method not allowed');
}

try {
    // Validate and sanitize form inputs
    $name = sanitizeInput($_POST['name'] ?? '');
    $startDate = sanitizeInput($_POST['start_date'] ?? '');
    $expDate = sanitizeInput($_POST['exp_date'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $project = sanitizeInput($_POST['project'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($startDate)) {
        $errors[] = 'License issue date is required';
    } elseif (!validateDate($startDate)) {
        $errors[] = 'Invalid license issue date format';
    }
    
    if (empty($expDate)) {
        $errors[] = 'License expiry date is required';
    } elseif (!validateDate($expDate)) {
        $errors[] = 'Invalid license expiry date format';
    }
    
    // Check if expiry date is after start date
    if (!empty($startDate) && !empty($expDate) && strtotime($expDate) <= strtotime($startDate)) {
        $errors[] = 'License expiry date must be after issue date';
    }
    
    if (empty($department) || !in_array($department, ['HR', 'IT', 'Finance', 'Marketing'])) {
        $errors[] = 'Valid department selection is required';
    }
    
    if (empty($project) || !in_array($project, ['Project A', 'Project B', 'Project C', 'Project D'])) {
        $errors[] = 'Valid project selection is required';
    }
    
    // Validate file uploads
    if (!isset($_FILES['image1']) || !validateFile($_FILES['image1'], $config['allowed_types'], $config['max_file_size'])) {
        $errors[] = 'First image upload is required and must be a valid image file (max 5MB)';
    }
    
    if (!isset($_FILES['image2']) || !validateFile($_FILES['image2'], $config['allowed_types'], $config['max_file_size'])) {
        $errors[] = 'Second image upload is required and must be a valid image file (max 5MB)';
    }
    
    if (!empty($errors)) {
        sendJsonResponse(false, implode(', ', $errors));
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($config['upload_dir'])) {
        mkdir($config['upload_dir'], 0755, true);
    }
    
    // Process file uploads
    $uploadedFiles = [];
    $attachments = [];
    
    for ($i = 1; $i <= 2; $i++) {
        $fileKey = "image{$i}";
        if (isset($_FILES[$fileKey])) {
            $file = $_FILES[$fileKey];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = "attachment_{$i}_" . time() . "_" . uniqid() . "." . $extension;
            $uploadPath = $config['upload_dir'] . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $uploadedFiles[] = $uploadPath;
                $attachments[] = [
                    'path' => $uploadPath,
                    'name' => $file['name'],
                    'type' => $file['type']
                ];
            } else {
                throw new Exception("Failed to upload {$fileKey}");
            }
        }
    }
    
    // Create email content
    $emailContent = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .value { margin-top: 5px; }
            .message-content { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Driver License Registration</h2>
                <p>Received on: " . date('Y-m-d H:i:s') . "</p>
            </div>
            
            <div class='field'>
                <div class='label'>Full Name:</div>
                <div class='value'>{$name}</div>
            </div>
            
            <div class='field'>
                <div class='label'>License Issue Date:</div>
                <div class='value'>{$startDate}</div>
            </div>
            
            <div class='field'>
                <div class='label'>License Expiry Date:</div>
                <div class='value'>{$expDate}</div>
            </div>
            
            <div class='field'>
                <div class='label'>Department:</div>
                <div class='value'>{$department}</div>
            </div>
            
            <div class='field'>
                <div class='label'>Project:</div>
                <div class='value'>{$project}</div>
            </div>
            
            <div class='field'>
                <div class='label'>License Images:</div>
                <div class='value'>
                    <ul>
                        <li>Front Side: " . $attachments[0]['name'] . "</li>
                        <li>Back Side: " . $attachments[1]['name'] . "</li>
                    </ul>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    $emailSent = createEmailWithAttachments(
        $config['recipient_email'],
        $config['subject'],
        $emailContent,
        $attachments,
        $config['sender_email'],
        $config['sender_name']
    );
    
    // Clean up uploaded files
    cleanupFiles($uploadedFiles);
    
    if ($emailSent) {
        sendJsonResponse(true, 'Your driver license registration has been submitted successfully!');
    } else {
        sendJsonResponse(false, 'Failed to submit registration. Please try again later or contact us directly.');
    }
    
} catch (Exception $e) {
    // Clean up any uploaded files in case of error
    if (isset($uploadedFiles)) {
        cleanupFiles($uploadedFiles);
    }
    
    // Log error (in production, log to file instead of displaying)
    error_log("Contact form error: " . $e->getMessage());
    
    sendJsonResponse(false, 'An error occurred while processing your request. Please try again later.');
}
?> 