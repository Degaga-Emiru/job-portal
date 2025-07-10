<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = "Coding Examples";
require_once 'includes/header.php';
?>

<!-- Coding Section -->
<section class="coding-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1>Coding Examples</h1>
                <p class="lead">Technical implementations from our job portal</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">Database Connection</h3>
                        <p class="card-text">Here's how we establish a secure database connection using PDO:</p>
                        
                        <pre><code class="language-php"><?php echo htmlspecialchars('<?php
// Database configuration
$host = "localhost";
$dbname = "job_portal";
$username = "db_user";
$password = "secure_password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>'); ?></code></pre>
                        
                        <p class="card-text mt-3">This implementation uses prepared statements to prevent SQL injection attacks.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">User Authentication</h3>
                        <p class="card-text">Secure user login system with password hashing:</p>
                        
                        <pre><code class="language-php"><?php echo htmlspecialchars('<?php
// Login logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user["password"])) {
        // Start session and redirect
        $_SESSION["user_id"] = $user["id"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>'); ?></code></pre>
                        
                        <p class="card-text mt-3">We use PHP's <code>password_hash()</code> and <code>password_verify()</code> for secure password handling.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Job Search Functionality</h3>
                        <p class="card-text">Dynamic job search with filtering options:</p>
                        
                        <pre><code class="language-php"><?php echo htmlspecialchars('<?php
// Get search parameters from URL
$search = $_GET["q"] ?? "";
$location = $_GET["location"] ?? "";
$category = $_GET["category"] ?? "";

// Build SQL query with parameters
$sql = "SELECT * FROM jobs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>'); ?></code></pre>
                        
                        <p class="card-text mt-3">This code safely handles user input and provides flexible search options.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>