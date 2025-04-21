<?php
$pageTitle = 'Companies';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a worker
if (!isLoggedIn() || !hasUserType(USER_TYPE_GOWORK)) {
    $_SESSION['error'] = 'You must be logged in as a worker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if worker exists in gowork_workers table
$workerId = getWorkerId($_SESSION['user_id']);
if (!$workerId) {
    $_SESSION['error'] = 'Worker account not found. Please contact an administrator.';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get search params
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get all companies with filter
$params = [];
$types = '';
$whereClause = '';

if (!empty($search)) {
    $whereClause .= ' WHERE (c.company_name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($status)) {
    if (empty($whereClause)) {
        $whereClause .= ' WHERE c.license_status = ?';
    } else {
        $whereClause .= ' AND c.license_status = ?';
    }
    $params[] = $status;
    $types .= 's';
}

$companies = fetchAll(
    "SELECT c.*, u.email, u.username
     FROM companies c
     JOIN users u ON c.user_id = u.user_id
     $whereClause
     ORDER BY c.company_name ASC",
    $types,
    $params
);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Companies</li>
                </ol>
            </nav>
            <h2>Companies</h2>
            <p class="text-muted">View and filter registered companies</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Search & Filter</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/pages/worker/companies.php" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Term</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Company name or email" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">License Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Not Approved" <?php echo $status === 'Not Approved' ? 'selected' : ''; ?>>Not Approved</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="<?php echo SITE_URL; ?>/pages/worker/companies.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($companies)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Company Name</th>
                                        <th>Email</th>
                                        <th>License Status</th>
                                        <th>Registered On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companies as $company): ?>
                                        <tr>
                                            <td><?php echo $company['company_id']; ?></td>
                                            <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($company['license_status']) {
                                                    case 'Approved':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'Pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'Not Approved':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $company['license_status']; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($company['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/worker/view_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($company['license_status'] === 'Pending'): ?>
                                                <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-file-contract"></i> Review License
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No companies found matching your criteria.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 