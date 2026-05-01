<?php

ob_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Database configuration
class Database {
    private $host = '127.0.0.1';
    private $username = 'root';
    private $database = 'santoshvastralay';
    private $password = '';
    private $conn = null;

    function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            // Set charset to utf8mb4
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }

    public function connect() {
        return $this->conn;
    }
}

// Initialize database connection
$dbInstance = new Database();
$db = $dbInstance->connect();

// Define base path
define('BASE_PATH', __DIR__);

// Include required files
require_once BASE_PATH . "/actions/function.class.php";
require_once BASE_PATH . "/Home/cart-functions.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : 'Guest';

// Define base URL dynamically so it works after deployment.
if (!defined('BASE_URL')) {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(__DIR__);
    $baseUrl = '/';

    if ($docRoot && $appRoot) {
        $docRootNorm = str_replace('\\', '/', rtrim($docRoot, '\\/'));
        $appRootNorm = str_replace('\\', '/', rtrim($appRoot, '\\/'));

        if (strpos($appRootNorm, $docRootNorm) === 0) {
            $relative = trim(substr($appRootNorm, strlen($docRootNorm)), '/');
            $baseUrl = '/' . ($relative !== '' ? $relative . '/' : '');
        }
    }

    define('BASE_URL', $baseUrl);
}

// Function to get absolute URL
function getAbsoluteUrl($path) {
    return BASE_URL . ltrim($path, '/');
}

// Function to redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
    
    // Clear any output buffer
    if (ob_get_length()) ob_end_clean();
    
    // Perform the redirect
    header('Location: ' . getAbsoluteUrl($url));
    exit();
}

// Function to display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $class = $message['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
        echo '<div class="border-l-4 p-4 mb-4 ' . $class . '">' . htmlspecialchars($message['text']) . '</div>';
        unset($_SESSION['flash_message']);
    }
}
?>