<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL or session
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

// Fetch user data with prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if user exists
if (!$user) {
    $_SESSION['error_message'] = 'User not found';
    header('Location: index.php');
    exit;
}

// Check if viewing own profile
$is_own_profile = ($user_id == $_SESSION['user_id']);

// Determine profile photo path
$profile_photo = !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : BASE_URL . '/assets/images/profile-placeholder.png';

$pageTitle = $user['full_name'] ? htmlspecialchars($user['full_name']) . "'s Profile" : 'User Profile';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <!-- Updated profile photo display -->
                    <img src="<?php echo $profile_photo; ?>" 
                         alt="Profile Photo" 
                         class="rounded-circle mb-3" 
                         width="150" 
                         height="150"
                         style="object-fit: cover;">
                    
                    <h3><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h3>
                    <p class="text-muted"><?php echo ucfirst($user['user_type']); ?></p>
                    
                    <?php if ($is_own_profile): ?>
                        <div class="d-grid gap-2 mb-4">
                            <a href="<?php echo BASE_URL; ?>/edit-profile.php" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if ($user['phone']): ?>
                            <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <?php endif; ?>
                        <p><i class="fas fa-calendar-alt me-2"></i> Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About</button>
                        </li>
                        <?php if ($user['user_type'] == 'job_seeker'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab">Resume</button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Activity</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content py-4" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="about" role="tabpanel">
                            <h4>About Me</h4>
                            <div class="mb-4">
                                <?php echo $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : '<p class="text-muted">No bio added yet.</p>'; ?>
                            </div>
                            
                            <?php if ($user['user_type'] == 'job_seeker'): ?>
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <h5>Skills</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if ($user['skills']): ?>
                                            <?php 
                                            $skills = array_filter(array_map('trim', explode(',', $user['skills'])));
                                            foreach ($skills as $skill): 
                                                if (!empty($skill)):
                                            ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($skill); ?></span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        <?php else: ?>
                                            <p class="text-muted">No skills added yet</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <h5>Education</h5>
                                    <?php if ($user['education']): ?>
                                        <p><?php echo htmlspecialchars($user['education']); ?></p>
                                    <?php else: ?>
                                        <p class="text-muted">No education information added</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($user['user_type'] == 'job_seeker'): ?>
                        <div class="tab-pane fade" id="resume" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4>My Resume</h4>
                                <?php if ($is_own_profile): ?>
                                    <a href="<?php echo BASE_URL; ?>/upload-resume.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-upload me-1"></i> Upload New
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php
                            // Fetch resume if exists
                            $resume_stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ?");
                            $resume_stmt->execute([$user_id]);
                            $resume = $resume_stmt->fetch();
                            ?>
                            
                            <?php if ($resume): ?>
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-1">Resume last updated: <?php echo date('F j, Y', strtotime($resume['updated_at'])); ?></p>
                                            <p class="mb-0">File: <?php echo htmlspecialchars($resume['file_name']); ?></p>
                                        </div>
                                        <a href="<?php echo BASE_URL; ?>/uploads/resumes/<?php echo $resume['file_name']; ?>" 
                                           class="btn btn-primary" 
                                           target="_blank"
                                           download>
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <p class="mb-3">No resume uploaded yet</p>
                                    <?php if ($is_own_profile): ?>
                                        <a href="<?php echo BASE_URL; ?>/upload-resume.php" class="btn btn-outline-primary">
                                            <i class="fas fa-upload me-1"></i> Upload Resume
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <h4 class="mb-4">Recent Activity</h4>
                            <?php if ($user['user_type'] == 'job_seeker'): ?>
                                <?php
                                $apps_stmt = $pdo->prepare("SELECT a.*, j.title, j.company 
                                                           FROM applications a 
                                                           JOIN jobs j ON a.job_id = j.id 
                                                           WHERE a.user_id = ? 
                                                           ORDER BY a.applied_at DESC 
                                                           LIMIT 5");
                                $apps_stmt->execute([$user_id]);
                                $applications = $apps_stmt->fetchAll();
                                ?>
                                
                                <?php if ($applications): ?>
                                    <div class="list-group">
                                        <?php foreach ($applications as $app): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($app['title']); ?></h5>
                                                    <small><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></small>
                                                </div>
                                                <p class="mb-1"><?php echo htmlspecialchars($app['company']); ?></p>
                                                <small>Status: <span class="badge bg-<?php 
                                                    echo $app['status'] == 'Accepted' ? 'success' : 
                                                         ($app['status'] == 'Rejected' ? 'danger' : 'warning'); 
                                                ?>"><?php echo $app['status']; ?></span></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0">No recent applications found</p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Employer activity would go here -->
                                <div class="alert alert-info">
                                    <p class="mb-0">Recent job postings and applicant activity would appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>