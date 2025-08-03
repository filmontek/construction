<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Construction Office', 'Director']);

$pageTitle = 'Pending Review';

// Get plans based on user role
if ($_SESSION['user_role'] === 'Construction Office') {
    $plans = getWorkPlans(['status' => 'submitted']);
    $reviewType = 'Office Review';
} else { // Director
    $plans = getWorkPlans(['status' => 'office_approved']);
    $reviewType = 'Director Final Approval';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Pending Review</h1>
                <p class="text-muted"><?php echo $reviewType; ?> - <?php echo count($plans); ?> plans awaiting your review</p>
            </div>
            <div>
                <div class="btn-group">
                    <button class="btn btn-outline-success" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Bulk Approve
                    </button>
                    <button class="btn btn-outline-secondary" onclick="printPage()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
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
                        <div class="h4 mb-0"><?php echo count($plans); ?></div>
                        <div>Pending Review</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                            <?php echo count(array_filter($plans, function($p) { return $p['priority'] === 'urgent'; })); ?>
                        </div>
                        <div>Urgent Priority</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            <?php 
                            $oldPlans = array_filter($plans, function($p) { 
                                return strtotime($p['created_at']) < strtotime('-3 days'); 
                            });
                            echo count($oldPlans);
                            ?>
                        </div>
                        <div>Over 3 Days Old</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-times fa-2x opacity-75"></i>
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
                            <?php echo count(array_unique(array_column($plans, 'workshop'))); ?>
                        </div>
                        <div>Workshops</div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-industry fa-2x opacity-75"></i>
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
                <input type="text" class="form-control" id="searchInput" placeholder="Search plans...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="priorityFilter" onchange="filterByPriority()">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="workshopFilter" onchange="filterByWorkshop()">
                    <option value="">All Workshops</option>
                    <?php foreach (array_unique(array_column($plans, 'workshop')) as $workshop): ?>
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

<!-- Plans Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-tasks me-2"></i>Plans Awaiting Review
            </h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                <label class="form-check-label" for="selectAll">
                    Select All
                </label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($plans)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5 class="text-muted">All Caught Up!</h5>
                <p class="text-muted">No plans are currently awaiting your review.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()">
                            </th>
                            <th>Plan #</th>
                            <th>Type</th>
                            <th>Construction Item</th>
                            <th>Workshop</th>
                            <th>Created By</th>
                            <th>Priority</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr data-priority="<?php echo $plan['priority']; ?>" data-workshop="<?php echo $plan['workshop']; ?>">
                                <td>
                                    <input type="checkbox" class="plan-checkbox" value="<?php echo $plan['id']; ?>">
                                </td>
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
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($plan['workshop']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($plan['user_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($plan['user_role']); ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php echo getPriorityBadge($plan['priority']); ?>">
                                        <?php echo ucfirst($plan['priority']); ?>
                                    </span>
                                    <?php if ($plan['priority'] === 'urgent'): ?>
                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="Urgent Priority"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo formatDate($plan['created_at']); ?></div>
                                    <small class="text-muted">
                                        <?php 
                                        $daysAgo = floor((time() - strtotime($plan['created_at'])) / (60 * 60 * 24));
                                        echo $daysAgo . ' days ago';
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view.php?id=<?php echo $plan['id']; ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($_SESSION['user_role'] === 'Construction Office'): ?>
                                            <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'office_approved', this)" 
                                                    class="btn btn-outline-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'director_approved', this)" 
                                                    class="btn btn-outline-success" title="Final Approve">
                                                <i class="fas fa-stamp"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'rejected', this)" 
                                                class="btn btn-outline-danger" title="Reject">
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

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.plan-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function filterByPriority() {
    const priority = document.getElementById('priorityFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        if (!priority || row.dataset.priority === priority) {
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
    document.getElementById('priorityFilter').value = '';
    document.getElementById('workshopFilter').value = '';
    document.getElementById('searchInput').value = '';
    
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}

function bulkApprove() {
    const selectedPlans = Array.from(document.querySelectorAll('.plan-checkbox:checked')).map(cb => cb.value);
    
    if (selectedPlans.length === 0) {
        showNotification('Please select at least one plan to approve', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedPlans.length} selected plans?`)) {
        const status = <?php echo json_encode($_SESSION['user_role'] === 'Construction Office' ? 'office_approved' : 'director_approved'); ?>;
        
        Promise.all(selectedPlans.map(planId => 
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ plan_id: planId, status: status })
            })
        )).then(() => {
            showNotification('Selected plans approved successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        }).catch(() => {
            showNotification('Some plans failed to approve', 'danger');
        });
    }
}
</script>

<?php include '../../includes/footer.php'; ?>