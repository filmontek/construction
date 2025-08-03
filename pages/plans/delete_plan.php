<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Workshop']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$planId = isset($input['plan_id']) ? (int)$input['plan_id'] : 0;

if (!$planId) {
    echo json_encode(['success' => false, 'message' => 'Invalid plan ID']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Check if plan exists and belongs to user
    $stmt = $pdo->prepare("SELECT status FROM work_plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$planId, $_SESSION['user_id']]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
        exit();
    }
    
    // Only allow deletion of draft plans
    if ($plan['status'] !== 'draft') {
        echo json_encode(['success' => false, 'message' => 'Only draft plans can be deleted']);
        exit();
    }
    
    // Delete the plan
    $stmt = $pdo->prepare("DELETE FROM work_plans WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$planId, $_SESSION['user_id']]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Plan deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete plan']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>