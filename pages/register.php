<?php
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    $workshop = sanitizeInput($_POST['workshop'] ?? '');
    $reason = sanitizeInput($_POST['reason']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role) || empty($reason)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (getUserByEmail($email)) {
        $error = 'Email address is already registered.';
    } elseif ($role === 'Workshop' && empty($workshop)) {
        $error = 'Please select a workshop.';
    } else {
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'workshop' => $role === 'Workshop' ? $workshop : null,
            'reason' => $reason,
            'status' => 'pending'
        ];
        
        if (createUser($userData)) {
            $success = 'Registration successful! Your account is pending approval. You will be notified once approved.';
            // Clear form data
            $name = $email = $role = $workshop = $reason = '';
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Construction Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <h3>Create Account</h3>
                <p class="mb-0">Register for Construction Management System</p>
            </div>
            
            <div class="auth-body">
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
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('password', 'passwordIcon')">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                            </div>
                            <small id="passwordFeedback" class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('confirm_password', 'confirmPasswordIcon')">
                                    <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                            <small id="passwordMatchFeedback" class="text-muted"></small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required onchange="toggleWorkshopField()">
                                <option value="">Select Role</option>
                                <?php foreach (getRoles() as $roleOption): ?>
                                    <option value="<?php echo $roleOption; ?>" 
                                            <?php echo ($role ?? '') === $roleOption ? 'selected' : ''; ?>>
                                        <?php echo $roleOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select your role.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="workshopField" style="display: none;">
                            <label for="workshop" class="form-label">Workshop *</label>
                            <select class="form-select" id="workshop" name="workshop">
                                <option value="">Select Workshop</option>
                                <?php foreach (getWorkshops() as $workshopOption): ?>
                                    <option value="<?php echo $workshopOption; ?>" 
                                            <?php echo ($workshop ?? '') === $workshopOption ? 'selected' : ''; ?>>
                                        <?php echo $workshopOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please select your workshop.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Access *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="Please explain why you need access to this system..." required><?php echo htmlspecialchars($reason ?? ''); ?></textarea>
                        <div class="invalid-feedback">
                            Please provide a reason for requesting access.
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>
                            Register
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p class="text-muted">Already have an account?</p>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function toggleWorkshopField() {
            var role = document.getElementById('role').value;
            var workshopField = document.getElementById('workshopField');
            var workshopSelect = document.getElementById('workshop');
            
            if (role === 'Workshop') {
                workshopField.style.display = 'block';
                workshopSelect.required = true;
            } else {
                workshopField.style.display = 'none';
                workshopSelect.required = false;
                workshopSelect.value = '';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleWorkshopField();
        });
    </script>
</body>
</html>