<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT status FROM clients WHERE client_id=?"); $stmt->execute([$id]);
    $status = $stmt->fetchColumn();
    if ($status !== false) {
        $new = $status === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE clients SET status=? WHERE client_id=?")->execute([$new,$id]);
        audit($pdo,'STATUS_CHANGE','clients',$id);
        set_flash('success', "Client status changed to $new.");
    }
}
redirect('/ccms/clients/list.php');
