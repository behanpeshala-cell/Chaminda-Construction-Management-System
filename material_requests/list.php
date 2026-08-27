<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff','Project Manager']);
$role = current_role();
$page_title = 'Material Requests';
$page_actions = '<a href="/ccms/material_requests/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Request</a>';

$requests = $pdo->query("SELECT r.*, p.project_name, m.name AS material_name, m.unit, u1.full_name AS requested_by_name, u2.full_name AS approved_by_name
                          FROM material_requests r
                          JOIN projects p ON p.project_id=r.project_id
                          JOIN materials m ON m.material_id=r.material_id
                          JOIN users u1 ON u1.user_id=r.requested_by
                          LEFT JOIN users u2 ON u2.user_id=r.approved_by
                          ORDER BY r.created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Date</th><th>Project</th><th>Material</th><th>Qty</th><th>Status</th><th>Requested By</th><th>Decision</th><?php if (in_array($role,['Administrator','Project Manager'])): ?><th>Actions</th><?php endif; ?></tr></thead>
    <tbody>
    <?php if (!$requests): ?><tr><td colspan="8" class="text-center text-muted py-4">No material requests yet.</td></tr><?php endif; ?>
    <?php foreach ($requests as $r): ?>
      <tr>
        <td><?php echo $r['created_at']; ?></td>
        <td><?php echo htmlspecialchars($r['project_name']); ?></td>
        <td><?php echo htmlspecialchars($r['material_name']); ?></td>
        <td><?php echo (int)$r['quantity_requested']; ?> <?php echo htmlspecialchars($r['unit']); ?></td>
        <td>
          <?php
          $badge = ['Pending'=>'secondary','Approved'=>'info','Rejected'=>'danger','Issued'=>'success'][$r['status']] ?? 'secondary';
          echo "<span class='badge bg-$badge'>{$r['status']}</span>";
          ?>
        </td>
        <td><?php echo htmlspecialchars($r['requested_by_name']); ?></td>
        <td><?php echo htmlspecialchars($r['approved_by_name'] ?? '—'); ?></td>
        <?php if (in_array($role,['Administrator','Project Manager'])): ?>
        <td>
          <?php if ($r['status'] === 'Pending'): ?>
            <form action="/ccms/material_requests/decide.php" method="post" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
              <input type="hidden" name="id" value="<?php echo $r['request_id']; ?>">
              <input type="hidden" name="decision" value="Approved">
              <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
            </form>
            <form action="/ccms/material_requests/decide.php" method="post" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
              <input type="hidden" name="id" value="<?php echo $r['request_id']; ?>">
              <input type="hidden" name="decision" value="Rejected">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
            </form>
          <?php elseif ($r['status'] === 'Approved'): ?>
            <form action="/ccms/material_requests/issue.php" method="post" class="d-inline" data-confirm="Issue this material and deduct stock?">
              <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
              <input type="hidden" name="id" value="<?php echo $r['request_id']; ?>">
              <button class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up"></i> Issue</button>
            </form>
          <?php else: ?>—<?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
