<?php
$pageTitle = 'Application Details';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get application ID
$applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($applicationId === 0) {
    $_SESSION['error'] = 'Invalid application ID';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Check if company license is approved
if (!isCompanyLicenseApproved($company['company_id'])) {
    $_SESSION['error'] = 'Your company license must be approved to view applications';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get application details with job and applicant info
$application = fetchRow(
    "SELECT a.*, j.job_title, j.job_type, j.location, j.company_id,
            u.username, u.email, u.profile_picture,
            p.full_name, p.phone, p.address, p.city, p.state, p.country, p.education
     FROM applications a
     JOIN jobs j ON a.job_id = j.job_id
     JOIN users u ON a.user_id = u.user_id
     LEFT JOIN user_profiles p ON u.user_id = p.user_id
     WHERE a.application_id = ?",
    'i',
    [$applicationId]
);

// Check if application exists and belongs to this company
if (!$application || $application['company_id'] !== $company['company_id']) {
    $_SESSION['error'] = 'Application not found or access denied';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Update application status to "Viewed" if it's currently "Pending"
if ($application['status'] === 'Pending') {
    updateData('applications', ['status' => 'Viewed'], 'application_id', $applicationId);
    $application['status'] = 'Viewed';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitizeInput($_POST['status']);
    
    // Check if application is already withdrawn - if so, don't allow status changes
    if ($application['status'] === 'Withdrawn') {
        $_SESSION['error'] = 'Cannot modify status of withdrawn applications';
    }
    // Otherwise proceed with the status update
    else if (in_array($newStatus, ['Pending', 'Viewed', 'Shortlisted', 'Rejected'])) {
        $updateResult = updateData('applications', ['status' => $newStatus], 'application_id', $applicationId);
        
        if ($updateResult) {
            $application['status'] = $newStatus;
            $_SESSION['success'] = 'Application status updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update application status';
        }
    } else {
        $_SESSION['error'] = 'Invalid application status';
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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/applications.php">Applications</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Application Details</li>
                </ol>
            </nav>
            <h2>Application Details</h2>
        </div>
    </div>
    
    <div class="row">
        <!-- Job and Application Info -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Job Information</h5>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($application['job_title']); ?></h5>
                    <div class="mb-3">
                        <div class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?php echo htmlspecialchars($application['location']); ?></div>
                        <div><i class="fas fa-briefcase me-2"></i> <?php echo htmlspecialchars($application['job_type']); ?></div>
                    </div>
                    
                    <h6 class="mb-2">Application Status</h6>
                    <?php
                    $statusClass = '';
                    switch ($application['status']) {
                        case 'Pending':
                            $statusClass = 'bg-warning text-dark';
                            break;
                        case 'Viewed':
                            $statusClass = 'bg-info';
                            break;
                        case 'Shortlisted':
                            $statusClass = 'bg-success';
                            break;
                        case 'Rejected':
                            $statusClass = 'bg-danger';
                            break;
                        case 'Withdrawn':
                            $statusClass = 'bg-secondary';
                            break;
                    }
                    ?>
                    <div class="mb-3">
                        <span class="badge <?php echo $statusClass; ?> p-2"><?php echo $application['status']; ?></span>
                    </div>
                    
                    <h6 class="mb-2">Application Date</h6>
                    <p><?php echo date('F j, Y', strtotime($application['applied_date'])); ?></p>
                    
                    <?php if ($application['status'] !== 'Withdrawn'): ?>
                    <form action="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $applicationId; ?>" method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label">Update Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Pending" <?php echo $application['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Viewed" <?php echo $application['status'] === 'Viewed' ? 'selected' : ''; ?>>Viewed</option>
                                <option value="Shortlisted" <?php echo $application['status'] === 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i> This application has been withdrawn by the applicant. Status updates are disabled.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Applicant Info -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Applicant Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-2 text-center">
                            <?php if (isset($application['profile_picture']) && $application['profile_picture']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $application['profile_picture']; ?>" alt="Profile" class="rounded-circle" width="80" height="80">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px">
                                    <i class="fas fa-user fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-10">
                            <h4><?php echo isset($application['full_name']) && $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']); ?></h4>
                            <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($application['email']); ?></p>
                            <?php if (isset($application['phone']) && $application['phone']): ?>
                                <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($application['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Nav tabs for profile sections -->
                    <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="true">Contact Info</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab" aria-controls="education" aria-selected="false">Education</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="experience-tab" data-bs-toggle="tab" data-bs-target="#experience" type="button" role="tab" aria-controls="experience" aria-selected="false">Work Experience</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab" aria-controls="skills" aria-selected="false">Skills</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab" aria-controls="resume" aria-selected="false">Resume</button>
                        </li>
                    </ul>
                    
                    <!-- Tab content -->
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Contact Information Tab -->
                        <div class="tab-pane fade show active" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Contact Information</h5>
                                    <dl>
                                        <?php if (isset($application['address']) && $application['address']): ?>
                                            <dt>Address</dt>
                                            <dd><?php echo htmlspecialchars($application['address']); ?></dd>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $location = [];
                                        if (isset($application['city']) && $application['city']) $location[] = htmlspecialchars($application['city']);
                                        if (isset($application['state']) && $application['state']) $location[] = htmlspecialchars($application['state']);
                                        if (isset($application['country']) && $application['country']) $location[] = htmlspecialchars($application['country']);
                                        
                                        if (!empty($location)):
                                        ?>
                                            <dt>Location</dt>
                                            <dd><?php echo implode(', ', $location); ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Education Tab -->
                        <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Education</h5>
                                    <?php if (isset($application['education']) && $application['education']): ?>
                                        <p><?php echo nl2br(htmlspecialchars($application['education'])); ?></p>
                                    <?php else: ?>
                                        <p class="text-muted">No education information provided.</p>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Get additional education fields
                                    $userProfile = getUserProfile($application['user_id']);
                                    if ($userProfile):
                                    ?>
                                        <?php if (!empty($userProfile['institution'])): ?>
                                            <div class="mb-3">
                                                <h6>Institution</h6>
                                                <p><?php echo htmlspecialchars($userProfile['institution']); ?>
                                                <?php if (!empty($userProfile['completion_status'])): ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($userProfile['completion_status']); ?></span>
                                                <?php endif; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($userProfile['education_highlights'])): ?>
                                            <div class="mb-3">
                                                <h6>Education Highlights</h6>
                                                <p><?php echo nl2br(htmlspecialchars($userProfile['education_highlights'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Work Experience Tab -->
                        <div class="tab-pane fade" id="experience" role="tabpanel" aria-labelledby="experience-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Work Experience</h5>
                                    <?php 
                                    // Get career history
                                    $careerHistory = getUserCareerHistory($application['user_id']);
                                    if (!empty($careerHistory)):
                                    ?>
                                        <div class="timeline">
                                            <?php foreach ($careerHistory as $job): ?>
                                                <div class="card mb-3 border-start border-primary border-3">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold"><?php echo htmlspecialchars($job['job_title']); ?></h6>
                                                        <p class="mb-1"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                                        <p class="text-muted small mb-2">
                                                            <?php 
                                                            echo date('M Y', strtotime($job['start_date'])); 
                                                            echo ' - '; 
                                                            echo !empty($job['end_date']) ? date('M Y', strtotime($job['end_date'])) : 'Present';
                                                            ?>
                                                        </p>
                                                        <?php if (!empty($job['description'])): ?>
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No work experience information provided.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Skills Tab -->
                        <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="skills-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Skills</h5>
                                    <?php 
                                    // Get skills from user profile
                                    if ($userProfile && !empty($userProfile['skills'])):
                                        $skills = explode(',', $userProfile['skills']);
                                    ?>
                                        <div>
                                            <?php foreach ($skills as $skill): ?>
                                                <?php $skill = trim($skill); ?>
                                                <?php if (!empty($skill)): ?>
                                                    <span class="badge bg-primary p-2 me-2 mb-2"><?php echo htmlspecialchars($skill); ?></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No skills information provided.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resume Tab -->
                        <div class="tab-pane fade" id="resume" role="tabpanel" aria-labelledby="resume-tab">
                            <?php
                            $resumePath = '';
                            
                            // Check if application has a specific resume path
                            if (isset($application['resume_path']) && $application['resume_path']) {
                                $resumePath = $application['resume_path'];
                            }
                            // Otherwise, check if user has a resume in their profile
                            else {
                                $userProfile = getUserProfile($application['user_id']);
                                if ($userProfile && isset($userProfile['resume_path']) && $userProfile['resume_path']) {
                                    $resumePath = $userProfile['resume_path'];
                                }
                            }
                            
                            if ($resumePath):
                            ?>
                                <div class="mb-3">
                                    <h5 class="mb-3">Resume</h5>
                                    <div class="mb-3">
                                        <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $resumePath; ?>" class="btn btn-outline-primary" target="_blank">
                                            <i class="fas fa-file-pdf me-2"></i> View Resume
                                        </a>
                                        <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $resumePath; ?>" class="btn btn-outline-secondary ms-2" download>
                                            <i class="fas fa-download me-2"></i> Download Resume
                                        </a>
                                    </div>
                                    
                                    <div class="ratio ratio-16x9">
                                        <iframe src="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $resumePath; ?>" allowfullscreen></iframe>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> No resume available for this application.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="<?php echo SITE_URL; ?>/pages/company/applications.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Applications
                </a>
                
                <?php if ($application['status'] !== 'Rejected' && $application['status'] !== 'Withdrawn'): ?>
                    <div>
                        <a href="mailto:<?php echo $application['email']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i> Contact Applicant
                        </a>
                        
                        <?php if ($application['status'] !== 'Shortlisted'): ?>
                            <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#shortlistModal">
                                <i class="fas fa-check me-2"></i> Shortlist
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i> Reject
                        </button>
                    </div>
                <?php elseif ($application['status'] === 'Withdrawn'): ?>
                    <div>
                        <a href="mailto:<?php echo $application['email']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i> Contact Applicant
                        </a>
                        <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i> Delete Application
                        </button>
                    </div>
                <?php else: ?>
                    <form action="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $applicationId; ?>" method="POST" class="d-inline">
                        <input type="hidden" name="status" value="Viewed">
                        <button type="submit" name="update_status" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-2"></i> Restore Application
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Shortlist Modal -->
<div class="modal fade" id="shortlistModal" tabindex="-1" aria-labelledby="shortlistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shortlistModalLabel">Shortlist Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to shortlist this applicant?</p>
                <p>Name: <strong><?php echo isset($application['full_name']) && $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']); ?></strong></p>
                <p>This will update their application status to "Shortlisted".</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $applicationId; ?>" method="POST" class="d-inline">
                    <input type="hidden" name="status" value="Shortlisted">
                    <button type="submit" name="update_status" class="btn btn-success">Shortlist Applicant</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this application?</p>
                <p>Name: <strong><?php echo isset($application['full_name']) && $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']); ?></strong></p>
                <p>This will update their application status to "Rejected".</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo SITE_URL; ?>/pages/company/application_details.php?id=<?php echo $applicationId; ?>" method="POST" class="d-inline">
                    <input type="hidden" name="status" value="Rejected">
                    <button type="submit" name="update_status" class="btn btn-danger">Reject Application</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete this application?</p>
                <p>Name: <strong><?php echo isset($application['full_name']) && $application['full_name'] ? htmlspecialchars($application['full_name']) : htmlspecialchars($application['username']); ?></strong></p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo SITE_URL; ?>/api/jobs/delete_application.php?id=<?php echo $applicationId; ?>" class="btn btn-danger">Delete Application</a>
            </div>
        </div>
    </div>
</div>

<!-- Style for timeline -->
<style>
.timeline .card {
    position: relative;
}
.timeline .card:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 10px;
    top: 100%;
    height: 20px;
    width: 2px;
    background-color: #dee2e6;
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
