<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "About Us";
require_once 'includes/header.php';
?>

<!-- About Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1>About Our Job Portal</h1>
                <p class="lead">Connecting talent with opportunity since 2025</p>
            </div>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2>Our Story</h2>
                <p>Founded in 2025, our job portal was created to bridge the gap between talented professionals and companies looking for their skills. We recognized that traditional job search methods were inefficient and often frustrating for both job seekers and employers.</p>
                <p>Our platform was designed to simplify the process, making it easier for candidates to find their dream jobs and for companies to discover top talent.</p>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/About-story.jpg" alt="Our Story" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 order-lg-2">
                <h2>Our Mission</h2>
                <p>To empower individuals in their career journeys by providing access to the best job opportunities and career resources. We believe everyone deserves to find fulfilling work that aligns with their skills and passions.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Simplify the job search process</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Provide valuable career resources</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Connect talent with great companies</li>
                </ul>
            </div>
            <div class="col-lg-6 order-lg-1">
                <img src="assets/images/about-mission.jpeg" alt="Our Mission" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card bg-light p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3>Ready to find your dream job?</h3>
                            <p class="mb-0">Join thousands of professionals who have already found success through our platform.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- About Us Page Content -->
<section class="py-5 bg-light">
    <div class="container">
        <h1 class="text-center mb-5">About Our Company</h1>
        
        <!-- Meet the Team Section -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">👥 Meet Our Team</h2>
                <div class="row g-4">
                    <?php
                    // Fetch team members from database (only Employees)
                    try {
                        $teamMembers = $pdo->query("
                            SELECT * FROM users 
                            WHERE user_type = 'Employee'
                            ORDER BY created_at DESC
                            LIMIT 6
                        ")->fetchAll();
                    } catch (PDOException $e) {
                        // If there's an error, use fallback data and log the error
                        error_log("Database error: " . $e->getMessage());
                        $teamMembers = [];
                    }

                    // Fallback data if no team members found
                    if (empty($teamMembers)) {
                        $teamMembers = [
                            ['name' => 'John Doe', 'user_type' => 'Employee', 'avatar' => 'default1.jpg', 'social_links' => '{}', 'position' => 'CEO'],
                            ['name' => 'Jane Smith', 'user_type' => 'Employee', 'avatar' => 'default2.jpg', 'social_links' => '{}', 'position' => 'CTO'],
                            ['name' => 'Mike Johnson', 'user_type' => 'Employee', 'avatar' => 'default3.jpg', 'social_links' => '{}', 'position' => 'Lead Developer']
                        ];
                    }

                    foreach ($teamMembers as $member):
                        $socialLinks = json_decode($member['social_links'] ?? '{}', true);
                        // Use position if available, otherwise default based on user_type
                        $position = $member['position'] ?? (($member['user_type'] === 'Employee') ? 'Team Member' : 'Job Seeker');
                    ?>
                    <div class="col-md-4 col-lg-2">
                        <div class="card team-card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="team-img-container">
                                <img src="assets/images/team/<?= htmlspecialchars($member['avatar'] ?? 'default.jpg') ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($member['name']) ?>">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($member['name']) ?></h5>
                                <p class="text-muted small mb-3"><?= htmlspecialchars($position) ?></p>
                                <div class="social-links">
                                    <?php if (!empty($socialLinks['linkedin'])): ?>
                                        <a href="<?= $socialLinks['linkedin'] ?>" class="text-primary mx-1"><i class="fab fa-linkedin-in"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socialLinks['twitter'])): ?>
                                        <a href="<?= $socialLinks['twitter'] ?>" class="text-info mx-1"><i class="fab fa-twitter"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socialLinks['github'])): ?>
                                        <a href="<?= $socialLinks['github'] ?>" class="text-dark mx-1"><i class="fab fa-github"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Social Media Feed -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">📱 Social Media Updates</h2>
                <div class="social-feed-carousel owl-carousel owl-theme">
                    <!-- Twitter Feed Card -->
                    <div class="item">
                        <div class="card social-card twitter-card border-0 shadow-sm h-100">
                            <div class="card-header bg-info text-white d-flex align-items-center">
                                <i class="fab fa-twitter fs-4 me-2"></i>
                                <span>Latest Tweets</span>
                            </div>
                            <div class="card-body">
                                <div class="tweet">
                                    <p>Just launched new job matching algorithm! Check it out #careers #hiring</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <hr>
                                <div class="tweet">
                                    <p>We're hiring! Looking for PHP developers with Laravel experience</p>
                                    <small class="text-muted">1 day ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instagram Feed Card -->
                    <div class="item">
                        <div class="card social-card instagram-card border-0 shadow-sm h-100">
                            <div class="card-header bg-instagram text-white d-flex align-items-center">
                                <i class="fab fa-instagram fs-4 me-2"></i>
                                <span>Instagram</span>
                            </div>
                            <div class="card-body p-0">
                                <img src="assets/images/social/office-team.jpg" class="img-fluid w-100" alt="Office team">
                                <div class="p-3">
                                    <p>Behind the scenes at our weekly team meeting!</p>
                                    <small class="text-muted">3 days ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- LinkedIn Feed Card -->
                    <div class="item">
                        <div class="card social-card linkedin-card border-0 shadow-sm h-100">
                            <div class="card-header bg-linkedin text-white d-flex align-items-center">
                                <i class="fab fa-linkedin-in fs-4 me-2"></i>
                                <span>LinkedIn</span>
                            </div>
                            <div class="card-body">
                                <p>We're proud to announce our partnership with TechHire initiative!</p>
                                <small class="text-muted">1 week ago</small>
                                <hr>
                                <p>Check out our CEO's latest article on remote work trends</p>
                                <small class="text-muted">2 weeks ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Behind the Scenes -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">🎬 Behind the Scenes</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow">
