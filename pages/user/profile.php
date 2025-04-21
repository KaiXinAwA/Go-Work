<?php
$pageTitle = 'My Profile';
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

// Get career history
$careerHistory = getUserCareerHistory($user['user_id']);

// Include header
require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <!-- Status messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- User info sidebar -->
        <div class="col-md-3">
            <div class="card mb-4 text-center">
                <div class="card-body">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Profile" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h5><?php echo isset($userProfile['full_name']) && !empty($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : htmlspecialchars($user['username']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <!-- Photo upload form -->
                    <form action="<?php echo SITE_URL; ?>/api/users/update_photo.php" method="POST" enctype="multipart/form-data" class="mt-3">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Change Photo</label>
                            <input type="file" class="form-control form-control-sm" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                    </form>
                </div>
            </div>
            
            <!-- Navigation links -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action">Profile Information</a>
                        <a href="#skills-section" class="list-group-item list-group-item-action">Skills</a>
                        <a href="#career-history" class="list-group-item list-group-item-action">Career History</a>
                        <a href="#education-section" class="list-group-item list-group-item-action">Education</a>
                        <a href="#resume-section" class="list-group-item list-group-item-action">Resume</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9">
            <!-- Personal & Contact Info Card -->
            <div class="card mb-4" id="profile-info">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="personal">
                        
                        <!-- Personal Information -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : ''; ?>">
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($userProfile['address']) ? htmlspecialchars($userProfile['address']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($userProfile['city']) ? htmlspecialchars($userProfile['city']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($userProfile['state']) ? htmlspecialchars($userProfile['state']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo isset($userProfile['country']) ? htmlspecialchars($userProfile['country']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark custom-btn">Save Profile Information</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Skills Card -->
            <div class="card mb-4" id="skills-section">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Skills</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="skills">
                        
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <textarea class="form-control" id="skills" name="skills" rows="4"><?php echo isset($userProfile['skills']) ? htmlspecialchars($userProfile['skills']) : ''; ?></textarea>
                            <small class="text-muted">Enter your skills separated by commas, or list them in detail.</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark custom-btn">Save Skills</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Career History Card -->
            <div class="card mb-4" id="career-history">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Career History</h5>
                    <button class="btn btn-outline-light custom-btn" data-bs-toggle="modal" data-bs-target="#addCareerModal">
                        <i class="fas fa-plus me-1"></i> Add Position
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($careerHistory)): ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-info-circle me-2"></i>
                            You haven't added any career history yet. Click the "Add Position" button to get started.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="careerAccordion">
                            <?php foreach ($careerHistory as $index => $position): ?>
                                <div class="accordion-item mb-3 border">
                                    <h2 class="accordion-header" id="heading<?php echo $position['id']; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $position['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $position['id']; ?>">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($position['job_title']); ?></strong> at <?php echo htmlspecialchars($position['company_name']); ?>
                                                </div>
                                                <div class="text-muted small ms-3">
                                                    <?php 
                                                    echo date('M Y', strtotime($position['start_date'])); 
                                                    echo ' - '; 
                                                    echo !empty($position['end_date']) ? date('M Y', strtotime($position['end_date'])) : 'Present';
                                                    ?>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $position['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $position['id']; ?>" data-bs-parent="#careerAccordion">
                                        <div class="accordion-body">
                                            <p><?php echo nl2br(htmlspecialchars($position['description'])); ?></p>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-dark custom-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCareerModal"
                                                        data-id="<?php echo $position['id']; ?>"
                                                        data-job-title="<?php echo htmlspecialchars($position['job_title']); ?>"
                                                        data-company-name="<?php echo htmlspecialchars($position['company_name']); ?>"
                                                        data-start-date="<?php echo $position['start_date']; ?>"
                                                        data-end-date="<?php echo $position['end_date']; ?>"
                                                        data-description="<?php echo htmlspecialchars($position['description']); ?>">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger custom-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteCareerModal"
                                                        data-id="<?php echo $position['id']; ?>"
                                                        data-job-title="<?php echo htmlspecialchars($position['job_title']); ?>">
                                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Education Card -->
            <div class="card mb-4" id="education-section">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Education</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="education">
                        
                        <div class="mb-3">
                            <label for="institution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="institution" name="institution" value="<?php echo isset($userProfile['institution']) ? htmlspecialchars($userProfile['institution']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="completion_status" class="form-label">Completion Status</label>
                            <select class="form-select" id="completion_status" name="completion_status">
                                <option value="">Select status</option>
                                <option value="In Progress" <?php echo (isset($userProfile['completion_status']) && $userProfile['completion_status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo (isset($userProfile['completion_status']) && $userProfile['completion_status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="On Hold" <?php echo (isset($userProfile['completion_status']) && $userProfile['completion_status'] === 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="education" class="form-label">Degree/Course</label>
                            <input type="text" class="form-control" id="education" name="education" value="<?php echo isset($userProfile['education']) ? htmlspecialchars($userProfile['education']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="education_highlights" class="form-label">Education Highlights</label>
                            <textarea class="form-control" id="education_highlights" name="education_highlights" rows="3"><?php echo isset($userProfile['education_highlights']) ? htmlspecialchars($userProfile['education_highlights']) : ''; ?></textarea>
                            <small class="text-muted">Include achievements, relevant coursework, etc.</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark custom-btn">Save Education</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Resume Card -->
            <div class="card mb-4" id="resume-section">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Resume</h5>
                </div>
                <div class="card-body">
                    <?php if ($userProfile && isset($userProfile['resume_path']) && $userProfile['resume_path']): ?>
                        <div class="alert alert-secondary mb-3">
                            <div class="d-flex align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Resume Uploaded</h6>
                                    <p class="mb-0 small"><?php echo basename($userProfile['resume_path']); ?></p>
                                </div>
                                <div class="ms-auto">
                                    <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $userProfile['resume_path']; ?>" class="btn btn-sm btn-outline-dark custom-btn" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger custom-btn ms-2" data-bs-toggle="modal" data-bs-target="#deleteResumeModal">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <p>Upload a new resume to replace the current one.</p>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You haven't uploaded a resume yet. Upload your resume to apply for jobs.
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/api/users/update_resume.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="resume" class="form-label">Upload Resume (PDF)</label>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf">
                            <small class="text-muted">Maximum file size: 5MB</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark custom-btn">Upload Resume</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Career History Modal -->
<div class="modal fade" id="addCareerModal" tabindex="-1" aria-labelledby="addCareerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="addCareerModalLabel">Add Career Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/users/update_career_history.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <div class="form-text">Leave blank if this is your current position</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        <div class="form-text">Describe your responsibilities, achievements, and key projects</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark custom-btn">Add Position</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Career History Modal -->
<div class="modal fade" id="editCareerModal" tabindex="-1" aria-labelledby="editCareerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="editCareerModalLabel">Edit Career Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/users/update_career_history.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="edit_job_title" name="job_title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="edit_company_name" name="company_name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            <div class="form-text">Leave blank if this is your current position</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        <div class="form-text">Describe your responsibilities, achievements, and key projects</div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark custom-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Career History Modal -->
<div class="modal fade" id="deleteCareerModal" tabindex="-1" aria-labelledby="deleteCareerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="deleteCareerModalLabel">Delete Career Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/users/update_career_history.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                
                <div class="modal-body">
                    <p>Are you sure you want to delete this position? This action cannot be undone.</p>
                    <p class="fw-bold" id="delete_position_title"></p>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark custom-btn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add CSS for button transitions and hover effects -->
<style>
.custom-btn {
    transition: all 0.3s ease;
}

.btn-dark.custom-btn:hover {
    background-color: #333;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-dark.custom-btn:hover {
    background-color: #333;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-secondary.custom-btn:hover {
    background-color: #6c757d;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-light.custom-btn:hover {
    background-color: #f8f9fa;
    color: #212529;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-danger.custom-btn:hover {
    background-color: #dc3545;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.list-group-item-action {
    transition: all 0.3s ease;
}

.list-group-item-action:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.accordion-button {
    transition: all 0.3s ease;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #212529;
}
</style>

<!-- JavaScript for Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Career Modal
    const editCareerModal = document.getElementById('editCareerModal');
    if (editCareerModal) {
        editCareerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Extract data from button attributes
            const id = button.getAttribute('data-id');
            const jobTitle = button.getAttribute('data-job-title');
            const companyName = button.getAttribute('data-company-name');
            const startDate = button.getAttribute('data-start-date');
            const endDate = button.getAttribute('data-end-date');
            const description = button.getAttribute('data-description');
            
            // Update the modal's content
            const modalIdInput = editCareerModal.querySelector('#edit_id');
            const modalJobTitle = editCareerModal.querySelector('#edit_job_title');
            const modalCompanyName = editCareerModal.querySelector('#edit_company_name');
            const modalStartDate = editCareerModal.querySelector('#edit_start_date');
            const modalEndDate = editCareerModal.querySelector('#edit_end_date');
            const modalDescription = editCareerModal.querySelector('#edit_description');
            
            modalIdInput.value = id;
            modalJobTitle.value = jobTitle;
            modalCompanyName.value = companyName;
            modalStartDate.value = startDate;
            modalEndDate.value = endDate;
            modalDescription.value = description;
        });
    }
    
    // Delete Career Modal
    const deleteCareerModal = document.getElementById('deleteCareerModal');
    if (deleteCareerModal) {
        deleteCareerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Extract data from button attributes
            const id = button.getAttribute('data-id');
            const jobTitle = button.getAttribute('data-job-title');
            
            // Update the modal's content
            const modalIdInput = deleteCareerModal.querySelector('#delete_id');
            const modalPositionTitle = deleteCareerModal.querySelector('#delete_position_title');
            
            modalIdInput.value = id;
            modalPositionTitle.textContent = jobTitle;
        });
    }
});
</script>

<!-- Delete Resume Modal -->
<div class="modal fade" id="deleteResumeModal" tabindex="-1" aria-labelledby="deleteResumeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="deleteResumeModalLabel">Delete Resume</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/users/delete_resume.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to delete your resume? This action cannot be undone.</p>
                    <p>You will need to upload a new resume before applying to any jobs.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger custom-btn">Delete Resume</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
