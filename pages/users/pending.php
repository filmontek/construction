<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Director', 'Construction Office']);

$pageTitle = 'Pending User Requests';

// Get pending users
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pendingUsers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Pending User Requests</h1>
                <p class="text-muted"><?php echo count($pendingUsers); ?> users awaiting approval</p>
            </div>
            <div>
                <a href="active.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-users me-2"></i>Active Users
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Create User
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0"><?php echo count($pendingUsers); ?></div>
                        <div>Pending Requests</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-clock fa-2x opacity-75"></i>
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
                            <?php echo count(array_filter($pendingUsers, function($u) { return $u['role'] === 'Workshop'; })); ?>
                        </div>
                        <div>Workshop Requests</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-industry fa-2x opacity-75"></i>
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
                            <?php echo count(array_filter($pendingUsers, function($u) { return $u['role'] === 'Construction Office'; })); ?>
                        </div>
                        <div>Office Requests</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x opacity-75"></i>
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
                            <?php 
                            $oldRequests = array_filter($pendingUsers, function($u) { 
                                return strtotime($u['created_at']) < strtotime('-7 days'); 
                            });
                            echo count($oldRequests);
                            ?>
                        </div>
                        <div>Over 7 Days Old</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Users Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-clock me-2"></i>User Registration Requests
            </h5>
            <div>
                <input type="text" class="form-control form-control-sm" id="searchInput" 
                       placeholder="Search users..." style="width: 200px;">
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($pendingUsers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5 class="text-muted">All Caught Up!</h5>
                <p class="text-muted">No pending user requests at the moment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Workshop</th>
                            <th>Reason</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $user): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
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
                                    <div class="reason-text" style="max-width: 200px;">
                                        <?php echo htmlspecialchars($user['reason']); ?>
                                    </div>
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
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" 
                                                onclick="viewUserDetails(<?php echo $user['id']; ?>)" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" 
                                                onclick="updateUserStatus(<?php echo $user['id']; ?>, 'active', this)" 
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" 
                                                onclick="updateUserStatus(<?php echo $user['id']; ?>, 'inactive', this)" 
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
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
                <button type="button" class="btn btn-success" id="approveUserBtn">
                    <i class="fas fa-check me-2"></i>Approve User
                </button>
                <button type="button" class="btn btn-danger" id="rejectUserBtn">
                    <i class="fas fa-times me-2"></i>Reject User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewUserDetails(userId) {
    // Find user data
    const users = <?php echo json_encode($pendingUsers); ?>;
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
                <h6 class="text-muted">Request Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-warning">${user.status}</span></td></tr>
                    <tr><td><strong>Requested:</strong></td><td>${new Date(user.created_at).toLocaleDateString()}</td></tr>
                    <tr><td><strong>Days Ago:</strong></td><td>${Math.floor((Date.now() - new Date(user.created_at)) / (1000 * 60 * 60 * 24))} days</td></tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6 class="text-muted">Reason for Access Request</h6>
                <div class="p-3 bg-light rounded">
                    ${user.reason}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('userDetailsContent').innerHTML = content;
    
    // Set up modal buttons
    document.getElementById('approveUserBtn').onclick = () => {
        updateUserStatus(userId, 'active', document.getElementById('approveUserBtn'));
        bootstrap.Modal.getInstance(document.getElementById('userDetailsModal')).hide();
    };
    
    document.getElementById('rejectUserBtn').onclick = () => {
        updateUserStatus(userId, 'inactive', document.getElementById('rejectUserBtn'));
        bootstrap.Modal.getInstance(document.getElementById('userDetailsModal')).hide();
    };
    
    new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
}
</script>

<?php include '../../includes/footer.php'; ?>