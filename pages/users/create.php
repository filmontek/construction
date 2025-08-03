<?php
require_once '../../includes/functions.php';
requireLogin();
requireRole(['Director', 'Construction Office']);

$pageTitle = 'Create User';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    $workshop = sanitizeInput($_POST['workshop'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (getUserByEmail($email)) {
        $error = 'Email address is already registered.';
    } elseif ($role === 'Workshop' && empty($workshop)) {
        $error = 'Please select a workshop for workshop users.';
    } else {
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'workshop' => $role === 'Workshop' ? $workshop : null,
            'reason' => 'Created by administrator',
            'status' => 'active'
        ];
        
        if (createUser($userData)) {
            $success = 'User created successfully! They can now log in to the system.';
            // Clear form data
            $name = $email = $role = $workshop = '';
        } else {
            $error = 'Failed to create user. Please try again.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Create New User</h1>
                <p class="text-muted">Add a new user to the system with immediate access</p>
            </div>
            <div>
                <a href="active.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-users me-2"></i>Active Users
                </a>
                <a href="pending.php" class="btn btn-outline-warning">
                    <i class="fas fa-user-clock me-2"></i>Pending Requests
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>User Information
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
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            <div class="invalid-feedback">
                                Please provide the user's full name.
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
                                Please select a role for the user.
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
                                Please select a workshop.
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Users created through this form will have immediate access to the system. 
                        They will receive active status and can log in right away.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary me-md-2" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-primary w-100" onclick="fillSampleData('workshop')">
                            <i class="fas fa-industry me-2"></i>Sample Workshop User
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-info w-100" onclick="fillSampleData('office')">
                            <i class="fas fa-building me-2"></i>Sample Office User
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-success w-100" onclick="fillSampleData('director')">
                            <i class="fas fa-user-tie me-2"></i>Sample Director
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

function resetForm() {
    document.querySelector('form').reset();
    toggleWorkshopField();
    document.getElementById('passwordStrength').style.width = '0%';
    document.getElementById('passwordFeedback').textContent = 'Minimum 6 characters';
    document.getElementById('passwordMatchFeedback').textContent = '';
}

function fillSampleData(type) {
    const samples = {
        workshop: {
            name: 'John Workshop Manager',
            email: 'john.workshop@construction.com',
            role: 'Workshop',
            workshop: 'Indode'
        },
        office: {
            name: 'Jane Office Manager',
            email: 'jane.office@construction.com',
            role: 'Construction Office',
            workshop: ''
        },
        director: {
            name: 'Mike Director',
            email: 'mike.director@construction.com',
            role: 'Director',
            workshop: ''
        }
    };
    
    const sample = samples[type];
    if (sample) {
        document.getElementById('name').value = sample.name;
        document.getElementById('email').value = sample.email;
        document.getElementById('role').value = sample.role;
        document.getElementById('password').value = 'password123';
        document.getElementById('confirm_password').value = 'password123';
        
        toggleWorkshopField();
        
        if (sample.workshop) {
            document.getElementById('workshop').value = sample.workshop;
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleWorkshopField();
});
</script>

<?php include '../../includes/footer.php'; ?>