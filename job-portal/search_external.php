<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db.php'; // For potential caching
// Add to the top of search_external.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
// Configuration - Replace with your actual API keys
define('ADZUNA_APP_ID', '2c77633a');
define('ADZUNA_APP_KEY', '08efd7d273d321eea12df26bc9129964');
define('REED_API_KEY', 'e3440ef0-5d2a-4bf3-b924-2bf1e6199709');
define('CACHE_TTL', 3600); // 1 hour cache

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

// Validate inputs
if (empty($search) && empty($location)) {
    echo json_encode(['success' => false, 'message' => 'Please provide search terms or location']);
    exit;
}

try {
    // Check cache first
    $cacheKey = 'ext_jobs_' . md5($search . $location);
    $cachedResults = getFromCache($cacheKey);
    
    if ($cachedResults !== false) {
        echo $cachedResults;
        exit;
    }

    // Search multiple APIs in parallel
    $jobs = [];
    $apis = [
        'adzuna' => searchAdzuna($search, $location),
        'reed' => searchReed($search, $location)
        // Add more APIs here
    ];

    // Wait for all API requests to complete
    $results = Promise\unwrap($apis);
    
    foreach ($results as $apiResults) {
        $jobs = array_merge($jobs, $apiResults);
    }

    // Remove duplicates (by title+company)
    $jobs = removeDuplicateJobs($jobs);

    // Sort by relevance (simple implementation)
    usort($jobs, function($a, $b) use ($search) {
        return similar_text($search, $b['title']) - similar_text($search, $a['title']);
    });

    // Limit results
    $jobs = array_slice($jobs, 0, 20);

    // Prepare final response
    $response = json_encode([
        'success' => true,
        'count' => count($jobs),
        'jobs' => $jobs,
        'source' => array_keys($apis)
    ]);

    // Cache the results
    saveToCache($cacheKey, $response);

    echo $response;

} catch (Exception $e) {
    error_log('External search error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching external jobs',
        'error' => $e->getMessage()
    ]);
}

/**
 * Search Adzuna API
 */
function searchAdzuna($search, $location) {
    $url = "https://api.adzuna.com/v1/api/jobs/us/search/1?" . http_build_query([
        'app_id' => 2c77633a,
        'app_key' => 08efd7d273d321eea12df26bc9129964,
        'what' => $search,
        'where' => $location,
        'max_days_old' => 30,
        'results_per_page' => 10,
        'content-type' => 'application/json'
    ]);

    $response = file_get_contents($url, false, stream_context_create([
        'http' => ['timeout' => 5] // 5 second timeout
    ]));

    if (!$response) {
        throw new Exception('Adzuna API request failed');
    }

    $data = json_decode($response, true);

    if (!isset($data['results'])) {
        return [];
    }

    return array_map(function($job) {
        return [
            'id' => 'adz-' . $job['id'],
            'title' => $job['title'],
            'company' => $job['company']['display_name'] ?? 'Unknown Company',
            'location' => $job['location']['display_name'] ?? 'Remote',
            'salary' => isset($job['salary_max']) ? formatSalary($job['salary_max'], $job['salary_currency']) : null,
            'type' => $job['contract_type'] ?? 'Full-time',
            'description' => strip_tags($job['description'] ?? ''),
            'posted_at' => isset($job['created']) ? date('M d, Y', strtotime($job['created'])) : null,
            'apply_url' => $job['redirect_url'],
            'source' => 'Adzuna'
        ];
    }, $data['results']);
}

/**
 * Search Reed.co.uk API
 */
function searchReed($search, $location) {
    $url = "https://www.reed.co.uk/api/1.0/search?" . http_build_query([
        'keywords' => $search,
        'locationName' => $location,
        'resultsToTake' => 10
    ]);

    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Basic " . base64_encode(REED_API_KEY . ":"),
            'timeout' => 5
        ]
    ]);

    $response = file_get_contents($url, false, $context);

    if (!$response) {
        throw new Exception('Reed API request failed');
    }

    $data = json_decode($response, true);

    if (!isset($data['results'])) {
        return [];
    }

    return array_map(function($job) {
        return [
            'id' => 'reed-' . $job['jobId'],
            'title' => $job['jobTitle'],
            'company' => $job['employerName'] ?? 'Unknown Company',
            'location' => $job['locationName'] ?? 'Remote',
            'salary' => isset($job['maximumSalary']) ? '£' . number_format($job['maximumSalary']) : null,
            'type' => formatReedContractType($job['contractType'] ?? ''),
            'description' => strip_tags($job['jobDescription'] ?? ''),
            'posted_at' => isset($job['date']) ? date('M d, Y', strtotime($job['date'])) : null,
            'apply_url' => $job['jobUrl'],
            'source' => 'Reed'
        ];
    }, $data['results']);
}

/**
 * Helper Functions
 */
function formatSalary($amount, $currency) {
    $symbols = ['USD' => '$', 'GBP' => '£', 'EUR' => '€'];
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount);
}

function formatReedContractType($type) {
    $mapping = [
        'Permanent' => 'Full-time',
        'Contract' => 'Contract',
        'Temporary' => 'Temporary',
        'PartTime' => 'Part-time'
    ];
    return $mapping[$type] ?? $type;
}

function removeDuplicateJobs($jobs) {
    $seen = [];
    return array_filter($jobs, function($job) use (&$seen) {
        $key = $job['title'] . $job['company'];
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            return true;
        }
        return false;
    });
}

function getFromCache($key) {
    // Implement your caching mechanism (Redis, Memcached, DB, etc.)
    // Example with database:
    global $pdo;
    $stmt = $pdo->prepare("SELECT data FROM api_cache WHERE cache_key = ? AND expires_at > NOW()");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function saveToCache($key, $data) {
    // Implement your caching mechanism
    // Example with database:
    global $pdo;
    $expiresAt = date('Y-m-d H:i:s', time() + CACHE_TTL);
    $stmt = $pdo->prepare("REPLACE INTO api_cache (cache_key, data, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$key, $data, $expiresAt]);
}
?>