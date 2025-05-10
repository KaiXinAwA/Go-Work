<?php
$pageTitle = 'Company Profile';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Get company ID from URL
$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$company_id) {
    // Redirect to jobs page if no company ID provided
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Get company data
$company = fetchRow(
    "SELECT c.*, u.profile_picture FROM companies c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.company_id = ?",
    'i',
    [$company_id]
);

if (!$company) {
    // Company not found, redirect to jobs page
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Get company culture information
$companyCulture = getCompanyCulture($company_id);

// Get company's active jobs
$jobs = fetchAll(
    "SELECT * FROM jobs WHERE company_id = ? AND is_active = 1 ORDER BY posted_date DESC",
    'i',
    [$company_id]
);

// Include header
require_once $root_path . 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/jobs.php">Jobs</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($company['company_name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- Company Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($company['profile_picture']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $company['profile_picture']; ?>" alt="<?php echo htmlspecialchars($company['company_name']); ?>" class="img-fluid rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 100px; height: 100px;">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h1 class="mb-1"><?php echo htmlspecialchars($company['company_name']); ?></h1>
                            <div>
                                <?php if ($company['city'] || $company['state'] || $company['country']): ?>
                                    <div class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php
                                            $location = [];
                                            if ($company['city']) $location[] = htmlspecialchars($company['city']);
                                            if ($company['state']) $location[] = htmlspecialchars($company['state']);
                                            if ($company['country']) $location[] = htmlspecialchars($company['country']);
                                            echo implode(', ', $location);
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <h5>About the Company</h5>
                    <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                    
                    <!-- Company Culture Section -->
                    <?php if (!empty($companyCulture) && !empty($companyCulture['values'])): ?>
                    <div class="mt-4">
                        <h5>Company Culture</h5>
                        
                        <?php if (!empty($companyCulture['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($companyCulture['description'])); ?></p>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <?php 
                            // Define culture attribute display names
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
                            
                            // Define value display names
                            $valueNames = [
                                // Work Environment
                                'collaborative' => 'Collaborative Workspace',
                                'focused' => 'Focused Individual Space',
                                'flexible' => 'Flexible & Remote Options',
                                'energetic' => 'Dynamic & High-Energy',
                                
                                // Overtime
                                'dedicated' => 'Dedicated Work Ethic',
                                'structured' => 'Structured Hours',
                                'passionate' => 'Passion-Driven',
                                
                                // Management
                                'autonomous' => 'Autonomous',
                                'collaborative' => 'Collaborative',
                                'results-focused' => 'Results-Focused',
                                
                                // Work-Life Balance
                                'balanced' => 'Highly Balanced',
                                'dedicated' => 'Work-Focused',
                                'integrated' => 'Work-Life Integration',
                                
                                // Dress Code
                                'traditional' => 'Business Professional',
                                'moderate' => 'Business Casual',
                                'casual' => 'Casual',
                                'relaxed' => 'No Dress Code',
                                
                                // Communication
                                'direct' => 'Direct & Face-to-Face',
                                'formal' => 'Formal & Written',
                                'tech-savvy' => 'Tech-Based Collaboration',
                                'adaptable' => 'Mixed Communication',
                                
                                // Decision Making
                                'agile' => 'Quick & Agile',
                                'analytical' => 'Analytical & Data-Driven',
                                'collaborative' => 'Collaborative',
                                'balanced' => 'Balanced Approach',
                                
                                // Innovation
                                'innovative' => 'Cutting-Edge Innovation',
                                'balanced' => 'Innovation with Stability',
                                'traditional' => 'Established Processes',
                                'incremental' => 'Incremental Improvement',
                                
                                // Social Events
                                'energetic' => 'Active Team Building',
                                'professional' => 'Professional Networking',
                                'social' => 'Social Gatherings',
                                'independent' => 'Minimal Social Events',
                                
                                // Feedback
                                'direct' => 'Direct & Straightforward',
                                'considerate' => 'Constructive & Private',
                                'reflective' => 'Written & Reflective',
                                'structured' => 'Structured Reviews'
                            ];
                            
                            foreach ($companyCulture['values'] as $attribute => $value):
                                if (isset($attributeNames[$attribute]) && isset($valueNames[$value])):
                            ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-light">
                                        <div class="card-body py-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge bg-dark me-2"><?php echo htmlspecialchars($attributeNames[$attribute]); ?></span>
                                            </div>
                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($valueNames[$value]); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && hasUserType(USER_TYPE_JOBSEEKER)): ?>
                        <div class="mt-4">
                            <a href="<?php echo SITE_URL; ?>/pages/culture_quiz.php" class="btn btn-outline-dark">
                                <i class="fas fa-clipboard-check me-1"></i> Take Cultural Fit Quiz
                            </a>
                            <p class="text-muted small mt-2">Find out if you're a cultural fit for this company!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Contact Information -->
            <?php if ($company['contact_number']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    Contact Information
                </div>
                <div class="card-body">
                    <?php if ($company['contact_number']): ?>
                        <div class="mb-2">
                            <i class="fas fa-phone-alt me-2"></i>
                            <?php echo htmlspecialchars($company['contact_number']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Open Jobs -->
            <div class="card mb-4">
                <div class="card-header">
                    Open Positions
                </div>
                <div class="card-body">
                    <?php if (count($jobs) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($jobs as $job): ?>
                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h6>
                                        <small class="text-muted"><?php echo timeElapsed($job['posted_date']); ?></small>
                                    </div>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($job['location']); ?></p>
                                    <small class="text-primary"><?php echo htmlspecialchars($job['job_type']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mb-0">This company doesn't have any open positions right now.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php?company=<?php echo $company_id; ?>" class="btn btn-outline-primary btn-sm">View All Jobs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?> 