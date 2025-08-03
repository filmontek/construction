<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Dispatcher']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$planId = isset($input['plan_id']) ? (int)$input['plan_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';
$reason = isset($input['reason']) ? sanitizeInput($input['reason']) : '';
$scheduleTimeFrom = isset($input['schedule_time_from']) ? $input['schedule_time_from'] : '';
$scheduleTimeTo = isset($input['schedule_time_to']) ? $input['schedule_time_to'] : '';

if (!$planId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Validate status
$validStatuses = ['scheduled', 'not_scheduled'];
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
    
    // Check if plan is director approved
    if ($plan['status'] !== 'director_approved') {
        echo json_encode(['success' => false, 'message' => 'Plan must be director approved to schedule']);
        exit();
    }
    
    // Prepare update data
    $updateData = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add schedule-specific fields
    if ($status === 'not_scheduled') {
        // Add reason to remarks or create a separate field
        $stmt = $pdo->prepare("UPDATE work_plans SET status = ?, remarks = CONCAT(COALESCE(remarks, ''), ?, ?), updated_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$status, "\nDispatcher Note: ", $reason, $planId]);
    } else {
        // For scheduled plans, optionally add schedule time
        if ($scheduleTimeFrom && $scheduleTimeTo) {
            $scheduleText = "From: " . date('M d, Y H:i', strtotime($scheduleTimeFrom)) . " To: " . date('M d, Y H:i', strtotime($scheduleTimeTo));
            $stmt = $pdo->prepare("UPDATE work_plans SET status = ?, remarks = CONCAT(COALESCE(remarks, ''), ?, ?), updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$status, "\nScheduled: ", $scheduleText, $planId]);
        } elseif ($scheduleTimeFrom) {
            $scheduleText = "From: " . date('M d, Y H:i', strtotime($scheduleTimeFrom));
            $stmt = $pdo->prepare("UPDATE work_plans SET status = ?, remarks = CONCAT(COALESCE(remarks, ''), ?, ?), updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$status, "\nScheduled: ", $scheduleText, $planId]);
        } else {
            $stmt = $pdo->prepare("UPDATE work_plans SET status = ?, updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$status, $planId]);
        }
    }
    
    if ($success) {
        // Log the action
        $action = $status === 'scheduled' ? 'Plan Scheduled' : 'Plan Not Scheduled';
        
        echo json_encode([
            'success' => true, 
            'message' => 'Schedule decision saved successfully',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update plan status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>