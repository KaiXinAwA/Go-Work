<?php
$pageTitle = 'Help Centre';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Include header
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1>Help Centre</h1>
            <p class="lead">Find answers to your questions about using GoWork</p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <!-- Getting Started -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-flag-checkered fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">Getting Started</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. How to Create an Account:</strong><br>
                        "To get started, click the 'Create Account' button on the homepage. Enter your user name
                        and email, create a password, and you're ready to go!"</p>
                        
                        <p><strong>2. Uploading Your Resume:</strong><br>
                        "After logging in, click 'User Profile' and click 'Update User Profile.' Then, click 'Update
                        Resume.' Choose a PDF or Word file, and your resume will be saved for future applications."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Management -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info text-white rounded-circle p-3 me-3">
                            <i class="fas fa-user-cog fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">Account Management</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. Resetting Your Password:</strong><br>
                        "If you forget your password, click 'Forgot Password' on the login page. Enter your email,
                        and we'll send you a link to reset it."</p>
                        
                        <p><strong>2. Updating Your Profile:</strong><br>
                        "To update your profile, click 'User Profile' then 'Update User Profile.' You can change
                        your information or upload a new resume."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Search & Application -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success text-white rounded-circle p-3 me-3">
                            <i class="fas fa-search fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">Job Search & Application</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. Searching for Jobs:</strong><br>
                        "Use the search bar on the homepage to find jobs by keyword or location. You can also
                        filter results by job type (e.g., internship, part-time)."</p>
                        
                        <p><strong>2. Applying for Jobs:</strong><br>
                        "When you find a job you like, click 'Apply.' Your uploaded resume will be sent directly to
                        the employer."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employer Features -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="fas fa-building fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">Employer Features</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. Posting a Job:</strong><br>
                        "Employers can post jobs by logging in and clicking 'Post a Job.' Fill out the form with job
                        details, and your posting will go live immediately."</p>
                        
                        <p><strong>2. Reviewing Applications:</strong><br>
                        "To view applications, log in and go to your dashboard. You'll see a list of candidates who
                        applied, along with their resumes."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger text-white rounded-circle p-3 me-3">
                            <i class="fas fa-tools fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">Troubleshooting</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. File Upload Issues:</strong><br>
                        "If you're having trouble uploading your resume, make sure it's in PDF or Word format and
                        under 5MB."</p>
                        
                        <p><strong>2. Email Not Received:</strong><br>
                        "Check your spam folder if you don't see our emails. If the issue persists, contact
                        support."</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQs -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-secondary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-question-circle fa-fw"></i>
                        </div>
                        <h5 class="card-title mb-0">FAQs</h5>
                    </div>
                    <div class="card-text">
                        <p><strong>1. Is GoWork free for students?</strong><br>
                        "Yes, GoWork is completely free for students to use."</p>
                        
                        <p><strong>2. How do employers contact candidates?</strong><br>
                        "Employers can reach out to candidates directly via the contact information provided in
                        their resumes."</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="bg-primary text-white rounded-circle p-3 d-inline-block mb-3">
                            <i class="fas fa-headset fa-2x"></i>
                        </div>
                        <h3>Contact Support</h3>
                    </div>
                    <p class="mb-4"><strong>Need More Help?</strong><br>
                    "If you can't find the answer you're looking for:<br>
                    email to <a href="mailto:servicegowork@gmail.com">servicegowork@gmail.com</a><br>
                    We're here to help!"</p>
                    
                    <div class="mt-4">
                        <h5>Have more questions?</h5>
                        <a href="mailto:servicegowork@gmail.com" class="btn btn-primary mt-2">
                            <i class="fas fa-envelope me-2"></i> Contact us by sending an email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?> 