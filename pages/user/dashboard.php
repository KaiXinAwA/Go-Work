<?php
$pageTitle = 'Dashboard';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$userProfile = getUserProfile($user['user_id']);

// Get user applications
$applications = getApplicationsByUser($user['user_id']);

// Get recommended jobs (placeholder - in a real app, you would have a recommendation algorithm)
$recommendedJobs = fetchAll(
    "SELECT j.*, c.company_name 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.company_id 
    WHERE j.is_active = 1 
    ORDER BY j.posted_date DESC 
    LIMIT 3"
);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Your Dashboard</h2>
                <a href="<?php echo SITE_URL; ?>/pages/user/profile.php" class="btn btn-outline-primary">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- User Profile Summary -->
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Profile" class="rounded-circle mb-3" width="120" height="120">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo $userProfile && $userProfile['full_name'] ? htmlspecialchars($userProfile['full_name']) : htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/pages/user/applications.php" class="btn btn-primary">
                            <i class="fas fa-briefcase"></i> My Applications
                        </a>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5><?php echo count($applications); ?></h5>
                            <small>Applications</small>
                        </div>
                        <div class="col-6">
                            <h5>
                                <?php 
                                $count = 0;
                                foreach ($applications as $app) {
                                    if ($app['status'] == 'Viewed' || $app['status'] == 'Shortlisted') {
                                        $count++;
                                    }
                                }
                                echo $count;
                                ?>
                            </h5>
                            <small>Views</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Completion -->
            <?php
            // Calculate profile completion percentage
            $totalFields = 8; // Total number of profile fields to check
            $completedFields = 0;
            
            if ($userProfile) {
                if (!empty($userProfile['full_name'])) $completedFields++;
                if (!empty($userProfile['phone'])) $completedFields++;
                if (!empty($userProfile['address'])) $completedFields++;
                if (!empty($userProfile['city'])) $completedFields++;
                if (!empty($userProfile['state'])) $completedFields++;
                if (!empty($userProfile['country'])) $completedFields++;
                if (!empty($userProfile['education'])) $completedFields++;
                if (!empty($userProfile['resume_path'])) $completedFields++;
            }
            
            $completionPercentage = round(($completedFields / $totalFields) * 100);
            ?>
            
            <div class="card mt-4">
                <div class="card-header">
                    Profile Completion
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progress</span>
                        <span><?php echo $completionPercentage; ?>%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $completionPercentage; ?>%" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <?php if ($completionPercentage < 100): ?>
                        <div class="alert alert-info">
                            <small>Complete your profile to increase your chances of getting hired.</small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <small>Your profile is complete! You're all set to apply for jobs.</small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($userProfile['resume_path'])): ?>
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/pages/user/profile.php#resume" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-upload"></i> Upload Resume
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Recent Applications -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Applications</span>
                    <a href="<?php echo SITE_URL; ?>/pages/user/applications.php" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($applications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentApplications = array_slice($applications, 0, 5);
                                    foreach ($recentApplications as $application): 
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($application['company_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($application['applied_date'])); ?></td>
                                            <td>
                                                <span class="badge application-status status-<?php echo strtolower($application['status']); ?>">
                                                    <?php echo $application['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't applied to any jobs yet.
                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="alert-link">Browse jobs</a> and start applying!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recommended Jobs -->
            <div class="card">
                <div class="card-header">
                    Recommended Jobs
                </div>
                <div class="card-body">
                    <?php if (!empty($recommendedJobs)): ?>
                        <?php foreach ($recommendedJobs as $job): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($job['job_title']); ?>
                                        </a>
                                    </h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                    
                                    <div class="job-details mb-3">
                                        <div class="job-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                        <div class="job-detail">
                                            <i class="fas fa-briefcase"></i>
                                            <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        </div>
                                        <div class="job-detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo timeElapsed($job['posted_date']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-outline-primary">View Job</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No recommended jobs at the moment. Please check back later.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
