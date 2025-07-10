<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Job Portal'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
</head>
<body>
    <!-- Debug information (remove in production) -->
    <div style="display:none;">
        BASE_URL: <?php echo BASE_URL; ?><br>
        Current directory: <?php echo __DIR__; ?>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container px-4">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Job Portal Logo"  width="70"height="60">
                <span class="ms-2 d-none d-sm-inline">Job Portal</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/about.php">
                            <i class="fas fa-info-circle me-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jobs.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/jobs.php">
                            <i class="fas fa-briefcase me-1"></i> Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/contact.php">
                            <i class="fas fa-envelope me-1"></i> Contact
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'coding.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/coding.php">
                            <i class="fas fa-code me-1"></i> Coding
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Logged-in User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if ($_SESSION['user_type'] == 'employer'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/post-job.php"><i class="fas fa-plus-circle me-1"></i> Post Job</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest Menu -->
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-light">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    <?php endif; ?>
                    
                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="btn btn-dark ms-2" title="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container mt-4">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>