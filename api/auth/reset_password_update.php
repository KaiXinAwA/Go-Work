<?php
/**
 * Reset Password API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Get and sanitize input
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$redirectUrl = isset($_POST['redirect_url']) ? sanitizeInput($_POST['redirect_url']) : '';

// Validate email
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Please enter a valid email address';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Ensure the password_resets table exists
ensurePasswordResetsTableExists();

// Check if user exists
$sql = "SELECT * FROM users WHERE email = ?";
$user = fetchRow($sql, 's', [$email]);

// Generate token and new password (regardless of whether user exists)
$token = bin2hex(random_bytes(32));
$newPassword = generateRandomPassword();
$hashedPassword = hashPassword($newPassword);

// Store token in the database (only if user exists)
if ($user) {
    // First check if there's an existing token for this email and delete it
    try {
        deleteData('password_resets', 'email', $email);
    } catch (Exception $e) {
        error_log("Error deleting existing tokens: " . $e->getMessage());
        // Continue with the process even if this fails
    }
    
    // Then insert the new token
    $tokenData = [
        'email' => $email,
        'token' => $token,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $inserted = false;
    try {
        $inserted = insertData('password_resets', $tokenData) > 0;
    } catch (Exception $e) {
        error_log("Error inserting token: " . $e->getMessage());
    }
    
    // Update user's password in the database
    if ($inserted) {
        try {
            $updated = updateData('users', ['password' => $hashedPassword], 'user_id', $user['user_id']);
            
            // Send email with the new password if update was successful
            if ($updated) {
                sendResetPasswordEmail($email, $token, $newPassword);
            }
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
        }
    }
}

// For security reasons, always show the same success message
// regardless of whether email exists or operation succeeded
$_SESSION['success'] = 'If your email exists in our system, you will receive password reset instructions shortly.';

// If the user is logged in and resetting their own password, redirect to the specified URL or default location
if (isLoggedIn()) {
    $loggedInUser = getCurrentUser();
    
    if ($loggedInUser['email'] === $email) {
        $_SESSION['success'] = 'Password reset email has been sent to your email address.';
        
        // Use the custom redirect URL if provided
        if (!empty($redirectUrl)) {
            redirectTo($redirectUrl);
        } else {
            // Default redirects based on user type
            if (hasUserType(USER_TYPE_COMPANY)) {
                redirectTo(SITE_URL . '/pages/company/profile.php#password');
            } else {
                redirectTo(SITE_URL . '/pages/user/profile.php#password');
            }
        }
    }
}

// Use the custom redirect URL if provided for non-logged in users too
if (!empty($redirectUrl)) {
    redirectTo($redirectUrl);
} else {
    // Default redirect to login page if not logged in or resetting another account
    redirectTo(SITE_URL . '/pages/login.php');
}

/**
 * Ensure that the password_resets table exists in the database
 */
function ensurePasswordResetsTableExists() {
    try {
        // Check if the table exists
        $result = fetchAll("SHOW TABLES LIKE 'password_resets'");
        
        // If table doesn't exist, create it
        if (empty($result)) {
            $createTableQuery = "CREATE TABLE IF NOT EXISTS `password_resets` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `email` VARCHAR(255) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `email_index` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            // Use direct connection since this is a DDL statement (no params to bind)
            $conn = getDbConnection();
            $conn->query($createTableQuery);
            
            // Log creation of the table for debugging
            error_log("Created password_resets table in the database");
        }
    } catch (Exception $e) {
        error_log("Error creating password_resets table: " . $e->getMessage());
        // Don't throw the exception further, let the process continue
    }
} 