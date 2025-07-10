<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "Find Your Dream Job Today";
require_once 'includes/header.php';

// Fetch featured jobs
$stmt = $pdo->query("SELECT * FROM jobs ORDER BY posted_at DESC LIMIT 6");
$featuredJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
try {
    $companies = $pdo->query("SELECT * FROM companies LIMIT 8")->fetchAll();
} catch (PDOException $e) {
    $companies = []; // Fallback to empty array if query fails
    error_log("Companies query error: " . $e->getMessage());
}

// If no companies found, use placeholder data
if (empty($companies)) {
    $companies = [
        ['name' => 'Google', 'logo' => 'assets/images/default-company.png'],
        ['name' => 'Microsoft', 'logo' => 'assets/images/default-company.png'],
        ['name' => 'Apple', 'logo' => 'assets/images/default-company.png'],
        ['name' => 'Amazon', 'logo' => 'assets/images/default-company.png']
    ];
}
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/bg1.jpg'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Find Your Dream Job Today</h1>
                <p class="lead mb-4">Join thousands of professionals who found their perfect career match through our platform. Fast, reliable, and trusted by top companies worldwide.</p>
                <div class="d-flex gap-2">
                    <a href="register.php" class="btn btn-light btn-lg px-4">Get Started</a>
                    <a href="jobs.php" class="btn btn-outline-light btn-lg px-4">Browse Jobs</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="assets/images/heroimage.jpg" alt="Job Search Illustration" class="img-fluid" style="width:100%; height:500px; object-fit:cover;">
            </div>
        </div>
    </div>
</section>

<!-- Job Search Section -->
<section class="job-search py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Search Thousands of Jobs</h2>
                        <form id="searchForm" action="jobs.php" method="get">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                                        <input type="text" name="q" id="searchInput" class="form-control border-start-0" 
                                               placeholder="Job title, keywords, or company"
                                               value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                        <input type="text" name="location" id="locationInput" class="form-control border-start-0" 
                                               placeholder="City, state"
                                               value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="includeExternal" name="external" value="1" <?= isset($_GET['external']) ? 'checked' : 'checked' ?>>
                                <label class="form-check-label" for="includeExternal">Include external job listings</label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced AJAX Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const includeExternal = document.getElementById('includeExternal');
    
    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(searchForm);
        const searchParams = new URLSearchParams(formData);
        
        // Redirect to jobs.php with search parameters
        window.location.href = `jobs.php?${searchParams.toString()}`;
        
        // Alternatively, for AJAX implementation:
        // performSearch(formData);
    });

    // Optional: Real-time search suggestions
    const searchInput = document.getElementById('searchInput');
    const locationInput = document.getElementById('locationInput');
    
    searchInput.addEventListener('input', debounce(function() {
        fetchSuggestions();
    }, 300));
    
    locationInput.addEventListener('input', debounce(function() {
        fetchSuggestions();
    }, 300));

    function fetchSuggestions() {
        // Implement search suggestion API call here
    }

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }
});
</script>

