<?php
/**
 * API endpoint for admin to delete user accounts
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to delete user accounts';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get redirect URL from query string
$redirect = isset($_GET['redirect']) ? sanitizeInput($_GET['redirect']) : 'users_combined.php';
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : '';
$redirectUrl = SITE_URL . '/pages/admin/' . $redirect;
if (!empty($tab)) {
    $redirectUrl .= '?tab=' . $tab;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo($redirectUrl);
}

// Do not allow admins to delete themselves
$currentUser = getCurrentUser();
if ($currentUser['user_id'] == $userId) {
    $_SESSION['error'] = 'You cannot delete your own admin account';
    redirectTo($redirectUrl);
}

// Check if user exists
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo($redirectUrl);
}

try {
    // Start transaction
    $conn = getDbConnection();
    $conn->begin_transaction();
    
    // First delete dependent records
    
    // 1. Delete user profile
    $sql = "DELETE FROM user_profiles WHERE user_id = ?";
    executeQuery($sql, 'i', [$userId]);
    
    // 2. Delete job applications if it's a job seeker
    if ($user['user_type'] == USER_TYPE_JOBSEEKER) {
        $sql = "DELETE FROM applications WHERE user_id = ?";
        executeQuery($sql, 'i', [$userId]);
    }
    
    // 3. Delete career history if it's a job seeker
    if ($user['user_type'] == USER_TYPE_JOBSEEKER) {
        $sql = "DELETE FROM career_history WHERE user_id = ?";
        executeQuery($sql, 'i', [$userId]);
    }
    
    // 4. If it's a company, delete company profile and jobs
    if ($user['user_type'] == USER_TYPE_COMPANY) {
        // Delete company jobs
        $sql = "DELETE FROM jobs WHERE company_id IN (SELECT company_id FROM companies WHERE user_id = ?)";
        executeQuery($sql, 'i', [$userId]);
        
        // Delete company profile
        $sql = "DELETE FROM companies WHERE user_id = ?";
        executeQuery($sql, 'i', [$userId]);
    }
    
    // 5. If it's a GoWork staff, delete their record
    if ($user['user_type'] == USER_TYPE_GOWORK) {
        $sql = "DELETE FROM gowork_workers WHERE user_id = ?";
        executeQuery($sql, 'i', [$userId]);
    }
    
    // 6. If it's an admin, delete their record
    if ($user['user_type'] == USER_TYPE_ADMIN) {
        $sql = "DELETE FROM admin WHERE user_id = ?";
        executeQuery($sql, 'i', [$userId]);
    }
    
    // Finally, delete the user account itself
    $sql = "DELETE FROM users WHERE user_id = ?";
    $result = executeQuery($sql, 'i', [$userId]);
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = 'User account has been deleted successfully';
    
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    
    $_SESSION['error'] = 'Failed to delete user account: ' . $e->getMessage();
}

// Redirect back to users page
redirectTo($redirectUrl);
?> 