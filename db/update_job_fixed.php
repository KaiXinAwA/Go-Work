<?php
/**
 * Update Job API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to update jobs';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

// Check if license is approved
if (!$company || $company['license_status'] !== 'Approved') {
    $_SESSION['error'] = 'You need an approved license to update jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Get form data
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$job_title = isset($_POST['job_title']) ? sanitizeInput($_POST['job_title']) : '';
$job_type = isset($_POST['job_type']) ? sanitizeInput($_POST['job_type']) : '';
$location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
$description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
$requirements = isset($_POST['requirements']) ? sanitizeInput($_POST['requirements']) : '';
$category_string = isset($_POST['category_string']) ? sanitizeInput($_POST['category_string']) : '';
$salary_min = isset($_POST['salary_min']) ? (float)$_POST['salary_min'] : 0;
$salary_max = isset($_POST['salary_max']) ? (float)$_POST['salary_max'] : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate job ID
if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Validate required fields
if (empty($job_title) || empty($job_type) || empty($location) || empty($description) || empty($requirements)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    redirectTo(SITE_URL . '/pages/company/edit_job.php?id=' . $job_id);
}

// Check if the job belongs to this company
$job = fetchRow(
    "SELECT j.* 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.company_id 
    WHERE j.job_id = ? AND c.company_id = ?", 
    'ii', 
    [$job_id, $company['company_id']]
);

if (!$job) {
    $_SESSION['error'] = 'Job not found or you do not have permission to edit it';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Update job data
$jobData = [
    'job_title' => $job_title,
    'job_type' => $job_type,
    'location' => $location,
    'description' => $description,
    'requirements' => $requirements,
    'categories' => $category_string,
    'salary_min' => $salary_min,
    'salary_max' => $salary_max,
    'is_active' => $is_active
    // Removed updated_at field as it doesn't exist in the jobs table
];

$updated = updateData('jobs', $jobData, 'job_id', $job_id);

if (!$updated) {
    $_SESSION['error'] = 'Failed to update job. Please try again.';
    redirectTo(SITE_URL . '/pages/company/edit_job.php?id=' . $job_id);
}

// Set success message
$_SESSION['success'] = 'Job updated successfully';

// Redirect back to the jobs page
redirectTo(SITE_URL . '/pages/company/jobs.php');
?> 