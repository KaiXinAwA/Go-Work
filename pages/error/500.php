<?php
$pageTitle = 'Server Error';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/GoWork/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/functions.php';

// Include header
require_once $root_path . 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page py-5">
                <h1 class="display-1 text-danger">500</h1>
                <h2 class="mb-4">Server Error</h2>
                <p class="lead">Sorry, something went wrong on our end. We're working to fix the issue.</p>
                <div class="mt-4">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Go Home</a>
                    <a href="javascript:history.back()" class="btn btn-outline-primary ms-2">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?>
