<?php
/**
 * Login API Endpoint
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
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get and sanitize input
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate email
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Please enter a valid email address';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Validate password
if (empty($password)) {
    $_SESSION['error'] = 'Please enter your password';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if user exists
$sql = "SELECT * FROM users WHERE email = ?";
$user = fetchRow($sql, 's', [$email]);

if (!$user) {
    $_SESSION['error'] = 'Invalid email or password';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Verify password
if (!verifyPassword($password, $user['password'])) {
    $_SESSION['error'] = 'Invalid email or password';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Login successful, set session variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['user_type'] = $user['user_type'];

// Redirect based on user type
switch ($user['user_type']) {
    case USER_TYPE_JOBSEEKER:
        redirectTo(SITE_URL . '/pages/user/dashboard.php');
        break;
    case USER_TYPE_COMPANY:
        redirectTo(SITE_URL . '/pages/company/dashboard.php');
        break;
    case USER_TYPE_GOWORK:
        // Check if user is a worker
        $workerId = getWorkerId($user['user_id']);
        if ($workerId) {
            // If user is a worker, redirect to worker dashboard
            redirectTo(SITE_URL . '/pages/worker/dashboard.php');
        } else {
            // Otherwise, redirect to regular GoWork dashboard
            redirectTo(SITE_URL . '/pages/gowork/dashboard.php');
        }
        break;
    case USER_TYPE_ADMIN:
        redirectTo(SITE_URL . '/pages/admin/dashboard.php');
        break;
    default:
        redirectTo(SITE_URL);
}
