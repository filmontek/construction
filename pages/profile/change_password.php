<?php
require_once '../../includes/functions.php';
requireLogin();

$pageTitle = 'Change Password';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get user data
    $user = getUserById($_SESSION['user_id']);
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (!verifyPassword($current_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif ($current_password === $new_password) {
        $error = 'New password must be different from current password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([hashPassword($new_password), $_SESSION['user_id']])) {
                $success = 'Password changed successfully!';
                
                // Log the action
                $stmt = $pdo->prepare("
                    INSERT INTO system_logs (user_id, action, table_name, record_id, ip_address, created_at) 
                    VALUES (?, 'Password Changed', 'users', ?, ?, NOW())
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_SESSION['user_id'],
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            } else {
                $error = 'Failed to change password. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred while changing your password.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Change Password</h1>
                <p class="text-muted">Update your account password for security</p>
            </div>
            <div>
                <a href="profile.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>Password Security
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
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('current_password', 'currentPasswordIcon')">
                                <i class="fas fa-eye" id="currentPasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Please enter your current password.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('new_password', 'newPasswordIcon')">
                                <i class="fas fa-eye" id="newPasswordIcon"></i>
                            </button>
                        </div>
                        <div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                        </div>
                        <small id="passwordFeedback" class="text-muted">Minimum 6 characters, different from current password</small>
                        <div class="invalid-feedback">
                            Please enter a new password (minimum 6 characters).
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('confirm_password', 'confirmPasswordIcon')">
                                <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                            </button>
                        </div>
                        <small id="passwordMatchFeedback" class="text-muted"></small>
                        <div class="invalid-feedback">
                            Please confirm your new password.
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Password Security Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Use at least 8 characters with a mix of letters, numbers, and symbols
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Avoid using personal information like names or birthdays
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Don't reuse passwords from other accounts
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Change your password regularly (every 3-6 months)
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success me-2"></i>
                        Never share your password with others
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced password validation
document.getElementById('new_password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    validatePasswordMatch();
});

document.getElementById('confirm_password').addEventListener('input', function() {
    validatePasswordMatch();
});

function validatePasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const feedback = document.getElementById('passwordMatchFeedback');
    
    if (confirmPassword && newPassword !== confirmPassword) {
        feedback.textContent = 'Passwords do not match';
        feedback.className = 'text-danger small';
        return false;
    } else if (confirmPassword && newPassword === confirmPassword) {
        feedback.textContent = 'Passwords match';
        feedback.className = 'text-success small';
        return true;
    } else {
        feedback.textContent = '';
        return true;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>