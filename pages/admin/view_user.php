<?php
$pageTitle = 'View User';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user details
$sql = "SELECT * FROM users WHERE user_id = ?";
$user = fetchRow($sql, 'i', [$userId]);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user profile
$userProfile = getUserProfile($userId);

// Get career history if user is a jobseeker
$careerHistory = ($user['user_type'] == USER_TYPE_JOBSEEKER) ? getUserCareerHistory($userId) : [];

// Get company details if user is a company
$companyDetails = ($user['user_type'] == USER_TYPE_COMPANY) ? getCompanyProfile($userId) : null;

// Get GoWork worker details if user is a GoWork worker
$goworkDetails = null;
if ($user['user_type'] == USER_TYPE_GOWORK) {
    $goworkDetails = fetchRow("SELECT * FROM gowork_workers WHERE user_id = ?", 'i', [$userId]);
}

// Get admin details if user is an admin
$adminDetails = null;
if ($user['user_type'] == USER_TYPE_ADMIN) {
    $adminDetails = fetchRow("SELECT * FROM admin WHERE user_id = ?", 'i', [$userId]);
}

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <!-- User Identification Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-primary">
                <h4 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Viewing Profile: <strong><?php echo htmlspecialchars($user['username']); ?></strong> 
                    <span class="badge bg-secondary ms-2">ID: <?php echo $user['user_id']; ?></span>
                </h4>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php">Manage Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View User</li>
                </ol>
            </nav>
            
            <!-- Page actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>User Details</h2>
                <div>
                    <a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/admin/edit_user.php?id=<?php echo $userId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- User Info -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if (isset($user['profile_picture']) && !empty($user['profile_picture'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <?php
                    $userTypeText = '';
                    $typeClass = '';
                    
                    switch ($user['user_type']) {
                        case USER_TYPE_JOBSEEKER:
                            $userTypeText = 'Job Seeker';
                            $typeClass = 'bg-primary';
                            break;
                        case USER_TYPE_COMPANY:
                            $userTypeText = 'Company';
                            $typeClass = 'bg-success';
                            break;
                        case USER_TYPE_GOWORK:
                            $userTypeText = 'GoWork';
                            $typeClass = 'bg-info';
                            break;
                        case USER_TYPE_ADMIN:
                            $userTypeText = 'Admin';
                            $typeClass = 'bg-danger';
                            break;
                    }
                    ?>
                    
                    <div class="d-flex justify-content-center mt-3">
                        <div class="badge <?php echo $typeClass; ?> me-2">
                            <?php echo $userTypeText; ?>
                        </div>
                        <div class="badge bg-secondary">
                            <i class="fas fa-calendar-alt"></i> Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Username:</div>
                            <div class="col-6 text-end"><?php echo htmlspecialchars($user['username']); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Email:</div>
                            <div class="col-6 text-end"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Joined:</div>
                            <div class="col-6 text-end"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Last Update:</div>
                            <div class="col-6 text-end"><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Details -->
        <div class="col-md-8">
            <?php if ($userProfile): ?>
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Full Name</h6>
                            <p><?php echo isset($userProfile['full_name']) && !empty($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : 'Not provided'; ?></p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Phone Number</h6>
                            <p><?php echo isset($userProfile['phone']) && !empty($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : 'Not provided'; ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Address</h6>
                        <p>
                            <?php 
                            if (isset($userProfile['address']) && !empty($userProfile['address'])) {
                                echo htmlspecialchars($userProfile['address']) . '<br>';
                            }
                            
                            $location = [];
                            if (isset($userProfile['city']) && !empty($userProfile['city'])) $location[] = htmlspecialchars($userProfile['city']);
                            if (isset($userProfile['state']) && !empty($userProfile['state'])) $location[] = htmlspecialchars($userProfile['state']);
                            if (isset($userProfile['country']) && !empty($userProfile['country'])) $location[] = htmlspecialchars($userProfile['country']);
                            
                            echo !empty($location) ? implode(', ', $location) : 'Address not provided';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Education Information (for JobSeekers) -->
            <?php if ($user['user_type'] == USER_TYPE_JOBSEEKER): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Education</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($userProfile['education']) && !empty($userProfile['education']) || isset($userProfile['institution']) && !empty($userProfile['institution'])): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Education Level</h6>
                                <p><?php echo isset($userProfile['education']) && !empty($userProfile['education']) ? htmlspecialchars($userProfile['education']) : 'Not provided'; ?></p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Institution</h6>
                                <p><?php echo isset($userProfile['institution']) && !empty($userProfile['institution']) ? htmlspecialchars($userProfile['institution']) : 'Not provided'; ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Completion Status</h6>
                                <p><?php echo isset($userProfile['completion_status']) && !empty($userProfile['completion_status']) ? htmlspecialchars($userProfile['completion_status']) : 'Not provided'; ?></p>
                            </div>
                        </div>
                        
                        <?php if (isset($userProfile['education_highlights']) && !empty($userProfile['education_highlights'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted">Education Highlights</h6>
                            <p><?php echo nl2br(htmlspecialchars($userProfile['education_highlights'])); ?></p>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No education information provided.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Skills Information (for JobSeekers) -->
            <?php if ($user['user_type'] == USER_TYPE_JOBSEEKER && isset($userProfile['skills']) && !empty($userProfile['skills'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Skills</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($userProfile['skills'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Resume Information (for JobSeekers) -->
            <?php if ($user['user_type'] == USER_TYPE_JOBSEEKER && isset($userProfile['resume_path']) && !empty($userProfile['resume_path'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resume</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $userProfile['resume_path']; ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> View Resume
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Career History (for JobSeekers) -->
            <?php if ($user['user_type'] == USER_TYPE_JOBSEEKER && !empty($careerHistory)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Career History</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($careerHistory as $job): ?>
                    <div class="mb-4 border-bottom pb-3">
                        <h6><?php echo htmlspecialchars($job['job_title']); ?> at <?php echo htmlspecialchars($job['company_name']); ?></h6>
                        <p class="text-muted">
                            <?php 
                            echo date('M Y', strtotime($job['start_date'])); 
                            echo ' - ';
                            echo !empty($job['end_date']) ? date('M Y', strtotime($job['end_date'])) : 'Present';
                            ?>
                        </p>
                        <?php if (!empty($job['description'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Company Information (for Companies) -->
            <?php if ($user['user_type'] == USER_TYPE_COMPANY && $companyDetails): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Company Name</h6>
                        <p><?php echo htmlspecialchars($companyDetails['company_name']); ?></p>
                    </div>
                    
                    <?php if (!empty($companyDetails['description'])): ?>
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($companyDetails['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">License Status</h6>
                        <span class="badge <?php 
                            echo $companyDetails['license_status'] === 'Approved' ? 'bg-success' : 
                                 ($companyDetails['license_status'] === 'Not Approved' ? 'bg-danger' : 'bg-warning'); 
                        ?>">
                            <?php echo $companyDetails['license_status']; ?>
                        </span>
                        
                        <?php if (!empty($companyDetails['license_path'])): ?>
                        <div class="mt-2">
                            <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $companyDetails['license_path']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> View License
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?php echo SITE_URL; ?>/pages/admin/view_company.php?id=<?php echo $companyDetails['company_id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-building me-2"></i> View Full Company Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- GoWork Information -->
            <?php if ($user['user_type'] == USER_TYPE_GOWORK && $goworkDetails): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">GoWork Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Department</h6>
                        <p><?php echo isset($goworkDetails['department']) && !empty($goworkDetails['department']) ? htmlspecialchars($goworkDetails['department']) : 'Not assigned'; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Joined GoWork</h6>
                        <p><?php echo date('M d, Y', strtotime($goworkDetails['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Admin Information -->
            <?php if ($user['user_type'] == USER_TYPE_ADMIN && $adminDetails): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Role</h6>
                        <p><?php echo htmlspecialchars($adminDetails['role']); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Admin Since</h6>
                        <p><?php echo date('M d, Y', strtotime($adminDetails['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        This user has not completed their profile yet.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone and will delete all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo SITE_URL; ?>/api/admin/delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger">Delete User</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 