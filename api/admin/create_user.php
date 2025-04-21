<?php
/**
 * API endpoint for admin to create new users
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to create users';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get redirect location (used for success/error redirects)
$redirect = isset($_POST['redirect']) ? sanitizeInput($_POST['redirect']) : 'users_combined.php';

// Get and sanitize input data
$username = sanitizeInput($_POST['username']);
$email = sanitizeInput($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$userType = (int)$_POST['user_type'];
$fullName = sanitizeInput($_POST['full_name'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$city = sanitizeInput($_POST['city'] ?? '');
$state = sanitizeInput($_POST['state'] ?? '');
$country = sanitizeInput($_POST['country'] ?? '');

// Validate input
$errors = [];

if (empty($username)) {
    $errors[] = 'Username is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} else {
    // Check if email already exists
    $existingUser = fetchRow("SELECT user_id FROM users WHERE email = ?", 's', [$email]);
    if ($existingUser) {
        $errors[] = 'This email is already registered';
    }
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
} elseif ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// Validate user type - only allow staff user types to be created from admin page
if (!in_array($userType, [USER_TYPE_GOWORK, USER_TYPE_ADMIN])) {
    $errors[] = 'Invalid user type';
}

// If there are validation errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirectTo(SITE_URL . "/pages/admin/add_staff.php");
}

// Hash password
$hashedPassword = hashPassword($password);

// Begin transaction
$conn = getDbConnection();
$conn->begin_transaction();

try {
    // Insert into users table
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'user_type' => $userType
    ];
    
    $userId = insertData('users', $userData);
    
    if (!$userId) {
        throw new Exception('Failed to create user account');
    }
    
    // Insert profile data
    $profileData = [
        'user_id' => $userId,
        'full_name' => $fullName,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'country' => $country
    ];
    
    $profileId = insertData('user_profiles', $profileData);
    
    if (!$profileId) {
        throw new Exception('Failed to create user profile');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['success'] = 'User created successfully';
    
    // Redirect to users page
    redirectTo(SITE_URL . '/pages/admin/' . $redirect);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error'] = $e->getMessage();
    
    // Redirect back
    redirectTo(SITE_URL . '/pages/admin/add_staff.php');
}
?> 