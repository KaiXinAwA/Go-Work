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
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
    $country = isset($_POST['country']) ? sanitizeInput($_POST['country']) : '';
    
    // Update personal and contact info using direct query
    $sql = "UPDATE user_profiles SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'ssssssi', [$fullName, $phone, $address, $city, $state, $country, $user['user_id']]);
    
    if (!$result) {
        $_SESSION['error'] = 'Failed to update personal information';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $_SESSION['success'] = 'Profile information updated successfully';
} elseif ($section === 'contact') {
    // Get and sanitize input
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
    $country = isset($_POST['country']) ? sanitizeInput($_POST['country']) : '';
    
    // Update contact info using direct query
    $sql = "UPDATE user_profiles SET phone = ?, address = ?, city = ?, state = ?, country = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'sssssi', [$phone, $address, $city, $state, $country, $user['user_id']]);
    
    if (!$result) {
        $_SESSION['error'] = 'Failed to update contact information';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $_SESSION['success'] = 'Contact information updated successfully';
} elseif ($section === 'education') {
    // Get and sanitize input
    $education = isset($_POST['education']) ? sanitizeInput($_POST['education']) : '';
    $institution = isset($_POST['institution']) ? sanitizeInput($_POST['institution']) : '';
    $completionStatus = isset($_POST['completion_status']) ? sanitizeInput($_POST['completion_status']) : '';
    $educationHighlights = isset($_POST['education_highlights']) ? sanitizeInput($_POST['education_highlights']) : '';
    
    // Update education info using direct query
    $sql = "UPDATE user_profiles SET education = ?, institution = ?, completion_status = ?, education_highlights = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'ssssi', [$education, $institution, $completionStatus, $educationHighlights, $user['user_id']]);
    
    if (!$result) {
        $_SESSION['error'] = 'Failed to update education information';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $_SESSION['success'] = 'Education information updated successfully';
} elseif ($section === 'skills') {
    // Get and sanitize input
    $skills = isset($_POST['skills']) ? sanitizeInput($_POST['skills']) : '';
    
    // Update skills info using direct query
    $sql = "UPDATE user_profiles SET skills = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'si', [$skills, $user['user_id']]);
    
    if (!$result) {
        $_SESSION['error'] = 'Failed to update skills information';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $_SESSION['success'] = 'Skills information updated successfully';
} else {
    $_SESSION['error'] = 'Invalid section';
}

// Redirect back to profile page
redirectTo(SITE_URL . '/pages/user/profile.php');
