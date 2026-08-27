<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff','Project Manager']);
$role = current_role();
$page_title = 'Material Catalogue';
$page_actions = in_array($role, ['Administrator','Site Staff'])
  ? '<a href="/ccms/materials/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Material</a>' : '';

$materials = $pdo->query("SELECT m.*, COALESCE(i.quantity_on_hand,0) AS qty
                           FROM materials m LEFT JOIN inventory i ON i.material_id=m.material_id
                           ORDER BY m.name")->fetchAll();
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Material</th><th>Unit</th><th>Unit Price (LKR)</th><th>Stock on Hand</th><th>Reorder Level</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$materials): ?><tr><td colspan="7" class="text-center text-muted py-4">No materials found.</td></tr><?php endif; ?>
    <?php foreach ($materials as $m): $low = $m['qty'] <= $m['reorder_level']; ?>
      <tr class="<?php echo $low ? 'table-warning' : ''; ?>">
        <td><?php echo htmlspecialchars($m['name']); ?></td>
        <td><?php echo htmlspecialchars($m['unit']); ?></td>
        <td><?php echo number_format($m['unit_price'],2); ?></td>
        <td><?php echo (int)$m['qty']; ?> <?php if ($low): ?><span class="badge bg-warning text-dark">Low</span><?php endif; ?></td>
        <td><?php echo (int)$m['reorder_level']; ?></td>
        <td><?php echo $m['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
        <td>
          <?php if (in_array($role, ['Administrator','Site Staff'])): ?>
          <a href="/ccms/materials/edit.php?id=<?php echo $m['material_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
