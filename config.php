<?php
/**
 * config.php
 * Application bootstrap: constants, session, autoloading.
 * Edit the DB_* constants below to match your environment.
 */

// ---- Database configuration ----
define('DB_HOST', 'localhost');
<<<<<<< HEAD
define('DB_NAME', 'saloon_v2');
=======
define('DB_NAME', 'kings_cut_saloon');
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- App configuration ----
// BASE_PATH is auto-detected from the folder index.php runs in, so it
// works whether the app sits at the domain root (http://localhost/) or in
// a sub-folder (http://localhost/kingcut/) — no manual editing needed.
// Only uncomment and hardcode this if auto-detection doesn't fit your
// server setup (e.g. a reverse proxy that rewrites paths).
$detectedBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
define('BASE_PATH', $detectedBasePath);
// define('BASE_PATH', '/kingcut'); // <- manual override example, if ever needed
define('APP_NAME', "King's Cut Saloon");

// ---- Error reporting (turn off display_errors in production) ----
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---- Session ----
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ---- Simple PSR-4-ish autoloader for the App\ namespace ----
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// ---- Small global helpers ----

/** Escape output for safe HTML rendering. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a naira amount consistently across views. */
function money($amount): string
{
    return '₦' . number_format((float) $amount, 0);
}

/** Build a URL relative to the app's BASE_PATH. */
function url(string $path = ''): string
{
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

/** Redirect helper. */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Set a one-time flash message shown on the next request. */
function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/** Generate (or reuse) a CSRF token for the current session. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Echo a hidden CSRF input field for forms. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted CSRF token, aborting the request if invalid. */
function csrf_verify(): void
{
    $stored = $_SESSION['csrf_token'] ?? '';
    $submitted = $_POST['csrf_token'] ?? '';
    if ($stored === '' || $submitted === '' || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        exit('Your session expired. Please go back and try again.');
    }
}
