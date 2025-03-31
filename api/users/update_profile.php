<?php
/**
 * API endpoint for updating user profile
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to update profile';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/user/profile.php');
}

// Get user data
$user = getCurrentUser();
$userProfile = getUserProfile($user['user_id']);

if (!$userProfile) {
    // Create profile if it doesn't exist
    $profileData = [
        'user_id' => $user['user_id']
    ];
    
    $profileId = insertData('user_profiles', $profileData);
    
    if (!$profileId) {
        $_SESSION['error'] = 'Failed to create user profile';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $userProfile = getUserProfile($user['user_id']);
}

// Get section being updated
$section = isset($_POST['section']) ? sanitizeInput($_POST['section']) : '';

// Update based on section
if ($section === 'personal') {
    // Get and sanitize input
    $fullName = isset($_POST['full_name']) ? sanitizeInput($_POST['full_name']) : '';
    
    // Update personal info
    $updateData = [
        'full_name' => $fullName
    ];
    
    $updateResult = updateData('user_profiles', $updateData, 'user_id', $user['user_id']);
    
    if (!$updateResult) {
        $_SESSION['error'] = 'Failed to update personal information';
        redirectTo(SITE_URL . '/pages/user/profile.php#personal-info');
    }
    
    $_SESSION['success'] = 'Personal information updated successfully';
    redirectTo(SITE_URL . '/pages/user/profile.php#personal-info');
} elseif ($section === 'contact') {
    // Get and sanitize input
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
    $country = isset($_POST['country']) ? sanitizeInput($_POST['country']) : '';
    
    // Update contact info
    $updateData = [
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'country' => $country
    ];
    
    $updateResult = updateData('user_profiles', $updateData, 'user_id', $user['user_id']);
    
    if (!$updateResult) {
        $_SESSION['error'] = 'Failed to update contact information';
        redirectTo(SITE_URL . '/pages/user/profile.php#contact-info');
    }
    
    $_SESSION['success'] = 'Contact information updated successfully';
    redirectTo(SITE_URL . '/pages/user/profile.php#contact-info');
} elseif ($section === 'education') {
    // Get and sanitize input
    $education = isset($_POST['education']) ? sanitizeInput($_POST['education']) : '';
    
    // Update education info
    $updateData = [
        'education' => $education
    ];
    
    $updateResult = updateData('user_profiles', $updateData, 'user_id', $user['user_id']);
    
    if (!$updateResult) {
        $_SESSION['error'] = 'Failed to update education information';
        redirectTo(SITE_URL . '/pages/user/profile.php#education');
    }
    
    $_SESSION['success'] = 'Education information updated successfully';
    redirectTo(SITE_URL . '/pages/user/profile.php#education');
} else {
    // Invalid section
    $_SESSION['error'] = 'Invalid update section';
    redirectTo(SITE_URL . '/pages/user/profile.php');
}
