<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "Browse Jobs";
require_once 'includes/header.php';

// Get search parameters
$search = isset($_GET['q']) ? $_GET['q'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Build SQL query
$sql = "SELECT * FROM jobs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE :search OR company LIKE :search OR description LIKE :search OR requirements LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($location)) {
    $sql .= " AND location LIKE :location";
    $params[':location'] = "%$location%";
}

if (!empty($category)) {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

if (!empty($type)) {
    $sql .= " AND type = :type";
    $params[':type'] = $type;
}

$sql .= " ORDER BY posted_at DESC";

// Prepare and execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM jobs")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Job Search Filters -->
<section class="job-filters py-4 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="q" class="form-label">Keywords</label>
                        <input type="text" class="form-control" id="q" name="q" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Job title, company, or keywords">
                    </div>
                    <div class="col-md-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?php echo htmlspecialchars($location); ?>" placeholder="City, state">
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Job Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="Full-time" <?php echo $type == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo $type == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo $type == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="Internship" <?php echo $type == 'Internship' ? 'selected' : ''; ?>>Internship</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Job Listings -->
<section class="job-listings py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Available Jobs</h2>
                <div class="text-muted"><?php echo count($jobs); ?> jobs found</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <?php if (empty($jobs)): ?>
                    <div class="alert alert-info">
                        No jobs found matching your criteria. Try adjusting your filters.
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="card mb-4 shadow-sm job-listing">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($job['type']); ?></span>
                                    <span class="text-muted small">Posted: <?php echo date('M d, Y', strtotime($job['posted_at'])); ?></span>
                                </div>
                                <h3 class="h4"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($job['company']); ?></p>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                                    <?php if (!empty($job['salary'])): ?>
                                        <span class="ms-3"><i class="fas fa-dollar-sign me-1"></i> <?php echo htmlspecialchars($job['salary']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-4"><?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 200))); ?>...</p>
                                
                                <!-- Added Requirements Preview -->
                                <?php if (!empty($job['requirements'])): ?>
                                    <div class="mb-3">
                                        <h5 class="h6">Key Requirements:</h5>
                                        <ul class="mb-0">
                                            <?php 
                                            $reqs = explode("\n", $job['requirements']);
                                            foreach (array_slice($reqs, 0, 3) as $req): 
                                                if (!empty(trim($req))): ?>
                                                    <li><?php echo htmlspecialchars(trim($req)); ?></li>
                                                <?php endif;
                                            endforeach; 
                                            ?>
                                            <?php if (count($reqs) > 3): ?>
                                                <li>...and <?php echo count($reqs) - 3; ?> more</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($job['category']); ?></span>
                                    <div class="btn-group">
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'job_seeker'): ?>
                                            <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary">Apply Now</a>
                                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login to Apply</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Job Alerts</h4>
                        <p>Get notified when new jobs match your criteria.</p>
                        <form id="jobAlertForm">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email" required>
                            </div>
                            <div class="mb-3">
                                <select class="form-select" id="alertCategory">
                                    <option value="">Any Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Job Categories</h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($categories as $cat): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="jobs.php?category=<?php echo urlencode($cat); ?>" class="text-decoration-none"><?php echo htmlspecialchars($cat); ?></a>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php 
                                            $count = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE category = ?");
                                            $count->execute([$cat]);
                                            echo $count->fetchColumn();
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<script>
// AJAX Search Functionality
$(document).ready(function() {
    // Live search on filter change
    $('select#category, select#type').change(function() {
        $('form').submit();
    });
    
    // Job Alert Form Submission
    $('#jobAlertForm').submit(function(e) {
        e.preventDefault();
        const email = $(this).find('input[type="email"]').val();
        const category = $('#alertCategory').val();
        
        $.ajax({
            url: 'api/save_alert.php',
            method: 'POST',
            data: { email, category },
            success: function() {
                alert('Job alert subscription saved!');
                $('#jobAlertForm')[0].reset();
            },
            error: function() {
                alert('Error saving alert. Please try again.');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>