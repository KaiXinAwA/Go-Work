<?php
$pageTitle = 'Edit Job';
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

// Check if license is approved
if (!$company || $company['license_status'] !== 'Approved') {
    $_SESSION['error'] = 'You need an approved license to edit jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get job types for dropdown
$jobTypes = getJobTypes();

// Check if a specific job ID is requested
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Get job data
$job = fetchRow(
    "SELECT j.* 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.company_id 
    WHERE j.job_id = ? AND c.company_id = ?", 
    'ii', 
    [$job_id, $company['company_id']]
);

if (!$job) {
    $_SESSION['error'] = 'Job not found or you do not have permission to edit it';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/jobs.php">Manage Jobs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Job</li>
                </ol>
            </nav>
            <h2>Edit Job</h2>
            <p class="text-muted">Update your job listing</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/jobs/update.php" method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                        
                        <div class="mb-4">
                            <h5>Job Details</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo htmlspecialchars($job['job_title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="">Select Job Type</option>
                                    <?php foreach ($jobTypes as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo ($job['job_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                                <small class="text-muted">State</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Categories</label>
                                <div class="accordion" id="categoriesAccordion">
                                    <!-- Technology & IT -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#techCategory" aria-expanded="false" aria-controls="techCategory">
                                                Technology & IT
                                            </button>
                                        </h2>
                                        <div id="techCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-programming" value="Technology & IT - Programming" <?php echo strpos($job['categories'], 'Technology & IT - Programming') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-programming">Programming</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-web" value="Technology & IT - Web Development" <?php echo strpos($job['categories'], 'Technology & IT - Web Development') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-web">Web Development</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-mobile" value="Technology & IT - Mobile Development" <?php echo strpos($job['categories'], 'Technology & IT - Mobile Development') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-mobile">Mobile Development</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-database" value="Technology & IT - Database Management" <?php echo strpos($job['categories'], 'Technology & IT - Database Management') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-database">Database Management</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-cloud" value="Technology & IT - Cloud Computing" <?php echo strpos($job['categories'], 'Technology & IT - Cloud Computing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-cloud">Cloud Computing</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-security" value="Technology & IT - Cybersecurity" <?php echo strpos($job['categories'], 'Technology & IT - Cybersecurity') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-security">Cybersecurity</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-devops" value="Technology & IT - DevOps" <?php echo strpos($job['categories'], 'Technology & IT - DevOps') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-devops">DevOps</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-ai" value="Technology & IT - AI & Machine Learning" <?php echo strpos($job['categories'], 'Technology & IT - AI & Machine Learning') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-ai">AI & Machine Learning</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-data" value="Technology & IT - Data Science" <?php echo strpos($job['categories'], 'Technology & IT - Data Science') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-data">Data Science</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="tech-blockchain" value="Technology & IT - Blockchain" <?php echo strpos($job['categories'], 'Technology & IT - Blockchain') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="tech-blockchain">Blockchain</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Business & Administration -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#businessCategory" aria-expanded="false" aria-controls="businessCategory">
                                                Business & Administration
                                            </button>
                                        </h2>
                                        <div id="businessCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-project" value="Business & Administration - Project Management" <?php echo strpos($job['categories'], 'Business & Administration - Project Management') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-project">Project Management</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-analysis" value="Business & Administration - Business Analysis" <?php echo strpos($job['categories'], 'Business & Administration - Business Analysis') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-analysis">Business Analysis</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-admin" value="Business & Administration - Office Administration" <?php echo strpos($job['categories'], 'Business & Administration - Office Administration') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-admin">Office Administration</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-hr" value="Business & Administration - Human Resources" <?php echo strpos($job['categories'], 'Business & Administration - Human Resources') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-hr">Human Resources</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-customer" value="Business & Administration - Customer Service" <?php echo strpos($job['categories'], 'Business & Administration - Customer Service') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-customer">Customer Service</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-supply" value="Business & Administration - Supply Chain & Logistics" <?php echo strpos($job['categories'], 'Business & Administration - Supply Chain & Logistics') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-supply">Supply Chain & Logistics</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-sales" value="Business & Administration - Sales & Marketing" <?php echo strpos($job['categories'], 'Business & Administration - Sales & Marketing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-sales">Sales & Marketing</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="business-finance" value="Business & Administration - Accounting & Finance" <?php echo strpos($job['categories'], 'Business & Administration - Accounting & Finance') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="business-finance">Accounting & Finance</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Healthcare & Medical -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#healthcareCategory" aria-expanded="false" aria-controls="healthcareCategory">
                                                Healthcare & Medical
                                            </button>
                                        </h2>
                                        <div id="healthcareCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-nursing" value="Healthcare & Medical - Nursing" <?php echo strpos($job['categories'], 'Healthcare & Medical - Nursing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-nursing">Nursing</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-medicine" value="Healthcare & Medical - Medicine" <?php echo strpos($job['categories'], 'Healthcare & Medical - Medicine') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-medicine">Medicine</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-pharmacy" value="Healthcare & Medical - Pharmacy" <?php echo strpos($job['categories'], 'Healthcare & Medical - Pharmacy') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-pharmacy">Pharmacy</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-lab" value="Healthcare & Medical - Medical Laboratory" <?php echo strpos($job['categories'], 'Healthcare & Medical - Medical Laboratory') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-lab">Medical Laboratory</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-physio" value="Healthcare & Medical - Physiotherapy" <?php echo strpos($job['categories'], 'Healthcare & Medical - Physiotherapy') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-physio">Physiotherapy</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-dental" value="Healthcare & Medical - Dental Care" <?php echo strpos($job['categories'], 'Healthcare & Medical - Dental Care') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-dental">Dental Care</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-mental" value="Healthcare & Medical - Mental Health" <?php echo strpos($job['categories'], 'Healthcare & Medical - Mental Health') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="healthcare-mental">Mental Health</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Engineering -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#engineeringCategory" aria-expanded="false" aria-controls="engineeringCategory">
                                                Engineering
                                            </button>
                                        </h2>
                                        <div id="engineeringCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-civil" value="Engineering - Civil Engineering" <?php echo strpos($job['categories'], 'Engineering - Civil Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-civil">Civil Engineering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-mechanical" value="Engineering - Mechanical Engineering" <?php echo strpos($job['categories'], 'Engineering - Mechanical Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-mechanical">Mechanical Engineering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-electrical" value="Engineering - Electrical Engineering" <?php echo strpos($job['categories'], 'Engineering - Electrical Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-electrical">Electrical Engineering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-electronics" value="Engineering - Electronics Engineering" <?php echo strpos($job['categories'], 'Engineering - Electronics Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-electronics">Electronics Engineering</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-chemical" value="Engineering - Chemical Engineering" <?php echo strpos($job['categories'], 'Engineering - Chemical Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-chemical">Chemical Engineering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-software" value="Engineering - Software Engineering" <?php echo strpos($job['categories'], 'Engineering - Software Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-software">Software Engineering</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-aerospace" value="Engineering - Aerospace Engineering" <?php echo strpos($job['categories'], 'Engineering - Aerospace Engineering') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="engineering-aerospace">Aerospace Engineering</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Creative & Design -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#creativeCategory" aria-expanded="false" aria-controls="creativeCategory">
                                                Creative & Design
                                            </button>
                                        </h2>
                                        <div id="creativeCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-graphic" value="Creative & Design - Graphic Design" <?php echo strpos($job['categories'], 'Creative & Design - Graphic Design') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-graphic">Graphic Design</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-ui" value="Creative & Design - UI/UX Design" <?php echo strpos($job['categories'], 'Creative & Design - UI/UX Design') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-ui">UI/UX Design</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-video" value="Creative & Design - Video Editing" <?php echo strpos($job['categories'], 'Creative & Design - Video Editing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-video">Video Editing</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-animation" value="Creative & Design - Animation" <?php echo strpos($job['categories'], 'Creative & Design - Animation') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-animation">Animation</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-photography" value="Creative & Design - Photography" <?php echo strpos($job['categories'], 'Creative & Design - Photography') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-photography">Photography</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-content" value="Creative & Design - Content Writing" <?php echo strpos($job['categories'], 'Creative & Design - Content Writing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-content">Content Writing</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="creative-social" value="Creative & Design - Social Media Management" <?php echo strpos($job['categories'], 'Creative & Design - Social Media Management') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="creative-social">Social Media Management</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Education & Training -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#educationCategory" aria-expanded="false" aria-controls="educationCategory">
                                                Education & Training
                                            </button>
                                        </h2>
                                        <div id="educationCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="education-teaching" value="Education & Training - Teaching" <?php echo strpos($job['categories'], 'Education & Training - Teaching') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="education-teaching">Teaching</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="education-tutoring" value="Education & Training - Tutoring" <?php echo strpos($job['categories'], 'Education & Training - Tutoring') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="education-tutoring">Tutoring</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="education-elearning" value="Education & Training - E-Learning" <?php echo strpos($job['categories'], 'Education & Training - E-Learning') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="education-elearning">E-Learning</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="education-corporate" value="Education & Training - Corporate Training" <?php echo strpos($job['categories'], 'Education & Training - Corporate Training') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="education-corporate">Corporate Training</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="education-language" value="Education & Training - Language Proficiency" <?php echo strpos($job['categories'], 'Education & Training - Language Proficiency') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="education-language">Language Proficiency</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Hospitality & Tourism -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hospitalityCategory" aria-expanded="false" aria-controls="hospitalityCategory">
                                                Hospitality & Tourism
                                            </button>
                                        </h2>
                                        <div id="hospitalityCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-hotel" value="Hospitality & Tourism - Hotel Management" <?php echo strpos($job['categories'], 'Hospitality & Tourism - Hotel Management') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="hospitality-hotel">Hotel Management</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-culinary" value="Hospitality & Tourism - Culinary Arts" <?php echo strpos($job['categories'], 'Hospitality & Tourism - Culinary Arts') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="hospitality-culinary">Culinary Arts</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-event" value="Hospitality & Tourism - Event Planning" <?php echo strpos($job['categories'], 'Hospitality & Tourism - Event Planning') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="hospitality-event">Event Planning</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-travel" value="Hospitality & Tourism - Travel Agency" <?php echo strpos($job['categories'], 'Hospitality & Tourism - Travel Agency') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="hospitality-travel">Travel Agency</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-customer" value="Hospitality & Tourism - Customer Relations" <?php echo strpos($job['categories'], 'Hospitality & Tourism - Customer Relations') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="hospitality-customer">Customer Relations</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Trades & Manual Work -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tradesWorkCategory" aria-expanded="false" aria-controls="tradesWorkCategory">
                                                Trades & Manual Work
                                            </button>
                                        </h2>
                                        <div id="tradesWorkCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="trades-plumbing" value="Trades & Manual Work - Plumbing" <?php echo strpos($job['categories'], 'Trades & Manual Work - Plumbing') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="trades-plumbing">Plumbing</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="trades-electrical" value="Trades & Manual Work - Electrical Work" <?php echo strpos($job['categories'], 'Trades & Manual Work - Electrical Work') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="trades-electrical">Electrical Work</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="trades-carpentry" value="Trades & Manual Work - Carpentry" <?php echo strpos($job['categories'], 'Trades & Manual Work - Carpentry') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="trades-carpentry">Carpentry</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="trades-welding" value="Trades & Manual Work - Welding" <?php echo strpos($job['categories'], 'Trades & Manual Work - Welding') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="trades-welding">Welding</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="trades-automotive" value="Trades & Manual Work - Automotive Repair" <?php echo strpos($job['categories'], 'Trades & Manual Work - Automotive Repair') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="trades-automotive">Automotive Repair</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Legal & Compliance -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#legalCategory" aria-expanded="false" aria-controls="legalCategory">
                                                Legal & Compliance
                                            </button>
                                        </h2>
                                        <div id="legalCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="legal-corporate" value="Legal & Compliance - Corporate Law" <?php echo strpos($job['categories'], 'Legal & Compliance - Corporate Law') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="legal-corporate">Corporate Law</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="legal-criminal" value="Legal & Compliance - Criminal Law" <?php echo strpos($job['categories'], 'Legal & Compliance - Criminal Law') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="legal-criminal">Criminal Law</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="legal-ip" value="Legal & Compliance - Intellectual Property" <?php echo strpos($job['categories'], 'Legal & Compliance - Intellectual Property') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="legal-ip">Intellectual Property</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="legal-compliance" value="Legal & Compliance - Compliance" <?php echo strpos($job['categories'], 'Legal & Compliance - Compliance') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="legal-compliance">Compliance</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Science & Research -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#scienceCategory" aria-expanded="false" aria-controls="scienceCategory">
                                                Science & Research
                                            </button>
                                        </h2>
                                        <div id="scienceCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="science-biotech" value="Science & Research - Biotechnology" <?php echo strpos($job['categories'], 'Science & Research - Biotechnology') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="science-biotech">Biotechnology</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="science-environmental" value="Science & Research - Environmental Science" <?php echo strpos($job['categories'], 'Science & Research - Environmental Science') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="science-environmental">Environmental Science</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="science-chemistry" value="Science & Research - Chemistry" <?php echo strpos($job['categories'], 'Science & Research - Chemistry') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="science-chemistry">Chemistry</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="categories[]" id="science-physics" value="Science & Research - Physics" <?php echo strpos($job['categories'], 'Science & Research - Physics') !== false ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="science-physics">Physics</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Add more categories as needed -->
                                </div>
                            </div>
                            
                            <!-- Hidden field to store concatenated categories -->
                            <input type="hidden" name="category_string" id="category-string" value="<?php echo htmlspecialchars($job['categories']); ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="salary_min" name="salary_min" min="0" step="100" value="<?php echo $job['salary_min']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="salary_max" name="salary_max" min="0" step="100" value="<?php echo $job['salary_max']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Job Description</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                                <small class="text-muted">Provide a detailed description of the job, responsibilities, and company benefits.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="6" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                                <small class="text-muted">List qualifications, skills, experience, education, and any other requirements.</small>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo $job['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Job is active and visible to job seekers
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/pages/company/jobs.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Add JavaScript for category selection
$extraJS = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const categoryString = document.getElementById('category-string');
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
    
    // Update category string when form is submitted
    form.addEventListener('submit', function(e) {
        const selectedCategories = [];
        
        // Collect all checked categories
        categoryCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                // Use the checkbox value directly which already contains the category text
                selectedCategories.push(checkbox.value);
            }
        });
        
        // Set the hidden field value with proper comma separation
        categoryString.value = selectedCategories.join(', ');
    });
});
</script>
EOT;

// Include footer
require_once '../../includes/footer.php';
?> 