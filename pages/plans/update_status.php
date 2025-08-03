<?php
require_once '../../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$planId = isset($input['plan_id']) ? (int)$input['plan_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';

if (!$planId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Validate status
$validStatuses = ['office_approved', 'director_approved', 'rejected', 'scheduled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get plan details
    $stmt = $pdo->prepare("SELECT status FROM work_plans WHERE id = ?");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
        exit();
    }
    
    // Check permissions based on role and current status
    $canUpdate = false;
    
    if ($_SESSION['user_role'] === 'Construction Office') {
        // Construction office can approve submitted plans
        if ($plan['status'] === 'submitted' && in_array($status, ['office_approved', 'rejected'])) {
            $canUpdate = true;
        }
    } elseif ($_SESSION['user_role'] === 'Director') {
        // Director can approve office-approved plans or reject any plan
        if (($plan['status'] === 'office_approved' && in_array($status, ['director_approved', 'rejected'])) ||
            ($status === 'rejected')) {
            $canUpdate = true;
        }
    } elseif ($_SESSION['user_role'] === 'Dispatcher') {
        // Dispatcher can schedule director-approved plans
        if ($plan['status'] === 'director_approved' && $status === 'scheduled') {
            $canUpdate = true;
        }
    }
    
    if (!$canUpdate) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action']);
        exit();
    }
    
    // Update the plan status
    $stmt = $pdo->prepare("UPDATE work_plans SET status = ?, updated_at = NOW() WHERE id = ?");
    $success = $stmt->execute([$status, $planId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Plan status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update plan status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>