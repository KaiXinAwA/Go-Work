<?php
$pageTitle = 'View User';
require_once '../../includes/init.php';

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
$viewedUser = fetchRow($sql, 'i', [$userId]);

if (!$viewedUser) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user profile
$userProfile = getUserProfile($userId);

// Get user's culture quiz results
$userCultureResults = getEnhancedUserCultureResults($userId);

// Get career history if user is a jobseeker
$careerHistory = ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER) ? getEnhancedUserCareerHistory($userId) : [];

// Get company details if user is a company
$companyDetails = ($viewedUser['user_type'] == USER_TYPE_COMPANY) ? getCompanyProfile($userId) : null;

// Get GoWork worker details if user is a GoWork worker
$goworkDetails = null;
if ($viewedUser['user_type'] == USER_TYPE_GOWORK) {
    $goworkDetails = fetchRow("SELECT * FROM gowork_workers WHERE user_id = ?", 'i', [$userId]);
}

// Get admin details if user is an admin
$adminDetails = null;
if ($viewedUser['user_type'] == USER_TYPE_ADMIN) {
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
                    Viewing Profile: <strong><?php echo htmlspecialchars($viewedUser['username']); ?></strong> 
                    <span class="badge bg-secondary ms-2">ID: <?php echo $viewedUser['user_id']; ?></span>
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
                    <?php if (isset($viewedUser['profile_picture']) && !empty($viewedUser['profile_picture'])): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $viewedUser['profile_picture']; ?>" alt="Profile Picture" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($viewedUser['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($viewedUser['email']); ?></p>
                    
                    <?php
                    $userTypeText = '';
                    $typeClass = '';
                    
                    switch ($viewedUser['user_type']) {
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
                            <i class="fas fa-calendar-alt"></i> Member since: <?php echo date('M Y', strtotime($viewedUser['created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Username:</div>
                            <div class="col-6 text-end"><?php echo htmlspecialchars($viewedUser['username']); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Email:</div>
                            <div class="col-6 text-end"><?php echo htmlspecialchars($viewedUser['email']); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Joined:</div>
                            <div class="col-6 text-end"><?php echo date('M d, Y', strtotime($viewedUser['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Last Update:</div>
                            <div class="col-6 text-end"><?php echo date('M d, Y', strtotime($viewedUser['updated_at'])); ?></div>
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
            <?php if ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER): ?>
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
            <?php if ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER && isset($userProfile['skills']) && !empty($userProfile['skills'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Skills</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($userProfile['skills'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Culture Preferences (for JobSeekers) -->
            <?php if ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER && $userCultureResults && !empty($userCultureResults['culture_profile']['values'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Cultural Preferences</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $attributeNames = [
                            'work_environment' => 'Work Environment',
                            'overtime' => 'Overtime Expectations',
                            'management' => 'Management Style',
                            'work_life_balance' => 'Work-Life Balance',
                            'dress_code' => 'Dress Code',
                            'communication' => 'Communication Style',
                            'decision_making' => 'Decision Making',
                            'innovation' => 'Innovation',
                            'social_events' => 'Social Events',
                            'feedback' => 'Feedback Style'
                        ];
                        
                        foreach ($userCultureResults['culture_profile']['values'] as $attribute => $value): 
                        ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">
                                        <?php 
                                        // Handle both attribute formats (key-value or direct value)
                                        if (isset($attributeNames[$attribute])) {
                                            echo htmlspecialchars($attributeNames[$attribute]);
                                        } else {
                                            echo htmlspecialchars(ucfirst($attribute));
                                        }
                                        ?>
                                    </span>
                                    <span><?php echo htmlspecialchars(ucfirst($value)); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Resume Information (for JobSeekers) -->
            <?php if ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER && isset($userProfile['resume_path']) && !empty($userProfile['resume_path'])): ?>
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
            <?php if ($viewedUser['user_type'] == USER_TYPE_JOBSEEKER && !empty($careerHistory)): ?>
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
            <?php if ($viewedUser['user_type'] == USER_TYPE_COMPANY && $companyDetails): ?>
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
                    
                    <!-- Contact Information -->
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Email</h6>
                            <p><?php echo htmlspecialchars($viewedUser['email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Company Culture (for Companies) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Company Culture</h5>
                </div>
                <div class="card-body">
                    <?php 
                    // Get company culture information
                    $companyCulture = getCompanyCulture($companyDetails['company_id']);
                    
                    // Define attribute display names for better readability
                    $attributeNames = [
                        'work_environment' => 'Work Environment',
                        'overtime' => 'Overtime Expectations',
                        'management' => 'Management Style',
                        'work_life_balance' => 'Work-Life Balance',
                        'dress_code' => 'Dress Code',
                        'communication' => 'Communication Style',
                        'decision_making' => 'Decision Making',
                        'innovation' => 'Innovation',
                        'social_events' => 'Social Events',
                        'feedback' => 'Feedback Style'
                    ];
                    
                    if ($companyCulture && !empty($companyCulture['values'])):
                    ?>
                        <div class="mb-4">
                            <?php if (!empty($companyCulture['description'])): ?>
                                <h6>About Their Culture</h6>
                                <p><?php echo nl2br(htmlspecialchars($companyCulture['description'])); ?></p>
                                <hr>
                            <?php endif; ?>
                            
                            <h6>Culture Values</h6>
                            <div class="row">
                                <?php foreach ($companyCulture['values'] as $attribute => $value): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-dark me-2"><?php echo isset($attributeNames[$attribute]) ? htmlspecialchars($attributeNames[$attribute]) : htmlspecialchars(ucfirst($attribute)); ?></span>
                                            <span><?php echo htmlspecialchars(ucfirst($value)); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>This company hasn't defined their company culture yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- License Information (for Companies) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">License Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Current Status</h6>
                        <span class="badge <?php 
                            echo $companyDetails['license_status'] === 'Approved' ? 'bg-success' : 
                                 ($companyDetails['license_status'] === 'Not Approved' ? 'bg-danger' : 'bg-warning'); 
                        ?> p-2">
                            <?php echo $companyDetails['license_status']; ?>
                        </span>
                    </div>
                    
                    <?php if (isset($companyDetails['license_path']) && !empty($companyDetails['license_path'])): ?>
                    <div class="mb-3">
                        <h6 class="text-muted">License Document</h6>
                        <div class="d-flex gap-2 mt-2">
                            <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $companyDetails['license_path']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> View License
                            </a>
                            
                            <a href="<?php echo SITE_URL; ?>/pages/admin/review_license.php?id=<?php echo $companyDetails['company_id']; ?>" class="btn btn-info">
                                <i class="fas fa-file-contract me-2"></i> Review License
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> No license document has been uploaded by this company.
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-0">
                        <h6 class="text-muted">Update License Status</h6>
                        <form action="<?php echo SITE_URL; ?>/api/admin/update_license_status.php" method="POST" class="mt-2">
                            <input type="hidden" name="company_id" value="<?php echo $companyDetails['company_id']; ?>">
                            
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <select class="form-select" name="license_status">
                                        <option value="Pending" <?php echo $companyDetails['license_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $companyDetails['license_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Not Approved" <?php echo $companyDetails['license_status'] === 'Not Approved' ? 'selected' : ''; ?>>Not Approved</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Job Listings (for Companies) -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Job Listings</h5>
                    <?php
                    // Get company's job listings
                    $jobs = fetchAll(
                        "SELECT * FROM jobs WHERE company_id = ? ORDER BY posted_date DESC", 
                        'i', 
                        [$companyDetails['company_id']]
                    );
                    ?>
                    <span class="badge bg-primary"><?php echo count($jobs); ?> Jobs</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($jobs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Posted</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['job_type']); ?></td>
                                            <td><?php echo htmlspecialchars($job['location']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $job['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No job listings found for this company.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- GoWork Information -->
            <?php if ($viewedUser['user_type'] == USER_TYPE_GOWORK && $goworkDetails): ?>
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
            <?php if ($viewedUser['user_type'] == USER_TYPE_ADMIN && $adminDetails): ?>
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
                <?php if ($viewedUser['user_type'] == USER_TYPE_COMPANY && $companyDetails): ?>
                    <!-- Company profile exists, don't show incomplete message -->
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
                <p>Are you sure you want to delete the user <strong><?php echo htmlspecialchars($viewedUser['username']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone and will delete all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo SITE_URL; ?>/api/admin/delete_user.php?id=<?php echo $viewedUser['user_id']; ?>" class="btn btn-danger">Delete User</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>