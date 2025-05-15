<?php
/**
 * Registration API Endpoint
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
    redirectTo(SITE_URL . '/pages/register.php');
}

// Get and sanitize input
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
$user_type = isset($_POST['user_type']) ? (int)$_POST['user_type'] : 0;

// For company registration
$company_name = isset($_POST['company_name']) ? sanitizeInput($_POST['company_name']) : '';
$company_description = isset($_POST['company_description']) ? sanitizeInput($_POST['company_description']) : '';

// Validate required fields
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'All fields are required';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Validate email
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Please enter a valid email address';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Validate password match
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Validate password strength
// This is a simple check, consider adding more complex validation
if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Validate user type
if (!in_array($user_type, [USER_TYPE_JOBSEEKER, USER_TYPE_COMPANY])) {
    $_SESSION['error'] = 'Invalid user type';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Check if company-specific fields are provided for company registration
if ($user_type === USER_TYPE_COMPANY && (empty($company_name) || empty($company_description))) {
    $_SESSION['error'] = 'Company name and description are required';
    redirectTo(SITE_URL . '/pages/register.php');
}

// Check if email already exists
if (userExistsByEmail($email)) {
    $_SESSION['error'] = 'Email address is already registered';
    redirectTo(SITE_URL . '/pages/register.php');
}

// All validations passed, proceed with registration
$hashed_password = hashPassword($password);

// Begin transaction
$conn = getDbConnection();
$conn->begin_transaction();

try {
    // Insert into users table
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'user_type' => $user_type
    ];
    
    $user_id = insertData('users', $userData);
    
    if (!$user_id) {
        throw new Exception('Failed to create user account');
    }
    
    // Insert additional data based on user type
    if ($user_type === USER_TYPE_JOBSEEKER) {
        // Create empty profile for job seeker
        $profileData = [
            'user_id' => $user_id
        ];
        
        $profile_id = insertData('user_profiles', $profileData);
        
        if (!$profile_id) {
            throw new Exception('Failed to create user profile');
        }
    } elseif ($user_type === USER_TYPE_COMPANY) {
        // Create company profile
        $companyData = [
            'user_id' => $user_id,
            'company_name' => $company_name,
            'description' => $company_description,
            'license_status' => 'Pending'
        ];
        
        $company_id = insertData('companies', $companyData);
        
        if (!$company_id) {
            throw new Exception('Failed to create company profile');
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['success'] = 'Registration successful! You can now login.';
    
    // Redirect to login page
    redirectTo(SITE_URL . '/pages/login.php');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error'] = $e->getMessage();
    
    // Redirect back to registration page
    redirectTo(SITE_URL . '/pages/register.php');
}
