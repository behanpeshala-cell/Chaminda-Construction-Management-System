<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT status FROM suppliers WHERE supplier_id=?"); $stmt->execute([$id]);
    $status = $stmt->fetchColumn();
    if ($status !== false) {
        $new = $status === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE suppliers SET status=? WHERE supplier_id=?")->execute([$new,$id]);
        audit($pdo,'STATUS_CHANGE','suppliers',$id);
        set_flash('success', "Supplier status changed to $new.");
    }
}
redirect('/ccms/suppliers/list.php');
