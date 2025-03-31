<?php
$pageTitle = 'Applications';
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

// Get job ID from query string (if provided)
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Get applications
$applications = [];
$job = null;

if ($company) {
    if ($job_id > 0) {
        // Check if the job belongs to this company
        $job = fetchRow("SELECT * FROM jobs WHERE job_id = ? AND company_id = ?", 'ii', [$job_id, $company['company_id']]);
        
        if ($job) {
            // Get applications for this specific job
            $applications = getApplicationsByJob($job_id);
        } else {
            $_SESSION['error'] = 'Invalid job ID';
            redirectTo(SITE_URL . '/pages/company/jobs.php');
        }
    } else {
        // Get all applications for all jobs of this company
        $sql = "SELECT a.*, j.job_title, u.email, u.username, p.full_name 
                FROM applications a 
                JOIN jobs j ON a.job_id = j.job_id
                JOIN users u ON a.user_id = u.user_id
                LEFT JOIN user_profiles p ON u.user_id = p.user_id
                WHERE j.company_id = ? 
                ORDER BY a.applied_date DESC";
        
        $applications = fetchAll($sql, 'i', [$company['company_id']]);
    }
}

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/dashboard.php">Dashboard</a></li>
                    <?php if ($job): ?>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/jobs.php">Manage Jobs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Applications for <?php echo htmlspecialchars($job['job_title']); ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page">All Applications</li>
                    <?php endif; ?>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?php if ($job): ?>
                        <h2>Applications for <?php echo htmlspecialchars($job['job_title']); ?></h2>
                    <?php else: ?>
                        <h2>All Applications</h2>
                    <?php endif; ?>
                    <p class="text-muted">Review and manage job applications</p>
                </div>
                <?php if ($job): ?>
                    <div>
                        <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-eye"></i> View Job
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/company/jobs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Jobs
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <?php if (empty($applications)): ?>
                <div class="card">
                    <div class="card-body py-5 text-center">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4>No Applications Yet</h4>
                        <?php if ($job): ?>
                            <p>You haven't received any applications for this job yet.</p>
                        <?php else: ?>
                            <p>You haven't received any applications for any of your jobs yet.</p>
                            
                            <?php if (empty($jobs)): ?>
                                <?php if ($company && $company['license_status'] === 'Approved'): ?>
                                    <p>Post a job to start receiving applications from candidates.</p>
                                    <a href="<?php echo SITE_URL; ?>/pages/company/post_job.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Post New Job
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <!-- Filter and Search (placeholder for future implementation) -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search applicants..." id="searchApplicants">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-md-end">
                                    <select class="form-select w-auto" id="filterStatus">
                                        <option value="">All Statuses</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Viewed">Viewed</option>
                                        <option value="Shortlisted">Shortlisted</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <?php if (!$job): ?>
                                            <th>Job Title</th>
                                        <?php endif; ?>
                                        <th>Applied Date</th>
                                        <th>Resume</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle text-center me-2" style="width: 40px; height: 40px; line-height: 40px;">
                                                        <i class="fas fa-user text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">
                                                            <?php 
                                                            $applicantName = $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']);
                                                            echo $applicantName;
                                                            ?>
                                                        </div>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($application['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <?php if (!$job): ?>
                                                <td>
                                                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $application['job_id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($application['job_title']); ?>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <td><?php echo date('M d, Y', strtotime($application['applied_date'])); ?></td>
                                            
                                            <td>
                                                <?php if ($application['resume_path']): ?>
                                                    <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $application['resume_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-file-pdf"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Resume</span>
                                                <?php endif; ?>
                                            </td>
                                            
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
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $application['application_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $application['application_id']; ?>">
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $application['application_id']; ?>">
                                                                <i class="fas fa-eye me-1"></i> View Details
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item <?php echo $application['status'] === 'Viewed' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/api/jobs/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=Viewed">
                                                                <i class="fas fa-check me-1"></i> Mark as Viewed
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item <?php echo $application['status'] === 'Shortlisted' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/api/jobs/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=Shortlisted">
                                                                <i class="fas fa-star me-1"></i> Shortlist
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item <?php echo $application['status'] === 'Rejected' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/api/jobs/update_application_status.php?id=<?php echo $application['application_id']; ?>&status=Rejected">
                                                                <i class="fas fa-times me-1"></i> Reject
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
