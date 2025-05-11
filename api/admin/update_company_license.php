<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from form
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($userId <= 0) {
        $_SESSION['error'] = 'Invalid user ID';
        redirectTo(SITE_URL . '/pages/admin/users_combined.php');
        exit;
    }
    
    // Get user details to verify it's a company
    $user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
    
    if (!$user || $user['user_type'] != USER_TYPE_COMPANY) {
        $_SESSION['error'] = 'User is not a company';
        redirectTo(SITE_URL . '/pages/admin/users_combined.php');
        exit;
    }
    
    // Get company details
    $company = getCompanyProfile($userId);
    $companyId = $company ? $company['company_id'] : null;
    
    if (!$companyId) {
        $_SESSION['error'] = 'Company profile not found';
        redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
        exit;
    }
    
    // Process license status update
    $licenseStatus = isset($_POST['license_status']) ? $_POST['license_status'] : 'Pending';
    
    // Check if a new license file was uploaded
    $licenseUpdated = false;
    $licensePath = $company['license_path'] ?? '';
    
    if (isset($_FILES['license']) && $_FILES['license']['error'] === UPLOAD_ERR_OK) {
        // Handle file upload
        $uploadDir = '../../uploads/licenses/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION);
        $newFileName = 'license_' . $userId . '_' . time() . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $newFileName;
        
        // Check file type (only allow PDF)
        if ($fileExtension !== 'pdf') {
            $_SESSION['error'] = 'Only PDF files are allowed for licenses';
            redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
            exit;
        }
        
        // Check file size (max 5MB)
        if ($_FILES['license']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'License file size must be less than 5MB';
            redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
            exit;
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['license']['tmp_name'], $targetFilePath)) {
            // Delete old license file if exists
            if (!empty($licensePath) && file_exists($uploadDir . $licensePath)) {
                unlink($uploadDir . $licensePath);
            }
            
            $licensePath = $newFileName;
            $licenseUpdated = true;
        } else {
            $_SESSION['error'] = 'Failed to upload license file';
            redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
            exit;
        }
    }
    
    // Update company license information
    $sql = "UPDATE company_profiles SET 
            license_status = ?"
            . ($licenseUpdated ? ", license_path = ?" : "") . "
            WHERE company_id = ?";
    
    $params = [];
    $types = '';
    
    $params[] = $licenseStatus;
    $types .= 's';
    
    if ($licenseUpdated) {
        $params[] = $licensePath;
        $types .= 's';
    }
    
    $params[] = $companyId;
    $types .= 'i';
    
    $result = executeQuery($sql, $types, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Company license updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update company license';
    }
    
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
} else {
    // If not a POST request, redirect to users page
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}