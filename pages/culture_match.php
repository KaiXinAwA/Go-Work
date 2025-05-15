<?php
$pageTitle = 'Culture Match Companies';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/login.php');
}

// Redirect if not a job seeker
if (!hasUserType(USER_TYPE_JOBSEEKER)) {
    redirectTo(SITE_URL . '/pages/dashboard.php');
}

// Get user culture results
$userCultureResults = getUserCultureResults($_SESSION['user_id']);

// If no culture results, redirect to quiz
if (!$userCultureResults) {
    redirectTo(SITE_URL . '/pages/culture_quiz.php');
}

// Get all companies with culture matching info (including non-matches)
$allCompanies = getMatchingCompanies($_SESSION['user_id'], 20, true);

// Filter out companies that haven't completed their culture profile
$filteredCompanies = [];
foreach ($allCompanies as $company) {
    if (isset($company['company_culture']) && !empty($company['company_culture'])) {
        $filteredCompanies[] = $company;
    }
}

// Include header
require_once '../includes/header.php';
?>

<style>
.culture-match-header {
    background-color: #000;
    color: #fff;
    padding: 2rem 0;
}
.match-card {
    transition: transform 0.3s;
    height: 100%;
}
.match-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.match-score {
    font-size: 1.1rem;
    font-weight: bold;
    color: #0d6efd;
}
.recommend-badge {
    background-color: #6c757d;
}
.match-badge {
    background-color: #0d6efd;
}
.highly-recommend-badge {
    background-color: #198754;
}
.value-match {
    background-color: #e9f5ff;
    border-radius: 4px;
    padding: 2px 8px;
    margin-right: 5px;
    margin-bottom: 5px;
    display: inline-block;
    font-size: 0.85rem;
}
.no-matches-msg {
    font-style: italic;
    color: #6c757d;
}
</style>

<section class="container-fluid culture-match-header">
    <div class="container">
        <h1>Companies Culture Match</h1>
        <p class="lead">Showing companies based on your cultural preferences</p>
        <a href="<?php echo SITE_URL; ?>/pages/culture_quiz.php" class="btn btn-light mt-2">
            <i class="fas fa-arrow-left"></i> Back to Culture Quiz
        </a>
    </div>
</section>

<section class="container my-5">
    <div class="row mb-3">
        <div class="col-12">
            <h2>Companies with Culture Data (<?php echo count($filteredCompanies); ?>)</h2>
            <p class="text-muted">
                Only showing companies that have completed their culture profile. Companies are ranked by culture match score.
            </p>
        </div>
    </div>

    <div class="row">
        <?php if (empty($filteredCompanies)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No companies with culture data found. Please check back later as more companies complete their cultural profile.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($filteredCompanies as $company): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card match-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($company['company_name']); ?></h5>
                            <div>
                                <span class="match-score me-2"><?php echo $company['match_score']; ?>% Match</span>
                                <?php if ($company['match_score'] >= 80): ?>
                                    <span class="badge highly-recommend-badge">Highly Recommend</span>
                                <?php elseif ($company['match_score'] >= 60): ?>
                                    <span class="badge recommend-badge">Recommend</span>
                                <?php elseif ($company['match_score'] > 0): ?>
                                    <span class="badge match-badge">Match</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><?php echo htmlspecialchars(substr($company['description'] ?? 'No description available', 0, 120) . (strlen($company['description'] ?? '') > 120 ? '...' : '')); ?></p>
                            
                            <?php if (!empty($company['matches'])): ?>
                                <div class="mb-3">
                                    <h6>Culture Matches:</h6>
                                    <div>
                                        <?php foreach ($company['matches'] as $match): ?>
                                            <span class="value-match"><?php echo htmlspecialchars(ucfirst($match)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <h6>Company Culture Values:</h6>
                                    <div>
                                        <?php 
                                        $companyCulture = json_decode($company['company_culture'], true);
                                        if (isset($companyCulture['values']) && is_array($companyCulture['values'])):
                                            foreach ($companyCulture['values'] as $attribute => $value): 
                                        ?>
                                            <span class="value-match"><?php echo htmlspecialchars(ucfirst($attribute)); ?>: <?php echo htmlspecialchars(ucfirst($value)); ?></span>
                                        <?php 
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo SITE_URL; ?>/pages/view_company_profile.php?id=<?php echo $company['company_id']; ?>" class="btn btn-outline-dark">View Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
require_once '../includes/footer.php';
?> 