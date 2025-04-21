<?php
/**
 * Job Application API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to apply for jobs';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Get job ID
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Check if job exists and is active
$job = fetchRow("SELECT j.*, c.company_id, c.user_id as company_user_id, u.email as company_email
                FROM jobs j 
                JOIN companies c ON j.company_id = c.company_id
                JOIN users u ON c.user_id = u.user_id
                WHERE j.job_id = ? AND j.is_active = 1", 
                'i', 
                [$job_id]);

if (!$job) {
    $_SESSION['error'] = 'Job not found or no longer active';
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Check if user has already applied for this job
$existingApplication = fetchRow("SELECT * FROM applications WHERE job_id = ? AND user_id = ?", 
                             'ii', 
                             [$job_id, $_SESSION['user_id']]);

if ($existingApplication) {
    // If application exists but was withdrawn, allow reapplying
    if ($existingApplication['status'] === 'Withdrawn') {
        // Update the existing application instead of creating a new one
        $updateData = [
            'status' => 'Pending',
            'applied_date' => date('Y-m-d H:i:s'), // Reset application date
            'resume_path' => $userProfile['resume_path']
        ];
        
        $updated = updateData('applications', $updateData, 'application_id', $existingApplication['application_id']);
        
        if (!$updated) {
            $_SESSION['error'] = 'Failed to reapply for this job. Please try again.';
            redirectTo(SITE_URL . '/pages/jobs.php?id=' . $job_id);
        }
        
        // Send notification email to company
        $emailSent = sendJobApplicationNotification($job_id, $_SESSION['user_id'], $job['company_email']);
        
        // Set success message
        $_SESSION['success'] = 'Your application has been resubmitted successfully!';
        
        // Redirect to job page
        redirectTo(SITE_URL . '/pages/jobs.php?id=' . $job_id);
        exit;
    } else {
        $_SESSION['error'] = 'You have already applied for this job';
        redirectTo(SITE_URL . '/pages/jobs.php?id=' . $job_id);
    }
}

// Get user profile and resume
$userProfile = getUserProfile($_SESSION['user_id']);

if (!$userProfile || !$userProfile['resume_path']) {
    $_SESSION['error'] = 'You need to upload a resume before applying for jobs';
    redirectTo(SITE_URL . '/pages/user/profile.php');
}

// All checks passed, proceed with application
$applicationData = [
    'job_id' => $job_id,
    'user_id' => $_SESSION['user_id'],
    'status' => 'Pending',
    'resume_path' => $userProfile['resume_path']
];

$application_id = insertData('applications', $applicationData);

if (!$application_id) {
    $_SESSION['error'] = 'Failed to submit application. Please try again.';
    redirectTo(SITE_URL . '/pages/jobs.php?id=' . $job_id);
}

// Send notification email to company
$emailSent = sendJobApplicationNotification($job_id, $_SESSION['user_id'], $job['company_email']);

// Set success message
$_SESSION['success'] = 'Your application has been submitted successfully!';

// Redirect to job page
redirectTo(SITE_URL . '/pages/jobs.php?id=' . $job_id);
