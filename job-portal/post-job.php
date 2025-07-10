<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Redirect if not logged in as employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$jobData = [
    'title' => '',
    'description' => '',
    'requirements' => '',
    'benefits' => '',
    'about_company' => '',
    'company' => '',
    'location' => '',
    'salary' => '',
    'type' => 'Full-time',
    'category' => '',
    'deadline' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobData = array_map('trim', $_POST);
    $jobData['is_remote'] = isset($_POST['is_remote']) ? 1 : 0;
    
    // Validate inputs
    if (empty($jobData['title'])) {
        $errors['title'] = 'Job title is required';
    }
    
    if (empty($jobData['description'])) {
        $errors['description'] = 'Job description is required';
    }
    
    if (empty($jobData['requirements'])) {
        $errors['requirements'] = 'Requirements are required';
    }
    
    if (empty($jobData['company'])) {
        $errors['company'] = 'Company name is required';
    }
    
    if (empty($jobData['location']) && !$jobData['is_remote']) {
        $errors['location'] = 'Location is required unless job is fully remote';
    }
    
    if (empty($jobData['category'])) {
        $errors['category'] = 'Category is required';
    }
    
    // If no errors, save job
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jobs (
                employer_id, title, description, requirements, benefits, about_company, 
                company, location, salary, type, category, deadline, posted_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $jobData['title'],
                $jobData['description'],
                $jobData['requirements'],
                $jobData['benefits'],
                $jobData['about_company'],
                $jobData['company'],
                $jobData['location'],
                $jobData['salary'],
                $jobData['type'],
                $jobData['category'],
                $jobData['deadline'] ?: null,
            ]);
            
            $_SESSION['success_message'] = 'Job posted successfully!';
            header('Location: jobs.php');
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Error posting job: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Post a Job";
require_once 'includes/header.php';
?>

<!-- Job Posting Form -->
<section class="post-job py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Post a Job Opening</h2>
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" novalidate>
                            <!-- Basic Job Info -->
                            <div class="mb-4">
                                <h4 class="mb-3 border-bottom pb-2">Basic Information</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">Job Title *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                               id="title" name="title" value="<?php echo htmlspecialchars($jobData['title']); ?>">
                                        <?php if (isset($errors['title'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['title']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="company" class="form-label">Company Name *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['company']) ? 'is-invalid' : ''; ?>" 
                                               id="company" name="company" value="<?php echo htmlspecialchars($jobData['company']); ?>">
                                        <?php if (isset($errors['company'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['company']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Job Details -->
                            <div class="mb-4">
                                <h4 class="mb-3 border-bottom pb-2">Job Details</h4>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="description" class="form-label">Job Description *</label>
                                        <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                                  id="description" name="description" rows="4"><?php echo htmlspecialchars($jobData['description']); ?></textarea>
                                        <?php if (isset($errors['description'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="requirements" class="form-label">Requirements * (one per line)</label>
                                        <textarea class="form-control <?php echo isset($errors['requirements']) ? 'is-invalid' : ''; ?>" 
                                                  id="requirements" name="requirements" rows="4"><?php echo htmlspecialchars($jobData['requirements']); ?></textarea>
                                        <?php if (isset($errors['requirements'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['requirements']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="benefits" class="form-label">Benefits (one per line)</label>
                                        <textarea class="form-control" id="benefits" name="benefits" rows="4"><?php echo htmlspecialchars($jobData['benefits']); ?></textarea>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="about_company" class="form-label">About Your Company</label>
                                        <textarea class="form-control" id="about_company" name="about_company" rows="4"><?php echo htmlspecialchars($jobData['about_company']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Job Specifications -->
                            <div class="mb-4">
                                <h4 class="mb-3 border-bottom pb-2">Job Specifications</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="location" class="form-label">Location *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>" 
                                               id="location" name="location" value="<?php echo htmlspecialchars($jobData['location']); ?>">
                                        <?php if (isset($errors['location'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['location']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="salary" class="form-label">Salary (optional)</label>
                                        <input type="text" class="form-control" id="salary" name="salary" 
                                               value="<?php echo htmlspecialchars($jobData['salary']); ?>" placeholder="e.g., $50,000 - $70,000">
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label for="type" class="form-label">Job Type *</label>
                                        <select class="form-select" id="type" name="type">
                                            <option value="Full-time" <?php echo $jobData['type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                            <option value="Part-time" <?php echo $jobData['type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                            <option value="Contract" <?php echo $jobData['type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                            <option value="Internship" <?php echo $jobData['type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="category" class="form-label">Category *</label>
                                        <input type="text" class="form-control <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>" 
                                               id="category" name="category" value="<?php echo htmlspecialchars($jobData['category']); ?>" 
                                               placeholder="e.g., Software Development, Marketing">
                                        <?php if (isset($errors['category'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['category']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="deadline" class="form-label">Application Deadline (optional)</label>
                                        <input type="date" class="form-control" id="deadline" name="deadline" 
                                               value="<?php echo htmlspecialchars($jobData['deadline']); ?>">
                                    </div>
                                </div>
                             </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg py-3">Post Job</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>