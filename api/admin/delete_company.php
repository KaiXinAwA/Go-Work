<?php
/**
 * API endpoint for admin to delete companies
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to delete companies';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get company ID from URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($companyId <= 0) {
    $_SESSION['error'] = 'Invalid company ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Check if company exists
$company = fetchRow("SELECT c.*, u.user_id FROM companies c JOIN users u ON c.user_id = u.user_id WHERE c.company_id = ?", 'i', [$companyId]);
if (!$company) {
    $_SESSION['error'] = 'Company not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

try {
    // Start transaction
    $conn = getDbConnection();
    $conn->begin_transaction();
    
    // Delete jobs and applications
    $sql = "DELETE FROM jobs WHERE company_id = ?";
    executeQuery($sql, 'i', [$companyId]);
    
    // Delete the company
    $sql = "DELETE FROM companies WHERE company_id = ?";
    executeQuery($sql, 'i', [$companyId]);
    
    // Delete the user account
    $userId = $company['user_id'];
    
    // Delete user profile
    $sql = "DELETE FROM user_profiles WHERE user_id = ?";
    executeQuery($sql, 'i', [$userId]);
    
    // Delete the user
    $sql = "DELETE FROM users WHERE user_id = ?";
    executeQuery($sql, 'i', [$userId]);
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = 'Company has been deleted successfully';
    
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    
    $_SESSION['error'] = 'Failed to delete company: ' . $e->getMessage();
}

// Redirect back to companies page
redirectTo(SITE_URL . '/pages/admin/companies.php');
?> 