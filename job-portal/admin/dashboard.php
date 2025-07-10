<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Redirect non-admin users
if ($_SESSION['user_type'] !== 'employer') {
    header('Location: ../index.php');
    exit;
}

$pageTitle = "Admin Dashboard";
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Admin Dashboard</h1>
            <p class="lead">Welcome, Administrator!</p>
        </div>
    </div>

    <!-- Admin Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                    $user_count = $stmt->fetchColumn();
                    ?>
                    <h2 class="card-text"><?php echo $user_count; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Job Seekers</h5>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'job_seeker'");
                    $seeker_count = $stmt->fetchColumn();
                    ?>
                    <h2 class="card-text"><?php echo $seeker_count; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Employers</h5>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'employer'");
                    $employer_count = $stmt->fetchColumn();
                    ?>
                    <h2 class="card-text"><?php echo $employer_count; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Total Jobs</h5>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM jobs");
                    $job_count = $stmt->fetchColumn();
                    ?>
                    <h2 class="card-text"><?php echo $job_count; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Recent Users</h5>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                    $recent_users = $stmt->fetchAll();
                    ?>
                    
                    <div class="list-group">
                        <?php foreach ($recent_users as $user): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
                                    <small><?php echo date('M j', strtotime($user['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo ucfirst($user['user_type']); ?></p>
                                <small><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Recent Jobs</h5>
                    <?php
                    $stmt = $pdo->query("SELECT j.*, u.username 
                                       FROM jobs j 
                                       JOIN users u ON j.employer_id = u.id 
                                       ORDER BY j.posted_at DESC 
                                       LIMIT 5");
                    $recent_jobs = $stmt->fetchAll();
                    ?>
                    
                    <div class="list-group">
                        <?php foreach ($recent_jobs as $job): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($job['title']); ?></h6>
                                    <small><?php echo date('M j', strtotime($job['posted_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($job['company']); ?></p>
                                <small>Posted by: <?php echo htmlspecialchars($job['username']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>