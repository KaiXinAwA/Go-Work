<?php
/**
 * API endpoint for updating company logo
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to update logo';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Check if profile picture is uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'No logo file uploaded or upload error occurred';
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['profile_picture']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = 'The uploaded file exceeds the maximum file size';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = 'The file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage = 'A PHP extension stopped the file upload';
                break;
        }
    }
    
    $_SESSION['error'] = $errorMessage;
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Get user data
$user = getCurrentUser();

// Validate file type (image files)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$uploadPath = PROFILE_PIC_DIR;

// Create upload directory if it doesn't exist
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

// Upload file
$logoFilename = uploadFile($_FILES['profile_picture'], $uploadPath, $allowedTypes, MAX_PROFILE_PIC_SIZE);

if (!$logoFilename) {
    $_SESSION['error'] = 'Invalid file type or size. Only image files up to 2MB are allowed.';
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Update profile picture in users table
$updateData = [
    'profile_picture' => $logoFilename
];

$updateResult = updateData('users', $updateData, 'user_id', $user['user_id']);

if (!$updateResult) {
    $_SESSION['error'] = 'Failed to update logo';
    
    // Remove uploaded file if database update failed
    if (file_exists($uploadPath . $logoFilename)) {
        unlink($uploadPath . $logoFilename);
    }
    
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Success
$_SESSION['success'] = 'Company logo updated successfully';
redirectTo(SITE_URL . '/pages/company/profile.php');
