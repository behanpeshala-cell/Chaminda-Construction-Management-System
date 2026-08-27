<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT status FROM employees WHERE employee_id=?"); $stmt->execute([$id]);
    $status = $stmt->fetchColumn();
    if ($status !== false) {
        $new = $status === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE employees SET status=? WHERE employee_id=?")->execute([$new,$id]);
        audit($pdo,'STATUS_CHANGE','employees',$id);
        set_flash('success', "Employee status changed to $new.");
    }
}
redirect('/ccms/employees/list.php');
