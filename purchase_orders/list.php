<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff','Project Manager','Site Staff','Finance Officer']);
$role = current_role();
$page_title = 'Purchase Orders';
$page_actions = in_array($role, ['Administrator','Procurement Staff','Project Manager'])
  ? '<a href="/ccms/purchase_orders/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Purchase Order</a>' : '';

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT po.*, s.name AS supplier_name, u.full_name AS created_by_name,
                                (SELECT COALESCE(SUM(quantity*unit_price),0) FROM purchase_order_items WHERE po_id=po.po_id) AS total
                         FROM purchase_orders po
                         JOIN suppliers s ON s.supplier_id=po.supplier_id
                         JOIN users u ON u.user_id=po.created_by
                         WHERE s.name LIKE ? OR po.status LIKE ? OR u.full_name LIKE ? OR po.po_id LIKE ?
                         ORDER BY po.created_at DESC");
    $term = "%$search%";
    $stmt->execute([$term, $term, $term, str_replace('po-', '', strtolower($search))]);
    $pos = $stmt->fetchAll();
} else {
    $pos = $pdo->query("SELECT po.*, s.name AS supplier_name, u.full_name AS created_by_name,
                                (SELECT COALESCE(SUM(quantity*unit_price),0) FROM purchase_order_items WHERE po_id=po.po_id) AS total
                         FROM purchase_orders po
                         JOIN suppliers s ON s.supplier_id=po.supplier_id
                         JOIN users u ON u.user_id=po.created_by
                         ORDER BY po.created_at DESC")->fetchAll();
}

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="q" class="form-control" placeholder="Search POs by supplier, status, ID or creator" value="<?php echo htmlspecialchars($search); ?>" oninput="filterTable(this.value)">
    </div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Search</button></div>
  </form>
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>PO #</th><th>Supplier</th><th>Total (LKR)</th><th>Status</th><th>Created By</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
    <?php if (!$pos): ?><tr><td colspan="7" class="text-center text-muted py-4">No purchase orders yet.</td></tr><?php endif; ?>
    <?php foreach ($pos as $po):
      $badge = ['Pending'=>'secondary','Approved'=>'info','Delivered'=>'success','Cancelled'=>'danger'][$po['status']] ?? 'secondary';
    ?>
      <tr>
        <td class="font-monospace text-cyan fw-bold">PO-<?php echo str_pad($po['po_id'],4,'0',STR_PAD_LEFT); ?></td>
        <td><?php echo htmlspecialchars($po['supplier_name']); ?></td>
        <td class="fw-bold font-monospace"><?php echo number_format($po['total'],2); ?></td>
        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($po['status']); ?></span></td>
        <td><?php echo htmlspecialchars($po['created_by_name']); ?></td>
        <td class="small text-muted"><?php echo $po['created_at']; ?></td>
        <td><a href="/ccms/purchase_orders/view.php?id=<?php echo $po['po_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
