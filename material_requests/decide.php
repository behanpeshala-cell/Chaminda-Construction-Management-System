<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    $decision = $_POST['decision'] ?? '';
    if (in_array($decision, ['Approved','Rejected'], true)) {
        $stmt = $pdo->prepare("UPDATE material_requests SET status=?, approved_by=?, decided_at=NOW() WHERE request_id=? AND status='Pending'");
        $stmt->execute([$decision, current_user_id(), $id]);
        audit($pdo, 'MATERIAL_REQUEST_' . strtoupper($decision), 'material_requests', $id);
        set_flash('success', "Request $decision.");
    }
}
redirect('/ccms/material_requests/list.php');
