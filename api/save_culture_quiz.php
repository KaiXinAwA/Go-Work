<?php
/**
 * API endpoint to save culture quiz results
 */
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);

// Validate data
if (!isset($postData['user_id']) || !isset($postData['culture_profile'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required data'
    ]);
    exit;
}

// Ensure the user can only save their own data
if ($_SESSION['user_id'] != $postData['user_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Save results to the database
try {
    $success = saveUserCultureResults($postData['user_id'], $postData['culture_profile']);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Culture quiz results saved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save culture quiz results'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 