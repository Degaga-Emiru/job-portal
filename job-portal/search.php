<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "Search Jobs";
require_once 'includes/header.php';

// Get search parameters from URL
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$includeExternal = isset($_GET['external']) ? (bool)$_GET['external'] : true;

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM jobs")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Job Search Section -->
<section class="job-search py-4 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="searchForm" method="get" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="q" class="form-label">Keywords</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="q" name="q" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Job title or company">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">Location</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?= htmlspecialchars($location) ?>" 
                                       placeholder="City or state">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= $category == $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label">Job Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="Full-time" <?= $type == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                                <option value="Part-time" <?= $type == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                                <option value="Contract" <?= $type == 'Contract' ? 'selected' : '' ?>>Contract</option>
                                <option value="Internship" <?= $type == 'Internship' ? 'selected' : '' ?>>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" id="searchButton" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="externalToggle" name="external" 
                               <?= $includeExternal ? 'checked' : '' ?>>
                        <label class="form-check-label" for="externalToggle">Include external job listings</label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="search-results py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Search Results</h2>
                <div class="text-muted" id="resultCount">
                    <!-- Results count will be updated dynamically -->
                </div>
            </div>
            
            <div class="col-lg-8">
                <div id="jobsContainer">
                    <!-- All jobs will be loaded here via AJAX -->
                </div>
                <div id="externalJobsContainer"></div>
                <div id="noResultsMessage" class="alert alert-info" style="display:none;">
                    No jobs found matching your criteria.
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Your existing sidebar content -->
            </div>
        </div>
    </div>
</section>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="text-center py-5" style="display:none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Searching jobs...</p>
</div>

<script>
$(document).ready(function() {
    // Initialize search from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('q') || '';
    const initialLocation = urlParams.get('location') || '';
    const initialCategory = urlParams.get('category') || '';
    const initialType = urlParams.get('type') || '';
    const includeExternal = urlParams.get('external') !== '0';

    if (initialSearch || initialLocation || initialCategory || initialType) {
        performSearch();
    }

    // Form submission handler
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        performSearch();
    });

    // Toggle external jobs
    $('#externalToggle').change(function() {
        performSearch();
    });

    // Perform the search
    function performSearch() {
        const query = $('#q').val().trim();
        const location = $('#location').val().trim();
        const category = $('#category').val();
        const type = $('#type').val();
        const includeExternal = $('#externalToggle').is(':checked');

        // Update URL without reloading
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('q', query);
        newUrl.searchParams.set('location', location);
        newUrl.searchParams.set('category', category);
        newUrl.searchParams.set('type', type);
        newUrl.searchParams.set('external', includeExternal ? '1' : '0');
        window.history.pushState({}, '', newUrl);

        // Show loading indicator
        $('#loadingIndicator').show();
        $('#jobsContainer').html('');
        $('#externalJobsContainer').html('');
        $('#noResultsMessage').hide();

        // Prepare data for AJAX request
        const searchData = {
            q: query,
            location: location,
            category: category,
            type: type
        };

        // Search local jobs
        $.ajax({
            url: 'api/search_local.php',
            method: 'GET',
            data: searchData,
            success: function(localResponse) {
                let allJobs = localResponse.jobs || [];
                let localCount = allJobs.length;

                // Update results count
                updateResultCount(localCount, 0);

                // Display local jobs
                if (localCount > 0) {
                    renderJobs(allJobs, 'jobsContainer', false);
                }

                // Search external jobs if enabled
                if (includeExternal && (query || location)) {
                    $.ajax({
                        url: 'api/search_external.php',
                        method: 'GET',
                        data: { q: query, location: location },
                        success: function(externalResponse) {
                            if (externalResponse.success && externalResponse.jobs.length > 0) {
                                const externalJobs = externalResponse.jobs;
                                renderJobs(externalJobs, 'externalJobsContainer', true);
                                updateResultCount(localCount, externalJobs.length);
                            }
                        },
                        error: function(xhr) {
                            console.error('External search error:', xhr.responseText);
                            $('#externalJobsContainer').html(`
                                <div class="alert alert-warning mt-4">
                                    Could not load external job listings.
                                </div>
                            `);
                        },
                        complete: function() {
                            $('#loadingIndicator').hide();
                            if ($('#jobsContainer').children().length === 0 && 
                                $('#externalJobsContainer').children().length === 0) {
                                $('#noResultsMessage').show();
                            }
                        }
                    });
                } else {
                    $('#loadingIndicator').hide();
                    if (localCount === 0) {
                        $('#noResultsMessage').show();
                    }
                }
            },
            error: function(xhr) {
                console.error('Local search error:', xhr.responseText);
                $('#jobsContainer').html(`
                    <div class="alert alert-danger">
                        Error loading local job listings.
                    </div>
                `);
                $('#loadingIndicator').hide();
            }
        });
    }

    // Update results count display
    function updateResultCount(localCount, externalCount) {
        let countText = '';
        if (localCount > 0 && externalCount > 0) {
            countText = `${localCount} local jobs + ${externalCount} external jobs found`;
        } else if (localCount > 0) {
            countText = `${localCount} local jobs found`;
        } else if (externalCount > 0) {
            countText = `${externalCount} external jobs found`;
        } else {
            countText = 'No jobs found';
        }
        $('#resultCount').text(countText);
    }

    // Render jobs in the specified container
    function renderJobs(jobs, containerId, isExternal) {
        let html = '';
        
        if (isExternal && jobs.length > 0) {
            html += '<h4 class="mt-5 mb-4 border-top pt-4">External Job Listings</h4>';
        }

        jobs.forEach(job => {
            html += `
                <div class="card mb-4 shadow-sm job-listing">
                    <div class="card-body">
                        ${isExternal ? '<span class="badge bg-warning float-end">External</span>' : ''}
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary">${job.type || 'Full-time'}</span>
                            <span class="text-muted small">${job.posted_at || 'Recently posted'}</span>
                        </div>
                        <h3 class="h4">${job.title}</h3>
                        <p class="text-muted mb-2">${job.company || 'Unknown Company'}</p>
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i> ${job.location || 'Remote'}
                            ${job.salary ? `<span class="ms-3"><i class="fas fa-dollar-sign me-1"></i> ${job.salary}</span>` : ''}
                        </p>
                        <p class="mb-4">${job.description.substring(0, 200)}...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary-subtle text-secondary">${job.category || 'General'}</span>
                            <div class="btn-group">
                                <a href="${isExternal ? job.apply_url : 'job-details.php?id='+job.id}" 
                                   class="btn ${isExternal ? 'btn-warning' : 'btn-outline-primary'}"
                                   ${isExternal ? 'target="_blank"' : ''}>
                                   ${isExternal ? 'Apply on External Site' : 'View Details'}
                                </a>
                                ${!isExternal ? `
                                    <a href="apply.php?job_id=${job.id}" class="btn btn-primary">
                                        Apply Now
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $(`#${containerId}`).append(html);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>