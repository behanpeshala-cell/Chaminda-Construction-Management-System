<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff','Project Manager']);
$role = current_role();
$page_title = 'Inventory / Stock Movements';
$page_actions = in_array($role, ['Administrator','Site Staff'])
  ? '<a href="/ccms/inventory/move.php" class="btn btn-success"><i class="bi bi-arrow-left-right"></i> Record Stock Movement</a>' : '';

$txns = $pdo->query("SELECT t.*, m.name AS material_name, m.unit, u.full_name AS by_user, p.project_name
                      FROM stock_transactions t
                      JOIN materials m ON m.material_id=t.material_id
                      JOIN users u ON u.user_id=t.created_by
                      LEFT JOIN projects p ON p.project_id=t.project_id
                      ORDER BY t.created_at DESC LIMIT 100")->fetchAll();
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Date</th><th>Material</th><th>Type</th><th>Qty</th><th>Reference / Project</th><th>Recorded By</th></tr></thead>
    <tbody>
    <?php if (!$txns): ?><tr><td colspan="6" class="text-center text-muted py-4">No stock movements recorded yet.</td></tr><?php endif; ?>
    <?php foreach ($txns as $t): ?>
      <tr>
        <td><?php echo $t['created_at']; ?></td>
        <td><?php echo htmlspecialchars($t['material_name']); ?></td>
        <td><?php echo $t['type']==='IN' ? '<span class="badge bg-success">Stock In</span>' : '<span class="badge bg-danger">Stock Out</span>'; ?></td>
        <td><?php echo (int)$t['quantity']; ?> <?php echo htmlspecialchars($t['unit']); ?></td>
        <td><?php echo htmlspecialchars($t['project_name'] ?? $t['reference']); ?></td>
        <td><?php echo htmlspecialchars($t['by_user']); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
