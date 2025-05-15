<?php
$pageTitle = 'Company Dashboard';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

// Get company jobs
$jobs = [];
if ($company) {
    $jobs = getJobsByCompany($company['company_id']);
}

// Get recent applications across all jobs
$recentApplications = [];
if ($company) {
    $sql = "SELECT a.*, j.job_title, u.email, u.username, p.full_name 
            FROM applications a 
            JOIN jobs j ON a.job_id = j.job_id
            JOIN users u ON a.user_id = u.user_id
            LEFT JOIN user_profiles p ON u.user_id = p.user_id
            WHERE j.company_id = ? 
            ORDER BY a.applied_date DESC 
            LIMIT 5";
    
    $recentApplications = fetchAll($sql, 'i', [$company['company_id']]);
}

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Company Dashboard</h2>
                <div>
                    <a href="<?php echo SITE_URL; ?>/pages/company/profile.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-building"></i> Company Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($company && $company['license_status'] !== 'Approved'): ?>
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> License Approval Required</h5>
            <p>Your company license is currently <strong><?php echo $company['license_status']; ?></strong>. You need an approved license to post jobs.</p>
            <?php if ($company['license_status'] === 'Pending'): ?>
                <p>Our team is reviewing your license. This process typically takes 1-2 business days.</p>
            <?php elseif ($company['license_status'] === 'Not Approved'): ?>
                <p>Your license was not approved. Please update your company information and submit a valid license.</p>
                <a href="<?php echo SITE_URL; ?>/pages/company/profile.php#license" class="btn btn-primary">Update License</a>
            <?php else: ?>
                <p>Please submit your company license for approval.</p>
                <a href="<?php echo SITE_URL; ?>/pages/company/profile.php#license" class="btn btn-primary">Submit License</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Company Information -->
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Company Logo" class="rounded-circle mb-3" width="120" height="120">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px">
                            <i class="fas fa-building fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo $company ? htmlspecialchars($company['company_name']) : htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <!-- License Status -->
                    <?php if ($company): ?>
                        <?php
                        $statusClass = '';
                        switch ($company['license_status']) {
                            case 'Approved':
                                $statusClass = 'bg-success';
                                break;
                            case 'Pending':
                                $statusClass = 'bg-warning text-dark';
                                break;
                            case 'Not Approved':
                                $statusClass = 'bg-danger';
                                break;
                            default:
                                $statusClass = 'bg-secondary';
                        }
                        ?>
                        <div class="mb-3">
                            <span class="badge <?php echo $statusClass; ?>">
                                License: <?php echo $company['license_status']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/pages/company/applications.php" class="btn btn-primary">
                            <i class="fas fa-users"></i> View All Applications
                        </a>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-6">
                            <h5><?php echo count($jobs); ?></h5>
                            <small>Active Jobs</small>
                        </div>
                        <div class="col-6">
                            <h5>
                                <?php 
                                $totalApplications = 0;
                                foreach ($jobs as $job) {
                                    $applications = fetchAll("SELECT * FROM applications WHERE job_id = ?", 'i', [$job['job_id']]);
                                    $totalApplications += count($applications);
                                }
                                echo $totalApplications;
                                ?>
                            </h5>
                            <small>Total Applications</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Jobs Overview -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Your Job Listings</span>
                    <a href="<?php echo SITE_URL; ?>/pages/company/jobs.php" class="btn btn-sm btn-link">Manage All Jobs</a>
                </div>
                <div class="card-body">
                    <?php if (empty($jobs)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't posted any jobs yet.
                            <?php if ($company && $company['license_status'] === 'Approved'): ?>
                                <a href="<?php echo SITE_URL; ?>/pages/company/post_job.php" class="alert-link">Post your first job</a> to start receiving applications.
                            <?php else: ?>
                                Get your license approved to start posting jobs.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Posted Date</th>
                                        <th>Applications</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentJobs = array_slice($jobs, 0, 5);
                                    foreach ($recentJobs as $job): 
                                        // Get application count for this job
                                        $applicationCount = fetchRow("SELECT COUNT(*) as count FROM applications WHERE job_id = ?", 'i', [$job['job_id']]);
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="text-decoration-none fw-bold">
                                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($job['location']); ?></td>
                                            <td><?php echo htmlspecialchars($job['job_type']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/company/applications.php?job_id=<?php echo $job['job_id']; ?>" class="text-decoration-none">
                                                    <?php echo $applicationCount ? $applicationCount['count'] : 0; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($job['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($jobs) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="<?php echo SITE_URL; ?>/pages/company/jobs.php" class="btn btn-outline-primary btn-sm">
                                    View All Jobs
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Applications</span>
                    <a href="<?php echo SITE_URL; ?>/pages/company/applications.php" class="btn btn-sm btn-link">View All Applications</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentApplications)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't received any applications yet.
                            <?php if (empty($jobs)): ?>
                                <?php if ($company && $company['license_status'] === 'Approved'): ?>
                                    <a href="<?php echo SITE_URL; ?>/pages/company/post_job.php" class="alert-link">Post a job</a> to start receiving applications.
                                <?php else: ?>
                                    Get your license approved to start posting jobs and receiving applications.
                                <?php endif; ?>
                            <?php else: ?>
                                Applications will appear here when job seekers apply for your posted positions.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job Title</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $application): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $applicantName = $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']);
                                                echo $applicantName;
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $application['job_id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($application['job_title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($application['applied_date'])); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($application['status']) {
                                                    case 'Pending':
                                                        $statusClass = 'badge bg-warning text-dark';
                                                        break;
                                                    case 'Viewed':
                                                        $statusClass = 'badge bg-info text-dark';
                                                        break;
                                                    case 'Shortlisted':
                                                        $statusClass = 'badge bg-success';
                                                        break;
                                                    case 'Rejected':
                                                        $statusClass = 'badge bg-danger';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge bg-secondary';
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>">
                                                    <?php echo $application['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $application['application_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
