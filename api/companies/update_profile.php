<?php
/**
 * API endpoint for updating company profile
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to update profile';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/profile.php');
}

// Get section being updated
$section = isset($_POST['section']) ? sanitizeInput($_POST['section']) : '';

// Update based on section
if ($section === 'company') {
    // Get and sanitize input
    $company_name = isset($_POST['company_name']) ? sanitizeInput($_POST['company_name']) : '';
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    
    // Validate input
    if (empty($company_name) || empty($description)) {
        $_SESSION['error'] = 'Company name and description are required';
        redirectTo(SITE_URL . '/pages/company/profile.php#company-info');
    }
    
    // Update company info
    $updateData = [
        'company_name' => $company_name,
        'description' => $description
    ];
    
    $updateResult = updateData('companies', $updateData, 'company_id', $company['company_id']);
    
    if (!$updateResult) {
        $_SESSION['error'] = 'Failed to update company information';
        redirectTo(SITE_URL . '/pages/company/profile.php#company-info');
    }
    
    $_SESSION['success'] = 'Company information updated successfully';
    redirectTo(SITE_URL . '/pages/company/profile.php#company-info');
} elseif ($section === 'contact') {
    // Get and sanitize input
    $contact_number = isset($_POST['contact_number']) ? sanitizeInput($_POST['contact_number']) : '';
    $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
    $country = isset($_POST['country']) ? sanitizeInput($_POST['country']) : '';
    
    // Update contact info
    $updateData = [
        'contact_number' => $contact_number,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'country' => $country
    ];
    
    $updateResult = updateData('companies', $updateData, 'company_id', $company['company_id']);
    
    if (!$updateResult) {
        $_SESSION['error'] = 'Failed to update contact information';
        redirectTo(SITE_URL . '/pages/company/profile.php#contact-info');
    }
    
    $_SESSION['success'] = 'Contact information updated successfully';
    redirectTo(SITE_URL . '/pages/company/profile.php#contact-info');
} else {
    // Invalid section
    $_SESSION['error'] = 'Invalid update section';
    redirectTo(SITE_URL . '/pages/company/profile.php');
}
