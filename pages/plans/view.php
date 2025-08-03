<?php
require_once '../../includes/functions.php';
requireLogin();

$pageTitle = 'View Work Plan';
$error = '';
$success = '';

// Get plan ID
$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$planId) {
    header('Location: my_plans.php?error=invalid_plan');
    exit();
}

// Get plan details
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT wp.*, u.name as user_name, u.workshop, u.role as user_role 
    FROM work_plans wp 
    JOIN users u ON wp.user_id = u.id 
    WHERE wp.id = ?
");
$stmt->execute([$planId]);
$plan = $stmt->fetch();

if (!$plan) {
    header('Location: my_plans.php?error=plan_not_found');
    exit();
}

// Check permissions
if ($_SESSION['user_role'] === 'Workshop' && $plan['user_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php?error=access_denied');
    exit();
}

// Get comments
$comments = getPlanComments($planId);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment = sanitizeInput($_POST['comment']);
    if (!empty($comment)) {
        if (addPlanComment($planId, $_SESSION['user_id'], $comment)) {
            $success = 'Comment added successfully!';
            // Refresh comments
            $comments = getPlanComments($planId);
        } else {
            $error = 'Failed to add comment.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Work Plan Details</h1>
                <p class="text-muted">Plan #<?php echo htmlspecialchars($plan['monthly_plan_number'] ?: $plan['id']); ?></p>
            </div>
            <div>
                <?php if ($_SESSION['user_role'] === 'Workshop'): ?>
                    <a href="my_plans.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to My Plans
                    </a>
                <?php else: ?>
                    <a href="all_plans.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Plans
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($plan['status'], ['draft', 'rejected']) && $plan['user_id'] == $_SESSION['user_id']): ?>
                    <a href="edit.php?id=<?php echo $plan['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Plan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- Plan Status and Actions -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Current Status</h6>
                        <span class="badge <?php echo getStatusBadge($plan['status']); ?> fs-6">
                            <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                        </span>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Priority</h6>
                        <span class="badge <?php echo getPriorityBadge($plan['priority']); ?> fs-6">
                            <?php echo ucfirst($plan['priority']); ?>
                        </span>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Workshop</h6>
                        <span class="badge bg-secondary fs-6">
                            <?php echo htmlspecialchars($plan['workshop']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php if ($_SESSION['user_role'] === 'Construction Office' && $plan['status'] === 'submitted'): ?>
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Office Review Actions</h6>
                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'office_approved', this)" 
                            class="btn btn-success me-2">
                        <i class="fas fa-check me-1"></i>Approve
                    </button>
                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'rejected', this)" 
                            class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject
                    </button>
                </div>
            </div>
        <?php elseif ($_SESSION['user_role'] === 'Director' && $plan['status'] === 'office_approved'): ?>
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Director Final Approval</h6>
                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'director_approved', this)" 
                            class="btn btn-success me-2">
                        <i class="fas fa-stamp me-1"></i>Final Approve
                    </button>
                    <button onclick="updatePlanStatus(<?php echo $plan['id']; ?>, 'rejected', this)" 
                            class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Plan Details -->
