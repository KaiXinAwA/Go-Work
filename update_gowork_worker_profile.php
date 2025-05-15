<?php
/**
 * Update GoWork Worker Profile
 * This script allows GoWork workers to update their profile information
 */
$pageTitle = 'Update GoWork Worker Profile';
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Check if user is logged in and is a GoWork worker
if (!isLoggedIn() || !hasUserType(USER_TYPE_GOWORK)) {
    $_SESSION['error'] = 'You must be logged in as a GoWork worker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if worker exists in gowork_workers table
$workerId = getWorkerId($_SESSION['user_id']);
if (!$workerId) {
    $_SESSION['error'] = 'Worker account not found. Please contact an administrator.';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get worker data from database
$worker = fetchRow("SELECT * FROM gowork_workers WHERE worker_id = ?", 'i', [$workerId]);
$user = getCurrentUser();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $fullName = isset($_POST['full_name']) ? sanitizeInput($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $department = isset($_POST['department']) ? sanitizeInput($_POST['department']) : '';
    
    // Update user table for name (if doesn't exist in user_profiles)
    $userProfile = getUserProfile($user['user_id']);
    if (!$userProfile) {
        // Create profile entry
        $profileData = [
            'user_id' => $user['user_id'],
            'full_name' => $fullName,
            'phone' => $phone
        ];
        
        insertData('user_profiles', $profileData);
    } else {
        // Update existing profile
        $updateData = [
            'full_name' => $fullName,
            'phone' => $phone
        ];
        
        updateData('user_profiles', $updateData, 'user_id', $user['user_id']);
    }
    
    // Update department in gowork_workers table
    $updateWorkerData = [
        'department' => $department
    ];
    
    updateData('gowork_workers', $updateWorkerData, 'worker_id', $workerId);
    
    $_SESSION['success'] = 'Your profile has been updated successfully';
    redirectTo(SITE_URL . '/update_gowork_worker_profile.php');
}

// Include header
require_once 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Profile</li>
                </ol>
            </nav>
            <h2>Update Profile</h2>
            <p class="text-muted">Update your GoWork worker profile information</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/update_gowork_worker_profile.php" method="POST">
                        <!-- Personal Information -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php 
                                $userProfile = getUserProfile($user['user_id']);
                                echo isset($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : '';
                            ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php 
                                echo isset($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : '';
                            ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department" value="<?php 
                                echo isset($worker['department']) ? htmlspecialchars($worker['department']) : '';
                            ?>">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Account Type:</strong> GoWork Worker
                        </li>
                        <li class="list-group-item">
                            <strong>Joined:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="<?php echo SITE_URL; ?>/pages/change_password.php" class="btn btn-outline-secondary w-100">Change Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 