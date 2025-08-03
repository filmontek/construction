<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Director', 'Construction Office']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';

if (!$userId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Validate status
$validStatuses = ['active', 'inactive'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Update user status
    $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
    $success = $stmt->execute([$status, $userId]);
    
    if ($success) {
        // Log the action
        $action = $status === 'active' ? 'User Approved' : 'User Rejected';
        $stmt = $pdo->prepare("
            INSERT INTO system_logs (user_id, action, table_name, record_id, new_values, ip_address, created_at) 
            VALUES (?, ?, 'users', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $userId,
            json_encode(['status' => $status]),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'User status updated successfully',
            'user_name' => $user['name'],
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>