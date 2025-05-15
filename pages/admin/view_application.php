<?php
$pageTitle = 'View Application';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get application ID from URL
$applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get application details joined with user and job information
$sql = "SELECT a.*, j.job_title, j.job_id, c.company_name, c.company_id, c.user_id as company_user_id,
               u.email as applicant_email, u.username as applicant_username, u.profile_picture as applicant_picture
        FROM applications a 
        JOIN jobs j ON a.job_id = j.job_id 
        JOIN companies c ON j.company_id = c.company_id
        JOIN users u ON a.user_id = u.user_id
        WHERE a.application_id = ?";
$application = fetchRow($sql, 'i', [$applicationId]);

if (!$application) {
    $_SESSION['error'] = 'Application not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/companies.php">Manage Companies</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/view_user.php?id=<?php echo $application['company_user_id']; ?>">View Company</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/view_job.php?id=<?php echo $application['job_id']; ?>">View Job</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Application</li>
                </ol>
            </nav>
            
            <!-- Page actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Application Details</h2>
                <div>
                    <a href="<?php echo SITE_URL; ?>/pages/admin/view_job.php?id=<?php echo $application['job_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Job
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Application Overview -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($application['applicant_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $application['applicant_picture']; ?>" alt="Applicant" class="rounded-circle mb-3" width="100" height="100">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px">
                            <i class="fas fa-user fa-3x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($application['applicant_username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($application['applicant_email']); ?></p>
                    
                    <!-- Application Badge -->
                    <?php
                    $statusClass = 'bg-secondary'; // Default background color
                    switch ($application['status']) {
                        case 'Pending':
                            $statusClass = 'bg-warning text-dark';
                            break;
                        case 'Reviewed':
                            $statusClass = 'bg-info';
                            break;
                        case 'Shortlisted':
                            $statusClass = 'bg-primary';
                            break;
                        case 'Rejected':
                            $statusClass = 'bg-danger';
                            break;
                        case 'Hired':
                            $statusClass = 'bg-success';
                            break;
                        // If status is empty or NULL, set a default
                        default:
                            if (empty($application['status'])) {
                                $application['status'] = 'Pending';
                                $statusClass = 'bg-warning text-dark';
                            }
                    }
                    ?>
                    <div class="mt-2">
                        <span class="badge <?php echo $statusClass; ?> p-2">
                            <?php echo $application['status']; ?>
                        </span>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Applied For:</div>
                            <div class="col-6 text-end">
                                <a href="<?php echo SITE_URL; ?>/pages/admin/view_job.php?id=<?php echo $application['job_id']; ?>">
                                    <?php echo htmlspecialchars($application['job_title']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Company:</div>
                            <div class="col-6 text-end">
                                <a href="<?php echo SITE_URL; ?>/pages/admin/view_user.php?id=<?php echo $application['company_user_id']; ?>">
                                    <?php echo htmlspecialchars($application['company_name']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Applied On:</div>
                            <div class="col-6 text-end"><?php echo "Application #" . $application['application_id']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Update -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Update Application Status</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/admin/update_application_status.php" method="POST">
                        <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Application Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?php echo $application['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Reviewed" <?php echo $application['status'] === 'Reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="Shortlisted" <?php echo $application['status'] === 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="Hired" <?php echo $application['status'] === 'Hired' ? 'selected' : ''; ?>>Hired</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Application Details -->
        <div class="col-md-8">
            <!-- Resume -->
            <?php if ($application['resume_path']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Resume</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid mb-3">
                            <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $application['resume_path']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> View Resume
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Cover Letter -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Cover Letter</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($application['cover_letter'])): ?>
                        <div class="cover-letter">
                            <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No cover letter provided.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($application['additional_info'])): ?>
                        <div class="additional-info">
                            <?php echo nl2br(htmlspecialchars($application['additional_info'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No additional information provided.</p>
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