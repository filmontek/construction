<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Construction Office', 'Director', 'Dispatcher']);

$pageTitle = 'All Plans';

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$workshopFilter = isset($_GET['workshop']) ? $_GET['workshop'] : '';

// Build filters array
$filters = [];
if ($statusFilter) {
    $filters['status'] = $statusFilter;
}
if ($workshopFilter) {
    $filters['workshop'] = $workshopFilter;
}

// Get plans based on role
$plans = getWorkPlans($filters);

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">All Work Plans</h1>
                <p class="text-muted">Review and manage work plans from all workshops</p>
            </div>
            <div>
                <button class="btn btn-outline-secondary" onclick="exportToCSV('plansTable', 'work_plans.csv')">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="submitted" <?php echo $statusFilter === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="office_approved" <?php echo $statusFilter === 'office_approved' ? 'selected' : ''; ?>>Office Approved</option>
                    <option value="director_approved" <?php echo $statusFilter === 'director_approved' ? 'selected' : ''; ?>>Director Approved</option>
                    <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="workshop" class="form-label">Workshop</label>
                <select class="form-select" id="workshop" name="workshop">
                    <option value="">All Workshops</option>
                    <?php foreach (getWorkshops() as $workshop): ?>
                        <option value="<?php echo $workshop; ?>" <?php echo $workshopFilter === $workshop ? 'selected' : ''; ?>>
                            <?php echo $workshop; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="searchInput" class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Search plans...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Plans Statistics -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-primary"><?php echo count($plans); ?></div>
                <small class="text-muted">Total Plans</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-warning">
                    <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'submitted'; })); ?>
                </div>
                <small class="text-muted">Submitted</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-info">
                    <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'office_approved'; })); ?>
                </div>
                <small class="text-muted">Office Approved</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-success">
                    <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'director_approved'; })); ?>
                </div>
                <small class="text-muted">Director Approved</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-primary">
                    <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'scheduled'; })); ?>
                </div>
                <small class="text-muted">Scheduled</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="h4 mb-0 text-danger">
                    <?php echo count(array_filter($plans, function($p) { return $p['status'] === 'rejected'; })); ?>
                </div>
                <small class="text-muted">Rejected</small>
            </div>
        </div>
    </div>
</div>

<!-- Plans Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Work Plans
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($plans)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Plans Found</h5>
                <p class="text-muted">No work plans match your current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="plansTable">
                    <thead>
                        <tr>
                            <th>Plan #</th>
                            <th>Type</th>
                            <th>Construction Item</th>
                            <th>Workshop</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr>
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
                                    <small class="text-muted"><?php echo htmlspecialchars($plan['user_role'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($plan['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo getPriorityBadge($plan['priority']); ?>">
                                        <?php echo ucfirst($plan['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo $plan['application_time'] ? formatDate($plan['application_time']) : 'N/A'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view.php?id=<?php echo $plan['id']; ?>" 
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

<!-- Schedule Modal for Dispatcher -->
<?php if ($_SESSION['user_role'] === 'Dispatcher'): ?>
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Plan Decision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="planId" value="">
                    <div class="mb-3">
                        <label class="form-label">Schedule Decision</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="schedule_decision" id="schedule_yes" value="scheduled">
                            <label class="form-check-label" for="schedule_yes">
                                <i class="fas fa-calendar-check text-success me-2"></i>Schedule Plan
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="schedule_decision" id="schedule_no" value="not_scheduled">
                            <label class="form-check-label" for="schedule_no">
                                <i class="fas fa-calendar-times text-warning me-2"></i>Do Not Schedule
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="reasonField" style="display: none;">
                        <label for="schedule_reason" class="form-label">Reason for Not Scheduling *</label>
                        <textarea class="form-control" id="schedule_reason" rows="3" 
                                  placeholder="Please provide a reason why this plan cannot be scheduled..."></textarea>
                    </div>
                    <div class="mb-3" id="scheduleTimeField" style="display: none;">
                        <label class="form-label">Scheduled Time Range</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="schedule_time_from" class="form-label">From</label>
                                <input type="datetime-local" class="form-control" id="schedule_time_from">
                            </div>
                            <div class="col-md-6">
                                <label for="schedule_time_to" class="form-label">To</label>
                                <input type="datetime-local" class="form-control" id="schedule_time_to">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitScheduleDecision()">
                    <i class="fas fa-save me-2"></i>Save Decision
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleModal(planId) {
    document.getElementById('planId').value = planId;
    document.getElementById('scheduleForm').reset();
    document.getElementById('reasonField').style.display = 'none';
    document.getElementById('scheduleTimeField').style.display = 'none';
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}

// Show/hide fields based on selection
document.addEventListener('DOMContentLoaded', function() {
    const scheduleRadios = document.querySelectorAll('input[name="schedule_decision"]');
    scheduleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const reasonField = document.getElementById('reasonField');
            const scheduleTimeField = document.getElementById('scheduleTimeField');
            
            if (this.value === 'not_scheduled') {
                reasonField.style.display = 'block';
                scheduleTimeField.style.display = 'none';
                document.getElementById('schedule_reason').required = true;
            } else if (this.value === 'scheduled') {
                reasonField.style.display = 'none';
                scheduleTimeField.style.display = 'block';
                document.getElementById('schedule_reason').required = false;
            }
        });
    });
});

function submitScheduleDecision() {
    const planId = document.getElementById('planId').value;
    const decision = document.querySelector('input[name="schedule_decision"]:checked');
    
    if (!decision) {
        showNotification('Please select a schedule decision', 'warning');
        return;
    }
    
    let status = decision.value;
    let reason = '';
    let scheduleTimeFrom = '';
    let scheduleTimeTo = '';
    
    if (status === 'not_scheduled') {
        reason = document.getElementById('schedule_reason').value.trim();
        if (!reason) {
            showNotification('Please provide a reason for not scheduling', 'warning');
            return;
        }
    } else if (status === 'scheduled') {
        scheduleTimeFrom = document.getElementById('schedule_time_from').value;
        scheduleTimeTo = document.getElementById('schedule_time_to').value;
        
        if (scheduleTimeFrom && scheduleTimeTo && scheduleTimeFrom >= scheduleTimeTo) {
            showNotification('End time must be after start time', 'warning');
            return;
        }
    }
    
    // Send the schedule decision
    fetch('schedule_plan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            plan_id: planId,
            status: status,
            reason: reason,
            schedule_time_from: scheduleTimeFrom,
            schedule_time_to: scheduleTimeTo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Schedule decision saved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to save schedule decision', 'danger');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'danger');
        console.error('Error:', error);
    });
}
</script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>