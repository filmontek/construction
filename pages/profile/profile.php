<?php
require_once '../../includes/functions.php';
requireLogin();

$pageTitle = 'My Profile';
$error = '';
$success = '';

// Get user data
$user = getUserById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if email is already taken by another user
        $existingUser = getUserByEmail($email);
        if ($existingUser && $existingUser['id'] != $_SESSION['user_id']) {
            $error = 'Email address is already taken by another user.';
        } else {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$name, $email, $_SESSION['user_id']])) {
                    // Update session data
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    $success = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $user = getUserById($_SESSION['user_id']);
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'An error occurred while updating your profile.';
            }
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">My Profile</h1>
                <p class="text-muted">Manage your account information</p>
            </div>
            <div>
                <a href="change_password.php" class="btn btn-outline-warning">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
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
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Get user's recent plans
                $recentPlans = getWorkPlans(['user_id' => $_SESSION['user_id']]);
                $recentPlans = array_slice($recentPlans, 0, 5);
                ?>
                
                <?php if (empty($recentPlans)): ?>
                    <p class="text-muted text-center py-3">No recent activity found.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentPlans as $plan): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?php echo htmlspecialchars($plan['construction_item']); ?></div>
                                    <small class="text-muted">
                                        Plan #<?php echo htmlspecialchars($plan['monthly_plan_number'] ?: $plan['id']); ?> • 
                                        <?php echo formatDate($plan['created_at']); ?>
                                    </small>
                                </div>
                                <span class="badge <?php echo getStatusBadge($plan['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="../plans/my_plans.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-2"></i>View All Plans
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Account Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-id-card me-2"></i>Account Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 24px;">
                        <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h5 mb-0 text-primary"><?php echo htmlspecialchars($user['role']); ?></div>
                            <small class="text-muted">Role</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h5 mb-0 text-secondary">
                            <?php echo $user['workshop'] ? htmlspecialchars($user['workshop']) : 'N/A'; ?>
                        </div>
                        <small class="text-muted">Workshop</small>
                    </div>
                </div>
                
                <hr>
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Account Status:</span>
                        <span class="badge <?php echo getStatusBadge($user['status']); ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Member Since:</span>
                        <span><?php echo formatDate($user['created_at']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <span><?php echo formatDate($user['updated_at']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <?php if ($_SESSION['user_role'] === 'Workshop'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>My Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $userStats = getDashboardStats($_SESSION['user_id'], $_SESSION['user_role']);
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0 text-primary"><?php echo $userStats['total_plans']; ?></div>
                            <small class="text-muted">Total Plans</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-0 text-warning"><?php echo $userStats['pending_plans']; ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="../plans/create.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Create New Plan
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>

<?php include '../../includes/footer.php'; ?>