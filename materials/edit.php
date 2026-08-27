<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff']);
$page_title = 'Edit Material';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM materials WHERE material_id=?"); $stmt->execute([$id]);
$mat = $stmt->fetch();
if (!$mat) { set_flash('danger','Material not found.'); redirect('/ccms/materials/list.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $price = $_POST['unit_price'] ?? 0;
    $reorder = $_POST['reorder_level'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    if ($name === '') $errors[] = 'Material name is required.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Unit price must be a non-negative number.';
    if (!$errors) {
        $pdo->prepare("UPDATE materials SET name=?, unit=?, unit_price=?, reorder_level=?, status=? WHERE material_id=?")
            ->execute([$name,$unit,$price,$reorder,$status,$id]);
        audit($pdo,'UPDATE','materials',$id);
        set_flash('success','Material updated.');
        redirect('/ccms/materials/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $mat['material_id']; ?>">
    <div class="mb-3"><label class="form-label required">Material Name</label>
      <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? $mat['name']); ?>"></div>
    <div class="mb-3"><label class="form-label required">Unit of Measure</label>
      <input type="text" name="unit" class="form-control" required value="<?php echo htmlspecialchars($_POST['unit'] ?? $mat['unit']); ?>"></div>
    <div class="mb-3"><label class="form-label required">Unit Price (LKR)</label>
      <input type="number" step="0.01" min="0" name="unit_price" class="form-control" required value="<?php echo htmlspecialchars($_POST['unit_price'] ?? $mat['unit_price']); ?>"></div>
    <div class="mb-3"><label class="form-label">Reorder Level</label>
      <input type="number" min="0" name="reorder_level" class="form-control" value="<?php echo htmlspecialchars($_POST['reorder_level'] ?? $mat['reorder_level']); ?>"></div>
    <div class="mb-3"><label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="active" <?php echo $mat['status']==='active'?'selected':''; ?>>Active</option>
        <option value="inactive" <?php echo $mat['status']==='inactive'?'selected':''; ?>>Inactive</option>
      </select>
    </div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="/ccms/materials/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
