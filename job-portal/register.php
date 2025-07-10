<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
$errors = [];
$username = $email = $full_name = $phone = $user_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $user_type = $_POST['user_type'];
    
    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($user_type)) {
        $errors['user_type'] = 'Please select user type';
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $errors['general'] = 'Username or email already exists';
        }
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $user_type, $full_name, $phone]);
            
            $_SESSION['success_message'] = 'Registration successful! Please login.';
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
$pageTitle = "Register";
require_once 'includes/header.php';
?>

<!-- Registration Form -->
<section class="registration py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Create Your Account</h2>
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                           id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                                           minlength="4" required>
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">4+ characters, letters, numbers and _ only</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" minlength="6" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" name="confirm_password" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($full_name); ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($phone); ?>">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">I am a *</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input <?php echo isset($errors['user_type']) ? 'is-invalid' : ''; ?>" 
                                                   type="radio" name="user_type" id="job_seeker" 
                                                   value="job_seeker" <?php echo $user_type === 'job_seeker' ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="job_seeker">
                                                Job Seeker
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input <?php echo isset($errors['user_type']) ? 'is-invalid' : ''; ?>" 
                                                   type="radio" name="user_type" id="employer" 
                                                   value="employer" <?php echo $user_type === 'employer' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="employer">
                                                Employer
                                            </label>
                                        </div>
                                    </div>
                                    <?php if (isset($errors['user_type'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo $errors['user_type']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> *
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100 py-2">Register Now</button>
                                </div>
                                
                                <div class="col-12 text-center">
                                    <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Acceptance of Terms</h6>
                <p>By accessing or using the Job Portal, you agree to comply with and be bound by these Terms and Conditions.</p>
                
                <h6>2. User Accounts</h6>
                <p>You must provide accurate and complete information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>
                
                <h6>3. Job Seeker Responsibilities</h6>
                <p>Job seekers must provide truthful information in their profiles and applications. Misrepresentation may result in account termination.</p>
                
                <h6>4. Employer Responsibilities</h6>
                <p>Employers must post accurate job descriptions and comply with all applicable employment laws. Discrimination of any kind is prohibited.</p>
                
                <h6>5. Privacy Policy</h6>
                <p>Your personal information will be handled in accordance with our Privacy Policy, which is incorporated into these Terms by reference.</p>
                
                <h6>6. Intellectual Property</h6>
                <p>All content on this platform is protected by copyright and other intellectual property laws.</p>
                
                <h6>7. Limitation of Liability</h6>
                <p>The Job Portal is not responsible for the accuracy of job postings or the hiring decisions of employers.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>