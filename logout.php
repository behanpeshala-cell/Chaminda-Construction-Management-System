<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) {
    audit($pdo, 'LOGOUT', 'users', current_user_id());
}
session_unset();
session_destroy();
redirect('/ccms/login.php');
