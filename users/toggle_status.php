<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    if ($id === current_user_id()) {
        set_flash('danger', 'You cannot deactivate your own account.');
    } else {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id=?");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();
        if ($status !== false) {
            $new = $status === 'active' ? 'inactive' : 'active';
            $pdo->prepare("UPDATE users SET status=? WHERE user_id=?")->execute([$new, $id]);
            audit($pdo, 'STATUS_CHANGE', 'users', $id);
            set_flash('success', "User status changed to $new.");
        }
    }
}
redirect('/ccms/users/list.php');