<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Plan Type</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['plan_type'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Monthly Plan Number</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['monthly_plan_number'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Construction Item</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['construction_item']); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Work Time</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['work_time']); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Section</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['section'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Up/Down Line</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['up_down_line'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Location & Route
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Starting and Ending Mileage</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['starting_ending_mileage'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Work Train</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['work_train'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Starting Station</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['starting_station'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Ending Station</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['ending_station'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Content -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools me-2"></i>Work Content & Requirements
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Work Content and Requirements</label>
                        <div class="fw-bold">
                            <?php 
                            $remarks = $plan['remarks'] ?: '';
                            
                            // Check for dispatcher scheduling information
                            if (strpos($remarks, 'Scheduled:') !== false) {
                                // Extract scheduled time
                                $scheduledInfo = substr($remarks, strpos($remarks, 'Scheduled:'));
                                $scheduledInfo = explode("\n", $scheduledInfo)[0];
                                echo '<div class="alert alert-success">';
                                echo '<i class="fas fa-calendar-check me-2"></i>';
                                echo '<strong>Plan Scheduled:</strong><br>';
                                echo htmlspecialchars($scheduledInfo);
                                echo '</div>';
                                
                                // Show other remarks if any
                                $otherRemarks = str_replace($scheduledInfo, '', $remarks);
                                if (trim($otherRemarks)) {
                                    echo '<div class="mt-2">';
                                    echo '<strong>Additional Notes:</strong><br>';
                                    echo nl2br(htmlspecialchars(trim($otherRemarks)));
                                    echo '</div>';
                                }
                            } elseif (strpos($remarks, 'Dispatcher Note:') !== false) {
                                // Extract dispatcher note for not scheduled
                                $dispatcherNote = substr($remarks, strpos($remarks, 'Dispatcher Note:'));
                                $dispatcherNote = explode("\n", $dispatcherNote)[0];
                                echo '<div class="alert alert-warning">';
                                echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                                echo '<strong>Not Scheduled - Reason:</strong><br>';
                                echo htmlspecialchars(str_replace('Dispatcher Note:', '', $dispatcherNote));
                                echo '</div>';
                                
                                // Show other remarks if any
                                $otherRemarks = str_replace($dispatcherNote, '', $remarks);
                                if (trim($otherRemarks)) {
                                    echo '<div class="mt-2">';
                                    echo '<strong>Additional Notes:</strong><br>';
                                    echo nl2br(htmlspecialchars(trim($otherRemarks)));
                                    echo '</div>';
                                }
                            } else {
                                echo htmlspecialchars($remarks ?: 'N/A');
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Affected Operation Area</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['affected_operation_area'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Personnel & Contact
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Main Unit Person in Charge</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['main_unit_person_charge'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['phone_number'] ?: 'N/A'); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Unit of Suit</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($plan['unit_of_suit'] ?: 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Plan Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Application Time</label>
                    <div class="fw-bold"><?php echo $plan['application_time'] ? formatDate($plan['application_time']) : 'N/A'; ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Created</label>
                    <div class="fw-bold"><?php echo formatDateTime($plan['created_at']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Last Updated</label>
                    <div class="fw-bold"><?php echo formatDateTime($plan['updated_at']); ?></div>
                </div>
            </div>
        </div>

        <!-- Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-tie me-2"></i>Management
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Lister</label>
                    <div class="fw-bold"><?php echo htmlspecialchars($plan['lister'] ?: 'N/A'); ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Workshop Head</label>
                    <div class="fw-bold"><?php echo htmlspecialchars($plan['workshop_head'] ?: 'N/A'); ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Area Manager</label>
                    <div class="fw-bold"><?php echo htmlspecialchars($plan['area_manager'] ?: 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-comments me-2"></i>Comments (<?php echo count($comments); ?>)
                </h5>
            </div>
            <div class="card-body">
                <!-- Add Comment Form -->
                <?php if (hasRole(['Construction Office', 'Director'])): ?>
                    <form method="POST" class="mb-3">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" 
                                      placeholder="Add your review comment..." required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary btn-sm">
                            <i class="fas fa-comment me-1"></i>Add Comment
                        </button>
                    </form>
                    <hr>
                <?php endif; ?>

                <!-- Comments List -->
                <?php if (empty($comments)): ?>
                    <p class="text-muted text-center">No comments yet.</p>
                <?php else: ?>
                    <div class="comments-list" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-meta">
                                    <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                    <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($comment['user_role']); ?></span>
                                    <small class="text-muted ms-2"><?php echo formatDateTime($comment['created_at']); ?></small>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal for Dispatcher -->
<?php if ($_SESSION['user_role'] === 'Dispatcher' && $plan['status'] === 'director_approved'): ?>
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Plan</h5>
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