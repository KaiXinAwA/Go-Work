<?php
/**
 * API endpoint for updating company license
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to update license';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Check if license file is uploaded
if (!isset($_FILES['license']) || $_FILES['license']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'No license file uploaded or upload error occurred';
    
    if (isset($_FILES['license']) && $_FILES['license']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['license']['error']) {
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
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Validate file type (only PDF is allowed)
$allowedTypes = ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'];
$uploadPath = LICENSE_DIR;

// DEBUG: Log detected MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedType = $finfo->file($_FILES['license']['tmp_name']);
error_log("Detected MIME type for license: " . $detectedType);

// Add the detected type to allowed types if it's a PDF-like type
if (strpos($detectedType, 'pdf') !== false) {
    $allowedTypes[] = $detectedType;
}

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
    }
}

// Upload file
$licenseFilename = uploadFile($_FILES['license'], $uploadPath, $allowedTypes, MAX_LICENSE_SIZE);

if (!$licenseFilename) {
    $_SESSION['error'] = 'Invalid file type or size. Only PDF files up to 5MB are allowed.';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Update license in database
$updateData = [
    'license_path' => $licenseFilename,
    'license_status' => 'Pending' // Reset status to pending for review
];

$updateResult = updateData('companies', $updateData, 'company_id', $company['company_id']);

if (!$updateResult) {
    $_SESSION['error'] = 'Failed to update license information';
    
    // Remove uploaded file if database update failed
    if (file_exists($uploadPath . $licenseFilename)) {
        unlink($uploadPath . $licenseFilename);
    }
    
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Success
$_SESSION['success'] = 'License uploaded successfully and is pending review';
redirectTo(SITE_URL . '/pages/company/profile.php#license');