<!-- Search Results Section -->
<section class="search-results py-5" id="searchResults">
    <div class="container">
        <div id="resultsContainer"></div>
        <div id="loadingIndicator" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Searching jobs...</p>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // API configuration
    const API_CONFIG = {
        adzuna: {
            url: 'https://api.adzuna.com/v1/api/jobs/us/search/1',
            params: {
                app_id: '2c77633a',
                app_key: '08efd7d273d321eea12df26bc9129964	',
                results_per_page: 10
            }
        },
        reed: {
            url: 'https://www.reed.co.uk/api/1.0/search',
            params: {
                resultsToTake: 10
            }
        }
    };

    // Search form handler
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        performSearch();
    });

    // Perform search (local + external)
    async function performSearch() {
        const query = $('#searchInput').val();
        const location = $('#locationInput').val();
        const includeExternal = $('#includeExternal').is(':checked');
        
        $('#loadingIndicator').show();
        $('#resultsContainer').html('');
        
        try {
            // 1. Search local jobs
            const localJobs = await searchLocalJobs(query, location);
            
            // 2. Search external APIs if enabled
            let externalJobs = [];
            if (includeExternal && query) {
                externalJobs = await searchExternalJobs(query, location);
            }
            
            // 3. Combine and display results
            displayResults([...localJobs, ...externalJobs]);
            
        } catch (error) {
            console.error('Search error:', error);
            $('#resultsContainer').html(`
                <div class="alert alert-danger">
                    Error loading search results: ${error.message}
                </div>
            `);
        } finally {
            $('#loadingIndicator').hide();
        }
    }

    // Search local database
    async function searchLocalJobs(query, location) {
        const response = await $.ajax({
            url: 'api/search_local.php',
            method: 'GET',
            data: { q: query, location }
        });
        return response.jobs || [];
    }

    // Search external APIs
    async function searchExternalJobs(query, location) {
        try {
            // Adzuna API
            const adzunaParams = new URLSearchParams({
                ...API_CONFIG.adzuna.params,
                what: query,
                where: location
            });
            
            const adzunaResponse = await fetch(`${API_CONFIG.adzuna.url}?${adzunaParams}`);
            const adzunaData = await adzunaResponse.json();
            
            // Process Adzuna results
            const adzunaJobs = adzunaData.results.map(job => ({
                id: `ext-${job.id}`,
                title: job.title,
                company: job.company?.display_name || 'Unknown Company',
                location: job.location?.display_name || 'Remote',
                salary: job.salary_max ? `$${job.salary_max.toLocaleString()}/year` : null,
                type: job.contract_type || 'Full-time',
                description: job.description,
                is_external: true,
                apply_url: job.redirect_url
            }));
            
            return adzunaJobs;
            
        } catch (error) {
            console.error('External API error:', error);
            return [];
        }
    }

    // Display results
    function displayResults(jobs) {
        if (jobs.length === 0) {
            $('#resultsContainer').html(`
                <div class="alert alert-info">
                    No jobs found matching your criteria.
                </div>
            `);
            return;
        }
        
        let html = '';
        jobs.forEach(job => {
            html += `
                <div class="card mb-4 shadow-sm job-listing">
                    <div class="card-body">
                        ${job.is_external ? '<span class="badge bg-warning float-end">External</span>' : ''}
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary">${job.type}</span>
                            <span class="text-muted small">${job.posted_at || 'Recently posted'}</span>
                        </div>
                        <h3 class="h4">${job.title}</h3>
                        <p class="text-muted mb-2">${job.company}</p>
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i> ${job.location}
                            ${job.salary ? `<span class="ms-3"><i class="fas fa-dollar-sign me-1"></i> ${job.salary}</span>` : ''}
                        </p>
                        <p class="mb-4">${job.description.substring(0, 200)}...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary-subtle text-secondary">${job.category || 'General'}</span>
                            <div class="btn-group">
                                <a href="${job.is_external ? job.apply_url : 'job-details.php?id='+job.id}" 
                                   class="btn ${job.is_external ? 'btn-warning' : 'btn-outline-primary'}">
                                   ${job.is_external ? 'Apply on External Site' : 'View Details'}
                                </a>
                                ${!job.is_external && job.can_apply ? 
                                    `<a href="apply.php?job_id=${job.id}" class="btn btn-primary">Apply Now</a>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#resultsContainer').html(html);
    }
});
</script>
<!-- Featured Jobs Section -->
<section class="featured-jobs py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Featured Jobs</h2>
                <a href="jobs.php" class="btn btn-outline-primary">View All Jobs</a>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredJobs as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 job-card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($job['type']); ?></span>
                                <span class="text-muted small"><?php echo date('M d, Y', strtotime($job['posted_at'])); ?></span>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($job['company']); ?></p>
                            <p class="card-text text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                                <?php if (!empty($job['salary'])): ?>
                                    <span class="ms-3"><i class="fas fa-dollar-sign me-1"></i> <?php echo htmlspecialchars($job['salary']); ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars(substr($job['description'], 0, 100)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($job['category']); ?></span>
                                <a href="jobs.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- Recruiter CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Are You Hiring?</h2>
        <p class="lead mb-5">Join thousands of companies finding their perfect candidates through our platform</p>
        <a href="register.php" class="btn btn-light btn-lg px-5">Post a Job Now</a>
    </div>
</section>
<!-- How It Works Section -->
<section class="how-it-works py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2>How It Works</h2>
                <p class="lead">Get your dream job in 3 simple steps</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="step-card p-4 h-100">
                    <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <h4>Create Account</h4>
                    <p>Register as a job seeker or employer in just a few minutes.</p>
            </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-card p-4 h-100">
                    <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                    <h4>Search or Post</h4>
                    <p>Find your perfect job or post your vacancy to find the best talent.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-card p-4 h-100">
                    <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h4>Get Connected</h4>
                    <p>Apply for jobs or review applications to find your perfect match.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Newsletter -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-3">Get Job Alerts</h2>
                <p class="lead text-muted mb-4">Subscribe to receive the latest job openings matching your profile</p>
                <form class="row g-2 justify-content-center">
                    <div class="col-md-8">
                        <input type="email" class="form-control form-control-lg" placeholder="Your email address">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<!-- Testimonials Section with Horizontal Scrolling -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        
        <div class="testimonial-scroller">
            <div class="testimonial-track">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user1.jpg" class="rounded-circle" width="60" height="60" alt="Sarah Johnson">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Sarah Johnson</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">Web Developer</small>
                                </div>
                            </div>
                            <p class="card-text">"Found my dream job within a week! The application process was incredibly smooth."</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user2.jpg" class="rounded-circle" width="60" height="60" alt="Michael Chen">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Michael Chen</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <small class="text-muted">HR Manager</small>
                                </div>
                            </div>
                            <p class="card-text">"As a recruiter, I've found excellent candidates through this portal. Highly recommend!"</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user3.jpeg" class="rounded-circle" width="60" height="60" alt="David Wilson">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">David Wilson</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">Data Scientist</small>
                                </div>
                            </div>
                            <p class="card-text">"The job matching algorithm is spot on. Got three interviews in my first week!"</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 4 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user8.jpeg" class="rounded-circle" width="60" height="60" alt="Emily Rodriguez">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Emily Rodriguez</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <small class="text-muted">UX Designer</small>
                                </div>
                            </div>
                            <p class="card-text">"Love the clean interface and regular job alerts. Made my job search so much easier."</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user5.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">James Kim</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>
<!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user6.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Abdi Gemechu h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>
       <!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user7.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Degaga</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>
<!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user8.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Dawit Pauolus</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>
<!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user9.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">Mihiretu Ayele</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>
<!-- Testimonial 5 -->
                <div class="testimonial-card">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="assets/images/user10.jpeg" class="rounded-circle" width="60" height="60" alt="James Kim">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">James Kim</h5>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <small class="text-muted">DevOps Engineer</small>
                                </div>
                            </div>
                            <p class="card-text">"From creating my profile to getting hired took just 2 weeks. Amazing platform!"</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
                <!-- Testimonial 6-10 (similar structure) -->
                <!-- Duplicate the testimonial-card div and change content for 6-10 -->
                
            </div>
        </div>
        
        <!-- Navigation Arrows -->
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary testimonial-prev mx-2">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-outline-primary testimonial-next mx-2">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<style>
    .testimonial-scroller {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 20px;
    }
    
    .testimonial-track {
        display: flex;
        gap: 20px;
        padding: 10px 0;
    }
    
    .testimonial-card {
        flex: 0 0 300px;
        scroll-snap-align: start;
    }
    
    /* Hide scrollbar but keep functionality */
    .testimonial-scroller::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.testimonial-track');
    const prevBtn = document.querySelector('.testimonial-prev');
    const nextBtn = document.querySelector('.testimonial-next');
    
    nextBtn.addEventListener('click', function() {
        track.scrollBy({ left: 320, behavior: 'smooth' });
    });
    
    prevBtn.addEventListener('click', function() {
        track.scrollBy({ left: -320, behavior: 'smooth' });
    });
});
</script>
<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h3 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        How do I apply for a job?
                    </button>
                </h3>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Simply browse jobs, click on a position you're interested in, and hit the "Apply Now" button. You'll need a complete profile to apply.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h3 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        Can I search for remote jobs?
                    </button>
                </h3>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! Use our job type filter to select "Remote" positions only. Many companies now offer fully remote opportunities.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h3 class="accordion-header" id="headingThree">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                   Can I edit or update my profile after registration?
                    </button>
                </h3>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
