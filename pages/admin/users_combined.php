<?php
$pageTitle = 'Manage Users';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get active tab
$activeTab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'users';

// Get filter parameters
$userType = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query based on which tab is active
if ($activeTab == 'users') {
    // Regular users tab (exclude staff)
    $sql = "SELECT * FROM users WHERE user_type NOT IN (3, 4)"; // Exclude GoWork workers and admins
    $validUserTypes = [USER_TYPE_JOBSEEKER, USER_TYPE_COMPANY];
} else {
    // Staff tab (only include staff)
    $sql = "SELECT * FROM users WHERE user_type IN (3, 4)"; // Only GoWork workers and admins
    $validUserTypes = [USER_TYPE_GOWORK, USER_TYPE_ADMIN];
}

$types = '';
$params = [];
$whereAdded = true; // We already added WHERE clause

if ($userType > 0 && in_array($userType, $validUserTypes)) {
    $sql .= " AND user_type = ?";
    $types .= 'i';
    $params[] = $userType;
}

if (!empty($searchTerm)) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $types .= 'ss';
    $searchParam = '%' . $searchTerm . '%';
    $params = array_merge($params, [$searchParam, $searchParam]);
}

$sql .= " ORDER BY created_at DESC";

// Get users
$users = empty($params) ? fetchAll($sql) : fetchAll($sql, $types, $params);

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
                    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2>Manage Users</h2>
                <div>
                    <?php if ($activeTab == 'users'): ?>
                        <!-- "Add User" button removed -->
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/pages/admin/add_staff.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Staff Member
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab == 'users' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=users">
                        <i class="fas fa-users"></i> Regular Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab == 'staff' ? 'active' : ''; ?>"
                       href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=staff">
                        <i class="fas fa-user-tie"></i> Staff Members
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/pages/admin/users_combined.php" method="GET">
                        <input type="hidden" name="tab" value="<?php echo $activeTab; ?>">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="type" class="form-label">User Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="0" <?php echo $userType === 0 ? 'selected' : ''; ?>>All Types</option>
                                    
                                    <?php if ($activeTab == 'users'): ?>
                                        <!-- Regular user types -->
                                        <option value="<?php echo USER_TYPE_JOBSEEKER; ?>" <?php echo $userType === USER_TYPE_JOBSEEKER ? 'selected' : ''; ?>>Job Seekers</option>
                                        <option value="<?php echo USER_TYPE_COMPANY; ?>" <?php echo $userType === USER_TYPE_COMPANY ? 'selected' : ''; ?>>Companies</option>
                                    <?php else: ?>
                                        <!-- Staff types -->
                                        <option value="<?php echo USER_TYPE_GOWORK; ?>" <?php echo $userType === USER_TYPE_GOWORK ? 'selected' : ''; ?>>GoWork Workers</option>
                                        <option value="<?php echo USER_TYPE_ADMIN; ?>" <?php echo $userType === USER_TYPE_ADMIN ? 'selected' : ''; ?>>Administrators</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" placeholder="Search by username or email" value="<?php echo htmlspecialchars($searchTerm); ?>">
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
    
    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($users)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>User Type</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php
                                                $userTypeText = '';
                                                $typeClass = '';
                                                
                                                switch ($user['user_type']) {
                                                    case USER_TYPE_JOBSEEKER:
                                                        $userTypeText = 'Job Seeker';
                                                        $typeClass = 'bg-primary';
                                                        break;
                                                    case USER_TYPE_COMPANY:
                                                        $userTypeText = 'Company';
                                                        $typeClass = 'bg-success';
                                                        break;
                                                    case USER_TYPE_GOWORK:
                                                        $userTypeText = 'GoWork Worker';
                                                        $typeClass = 'bg-info';
                                                        break;
                                                    case USER_TYPE_ADMIN:
                                                        $userTypeText = 'Administrator';
                                                        $typeClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $typeClass; ?>"><?php echo $userTypeText; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo SITE_URL; ?>/pages/admin/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo SITE_URL; ?>/pages/admin/view_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php 
                                                    // For staff members, prevent self-deletion
                                                    $showDelete = true;
                                                    if ($activeTab == 'staff' && $user['user_id'] == $_SESSION['user_id']) {
                                                        $showDelete = false;
                                                    }
                                                    
                                                    if ($showDelete):
                                                    ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['user_id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <?php if ($showDelete): ?>
                                                <div class="modal fade" id="deleteModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['user_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $user['user_id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete 
                                                                <?php if ($activeTab == 'staff'): ?>
                                                                    the staff member
                                                                <?php else: ?>
                                                                    the user
                                                                <?php endif; ?>
                                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                                                                <p class="text-danger">This action cannot be undone and will delete all associated data.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="<?php echo SITE_URL; ?>/api/admin/delete_user.php?id=<?php echo $user['user_id']; ?>&redirect=users_combined.php?tab=<?php echo $activeTab; ?>" class="btn btn-danger">
                                                                    <?php if ($activeTab == 'staff'): ?>
                                                                        Delete Staff Member
                                                                    <?php else: ?>
                                                                        Delete User
                                                                    <?php endif; ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <?php if ($activeTab == 'staff'): ?>
                                No staff members found matching your criteria.
                            <?php else: ?>
                                No users found matching your criteria.
                            <?php endif; ?>
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