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

// Validate email
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Please enter a valid email address';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Check if user exists
$sql = "SELECT * FROM users WHERE email = ?";
$user = fetchRow($sql, 's', [$email]);

if (!$user) {
    // For security reasons, still show success message even if email doesn't exist
    $_SESSION['success'] = 'If your email exists in our system, you will receive password reset instructions shortly.';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Generate token and new password
$token = bin2hex(random_bytes(32));
$newPassword = generateRandomPassword();
$hashedPassword = hashPassword($newPassword);

// Store token in the database (you would need to create a password_resets table)
$tokenData = [
    'email' => $email,
    'token' => $token
];

$inserted = insertData('password_resets', $tokenData);

if (!$inserted) {
    $_SESSION['error'] = 'Failed to process your request. Please try again later.';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Update user's password in the database
$updated = updateData('users', ['password' => $hashedPassword], 'user_id', $user['user_id']);

if (!$updated) {
    $_SESSION['error'] = 'Failed to reset your password. Please try again later.';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Send email with the new password
$emailSent = sendResetPasswordEmail($email, $token, $newPassword);

if (!$emailSent) {
    $_SESSION['error'] = 'Failed to send email. Please try again later.';
    redirectTo(SITE_URL . '/pages/forgot_password.php');
}

// Set success message
$_SESSION['success'] = 'Password reset successful! Check your email for your new password.';

// Redirect to login page
redirectTo(SITE_URL . '/pages/login.php');