Yes! After logging in, go to your profile page where you can update your name, email, phone number, skills, education level, bio, and upload a new resume or profile picture anytime.                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h3 class="accordion-header" id="headingFour">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                               Is my personal data secure on this platform?
                    </button>
                </h3>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
Absolutely. We use secure encryption and hashing techniques to protect your data. Your password is stored securely using hashing, and we do not share your information with third parties without your consent.

                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h3 class="accordion-header" id="headingFive">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                               I forgot my password. How can I reset it?
                    </button>
                </h3>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
Click the "Forgot Password" link on the login page. Enter your registered email address, and we’ll send you a password reset link so you can create a new one securely.

                    </div>
                </div>
            </div>
            
            <!-- Add more FAQ items as needed -->
        </div>
    </div>
</section>
<!-- Job Market Statistics Section -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5">Job Market Insights</h2>
        
        <div class="row g-4">
            <!-- Jobs Posted Today -->
            <div class="col-md-4">
                <div class="stat-card text-center p-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 mx-auto mb-3">
                        <img src="assets/images/job-posted.jpeg" alt="Jobs posted" class="img-fluid" width="60">
                    </div>
<h3 class="display-5 fw-bold text-primary mb-2">
    75+ Jobs Posted Today
</h3>
                    <p class="fs-5 text-muted mb-0">Jobs Posted Today</p>
                    <div class="mt-3">
                        <img src="assets/images/video-editing.jpeg" alt="Company logos" class="img-fluid" style="max-height: 40px;">
                    </div>
                </div>
            </div>
            
            <!-- Companies Hiring -->
            <div class="col-md-4">
                <div class="stat-card text-center p-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 mx-auto mb-3">
                        <img src="assets/images/companies/apple.png" alt="Companies hiring" class="img-fluid" width="60">
                    </div>
