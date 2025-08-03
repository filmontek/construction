<?php
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Dashboard';
$stats = getDashboardStats($_SESSION['user_id'], $_SESSION['user_role']);

// Get recent plans based on user role
$recentPlans = [];
if ($_SESSION['user_role'] === 'Workshop') {
    $recentPlans = getWorkPlans(['user_id' => $_SESSION['user_id']]);
} elseif ($_SESSION['user_role'] === 'Construction Office') {
    $recentPlans = getWorkPlans(['status' => 'submitted']);
} elseif ($_SESSION['user_role'] === 'Director') {
    $recentPlans = getWorkPlans(['status' => 'office_approved']);
} else {
    $recentPlans = getWorkPlans();
}

// Limit to 5 recent plans
$recentPlans = array_slice($recentPlans, 0, 5);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Dashboard</h1>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            <div>
                <span class="badge bg-primary fs-6">
                    <?php echo htmlspecialchars($_SESSION['user_role']); ?>
                    <?php if ($_SESSION['user_workshop']): ?>
                        - <?php echo htmlspecialchars($_SESSION['user_workshop']); ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['total_plans']; ?></div>
                    <div>Total Plans</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['pending_plans']; ?></div>
                    <div>Pending Review</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($stats['approved_plans'])): ?>
    <div class="col-md-3 mb-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['approved_plans']; ?></div>
                    <div>Approved Plans</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($stats['pending_users'])): ?>
    <div class="col-md-3 mb-3">
        <div class="stats-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['pending_users']; ?></div>
                    <div>Pending Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($_SESSION['user_role'] === 'Workshop'): ?>
                        <!-- Plan Type Selection for Workshop Users -->
                        <div class="col-12 mb-3">
                            <h6 class="text-muted mb-3">Create New Work Plan</h6>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="plans/create.php?type=Type%20I" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-clipboard-list me-2"></i>Type I Plan
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="plans/create.php?type=Type%20II" class="btn btn-outline-info w-100">
                                        <i class="fas fa-clipboard-list me-2"></i>Type II Plan
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="plans/create.php?type=Type%20III" class="btn btn-outline-success w-100">
                                        <i class="fas fa-clipboard-list me-2"></i>Type III Plan
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="plans/create.php?type=Temporary%20plan" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-clock me-2"></i>Temporary Plan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-4 mb-2">
                            <a href="plans/my_plans.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list me-2"></i>My Plans
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_role'] === 'Construction Office'): ?>
                        <div class="col-md-4 mb-2">
                            <a href="plans/pending_review.php" class="btn btn-warning w-100">
                                <i class="fas fa-eye me-2"></i>Review Plans
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="users/pending.php" class="btn btn-info w-100">
                                <i class="fas fa-user-check me-2"></i>Approve Users
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_role'] === 'Director'): ?>
                        <div class="col-md-3 mb-2">
                            <a href="plans/pending_review.php" class="btn btn-success w-100">
                                <i class="fas fa-stamp me-2"></i>Final Approval
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="users/pending.php" class="btn btn-info w-100">
                                <i class="fas fa-user-check me-2"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="users/create.php" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-2"></i>Create User
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 mb-2">
                        <a href="profile/profile.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Plans
                </h5>
                <a href="plans/<?php echo $_SESSION['user_role'] === 'Workshop' ? 'my_plans.php' : 'all_plans.php'; ?>" 
                   class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentPlans)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No plans found.</p>
                        <?php if ($_SESSION['user_role'] === 'Workshop'): ?>
                            <a href="plans/create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Your First Plan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Plan #</th>
                                    <th>Construction Item</th>
                                    <th>Workshop</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPlans as $plan): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($plan['monthly_plan_number']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($plan['construction_item']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($plan['workshop']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadge($plan['status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                                            </span>
                                            <?php if ($plan['status'] === 'scheduled'): ?>
                                                <br><small class="text-success">
                                                    <i class="fas fa-clock me-1"></i>Ready for execution
                                                </small>
                                            <?php elseif ($plan['status'] === 'not_scheduled'): ?>
                                                <br><small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>See details for reason
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getPriorityBadge($plan['priority']); ?>">
                                                <?php echo ucfirst($plan['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($plan['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="plans/view.php?id=<?php echo $plan['id']; ?>" 
                                                   class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($_SESSION['user_role'] === 'Construction Office' && $plan['status'] === 'submitted'): ?>
                                                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'office_approved', this)" 
                                                            class="btn btn-outline-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'rejected', this)" 
                                                            class="btn btn-outline-danger" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($_SESSION['user_role'] === 'Director' && $plan['status'] === 'office_approved'): ?>
                                                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'director_approved', this)" 
                                                            class="btn btn-outline-success" title="Final Approve">
                                                        <i class="fas fa-stamp"></i>
                                                    </button>
                                                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'rejected', this)" 
                                                            class="btn btn-outline-danger" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($_SESSION['user_role'] === 'Dispatcher' && $plan['status'] === 'director_approved'): ?>
                                                    <button onclick="showScheduleModal(<?php echo $plan['id']; ?>)" 
                                                            class="btn btn-outline-primary" title="Schedule">
                                                        <i class="fas fa-calendar"></i>
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
    </div>
</div>

<!-- Schedule Info Modal -->
<div class="modal fade" id="scheduleInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleInfoContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Not Scheduled Reason Modal -->
<div class="modal fade" id="notScheduledModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Not Scheduled - Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notScheduledContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleInfo(planId) {
    const plans = <?php echo json_encode($recentPlans); ?>;
    const plan = plans.find(p => p.id == planId);
    
    if (!plan) return;
    
    let content = '<div class="alert alert-success">';
    content += '<i class="fas fa-calendar-check me-2"></i>';
    content += '<h6>Plan is Scheduled</h6>';
    
    // Extract scheduled time from remarks
    if (plan.remarks && plan.remarks.includes('Scheduled:')) {
        const scheduledInfo = plan.remarks.substring(plan.remarks.indexOf('Scheduled:'));
        const scheduledLine = scheduledInfo.split('\n')[0];
        content += '<p class="mb-0">' + scheduledLine + '</p>';
    } else {
        content += '<p class="mb-0">This plan has been scheduled for execution.</p>';
    }
    
    content += '</div>';
    content += '<p><strong>Plan:</strong> ' + plan.construction_item + '</p>';
    content += '<p><strong>Status:</strong> <span class="badge bg-success">Scheduled</span></p>';
    
    document.getElementById('scheduleInfoContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('scheduleInfoModal')).show();
}

function showNotScheduledReason(planId) {
    const plans = <?php echo json_encode($recentPlans); ?>;
    const plan = plans.find(p => p.id == planId);
    
    if (!plan) return;
    
    let content = '<div class="alert alert-warning">';
    content += '<i class="fas fa-exclamation-triangle me-2"></i>';
    content += '<h6>Plan Not Scheduled</h6>';
    
    // Extract dispatcher note from remarks
    if (plan.remarks && plan.remarks.includes('Dispatcher Note:')) {
        const dispatcherNote = plan.remarks.substring(plan.remarks.indexOf('Dispatcher Note:') + 17);
        const reasonLine = dispatcherNote.split('\n')[0];
        content += '<p class="mb-0"><strong>Reason:</strong> ' + reasonLine.trim() + '</p>';
    } else {
        content += '<p class="mb-0">This plan has not been scheduled by the dispatcher.</p>';
    }
    
    content += '</div>';
    content += '<p><strong>Plan:</strong> ' + plan.construction_item + '</p>';
    content += '<p><strong>Status:</strong> <span class="badge bg-warning">Not Scheduled</span></p>';
    
    document.getElementById('notScheduledContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('notScheduledModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>