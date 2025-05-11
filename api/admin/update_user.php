<?php
/**
 * API endpoint for admin to update user profiles
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to update user profiles';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user ID
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user data
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get and sanitize input data
$username = sanitizeInput($_POST['username']);
$email = sanitizeInput($_POST['email']);
$userType = (int)$_POST['user_type'];
$fullName = sanitizeInput($_POST['full_name']);
$phone = sanitizeInput($_POST['phone']);
$address = sanitizeInput($_POST['address']);
$city = sanitizeInput($_POST['city']);
$state = sanitizeInput($_POST['state']);
$country = sanitizeInput($_POST['country']);

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
    // Check if email already exists for another user
    $existingUser = fetchRow("SELECT user_id FROM users WHERE email = ? AND user_id != ?", 'si', [$email, $userId]);
    if ($existingUser) {
        $errors[] = 'This email is already registered by another user';
    }
}

if (empty($userType)) {
    $errors[] = 'User type is required';
}

// If there are validation errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirectTo(SITE_URL . "/pages/admin/edit_user.php?id=$userId");
}

// Update user table using direct queries instead of transactions
try {
    // Update user table
    $sql = "UPDATE users SET username = ?, email = ?, user_type = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'ssii', [$username, $email, $userType, $userId]);
    
    if (!$result) {
        throw new Exception('Failed to update user information');
    }
    
    // Check if user profile exists
    $profile = fetchRow("SELECT * FROM user_profiles WHERE user_id = ?", 'i', [$userId]);
    
    if ($profile) {
        // Update profile
        $sql = "UPDATE user_profiles SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
        $result = executeQuery($sql, 'ssssssi', [$fullName, $phone, $address, $city, $state, $country, $userId]);
        
        if (!$result) {
            throw new Exception('Failed to update user profile');
        }
    } else {
        // Insert new profile
        $sql = "INSERT INTO user_profiles (user_id, full_name, phone, address, city, state, country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = executeQuery($sql, 'issssss', [$userId, $fullName, $phone, $address, $city, $state, $country]);
        
        if (!$result) {
            throw new Exception('Failed to create user profile');
        }
    }
    
    $_SESSION['success'] = 'User updated successfully';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    redirectTo(SITE_URL . "/pages/admin/edit_user.php?id=$userId");
}
?> 