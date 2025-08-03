<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Director', 'Construction Office']);

$pageTitle = 'Active Users';

// Get active users
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'active' ORDER BY name ASC");
$stmt->execute();
$activeUsers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Active Users</h1>
                <p class="text-muted"><?php echo count($activeUsers); ?> active users in the system</p>
            </div>
            <div>
                <a href="pending.php" class="btn btn-outline-warning me-2">
                    <i class="fas fa-user-clock me-2"></i>Pending Requests
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Create User
                </a>
            </div>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0"><?php echo count($activeUsers); ?></div>
                        <div>Active Users</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($activeUsers, function($u) { return $u['role'] === 'Workshop'; })); ?>
                        </div>
                        <div>Workshop Users</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-industry fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($activeUsers, function($u) { return $u['role'] === 'Construction Office'; })); ?>
                        </div>
                        <div>Office Users</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($activeUsers, function($u) { return $u['role'] === 'Director'; })); ?>
                        </div>
                        <div>Directors</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-tie fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="roleFilter" onchange="filterByRole()">
                    <option value="">All Roles</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Construction Office">Construction Office</option>
                    <option value="Director">Director</option>
                    <option value="Dispatcher">Dispatcher</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="workshopFilter" onchange="filterByWorkshop()">
                    <option value="">All Workshops</option>
                    <?php foreach (getWorkshops() as $workshop): ?>
                        <option value="<?php echo $workshop; ?>"><?php echo $workshop; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Users Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>System Users
            </h5>
            <div>
                <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('usersTable', 'active_users.csv')">
                    <i class="fas fa-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($activeUsers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Active Users</h5>
                <p class="text-muted">No active users found in the system.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Workshop</th>
                            <th>Joined</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeUsers as $user): ?>
                            <tr data-role="<?php echo $user['role']; ?>" data-workshop="<?php echo $user['workshop']; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <small class="text-primary">You</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['workshop']): ?>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($user['workshop']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo formatDate($user['created_at']); ?></div>
                                    <small class="text-muted">
                                        <?php 
                                        $daysAgo = floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24));
                                        echo $daysAgo . ' days ago';
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <div><?php echo formatDate($user['updated_at']); ?></div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" 
                                                onclick="viewUserDetails(<?php echo $user['id']; ?>)" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="updateUserStatus(<?php echo $user['id']; ?>, 'inactive', this)" 
                                                    title="Deactivate">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>

<script>
function filterByRole() {
    const role = document.getElementById('roleFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        if (!role || row.dataset.role === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByWorkshop() {
    const workshop = document.getElementById('workshopFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        if (!workshop || row.dataset.workshop === workshop) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('roleFilter').value = '';
    document.getElementById('workshopFilter').value = '';
    document.getElementById('searchInput').value = '';
    
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}

function viewUserDetails(userId) {
    const users = <?php echo json_encode($activeUsers); ?>;
    const user = users.find(u => u.id == userId);
    
    if (!user) return;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Personal Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${user.name}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${user.email}</td></tr>
                    <tr><td><strong>Role:</strong></td><td><span class="badge bg-primary">${user.role}</span></td></tr>
                    <tr><td><strong>Workshop:</strong></td><td>${user.workshop ? '<span class="badge bg-secondary">' + user.workshop + '</span>' : 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Account Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">${user.status}</span></td></tr>
                    <tr><td><strong>Joined:</strong></td><td>${new Date(user.created_at).toLocaleDateString()}</td></tr>
                    <tr><td><strong>Last Updated:</strong></td><td>${new Date(user.updated_at).toLocaleDateString()}</td></tr>
                    <tr><td><strong>Days Active:</strong></td><td>${Math.floor((Date.now() - new Date(user.created_at)) / (1000 * 60 * 60 * 24))} days</td></tr>
                </table>
            </div>
        </div>
        ${user.reason ? `
        <div class="row">
            <div class="col-12">
                <h6 class="text-muted">Original Access Request Reason</h6>
                <div class="p-3 bg-light rounded">
                    ${user.reason}
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('userDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
}
</script>

<?php include '../../includes/footer.php'; ?>