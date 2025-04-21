<?php
/**
 * API endpoint for managing career history
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to update career history';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/user/profile.php');
}

// Get user data
$user = getCurrentUser();

// Get action type
$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';

if ($action === 'add' || $action === 'update') {
    // Get and sanitize input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $jobTitle = isset($_POST['job_title']) ? sanitizeInput($_POST['job_title']) : '';
    $companyName = isset($_POST['company_name']) ? sanitizeInput($_POST['company_name']) : '';
    $startDate = isset($_POST['start_date']) ? sanitizeInput($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : null;
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    
    // Validate required fields
    if (empty($jobTitle) || empty($companyName) || empty($startDate)) {
        $_SESSION['error'] = 'Job title, company name, and start date are required';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    // Prepare data
    $careerData = [
        'job_title' => $jobTitle,
        'company_name' => $companyName,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'description' => $description
    ];
    
    if ($action === 'add') {
        // Add user_id for new records
        $careerData['user_id'] = $user['user_id'];
        
        // Insert new record
        $inserted = insertData('career_history', $careerData);
        
        if (!$inserted) {
            $_SESSION['error'] = 'Failed to add career history';
            redirectTo(SITE_URL . '/pages/user/profile.php');
        }
        
        $_SESSION['success'] = 'Career history added successfully';
    } else {
        // Update existing record
        // First verify the record belongs to the user
        $career = fetchRow("SELECT * FROM career_history WHERE id = ? AND user_id = ?", 'ii', [$id, $user['user_id']]);
        
        if (!$career) {
            $_SESSION['error'] = 'Career history record not found or does not belong to you';
            redirectTo(SITE_URL . '/pages/user/profile.php');
        }
        
        $updated = updateData('career_history', $careerData, 'id', $id);
        
        if (!$updated) {
            $_SESSION['error'] = 'Failed to update career history';
            redirectTo(SITE_URL . '/pages/user/profile.php');
        }
        
        $_SESSION['success'] = 'Career history updated successfully';
    }
} elseif ($action === 'delete') {
    // Get record ID
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['error'] = 'Invalid career history ID';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    // Verify the record belongs to the user
    $career = fetchRow("SELECT * FROM career_history WHERE id = ? AND user_id = ?", 'ii', [$id, $user['user_id']]);
    
    if (!$career) {
        $_SESSION['error'] = 'Career history record not found or does not belong to you';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    // Delete the record
    $deleted = deleteData('career_history', 'id', $id);
    
    if (!$deleted) {
        $_SESSION['error'] = 'Failed to delete career history';
        redirectTo(SITE_URL . '/pages/user/profile.php');
    }
    
    $_SESSION['success'] = 'Career history deleted successfully';
} else {
    $_SESSION['error'] = 'Invalid action';
}

// Redirect back to profile page
redirectTo(SITE_URL . '/pages/user/profile.php'); 