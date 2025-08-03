<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Security functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /construction_management/pages/login.php');
        exit();
    }
}

function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        header('Location: /construction_management/pages/dashboard.php?error=access_denied');
        exit();
    }
}

// User functions
function getUserById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserByEmail($email) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function createUser($data) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, workshop, reason, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['email'],
        hashPassword($data['password']),
        $data['role'],
        $data['workshop'] ?? null,
        $data['reason'] ?? null,
        $data['status'] ?? 'pending'
    ]);
}

function updateUserStatus($userId, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $userId]);
}

// Plan functions
function createWorkPlan($data) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO work_plans (
            user_id, monthly_plan_number, plan_type, construction_item, work_time,
            section, up_down_line, starting_ending_mileage, work_train, starting_station,
            ending_station, work_content_requirements, affected_operation_area,
            power_on_off, power_outage_range, speed_limit_change, equipment_changes,
            main_unit_person_charge, phone_number, unit_of_suit, remarks,
            lister, workshop_head, area_manager, application_time, status, priority
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['user_id'], $data['monthly_plan_number'], $data['plan_type'],
        $data['construction_item'], $data['work_time'], $data['section'],
        $data['up_down_line'], $data['starting_ending_mileage'], $data['work_train'],
        $data['starting_station'], $data['ending_station'], $data['work_content_requirements'],
        $data['affected_operation_area'], $data['power_on_off'], $data['power_outage_range'],
        $data['speed_limit_change'], $data['equipment_changes'], $data['main_unit_person_charge'],
        $data['phone_number'], $data['unit_of_suit'], $data['remarks'],
        $data['lister'], $data['workshop_head'], $data['area_manager'],
        $data['application_time'], $data['status'] ?? 'draft', $data['priority'] ?? 'medium'
    ]);
}

function getWorkPlans($filters = []) {
    $pdo = getDBConnection();
    $sql = "SELECT wp.*, u.name as user_name, u.workshop, u.role as user_role FROM work_plans wp 
            JOIN users u ON wp.user_id = u.id WHERE 1=1";
    $params = [];
    
    if (isset($filters['status'])) {
        $sql .= " AND wp.status = ?";
        $params[] = $filters['status'];
    }
    
    if (isset($filters['user_id'])) {
        $sql .= " AND wp.user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if (isset($filters['workshop'])) {
        $sql .= " AND u.workshop = ?";
        $params[] = $filters['workshop'];
    }
    
    $sql .= " ORDER BY wp.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function updatePlanStatus($planId, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE work_plans SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $planId]);
}

// Comment functions
function addPlanComment($planId, $userId, $comment) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO plan_comments (plan_id, user_id, comment) VALUES (?, ?, ?)");
    return $stmt->execute([$planId, $userId, $comment]);
}

function getPlanComments($planId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT pc.*, u.name as user_name, u.role as user_role 
        FROM plan_comments pc 
        JOIN users u ON pc.user_id = u.id 
        WHERE pc.plan_id = ? 
        ORDER BY pc.created_at ASC
    ");
    $stmt->execute([$planId]);
    return $stmt->fetchAll();
}

// Dashboard statistics
function getDashboardStats($userId = null, $role = null) {
    $pdo = getDBConnection();
    $stats = [];
    
    // Total plans
    if ($role === 'Workshop') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans");
        $stmt->execute();
    }
    $stats['total_plans'] = $stmt->fetch()['count'];
    
    // Pending plans
    if ($role === 'Construction Office') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE status = 'submitted'");
    } elseif ($role === 'Director') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE status = 'office_approved'");
    } elseif ($role === 'Workshop') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE user_id = ? AND status IN ('draft', 'submitted')");
        $stmt->execute([$userId]);
        $stats['pending_plans'] = $stmt->fetch()['count'];
        return $stats;
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE status IN ('submitted', 'office_approved')");
    }
    $stmt->execute();
    $stats['pending_plans'] = $stmt->fetch()['count'];
    
    // Approved plans
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM work_plans WHERE status = 'director_approved'");
    $stmt->execute();
    $stats['approved_plans'] = $stmt->fetch()['count'];
    
    // Pending users (for admins)
    if (in_array($role, ['Director', 'Construction Office'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_users'] = $stmt->fetch()['count'];
    }
    
    return $stats;
}

// Utility functions
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

function getStatusBadge($status) {
    $badges = [
        'draft' => 'badge-secondary',
        'submitted' => 'badge-warning',
        'office_approved' => 'badge-info',
        'director_approved' => 'badge-success',
        'scheduled' => 'badge-primary',
        'not_scheduled' => 'badge-danger',
        'rejected' => 'badge-danger',
        'pending' => 'badge-warning',
        'active' => 'badge-success',
        'inactive' => 'badge-secondary'
    ];
    
    return $badges[$status] ?? 'badge-secondary';
}

function getPriorityBadge($priority) {
    $badges = [
        'low' => 'badge-success',
        'medium' => 'badge-warning',
        'high' => 'badge-danger',
        'urgent' => 'badge-dark'
    ];
    
    return $badges[$priority] ?? 'badge-secondary';
}

// Workshop list
function getWorkshops() {
    return ['Indode', 'Adama', 'Metahra', 'Mieso', 'Bike', 'Diredawa', 'Adigal', 'Dewanle', 'Nagad'];
}

// Role list
function getRoles() {
    return ['Workshop', 'Construction Office', 'Director', 'Dispatcher'];
}
?>