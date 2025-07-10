<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "Contact Us";
require_once 'includes/header.php';

$errors = [];
$success = false;
$name = $email = $subject = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name cannot exceed 100 characters';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email cannot exceed 100 characters';
    }
    
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    } elseif (strlen($subject) > 200) {
        $errors['subject'] = 'Subject cannot exceed 200 characters';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }
    
    // If no errors, store in database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $success = true;
            // Clear form fields
            $name = $email = $subject = $message = '';
            
            // Send email notification (optional)
            // $to = "admin@yourdomain.com";
            // $emailSubject = "New Contact Message: $subject";
            // $emailBody = "You have received a new message from $name ($email):\n\n$message";
            // mail($to, $emailSubject, $emailBody);
            
        } catch (PDOException $e) {
            $errors['general'] = 'Error submitting message. Please try again later.';
            error_log("Database error: " . $e->getMessage());
        }
    }
}
?>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1>Contact Us</h1>
                <p class="lead">We'd love to hear from you</p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <div class="alert alert-success">
                        Thank you for your message! We'll get back to you soon.
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2>Get in Touch</h2>
                <p>Have questions about our job portal or need assistance? Fill out the form and our team will get back to you as soon as possible.</p>
                
                <div class="contact-info mt-4">
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5>Address</h5>
                            <p>IoT Hawassa University, Hawassa City, Sidama, Ethiopia</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h5>Phone</h5>
                            <p>+251943091493</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5>Email</h5>
                            <p>info@jobportal.com</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <h2>Send Us a Message</h2>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name *</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Your Email *</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                               id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
                        <?php if (isset($errors['subject'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['subject']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message *</label>
                        <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                  id="message" name="message" rows="5"><?php echo htmlspecialchars($message); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['message']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>