<h3 class="display-5 fw-bold text-success mb-2">
    50+ Top Hiring Companies </h3>                    <p class="fs-5 text-muted mb-0">Companies Hiring</p>
                    <div class="mt-3">
                        <img src="assets/images/companies/intel.png" alt="Tech company logos" class="img-fluid" style="max-height: 40px;">
                    </div>
                </div>
            </div>
            
            <!-- Users Hired This Week -->
            <div class="col-md-4">
                <div class="stat-card text-center p-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 mx-auto mb-3">
                        <img src="assets/images/user7.jpeg" alt="Users hired" class="img-fluid" width="60">
                    </div>
<h3 class="display-5 fw-bold text-warning mb-2">
    120+ Weekly Hires
</h3>
                    <p class="fs-5 text-muted mb-0">Users Hired This Week</p>
                    <div class="mt-3">
                        <img src="assets/images/user3.jpeg" alt="Happy users" class="img-fluid" style="max-height: 40px;">
                    </div>
                </div>
            </div>
        </div>
        
       <!-- Additional Visual -->
<div class="text-center mt-5">
    <img src="assets/images/job-market-trends.jpeg" alt="Job market trends" class="img-fluid rounded mb-3" style="max-height: 200px;">

    <!-- Description about market trends -->
    <p class="lead text-muted">
        The job market is rapidly evolving with a strong demand for tech, healthcare, and remote roles. Stay updated with current trends to stay competitive in your career.
    </p>

    <!-- Icons to represent trends -->
    <div class="d-flex justify-content-center gap-4 mt-3">
        <div class="text-center">
            <i class="fas fa-laptop-code fa-2x text-primary"></i>
            <p class="small mt-2">Tech Jobs Rising</p>
        </div>
        <div class="text-center">
            <i class="fas fa-user-nurse fa-2x text-success"></i>
            <p class="small mt-2">Healthcare Growth</p>
        </div>
        <div class="text-center">
            <i class="fas fa-house-laptop fa-2x text-warning"></i>
            <p class="small mt-2">Remote Work</p>
        </div>
        <div class="text-center">
            <i class="fas fa-chart-line fa-2x text-danger"></i>
            <p class="small mt-2">Career Upskilling</p>
        </div>
    </div>
</div>

</section>

<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: linear-gradient(to bottom, #f8f9fa, #fff);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .stat-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
// Animated counter
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.stat-counter');
    const speed = 200;
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCounter, 1);
        } else {
            counter.innerText = target;
        }
        
        function updateCounter() {
            const count = +counter.innerText;
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCounter, 1);
            } else {
                counter.innerText = target;
            }
        }
    });
});
</script>
<!-- Job Types -->
<section class="py-5 bg-light">
<div class="container">
        <h2 class="text-center mb-5">Find Jobs By Type</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="jobs.php?type=Full-time" class="card type-card bg-white text-decoration-none h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-briefcase fs-1 text-primary mb-3"></i>
                        <h5>Full-Time Jobs</h5>
                        <p class="text-muted mb-0">Stable positions with benefits</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="jobs.php?type=Part-time" class="card type-card bg-white text-decoration-none h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-clock fs-1 text-primary mb-3"></i>
                        <h5>Part-Time Jobs</h5>
                        <p class="text-muted mb-0">Flexible work schedules</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="jobs.php?type=Remote" class="card type-card bg-white text-decoration-none h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-laptop-house fs-1 text-primary mb-3"></i>
                        <h5>Remote Jobs</h5>
                        <p class="text-muted mb-0">Work from anywhere</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- Top Companies Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Top Hiring Companies</h2>
        <div class="row g-4">
            <?php foreach ($companies as $company): ?>
            <div class="col-6 col-md-3 text-center">
                <div class="p-3 border rounded-3 h-100 d-flex align-items-center justify-content-center">
                    <img src="<?= !empty($company['logo']) ? htmlspecialchars($company['logo']) : 'assets/images/default-company.png' ?>" 
                         alt="<?= htmlspecialchars($company['name']) ?>" 
                         class="img-fluid" style="max-height: 80px;">
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

</section>
<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center py-4">
        <h2 class="mb-4">Ready to take the next step in your career?</h2>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-light btn-lg px-4">Register Now</a>
            <a href="jobs.php" class="btn btn-outline-light btn-lg px-4">Browse Jobs</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>