<?php
/**
 * API endpoint for updating user profile picture
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to update profile picture';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/user/profile.php');
}

// Check if profile picture is uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'No profile picture uploaded or upload error occurred';
    
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
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php');
    } else {
        redirectTo(SITE_URL);
    }
}

// Get user data
$user = getCurrentUser();

// Validate file type (image files)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$uploadPath = PROFILE_PIC_DIR;

// Debug information
error_log('Photo Upload: Starting upload process');
error_log('Photo Upload: Upload path set to ' . $uploadPath);

// Create upload directory if it doesn't exist
if (!file_exists($uploadPath)) {
    $dirCreated = @mkdir($uploadPath, 0777, true);
    if (!$dirCreated) {
        error_log("Error: Could not create directory " . $uploadPath);
        // Attempt to save the file in the parent directory as a fallback
        $parentDir = dirname($uploadPath);
        if (!file_exists($parentDir)) {
            @mkdir($parentDir, 0777, true);
        }
        $uploadPath = $parentDir . '/';
        error_log('Photo Upload: Changed upload path to parent directory: ' . $uploadPath);
    }
}

// Get file MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedType = $finfo->file($_FILES['profile_picture']['tmp_name']);
error_log('Photo Upload: Detected file type: ' . $detectedType);

// Add the detected MIME type if it contains "image"
if (strpos($detectedType, 'image') !== false) {
    $allowedTypes[] = $detectedType;
    error_log('Photo Upload: Added detected image MIME type to allowed types');
}

error_log('Photo Upload: Allowed types: ' . implode(', ', $allowedTypes));

// Upload file
$photoFilename = uploadFile($_FILES['profile_picture'], $uploadPath, $allowedTypes, MAX_PROFILE_PIC_SIZE);

if (!$photoFilename) {
    error_log('Photo Upload: File upload failed - invalid type or size');
    $_SESSION['error'] = 'Invalid file type or size. Only image files up to 2MB are allowed.';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php');
    } else {
        redirectTo(SITE_URL);
    }
}

error_log('Photo Upload: File uploaded successfully as: ' . $photoFilename);

// Delete old profile picture if exists
if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
    $oldPhotoPath = $uploadPath . $user['profile_picture'];
    if (file_exists($oldPhotoPath)) {
        @unlink($oldPhotoPath);
        error_log('Photo Upload: Deleted old profile picture: ' . $oldPhotoPath);
    }
}

// Update profile picture in database
$updateData = [
    'profile_picture' => $photoFilename
];

$updateResult = updateData('users', $updateData, 'user_id', $user['user_id']);

if (!$updateResult) {
    error_log('Photo Upload: Database update failed');
    $_SESSION['error'] = 'Failed to update profile picture';
    
    // Remove uploaded file if database update failed
    if (file_exists($uploadPath . $photoFilename)) {
        unlink($uploadPath . $photoFilename);
        error_log('Photo Upload: Removed file due to database update failure');
    }
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php');
    } else {
        redirectTo(SITE_URL);
    }
}

// Success
error_log('Photo Upload: Process completed successfully');
$_SESSION['success'] = 'Profile picture updated successfully';

// Redirect based on user type
if (hasUserType(USER_TYPE_JOBSEEKER)) {
    redirectTo(SITE_URL . '/pages/user/profile.php');
} else if (hasUserType(USER_TYPE_COMPANY)) {
    redirectTo(SITE_URL . '/pages/company/profile.php');
} else {
    redirectTo(SITE_URL);
}
