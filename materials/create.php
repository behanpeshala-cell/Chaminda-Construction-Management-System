<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff']);
$page_title = 'New Material';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $price = $_POST['unit_price'] ?? 0;
    $reorder = $_POST['reorder_level'] ?? 0;
    $opening = (int)($_POST['opening_stock'] ?? 0);

    if ($name === '') $errors[] = 'Material name is required.';
    if ($unit === '') $errors[] = 'Unit of measure is required.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Unit price must be a non-negative number.';

    if (!$errors) {
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO materials (name, unit, unit_price, reorder_level) VALUES (?,?,?,?)")
            ->execute([$name,$unit,$price,$reorder]);
        $material_id = (int)$pdo->lastInsertId();
        $pdo->prepare("INSERT INTO inventory (material_id, quantity_on_hand) VALUES (?,?)")->execute([$material_id, $opening]);
        if ($opening > 0) {
            $pdo->prepare("INSERT INTO stock_transactions (material_id, type, quantity, reference, created_by) VALUES (?, 'IN', ?, 'Opening stock', ?)")
                ->execute([$material_id, $opening, current_user_id()]);
        }
        $pdo->commit();
        audit($pdo,'CREATE','materials',$material_id);
        set_flash('success','Material added to catalogue.');
        redirect('/ccms/materials/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <div class="mb-3"><label class="form-label required">Material Name</label>
      <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label required">Unit of Measure</label>
      <input type="text" name="unit" class="form-control" placeholder="e.g. bag, kg, m3" required value="<?php echo htmlspecialchars($_POST['unit'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label required">Unit Price (LKR)</label>
      <input type="number" step="0.01" min="0" name="unit_price" class="form-control" required value="<?php echo htmlspecialchars($_POST['unit_price'] ?? '0'); ?>"></div>
    <div class="mb-3"><label class="form-label">Reorder Level</label>
      <input type="number" min="0" name="reorder_level" class="form-control" value="<?php echo htmlspecialchars($_POST['reorder_level'] ?? '0'); ?>"></div>
    <div class="mb-3"><label class="form-label">Opening Stock</label>
      <input type="number" min="0" name="opening_stock" class="form-control" value="<?php echo htmlspecialchars($_POST['opening_stock'] ?? '0'); ?>"></div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Material</button>
    <a href="/ccms/materials/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
