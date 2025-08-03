<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Workshop']);

$pageTitle = 'My Plans';

// Get user's plans
$plans = getWorkPlans(['user_id' => $_SESSION['user_id']]);

// Handle success message
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'plan_submitted':
            $success = 'Work plan submitted successfully!';
            break;
        case 'plan_updated':
            $success = 'Work plan updated successfully!';
            break;
        case 'plan_deleted':
            $success = 'Work plan deleted successfully!';
            break;
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">My Work Plans</h1>
                <p class="text-muted">Manage your work plans and track their status</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Plan
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Plans Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0"><?php echo count($plans); ?></div>
                        <div>Total Plans</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($plans, function($p) { return in_array($p['status'], ['draft', 'submitted']); })); ?>
                        </div>
                        <div>Pending</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'director_approved'; })); ?>
                        </div>
                        <div>Approved</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="h4 mb-0">
                            <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'rejected'; })); ?>
                        </div>
                        <div>Rejected</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plans Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Work Plans
            </h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" id="searchInput" 
                       placeholder="Search plans..." style="width: 200px;">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('all')">All Plans</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('draft')">Draft</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('submitted')">Submitted</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('office_approved')">Office Approved</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('director_approved')">Director Approved</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('rejected')">Rejected</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($plans)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Work Plans Found</h5>
                <p class="text-muted">You haven't created any work plans yet.</p>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Your First Plan
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Plan #</th>
                            <th>Type</th>
                            <th>Construction Item</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Application Date</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr data-status="<?php echo $plan['status']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($plan['monthly_plan_number'] ?: 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($plan['plan_type'] ?: 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($plan['construction_item']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($plan['section']); ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($plan['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                                    </span>
                                    <?php if ($plan['status'] === 'scheduled'): ?>
                                        <br><small class="text-success">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            <?php 
                                            // Extract scheduled time from remarks
                                            if (strpos($plan['remarks'], 'Scheduled:') !== false) {
                                                $scheduledInfo = substr($plan['remarks'], strpos($plan['remarks'], 'Scheduled:'));
                                                $scheduledInfo = explode("\n", $scheduledInfo)[0];
                                                echo htmlspecialchars($scheduledInfo);
                                            } else {
                                                echo 'Ready for execution';
                                            }
                                            ?>
                                        </small>
                                    <?php elseif ($plan['status'] === 'not_scheduled'): ?>
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <?php 
                                            // Extract dispatcher note from remarks
                                            if (strpos($plan['remarks'], 'Dispatcher Note:') !== false) {
                                                $dispatcherNote = substr($plan['remarks'], strpos($plan['remarks'], 'Dispatcher Note:') + 17);
                                                $dispatcherNote = explode("\n", $dispatcherNote)[0];
                                                echo htmlspecialchars(trim($dispatcherNote));
                                            } else {
                                                echo 'Not scheduled - see details';
                                            }
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getPriorityBadge($plan['priority']); ?>">
                                        <?php echo ucfirst($plan['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo $plan['application_time'] ? formatDate($plan['application_time']) : 'N/A'; ?></td>
                                <td><?php echo formatDate($plan['created_at']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view.php?id=<?php echo $plan['id']; ?>" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if (in_array($plan['status'], ['draft', 'rejected'])): ?>
                                            <a href="edit.php?id=<?php echo $plan['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($plan['status'] === 'draft'): ?>
                                            <button onclick="deletePlan(<?php echo $plan['id']; ?>)" 
                                                    class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
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

<script>
function filterByStatus(status) {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function deletePlan(planId) {
    if (confirm('Are you sure you want to delete this plan? This action cannot be undone.')) {
        fetch('delete_plan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ plan_id: planId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Plan deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Failed to delete plan', 'danger');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'danger');
            console.error('Error:', error);
        });
    }
}
</script>

<?php include '../../includes/footer.php'; ?>