<?php
$pageTitle = 'Post a Job';
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
    $_SESSION['error'] = 'You need an approved license to post jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get job types for dropdown
$jobTypes = getJobTypes();

// Check if a specific job ID is requested
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job data
if ($job_id > 0) {
    // Get single job details
    $job = fetchRow(
        "SELECT j.*, c.company_name, c.description as company_description 
        FROM jobs j 
        JOIN companies c ON j.company_id = c.company_id 
        WHERE j.job_id = ? AND j.is_active = 1", 
        'i', 
        [$job_id]
    );
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
                    <li class="breadcrumb-item active" aria-current="page">Post a Job</li>
                </ol>
            </nav>
            <h2>Post a New Job</h2>
            <p class="text-muted">Create a new job listing to find the perfect candidate</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <?php if ($job_id > 0 && $job): ?>
                        <!-- Single Job View -->
                        <!-- ... HTML for displaying job details ... -->
                    <?php else: ?>
                        <!-- Job Search and Listing -->
                    <form action="<?php echo SITE_URL; ?>/api/jobs/create.php" method="POST">
                        <div class="mb-4">
                            <h5>Job Details</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job_title" name="job_title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="">Select Job Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Temporary">Temporary</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Volunteer">Volunteer</option>
                                    <option value="Remote">Remote</option>
                                    <option value="Hybrid">Hybrid</option>
                                    <option value="On-site">On-site</option>
                                    <option value="Seasonal">Seasonal</option>
                                    <option value="Apprenticeship">Apprenticeship</option>
                                    <option value="Consultant">Consultant</option>
                                    <option value="Per Diem">Per Diem</option>
                                    <option value="Work From Home">Work From Home</option>
                                    <option value="Gig">Gig</option>
                                    <option value="Entry-level">Entry-level</option>
                                    <option value="Executive">Executive</option>
                                    <option value="Commission-based">Commission-based</option>
                                    <option value="Night Shift">Night Shift</option>
                                    <option value="Travel Nursing">Travel Nursing</option>
                                    <option value="Tenure-track">Tenure-track</option>
                                    <option value="Locum Tenens">Locum Tenens</option>
                                    <option value="Performing Artist">Performing Artist</option>
                                    <option value="Adjunct">Adjunct</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required>
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-programming" value="Technology & IT - Programming">
                                                                <label class="form-check-label" for="tech-programming">Programming</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-web" value="Technology & IT - Web Development">
                                                                <label class="form-check-label" for="tech-web">Web Development</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-mobile" value="Technology & IT - Mobile Development">
                                                                <label class="form-check-label" for="tech-mobile">Mobile Development</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-database" value="Technology & IT - Database Management">
                                                                <label class="form-check-label" for="tech-database">Database Management</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-cloud" value="Technology & IT - Cloud Computing">
                                                                <label class="form-check-label" for="tech-cloud">Cloud Computing</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-security" value="Technology & IT - Cybersecurity">
                                                                <label class="form-check-label" for="tech-security">Cybersecurity</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-devops" value="Technology & IT - DevOps">
                                                                <label class="form-check-label" for="tech-devops">DevOps</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-ai" value="Technology & IT - AI & Machine Learning">
                                                                <label class="form-check-label" for="tech-ai">AI & Machine Learning</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-data" value="Technology & IT - Data Science">
                                                                <label class="form-check-label" for="tech-data">Data Science</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="tech-blockchain" value="Technology & IT - Blockchain">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-project" value="Business & Administration - Project Management">
                                                                <label class="form-check-label" for="business-project">Project Management</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-analysis" value="Business & Administration - Business Analysis">
                                                                <label class="form-check-label" for="business-analysis">Business Analysis</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-admin" value="Business & Administration - Office Administration">
                                                                <label class="form-check-label" for="business-admin">Office Administration</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-hr" value="Business & Administration - Human Resources">
                                                                <label class="form-check-label" for="business-hr">Human Resources</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-customer" value="Business & Administration - Customer Service">
                                                                <label class="form-check-label" for="business-customer">Customer Service</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-supply" value="Business & Administration - Supply Chain & Logistics">
                                                                <label class="form-check-label" for="business-supply">Supply Chain & Logistics</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-sales" value="Business & Administration - Sales & Marketing">
                                                                <label class="form-check-label" for="business-sales">Sales & Marketing</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="business-finance" value="Business & Administration - Accounting & Finance">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-nursing" value="Healthcare & Medical - Nursing">
                                                                <label class="form-check-label" for="healthcare-nursing">Nursing</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-medicine" value="Healthcare & Medical - Medicine">
                                                                <label class="form-check-label" for="healthcare-medicine">Medicine</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-pharmacy" value="Healthcare & Medical - Pharmacy">
                                                                <label class="form-check-label" for="healthcare-pharmacy">Pharmacy</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-lab" value="Healthcare & Medical - Medical Laboratory">
                                                                <label class="form-check-label" for="healthcare-lab">Medical Laboratory</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-physio" value="Healthcare & Medical - Physiotherapy">
                                                                <label class="form-check-label" for="healthcare-physio">Physiotherapy</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-dental" value="Healthcare & Medical - Dental Care">
                                                                <label class="form-check-label" for="healthcare-dental">Dental Care</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="healthcare-mental" value="Healthcare & Medical - Mental Health">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-civil" value="Engineering - Civil Engineering">
                                                                <label class="form-check-label" for="engineering-civil">Civil Engineering</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-mechanical" value="Engineering - Mechanical Engineering">
                                                                <label class="form-check-label" for="engineering-mechanical">Mechanical Engineering</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-electrical" value="Engineering - Electrical Engineering">
                                                                <label class="form-check-label" for="engineering-electrical">Electrical Engineering</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-electronics" value="Engineering - Electronics Engineering">
                                                                <label class="form-check-label" for="engineering-electronics">Electronics Engineering</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-chemical" value="Engineering - Chemical Engineering">
                                                                <label class="form-check-label" for="engineering-chemical">Chemical Engineering</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-software" value="Engineering - Software Engineering">
                                                                <label class="form-check-label" for="engineering-software">Software Engineering</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="engineering-aerospace" value="Engineering - Aerospace Engineering">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-graphic" value="Creative & Design - Graphic Design">
                                                                <label class="form-check-label" for="creative-graphic">Graphic Design</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-ui" value="Creative & Design - UI/UX Design">
                                                                <label class="form-check-label" for="creative-ui">UI/UX Design</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-video" value="Creative & Design - Video Editing">
                                                                <label class="form-check-label" for="creative-video">Video Editing</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-animation" value="Creative & Design - Animation">
                                                                <label class="form-check-label" for="creative-animation">Animation</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-photography" value="Creative & Design - Photography">
                                                                <label class="form-check-label" for="creative-photography">Photography</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-content" value="Creative & Design - Content Writing">
                                                                <label class="form-check-label" for="creative-content">Content Writing</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="creative-social" value="Creative & Design - Social Media Management">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="education-teaching" value="Education & Training - Teaching">
                                                                <label class="form-check-label" for="education-teaching">Teaching</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="education-tutoring" value="Education & Training - Tutoring">
                                                                <label class="form-check-label" for="education-tutoring">Tutoring</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="education-elearning" value="Education & Training - E-Learning">
                                                                <label class="form-check-label" for="education-elearning">E-Learning</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="education-corporate" value="Education & Training - Corporate Training">
                                                                <label class="form-check-label" for="education-corporate">Corporate Training</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="education-language" value="Education & Training - Language Proficiency">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-hotel" value="Hospitality & Tourism - Hotel Management">
                                                                <label class="form-check-label" for="hospitality-hotel">Hotel Management</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-culinary" value="Hospitality & Tourism - Culinary Arts">
                                                                <label class="form-check-label" for="hospitality-culinary">Culinary Arts</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-event" value="Hospitality & Tourism - Event Planning">
                                                                <label class="form-check-label" for="hospitality-event">Event Planning</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-travel" value="Hospitality & Tourism - Travel Agency">
                                                                <label class="form-check-label" for="hospitality-travel">Travel Agency</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="hospitality-customer" value="Hospitality & Tourism - Customer Relations">
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
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tradesCategory" aria-expanded="false" aria-controls="tradesCategory">
                                                    Trades & Manual Work
                                                </button>
                                            </h2>
                                            <div id="tradesCategory" class="accordion-collapse collapse" data-bs-parent="#categoriesAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="trades-plumbing" value="Trades & Manual Work - Plumbing">
                                                                <label class="form-check-label" for="trades-plumbing">Plumbing</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="trades-electrical" value="Trades & Manual Work - Electrical Work">
                                                                <label class="form-check-label" for="trades-electrical">Electrical Work</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="trades-carpentry" value="Trades & Manual Work - Carpentry">
                                                                <label class="form-check-label" for="trades-carpentry">Carpentry</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="trades-welding" value="Trades & Manual Work - Welding">
                                                                <label class="form-check-label" for="trades-welding">Welding</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="trades-automotive" value="Trades & Manual Work - Automotive Repair">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="legal-corporate" value="Legal & Compliance - Corporate Law">
                                                                <label class="form-check-label" for="legal-corporate">Corporate Law</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="legal-criminal" value="Legal & Compliance - Criminal Law">
                                                                <label class="form-check-label" for="legal-criminal">Criminal Law</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="legal-ip" value="Legal & Compliance - Intellectual Property">
                                                                <label class="form-check-label" for="legal-ip">Intellectual Property</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="legal-compliance" value="Legal & Compliance - Compliance">
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
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="science-biotech" value="Science & Research - Biotechnology">
                                                                <label class="form-check-label" for="science-biotech">Biotechnology</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="science-environmental" value="Science & Research - Environmental Science">
                                                                <label class="form-check-label" for="science-environmental">Environmental Science</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="science-chemistry" value="Science & Research - Chemistry">
                                                                <label class="form-check-label" for="science-chemistry">Chemistry</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" id="science-physics" value="Science & Research - Physics">
                                                                <label class="form-check-label" for="science-physics">Physics</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min" min="0" step="100">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max" min="0" step="100">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden field to store concatenated categories -->
                        <input type="hidden" name="category_string" id="category-string" value="">
                        
                        <div class="mb-4">
                            <h5>Job Description</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                                <small class="text-muted">Provide a detailed description of the job, responsibilities, and company benefits.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="6" required></textarea>
                                <small class="text-muted">List qualifications, skills, experience, education, and any other requirements.</small>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Publish this job immediately
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
                        </div>
                    </form>
                    <?php endif; ?>
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
                // Get the actual text from the label rather than the value
                const label = checkbox.nextElementSibling;
                // Create the category string directly with actual characters, not HTML entities
                selectedCategories.push(checkbox.value.replace('&amp;', '&'));
            }
        });
        
        // Set the hidden field value
        categoryString.value = selectedCategories.join(', ');
    });
});
</script>
EOT;

// Include footer
require_once '../../includes/footer.php';
?>
