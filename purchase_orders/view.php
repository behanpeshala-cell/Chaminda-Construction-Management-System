<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff']);
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT po.*, s.name AS supplier_name, u.full_name AS created_by_name
                        FROM purchase_orders po JOIN suppliers s ON s.supplier_id=po.supplier_id
                        JOIN users u ON u.user_id=po.created_by WHERE po.po_id=?");
$stmt->execute([$id]);
$po = $stmt->fetch();
if (!$po) { set_flash('danger','Purchase order not found.'); redirect('/ccms/purchase_orders/list.php'); }

$items = $pdo->prepare("SELECT poi.*, m.name, m.unit FROM purchase_order_items poi JOIN materials m ON m.material_id=poi.material_id WHERE poi.po_id=?");
$items->execute([$id]);
$items = $items->fetchAll();
$total = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $items));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($po['status'] === 'Delivered') {
        set_flash('danger', 'Delivered purchase orders cannot be modified.');
        redirect('/ccms/purchase_orders/view.php?id='.$id);
    }

    if ($action === 'approve' && $po['status'] === 'Pending') {
        $pdo->prepare("UPDATE purchase_orders SET status='Approved' WHERE po_id=?")->execute([$id]);
        audit($pdo, 'PO_APPROVED', 'purchase_orders', $id);
        set_flash('success', 'Purchase order approved.');
    } elseif ($action === 'cancel' && in_array($po['status'], ['Pending','Approved'])) {
        $pdo->prepare("UPDATE purchase_orders SET status='Cancelled' WHERE po_id=?")->execute([$id]);
        audit($pdo, 'PO_CANCELLED', 'purchase_orders', $id);
        set_flash('success', 'Purchase order cancelled.');
    } elseif ($action === 'deliver' && $po['status'] === 'Approved') {
        // Delivered POs automatically trigger a stock-in transaction for every line item
        $pdo->beginTransaction();
        foreach ($items as $it) {
            $pdo->prepare("INSERT INTO inventory (material_id, quantity_on_hand) VALUES (?,?)
                            ON DUPLICATE KEY UPDATE quantity_on_hand = quantity_on_hand + VALUES(quantity_on_hand)")
                ->execute([$it['material_id'], $it['quantity']]);
            $pdo->prepare("INSERT INTO stock_transactions (material_id, type, quantity, reference, created_by) VALUES (?, 'IN', ?, ?, ?)")
                ->execute([$it['material_id'], $it['quantity'], 'PO-' . str_pad($id,4,'0',STR_PAD_LEFT), current_user_id()]);
        }
        $pdo->prepare("UPDATE purchase_orders SET status='Delivered', delivered_at=NOW() WHERE po_id=?")->execute([$id]);
        $pdo->commit();
        audit($pdo, 'PO_DELIVERED', 'purchase_orders', $id);
        set_flash('success', 'Purchase order marked as delivered — stock levels updated automatically.');
    }
    redirect('/ccms/purchase_orders/view.php?id='.$id);
}

$page_title = 'PO-' . str_pad($po['po_id'],4,'0',STR_PAD_LEFT);
require_once __DIR__ . '/../includes/page_start.php';
$badge = ['Pending'=>'secondary','Approved'=>'info','Delivered'=>'success','Cancelled'=>'danger'][$po['status']] ?? 'secondary';
?>
<div class="card p-3 mb-3">
  <div class="d-flex justify-content-between flex-wrap">
    <div>
      <p class="mb-1"><strong>Supplier:</strong> <?php echo htmlspecialchars($po['supplier_name']); ?></p>
      <p class="mb-1"><strong>Created by:</strong> <?php echo htmlspecialchars($po['created_by_name']); ?> on <?php echo $po['created_at']; ?></p>
      <p class="mb-0"><strong>Status:</strong> <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($po['status']); ?></span></p>
    </div>
    <div class="align-self-start">
      <?php if ($po['status'] === 'Pending'): ?>
        <form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button></form>
        <form method="post" class="d-inline" data-confirm="Cancel this purchase order?"><input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>"><input type="hidden" name="action" value="cancel"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel</button></form>
      <?php elseif ($po['status'] === 'Approved'): ?>
        <form method="post" class="d-inline" data-confirm="Mark as delivered? This will add stock automatically and cannot be undone."><input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>"><input type="hidden" name="action" value="deliver"><button class="btn btn-sm btn-success"><i class="bi bi-truck"></i> Mark Delivered</button></form>
        <form method="post" class="d-inline" data-confirm="Cancel this purchase order?"><input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>"><input type="hidden" name="action" value="cancel"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel</button></form>
      <?php elseif ($po['status'] === 'Delivered'): ?>
        <span class="text-muted small"><i class="bi bi-lock"></i> Delivered POs cannot be modified.</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card p-3">
  <h6>Line Items</h6>
  <div class="table-responsive">
  <table class="table table-sm">
    <thead><tr><th>Material</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
    <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?php echo htmlspecialchars($it['name']); ?></td>
        <td><?php echo (int)$it['quantity']; ?> <?php echo htmlspecialchars($it['unit']); ?></td>
        <td><?php echo number_format($it['unit_price'],2); ?></td>
        <td><?php echo number_format($it['quantity']*$it['unit_price'],2); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th>LKR <?php echo number_format($total,2); ?></th></tr></tfoot>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
