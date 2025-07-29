<?php
/**
 * Debug version of the email handler
 * Use this to troubleshoot issues with the main script
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

echo "Debug Email Handler\n";
echo "==================\n\n";

// Check if this is a POST request
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Error: Only POST requests are allowed\n";
    exit;
}

echo "POST Data received:\n";
var_dump($_POST);

echo "\nFILES Data received:\n";
var_dump($_FILES);

// Test basic validation
$name = $_POST['name'] ?? '';
$startDate = $_POST['start_date'] ?? '';
$expDate = $_POST['exp_date'] ?? '';
$department = $_POST['department'] ?? '';
$project = $_POST['project'] ?? '';

echo "\nValidation:\n";
echo "Name: " . $name . "\n";
echo "Start Date: " . $startDate . "\n";
echo "Expiry Date: " . $expDate . "\n";
echo "Department: " . $department . "\n";
echo "Project: " . $project . "\n";

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($startDate)) {
    $errors[] = 'Start date is required';
}

if (empty($expDate)) {
    $errors[] = 'Expiry date is required';
}

if (empty($department) || !in_array($department, ['HR', 'IT', 'Finance', 'Marketing'])) {
    $errors[] = 'Invalid department';
}

if (empty($project) || !in_array($project, ['Project A', 'Project B', 'Project C', 'Project D'])) {
    $errors[] = 'Invalid project';
}

if (!isset($_FILES['image1']) || $_FILES['image1']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Image 1 upload failed';
}

if (!isset($_FILES['image2']) || $_FILES['image2']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Image 2 upload failed';
}

echo "\nValidation Errors:\n";
if (empty($errors)) {
    echo "No validation errors!\n";
} else {
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
}

// Test JSON output
echo "\n" . str_repeat("=", 50) . "\n";
echo "JSON Response Test:\n";

// Clear the debug output
ob_clean();

// Set proper headers
header('Content-Type: application/json');

// Return JSON
if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'message' => 'Debug test successful! All validation passed.',
        'debug_info' => [
            'department' => $department,
            'project' => $project,
            'files_received' => count($_FILES)
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed: ' . implode(', ', $errors),
        'debug_info' => [
            'errors' => $errors,
            'post_data' => $_POST,
            'files_data' => array_keys($_FILES)
        ]
    ]);
}
?> 