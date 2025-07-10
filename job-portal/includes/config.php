<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Automatically detect host and port
$host = $_SERVER['HTTP_HOST']; // e.g., "localhost:8080"
$basePath = '/job-portal';

define('BASE_URL', "http://$host$basePath");
?>