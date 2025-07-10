<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Get the job ID from the URL parameter
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch job details from the database
$job = null;
if ($job_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
}

// If job not found, redirect to homepage or show error
if (!$job) {
    header("Location: jobs.php");
    exit();
}

$pageTitle = $job['title'] . " - " . $job['company'];
require_once 'includes/header.php';
?>

<div class="container py-5">
    <!-- Back button -->
    <a href="jobs.php" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i>Back to Jobs
    </a>

    <!-- Job Header Section -->
    <div class="job-header bg-light p-4 rounded mb-4">
        <div class="row align-items-center">
            <div class="col-md-2 mb-3 mb-md-0 text-center">
                <img src="<?php echo htmlspecialchars($job['company_logo'] ?? 'assets/img/default-company.png'); ?>" 
                     alt="<?php echo htmlspecialchars($job['company']); ?> Logo" 
                     class="img-fluid rounded-circle" style="max-width: 100px;">
            </div>
            <div class="col-md-8">
                <h1 class="h3 mb-1"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="lead mb-2"><?php echo htmlspecialchars($job['company']); ?></p>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge bg-primary"><?php echo htmlspecialchars($job['type']); ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($job['category']); ?></span>
                    <span class="badge bg-info text-dark">
                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                    </span>
                    <?php if (!empty($job['is_remote']) && $job['is_remote']): ?>
                        <span class="badge bg-success">Remote</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-2 text-center text-md-end">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'job_seeker'): ?>
                    <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary btn-lg w-100 mb-2">
                        Apply Now
                    </a>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary btn-lg w-100 mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Login to Apply
                    </button>
                <?php endif; ?>
                <small class="text-muted">Posted: <?php echo date('M d, Y', strtotime($job['posted_at'])); ?></small>
            </div>
        </div>
    </div>

    <!-- Job Highlights -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-dollar-sign text-primary me-2"></i>Salary</h5>
                    <p class="card-text"><?php echo htmlspecialchars($job['salary'] ?? 'Negotiable'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-clock text-primary me-2"></i>Job Type</h5>
                    <p class="card-text"><?php echo htmlspecialchars($job['type']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-times text-primary me-2"></i>Deadline</h5>
                    <p class="card-text">
                        <?php echo !empty($job['deadline']) ? date('M d, Y', strtotime($job['deadline'])) : 'None'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Description Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 card-title"><i class="fas fa-file-alt text-primary me-2"></i>Job Description</h2>
            <div class="card-text">
                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 card-title"><i class="fas fa-check-circle text-primary me-2"></i>Requirements</h2>
            <ul class="card-text">
                <?php
                $requirements = explode("\n", $job['requirements']);
                foreach ($requirements as $item) {
                    if (!empty(trim($item))) {
                        echo '<li>' . htmlspecialchars(trim($item)) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- About Company Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 card-title"><i class="fas fa-building text-primary me-2"></i>About <?php echo htmlspecialchars($job['company']); ?></h2>
            <div class="card-text">
                <?php echo nl2br(htmlspecialchars($job['about_company'] ?? 'Information not available')); ?>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="text-center my-5">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'job_seeker'): ?>
            <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary btn-lg me-3">
                Apply Now <i class="fas fa-paper-plane ms-2"></i>
            </a>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <button class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                Login to Apply <i class="fas fa-sign-in-alt ms-2"></i>
            </button>
        <?php endif; ?>
        <a href="jobs.php" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-briefcase me-2"></i>Browse More Jobs
        </a>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You need to login as a job seeker to apply for jobs.</p>
                <div class="d-flex gap-2">
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-outline-primary">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>