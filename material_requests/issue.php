<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT * FROM material_requests WHERE request_id=? AND status='Approved'");
    $stmt->execute([$id]);
    $req = $stmt->fetch();

    if (!$req) {
        set_flash('danger', 'Request not found or not approved.');
    } else {
        $stockStmt = $pdo->prepare("SELECT quantity_on_hand FROM inventory WHERE material_id=?");
        $stockStmt->execute([$req['material_id']]);
        $onHand = (int)($stockStmt->fetchColumn() ?: 0);

        if ($req['quantity_requested'] > $onHand) {
            set_flash('danger', "Cannot issue: only $onHand unit(s) in stock. Negative stock balances are not permitted.");
        } else {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE inventory SET quantity_on_hand = quantity_on_hand - ? WHERE material_id=?")
                ->execute([$req['quantity_requested'], $req['material_id']]);
            $pdo->prepare("INSERT INTO stock_transactions (material_id, type, quantity, reference, project_id, created_by) VALUES (?, 'OUT', ?, ?, ?, ?)")
                ->execute([$req['material_id'], $req['quantity_requested'], 'Material Request #' . $id, $req['project_id'], current_user_id()]);
            $pdo->prepare("UPDATE material_requests SET status='Issued' WHERE request_id=?")->execute([$id]);
            $pdo->commit();
            audit($pdo, 'MATERIAL_REQUEST_ISSUED', 'material_requests', $id);
            set_flash('success', 'Material issued and stock updated.');
        }
    }
}
redirect('/ccms/material_requests/list.php');
