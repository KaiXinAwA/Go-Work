<?php
$pageTitle = 'Manage Companies';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get filter parameters
$licenseStatus = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query based on filters
$sql = "SELECT c.*, u.email, u.username FROM companies c JOIN users u ON c.user_id = u.user_id";
$types = '';
$params = [];
$whereAdded = false;

if (!empty($licenseStatus)) {
    $sql .= " WHERE c.license_status = ?";
    $types .= 's';
    $params[] = $licenseStatus;
    $whereAdded = true;
}

if (!empty($searchTerm)) {
    if ($whereAdded) {
        $sql .= " AND (c.company_name LIKE ? OR u.email LIKE ?)";
    } else {
        $sql .= " WHERE (c.company_name LIKE ? OR u.email LIKE ?)";
        $whereAdded = true;
    }
    $types .= 'ss';
    $searchParam = '%' . $searchTerm . '%';
    $params = array_merge($params, [$searchParam, $searchParam]);
}

$sql .= " ORDER BY c.created_at DESC";

// Get companies
$companies = empty($params) ? fetchAll($sql) : fetchAll($sql, $types, $params);

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
                    <li class="breadcrumb-item active" aria-current="page">Manage Companies</li>
                </ol>
            </nav>
            <h2>Manage Companies</h2>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/pages/admin/companies.php" method="GET">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="status" class="form-label">License Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="" <?php echo $licenseStatus === '' ? 'selected' : ''; ?>>All Statuses</option>
                                    <option value="Pending" <?php echo $licenseStatus === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $licenseStatus === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Not Approved" <?php echo $licenseStatus === 'Not Approved' ? 'selected' : ''; ?>>Not Approved</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" placeholder="Search by company name or email" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Companies Table -->
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
                                        <th>Created</th>
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
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo SITE_URL; ?>/pages/admin/edit_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo SITE_URL; ?>/pages/admin/view_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (isset($company['license_path']) && $company['license_path']): ?>
                                                        <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" class="btn btn-sm btn-outline-secondary" title="View License" target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $company['company_id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $company['company_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $company['company_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $company['company_id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the company <strong><?php echo htmlspecialchars($company['company_name']); ?></strong>?</p>
                                                                <p class="text-danger">This action cannot be undone and will delete all associated jobs and applications.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="<?php echo SITE_URL; ?>/api/admin/delete_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-danger">Delete Company</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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

<?php
// Include footer
require_once '../../includes/footer.php';
?>
