<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Redirect if not logged in as job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header('Location: login.php');
    exit;
}

// Check if job ID is provided
if (!isset($_GET['job_id'])) {
    header('Location: jobs.php');
    exit;
}

$jobId = $_GET['job_id'];

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: jobs.php');
    exit;
}

$pageTitle = "Apply for " . $job['title'];
require_once 'includes/header.php';

$errors = [];
$coverLetter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coverLetter = trim($_POST['cover_letter']);
    
    // Validate resume file
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $errors['resume'] = 'Resume file is required';
    } else {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $_FILES['resume']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['resume'] = 'Only PDF and Word documents are allowed';
        }
        
        if ($_FILES['resume']['size'] > 5 * 1024 * 1024) { // 5MB
            $errors['resume'] = 'File size must be less than 5MB';
        }
    }
    
    // If no errors, process application
    if (empty($errors)) {
        // Create uploads directory if it doesn't exist
        if (!is_dir('../uploads')) {
            mkdir('../uploads', 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
        $filename = 'resume_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
        $destination = '../uploads/' . $filename;
        
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $destination)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO applications (job_id, user_id, resume_path, cover_letter) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $jobId,
                    $_SESSION['user_id'],
                    $filename,
                    $coverLetter
                ]);
                
                $_SESSION['success_message'] = 'Application submitted successfully!';
                header('Location: ../pages/jobs.php');
                exit;
            } catch (PDOException $e) {
                $errors['general'] = 'Error submitting application: ' . $e->getMessage();
            }
        } else {
            $errors['general'] = 'Error uploading resume';
        }
    }
}
?>

<!-- Application Form -->
<section class="apply-job py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Apply for <?php echo htmlspecialchars($job['title']); ?></h2>
                        <p class="text-center mb-4">at <?php echo htmlspecialchars($job['company']); ?></p>
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data" novalidate>
                            <div class="mb-3">
                                <label for="resume" class="form-label">Resume (PDF or Word) *</label>
                                <input type="file" class="form-control <?php echo isset($errors['resume']) ? 'is-invalid' : ''; ?>" 
                                       id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <?php if (isset($errors['resume'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['resume']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Max file size: 5MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cover_letter" class="form-label">Cover Letter (optional)</label>
                                <textarea class="form-control" id="cover_letter" name="cover_letter" rows="5"><?php echo htmlspecialchars($coverLetter); ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
