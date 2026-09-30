<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

/** Redirect helper */
function redirect($path) {
    header("Location: $path");
    exit;
}


function require_login() {
    if (empty($_SESSION['user_id'])) {
        redirect('/ccms/login.php');
    }
    // Session (idle) timeout
    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT_SECONDS) {
        session_unset();
        session_destroy();
        redirect('/ccms/login.php?timeout=1');
    }
    $_SESSION['last_activity'] = time();
}

function require_role(array $roles) {
    require_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center">
                <h2>403 - Access Denied</h2>
                <p>Your role (' . htmlspecialchars($_SESSION['role']) . ') does not have permission to view this page.</p>
                <a href="/ccms/dashboard.php">&larr; Back to Dashboard</a>
             </div>');
    }
}

function current_user_id() { return $_SESSION['user_id'] ?? null; }
function current_role()    { return $_SESSION['role'] ?? null; }
function current_name()    { return $_SESSION['full_name'] ?? ''; }

function audit(PDO $pdo, string $action, string $table, ?int $record_id = null) {
    $stmt = $pdo->prepare(
        "INSERT INTO audit_log (user_id, action, table_name, record_id) VALUES (?,?,?,?)"
    );
    $stmt->execute([current_user_id(), $action, $table, $record_id]);
}

function set_flash(string $type, string $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}


function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_check() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('Invalid or expired form submission (CSRF check failed). Please go back and try again.');
    }
}
