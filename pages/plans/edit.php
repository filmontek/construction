<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Workshop']);

$pageTitle = 'Edit Work Plan';
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
$stmt = $pdo->prepare("SELECT * FROM work_plans WHERE id = ? AND user_id = ?");
$stmt->execute([$planId, $_SESSION['user_id']]);
$plan = $stmt->fetch();

if (!$plan) {
    header('Location: my_plans.php?error=plan_not_found');
    exit();
}

// Check if plan can be edited
if (!in_array($plan['status'], ['draft', 'rejected'])) {
    header('Location: view.php?id=' . $planId . '&error=cannot_edit');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [
        'monthly_plan_number' => sanitizeInput($_POST['monthly_plan_number']),
        'plan_type' => sanitizeInput($_POST['plan_type']),
        'construction_item' => sanitizeInput($_POST['construction_item']),
        'work_time' => sanitizeInput($_POST['work_time']),
        'section' => sanitizeInput($_POST['section']),
        'up_down_line' => sanitizeInput($_POST['up_down_line']),
        'starting_ending_mileage' => sanitizeInput($_POST['starting_ending_mileage']),
        'work_train' => sanitizeInput($_POST['work_train']),
        'starting_station' => sanitizeInput($_POST['starting_station']),
        'ending_station' => sanitizeInput($_POST['ending_station']),
        'work_content_requirements' => sanitizeInput($_POST['work_content_requirements']),
        'affected_operation_area' => sanitizeInput($_POST['affected_operation_area']),
        'power_on_off' => sanitizeInput($_POST['power_on_off']),
        'power_outage_range' => sanitizeInput($_POST['power_outage_range']),
        'speed_limit_change' => sanitizeInput($_POST['speed_limit_change']),
        'equipment_changes' => sanitizeInput($_POST['equipment_changes']),
        'main_unit_person_charge' => sanitizeInput($_POST['main_unit_person_charge']),
        'phone_number' => sanitizeInput($_POST['phone_number']),
        'unit_of_suit' => sanitizeInput($_POST['unit_of_suit']),
        'remarks' => sanitizeInput($_POST['remarks']),
        'lister' => sanitizeInput($_POST['lister']),
        'workshop_head' => sanitizeInput($_POST['workshop_head']),
        'area_manager' => sanitizeInput($_POST['area_manager']),
        'application_time' => sanitizeInput($_POST['application_time']),
        'priority' => sanitizeInput($_POST['priority']),
        'status' => isset($_POST['submit_plan']) ? 'submitted' : 'draft'
    ];
    
    // Basic validation
    if (empty($updateData['construction_item']) || empty($updateData['work_time'])) {
        $error = 'Please fill in all required fields.';
    } else {
        // Update the plan
        $sql = "UPDATE work_plans SET 
                monthly_plan_number = ?, plan_type = ?, construction_item = ?, work_time = ?,
                section = ?, up_down_line = ?, starting_ending_mileage = ?, work_train = ?,
                starting_station = ?, ending_station = ?, work_content_requirements = ?,
                affected_operation_area = ?, power_on_off = ?, power_outage_range = ?,
                speed_limit_change = ?, equipment_changes = ?, main_unit_person_charge = ?,
                phone_number = ?, unit_of_suit = ?, remarks = ?, lister = ?, workshop_head = ?,
                area_manager = ?, application_time = ?, priority = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $params = array_values($updateData);
        $params[] = $planId;
        $params[] = $_SESSION['user_id'];
        
        if ($stmt->execute($params)) {
            $success = $updateData['status'] === 'submitted' ? 
                'Work plan updated and submitted successfully!' : 
                'Work plan updated and saved as draft.';
            
            if ($updateData['status'] === 'submitted') {
                header('Location: my_plans.php?success=plan_updated');
                exit();
            }
            
            // Refresh plan data
            $stmt = $pdo->prepare("SELECT * FROM work_plans WHERE id = ? AND user_id = ?");
            $stmt->execute([$planId, $_SESSION['user_id']]);
            $plan = $stmt->fetch();
        } else {
            $error = 'Failed to update work plan. Please try again.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Edit Work Plan</h1>
                <p class="text-muted">Plan #<?php echo htmlspecialchars($plan['monthly_plan_number'] ?: $plan['id']); ?></p>
            </div>
            <div>
                <a href="view.php?id=<?php echo $plan['id']; ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-eye me-2"></i>View Plan
                </a>
                <a href="my_plans.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to My Plans
                </a>
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

<form method="POST" id="planForm" class="needs-validation" novalidate>
    <!-- Plan Type Selection -->
    <div class="plan-form-section">
        <h5><i class="fas fa-clipboard-list me-2"></i>Plan Type & Basic Information</h5>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="plan_type" class="form-label">Plan Type *</label>
                <select class="form-select" id="plan_type" name="plan_type" required>
                    <option value="">Select Type</option>
                    <option value="Type I" <?php echo $plan['plan_type'] === 'Type I' ? 'selected' : ''; ?>>Type I</option>
                    <option value="Type II" <?php echo $plan['plan_type'] === 'Type II' ? 'selected' : ''; ?>>Type II</option>
                    <option value="Type III" <?php echo $plan['plan_type'] === 'Type III' ? 'selected' : ''; ?>>Type III</option>
                    <option value="Temporary plan" <?php echo $plan['plan_type'] === 'Temporary plan' ? 'selected' : ''; ?>>Temporary Plan</option>
                </select>
                <div class="invalid-feedback">Please select a plan type.</div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="monthly_plan_number" class="form-label">Monthly Plan Number</label>
                <input type="text" class="form-control" id="monthly_plan_number" name="monthly_plan_number" 
                       value="<?php echo htmlspecialchars($plan['monthly_plan_number']); ?>" placeholder="e.g., III">
            </div>
            <div class="col-md-6 mb-3">
                <label for="construction_item" class="form-label">Construction Item *</label>
                <input type="text" class="form-control" id="construction_item" name="construction_item" 
                       value="<?php echo htmlspecialchars($plan['construction_item']); ?>" 
                       placeholder="e.g., CT14 Worker deliver" required>
                <div class="invalid-feedback">Please enter the construction item.</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="work_time" class="form-label">Work Time *</label>
                <input type="text" class="form-control" id="work_time" name="work_time" 
                       value="<?php echo htmlspecialchars($plan['work_time']); ?>" 
                       placeholder="e.g., 60 minutes" required>
                <div class="invalid-feedback">Please enter the work time.</div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" 
                       value="<?php echo htmlspecialchars($plan['section']); ?>" 
                       placeholder="e.g., Indode-Bishoftu">
            </div>
            <div class="col-md-4 mb-3">
                <label for="up_down_line" class="form-label">Up/Down Line</label>
                <select class="form-select" id="up_down_line" name="up_down_line">
                    <option value="">Select Direction</option>
                    <option value="up" <?php echo $plan['up_down_line'] === 'up' ? 'selected' : ''; ?>>Up</option>
                    <option value="down" <?php echo $plan['up_down_line'] === 'down' ? 'selected' : ''; ?>>Down</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Location Information -->
    <div class="plan-form-section">
        <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Route Information</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="starting_ending_mileage" class="form-label">Starting and Ending Mileage</label>
                <input type="text" class="form-control" id="starting_ending_mileage" name="starting_ending_mileage" 
                       value="<?php echo htmlspecialchars($plan['starting_ending_mileage']); ?>" 
                       placeholder="e.g., 59km+200m-61km+600m">
            </div>
            <div class="col-md-6 mb-3">
                <label for="work_train" class="form-label">Work Train</label>
                <input type="text" class="form-control" id="work_train" name="work_train" 
                       value="<?php echo htmlspecialchars($plan['work_train']); ?>" 
                       placeholder="e.g., One Rail car+N1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="starting_station" class="form-label">Starting Station</label>
                <input type="text" class="form-control" id="starting_station" name="starting_station" 
                       value="<?php echo htmlspecialchars($plan['starting_station']); ?>" 
                       placeholder="e.g., Indode">
            </div>
            <div class="col-md-6 mb-3">
                <label for="ending_station" class="form-label">Ending Station</label>
                <input type="text" class="form-control" id="ending_station" name="ending_station" 
                       value="<?php echo htmlspecialchars($plan['ending_station']); ?>" 
                       placeholder="e.g., Bishoftu">
            </div>
        </div>
    </div>

    <!-- Work Details -->
    <div class="plan-form-section">
        <h5><i class="fas fa-tools me-2"></i>Work Content & Requirements</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="work_content_requirements" class="form-label">Work Content and Requirements</label>
                <textarea class="form-control" id="work_content_requirements" name="work_content_requirements" 
                          rows="3" placeholder="Describe the work to be performed..."><?php echo htmlspecialchars($plan['work_content_requirements']); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="affected_operation_area" class="form-label">Affected Operation Area</label>
                <input type="text" class="form-control" id="affected_operation_area" name="affected_operation_area" 
                       value="<?php echo htmlspecialchars($plan['affected_operation_area']); ?>" 
                       placeholder="e.g., /">
            </div>
        </div>
    </div>

    <!-- Power & Safety -->
    <div class="plan-form-section">
        <h5><i class="fas fa-bolt me-2"></i>Power & Safety Information</h5>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="power_on_off" class="form-label">Power ON/OFF</label>
                <select class="form-select" id="power_on_off" name="power_on_off">
                    <option value="">Select</option>
                    <option value="ON" <?php echo $plan['power_on_off'] === 'ON' ? 'selected' : ''; ?>>ON</option>
                    <option value="OFF" <?php echo $plan['power_on_off'] === 'OFF' ? 'selected' : ''; ?>>OFF</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="power_outage_range" class="form-label">Power Outage Range</label>
                <input type="text" class="form-control" id="power_outage_range" name="power_outage_range" 
                       value="<?php echo htmlspecialchars($plan['power_outage_range']); ?>" 
                       placeholder="e.g., /">
            </div>
            <div class="col-md-6 mb-3">
                <label for="speed_limit_change" class="form-label">Speed Limit Change in Operation</label>
                <input type="text" class="form-control" id="speed_limit_change" name="speed_limit_change" 
                       value="<?php echo htmlspecialchars($plan['speed_limit_change']); ?>" 
                       placeholder="e.g., /">
            </div>
        </div>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="equipment_changes" class="form-label">Equipment Changes</label>
                <textarea class="form-control" id="equipment_changes" name="equipment_changes" 
                          rows="2" placeholder="Describe any equipment changes..."><?php echo htmlspecialchars($plan['equipment_changes']); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Personnel Information -->
    <div class="plan-form-section">
        <h5><i class="fas fa-users me-2"></i>Personnel & Contact Information</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="main_unit_person_charge" class="form-label">Main Unit Person in Charge</label>
                <input type="text" class="form-control" id="main_unit_person_charge" name="main_unit_person_charge" 
                       value="<?php echo htmlspecialchars($plan['main_unit_person_charge']); ?>" 
                       placeholder="e.g., Abdi Bekele">
            </div>
            <div class="col-md-4 mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                       value="<?php echo htmlspecialchars($plan['phone_number']); ?>" 
                       placeholder="e.g., 09404073 08">
            </div>
            <div class="col-md-4 mb-3">
                <label for="unit_of_suit" class="form-label">Unit of Suit</label>
                <input type="text" class="form-control" id="unit_of_suit" name="unit_of_suit" 
                       value="<?php echo htmlspecialchars($plan['unit_of_suit']); ?>">
            </div>
        </div>
    </div>

    <!-- Management Approval -->
    <div class="plan-form-section">
        <h5><i class="fas fa-user-tie me-2"></i>Management & Approval</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="lister" class="form-label">Lister</label>
                <input type="text" class="form-control" id="lister" name="lister" 
                       value="<?php echo htmlspecialchars($plan['lister']); ?>" 
                       placeholder="e.g., Evunetu K">
            </div>
            <div class="col-md-4 mb-3">
                <label for="workshop_head" class="form-label">Workshop Head</label>
                <input type="text" class="form-control" id="workshop_head" name="workshop_head" 
                       value="<?php echo htmlspecialchars($plan['workshop_head']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="area_manager" class="form-label">Area Manager</label>
                <input type="text" class="form-control" id="area_manager" name="area_manager" 
                       value="<?php echo htmlspecialchars($plan['area_manager']); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="application_time" class="form-label">Application Time</label>
                <input type="date" class="form-control" id="application_time" name="application_time" 
                       value="<?php echo $plan['application_time']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="priority" class="form-label">Priority Level</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="low" <?php echo $plan['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $plan['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $plan['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="urgent" <?php echo $plan['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <input type="text" class="form-control" id="remarks" name="remarks" 
                       value="<?php echo htmlspecialchars($plan['remarks']); ?>" 
                       placeholder="Additional notes...">
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="plan-form-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Current status: <strong><?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?></strong>
                </small>
            </div>
            <div>
                <button type="submit" name="save_draft" class="btn btn-outline-primary me-2">
                    <i class="fas fa-save me-2"></i>Save as Draft
                </button>
                <button type="submit" name="submit_plan" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Submit Plan
                </button>
            </div>
        </div>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>