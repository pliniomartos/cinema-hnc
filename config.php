<?php
/**
 * Secure Configuration File for Cinema Website
 * 
 * This file handles:
 * - Environment variable loading
 * - Database connection with security settings
 * - CSRF token generation
 * - Session security
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
    
    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (strlen($value) > 1 && ($value[0] === '"' || $value[0] === "'")) {
                $value = trim($value, '"\'');
            }
            
            if (!empty($key) && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
    return true;
}

// Load .env file
$envLoaded = loadEnv(__DIR__ . '/.env');

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
if (empty($_ENV['DB_USER'])) { die('Configuration error: DB_USER not set. Check .env file.'); }
if (!isset($_ENV['DB_PASS'])) { die('Configuration error: DB_PASS not set. Check .env file.'); }
if (empty($_ENV['DB_NAME'])) { die('Configuration error: DB_NAME not set. Check .env file.'); }
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);

// API Keys - Use environment variables only!
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('OMDB_API_KEY', $_ENV['OMDB_API_KEY'] ?? '');
define('TMDB_API_KEY', $_ENV['TMDB_API_KEY'] ?? '');

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

/**
 * Get database connection with error handling
 * @return mysqli
 */
function getDBConnection() {
    static $link = null;
    
    if ($link === null) {
        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$link) {
            error_log("Database connection failed: " . mysqli_connect_error());
            die('System error. Please try again later.');
        }
        
        // Set charset to handle special characters properly
        mysqli_set_charset($link, 'utf8mb4');
    }
    
    return $link;
}

/**
 * Generate or retrieve CSRF token
 * @return string
 */
function getCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize output for HTML display
 * @param string $text
 * @return string
 */
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input string
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    return trim(strip_tags($data));
}

/**
 * Redirect with exit
 * @param string $url
 */
function safeRedirect($url) {
    // Prevent header injection
    $url = str_replace(["\r", "\n"], '', $url);
    header("Location: $url");
    exit();
}

/**
 * Log security events
 * @param string $event
 * @param string $details
 */
function logSecurityEvent($event, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logMessage = "[$timestamp] [$event] [IP: $ip] $details" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/logs/security.log');
}