<iframe width="560" height="315" src="https://www.youtube.com/embed/DvEh04LNJ_I?si=TDneX9BTOQS6OvBO" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>                                 
    title="Our Team at Work
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="gallery-grid">
                            <div class="gallery-item">
                                <img src="assets/images/office/office1.jpeg" class="img-fluid rounded" alt="Office space">
                            </div>
                            <div class="gallery-item">
                                <img src="assets/images/office/office2.jpeg" class="img-fluid rounded" alt="Team meeting">
                            </div>
                            <div class="gallery-item">
                                <img src="assets/images/office/office3.jpeg" class="img-fluid rounded" alt="Workspace">
                            </div>
                            <div class="gallery-item">
                                <img src="assets/images/office/office4.jpeg" class="img-fluid rounded" alt="Company event">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Map -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">📍 Our Location</h2>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="map-container h-100">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.4616457526968!2d38.86876887361195!3d9.021582889107899!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b9082bc707d0f%3A0xdd1ed73261ab996a!2sAyat%20Adebabay%20Station!5e0!3m2!1sen!2set!4v1750833908223!5m2!1sen!2set" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                                       
                            </div>
                        </div>
                        <div class="col-md-6 bg-dark text-white p-4">
                            <h3>Visit Our Office</h3>
                            <p><i class="fas fa-map-marker-alt me-2"></i> 123 Job Street, Tech City, TC 10001</p>
                            <p><i class="fas fa-phone me-2"></i> (123) 456-7890</p>
                            <p><i class="fas fa-envelope me-2"></i> info@jobportal.com</p>
                            <hr class="bg-light">
                            <h4>Working Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 10:00 AM - 4:00 PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Chat Support -->
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">🛎️ We're Here to Help</h2>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-6 bg-primary text-white p-5">
                            <h3 class="text-white">Contact Support</h3>
                            <p>Our team is ready to assist you with any questions</p>
                            <div class="support-methods mt-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="support-icon bg-white text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-comment-dots fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Live Chat</h5>
                                        <small>Available 24/7</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="support-icon bg-white text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-envelope fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Email Us</h5>
                                        <small>support@jobportal.com</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="support-icon bg-white text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-phone fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Call Us</h5>
                                        <small>+1 (123) 456-7890</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 p-5">
                            <h3>Quick Contact Form</h3>
                            <form>
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Your Name">
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Email Address">
                                </div>
                                <div class="mb-3">
                                    <select class="form-select">
                                        <option>Select Department</option>
                                        <option>Technical Support</option>
                                        <option>Account Help</option>
                                        <option>General Inquiry</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Your Message"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styles -->
<style>
    /* Team Cards */
    .team-card {
        transition: transform 0.3s ease;
    }
    .team-card:hover {
        transform: translateY(-5px);
    }
    .team-img-container {
        height: 200px;
        overflow: hidden;
    }
    .team-img-container img {
        object-fit: cover;
        height: 100%;
        width: 100%;
    }
    
    /* Social Media Cards */
    .social-card {
        min-height: 300px;
    }
    .twitter-card .card-header {
        background: #1DA1F2 !important;
    }
    .instagram-card .card-header {
        background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D) !important;
    }
    .linkedin-card .card-header {
        background: #0077B5 !important;
    }
    
    /* Gallery Grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        height: 100%;
    }
    .gallery-item {
        height: 150px;
        overflow: hidden;
        border-radius: 8px !important;
    }
    .gallery-item img {
        object-fit: cover;
        height: 100%;
        width: 100%;
    }
    
    /* Map Container */
    .map-container {
        min-height: 300px;
    }
    
    /* Support Icons */
    .support-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<!-- JavaScript for Carousel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
$(document).ready(function(){
    $('.social-feed-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            992: { items: 3 }
        }
    });
    
    // Initialize live chat widget
    $('.live-chat-btn').click(function() {
        $('#chatModal').modal('show');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>