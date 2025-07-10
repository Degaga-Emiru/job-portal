<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $education = trim($_POST['education']);

    // Validate inputs
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    }

    // Handle file upload
    $profile_photo = $user['profile_photo']; // Keep existing photo by default
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors['profile_photo'] = 'Only JPG, PNG, and GIF images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors['profile_photo'] = 'Image size must be less than 2MB.';
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/profile_photos/' . $filename;
            
            // Create directory if it doesn't exist
            if (!file_exists('uploads/profile_photos')) {
                mkdir('uploads/profile_photos', 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old photo if it exists
                if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
                    unlink($user['profile_photo']);
                }
                $profile_photo = $upload_path;
            } else {
                $errors['profile_photo'] = 'Failed to upload image.';
            }
        }
    } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['profile_photo'] = 'Error uploading file.';
    }

    // If no errors, update profile
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET 
                                 full_name = ?, 
                                 phone = ?, 
                                 bio = ?, 
                                 skills = ?, 
                                 education = ?,
                                 profile_photo = ?
                                 WHERE id = ?");
            $stmt->execute([$full_name, $phone, $bio, $skills, $education, $profile_photo, $user_id]);
            
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: profile.php');
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Error updating profile: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Edit Profile";
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Edit Profile</h2>
                    
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <!-- Profile Photo Upload -->
                        <div class="mb-4 text-center">
                            <div class="mb-3">
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                                         class="rounded-circle" 
                                         width="150" 
                                         height="150" 
                                         alt="Profile Photo"
                                         style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" 
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-3x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Change Profile Photo</label>
                                <input type="file" class="form-control <?php echo isset($errors['profile_photo']) ? 'is-invalid' : ''; ?>" 
                                       id="profile_photo" name="profile_photo" accept="image/*">
                                <?php if (isset($errors['profile_photo'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['profile_photo']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Max size 2MB (JPG, PNG, GIF)</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                   id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <div class="form-text">Tell us about yourself</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" name="skills" 
                                   value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>">
                            <div class="form-text">Separate skills with commas (e.g., PHP, JavaScript, MySQL)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <input type="text" class="form-control" id="education" name="education" 
                                   value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>">
                            <div class="form-text">Your highest education level or degree</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>