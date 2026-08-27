<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff','Project Manager']);
$page_title = 'New Material Request';

$projects = $pdo->query("SELECT project_id, project_name FROM projects WHERE status IN ('Planned','Ongoing') ORDER BY project_name")->fetchAll();
$materials = $pdo->query("SELECT material_id, name, unit FROM materials WHERE status='active' ORDER BY name")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $project_id = (int)($_POST['project_id'] ?? 0);
    $material_id = (int)($_POST['material_id'] ?? 0);
    $qty = (int)($_POST['quantity_requested'] ?? 0);

    if (!$project_id) $errors[] = 'Please select a project.';
    if (!$material_id) $errors[] = 'Please select a material.';
    if ($qty <= 0) $errors[] = 'Quantity requested must be greater than zero.';

    if (!$errors) {
        $pdo->prepare("INSERT INTO material_requests (project_id, material_id, quantity_requested, requested_by) VALUES (?,?,?,?)")
            ->execute([$project_id, $material_id, $qty, current_user_id()]);
        audit($pdo, 'CREATE', 'material_requests', (int)$pdo->lastInsertId());
        set_flash('success', 'Material request submitted for approval.');
        redirect('/ccms/material_requests/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <div class="mb-3">
      <label class="form-label required">Project</label>
      <select name="project_id" class="form-select" required>
        <option value="">-- Select project --</option>
        <?php foreach ($projects as $p): ?><option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['project_name']); ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label required">Material</label>
      <select name="material_id" class="form-select" required>
        <option value="">-- Select material --</option>
        <?php foreach ($materials as $m): ?><option value="<?php echo $m['material_id']; ?>"><?php echo htmlspecialchars($m['name']); ?> (<?php echo htmlspecialchars($m['unit']); ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label required">Quantity Requested</label>
      <input type="number" min="1" name="quantity_requested" class="form-control" required>
    </div>
    <button class="btn btn-success"><i class="bi bi-send"></i> Submit Request</button>
    <a href="/ccms/material_requests/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
