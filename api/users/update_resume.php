<?php
/**
 * API endpoint for updating user resume
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to update resume';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume');
}

// Check if resume file is uploaded
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'No resume file uploaded or upload error occurred';
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['resume']['error']) {
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
    redirectTo(SITE_URL . '/pages/user/profile.php#resume');
}

// Get user data
$user = getCurrentUser();
$userProfile = getUserProfile($user['user_id']);

if (!$userProfile) {
    $_SESSION['error'] = 'User profile not found';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume');
}

// Validate file type (only PDF is allowed)
$allowedTypes = ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'];
$uploadPath = RESUME_DIR;

// Debug log
error_log('Resume Upload: Starting upload process');
error_log('Resume Upload: Upload path set to ' . $uploadPath);

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
        error_log('Resume Upload: Changed upload path to parent directory: ' . $uploadPath);
    }
}

// Get file MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedType = $finfo->file($_FILES['resume']['tmp_name']);
error_log('Resume Upload: Detected file type: ' . $detectedType);

// For PDF files, be more lenient
if (strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION)) === 'pdf') {
    $allowedTypes[] = $detectedType; // Add detected type to allowed types for PDFs
    error_log('Resume Upload: File has PDF extension, allowing detected MIME type');
}

error_log('Resume Upload: Allowed types: ' . implode(', ', $allowedTypes));

// Upload file
$resumeFilename = uploadFile($_FILES['resume'], $uploadPath, $allowedTypes, MAX_RESUME_SIZE);

if (!$resumeFilename) {
    error_log('Resume Upload: File upload failed - invalid type or size');
    $_SESSION['error'] = 'Invalid file type or size. Only PDF files up to 5MB are allowed.';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume');
}

error_log('Resume Upload: File uploaded successfully as: ' . $resumeFilename);

// Delete old resume if exists
if (isset($userProfile['resume_path']) && !empty($userProfile['resume_path'])) {
    $oldResumePath = $uploadPath . $userProfile['resume_path'];
    if (file_exists($oldResumePath)) {
        @unlink($oldResumePath);
        error_log('Resume Upload: Deleted old resume: ' . $oldResumePath);
    }
}

// Update resume in database
$updateData = [
    'resume_path' => $resumeFilename
];

$updateResult = updateData('user_profiles', $updateData, 'user_id', $user['user_id']);

if (!$updateResult) {
    error_log('Resume Upload: Database update failed');
    $_SESSION['error'] = 'Failed to update resume information';
    
    // Remove uploaded file if database update failed
    if (file_exists($uploadPath . $resumeFilename)) {
        unlink($uploadPath . $resumeFilename);
        error_log('Resume Upload: Removed file due to database update failure');
    }
    
    redirectTo(SITE_URL . '/pages/user/profile.php#resume');
}

// Success
error_log('Resume Upload: Process completed successfully');
$_SESSION['success'] = 'Resume uploaded successfully';
redirectTo(SITE_URL . '/pages/user/profile.php#resume');
