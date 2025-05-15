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
    
    // If company profile doesn't exist yet, create it
    if (!$companyId) {
        // Insert new company profile
        $sql = "INSERT INTO companies (user_id, company_name, description) VALUES (?, ?, ?)";
        $result = executeQuery($sql, 'iss', [
            $userId,
            $_POST['company_name'] ?? '',
            $_POST['description'] ?? ''
        ]);
        
        if ($result) {
            $companyId = getLastInsertId();
        } else {
            $_SESSION['error'] = 'Failed to create company profile';
            redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
            exit;
        }
    } else {
        // Update existing company profile
        $sql = "UPDATE companies SET 
                company_name = ?, 
                description = ?, 
                contact_number = ?, 
                address = ?, 
                city = ?, 
                state = ?, 
                country = ?, 
                updated_at = NOW() 
                WHERE company_id = ?";
        
        $result = executeQuery($sql, 'sssssssi', [
            $_POST['company_name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['contact_number'] ?? '',
            $_POST['address'] ?? '',
            $_POST['city'] ?? '',
            $_POST['state'] ?? '',
            $_POST['country'] ?? '',
            $companyId
        ]);
        
        if (!$result) {
            $_SESSION['error'] = 'Failed to update company profile';
            redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
            exit;
        }
    }
    
    $_SESSION['success'] = 'Company information updated successfully';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
} else {
    // If not a POST request, redirect to users page
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}