<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Site Staff']);
$page_title = 'Record Stock Movement';

$materials = $pdo->query("SELECT m.material_id, m.name, m.unit, COALESCE(i.quantity_on_hand,0) qty
                           FROM materials m LEFT JOIN inventory i ON i.material_id=m.material_id
                           WHERE m.status='active' ORDER BY m.name")->fetchAll();
$projects = $pdo->query("SELECT project_id, project_name FROM projects WHERE status IN ('Planned','Ongoing') ORDER BY project_name")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $material_id = (int)($_POST['material_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $qty = (int)($_POST['quantity'] ?? 0);
    $project_id = $_POST['project_id'] !== '' ? (int)$_POST['project_id'] : null;
    $reference = trim($_POST['reference'] ?? '');

    if (!$material_id) $errors[] = 'Please select a material.';
    if (!in_array($type, ['IN','OUT'], true)) $errors[] = 'Please select stock in or stock out.';
    if ($qty <= 0) $errors[] = 'Quantity must be greater than zero.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT quantity_on_hand FROM inventory WHERE material_id=?");
        $stmt->execute([$material_id]);
        $current = (int)($stmt->fetchColumn() ?: 0);

        if ($type === 'OUT' && $qty > $current) {
            $errors[] = "Insufficient stock: only $current unit(s) available. Negative stock balances are not permitted.";
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();
        $delta = $type === 'IN' ? $qty : -$qty;
        $pdo->prepare("UPDATE inventory SET quantity_on_hand = quantity_on_hand + ? WHERE material_id=?")
            ->execute([$delta, $material_id]);
        $pdo->prepare("INSERT INTO stock_transactions (material_id, type, quantity, reference, project_id, created_by) VALUES (?,?,?,?,?,?)")
            ->execute([$material_id, $type, $qty, $reference, $project_id, current_user_id()]);
        $pdo->commit();
        audit($pdo, 'STOCK_' . $type, 'stock_transactions', $material_id);
        set_flash('success', 'Stock movement recorded.');
        redirect('/ccms/inventory/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <div class="mb-3">
      <label class="form-label required">Material</label>
      <select name="material_id" class="form-select" required onchange="showQty(this)">
        <option value="">-- Select material --</option>
        <?php foreach ($materials as $m): ?>
          <option value="<?php echo $m['material_id']; ?>" data-qty="<?php echo $m['qty']; ?>" data-unit="<?php echo htmlspecialchars($m['unit']); ?>">
            <?php echo htmlspecialchars($m['name']); ?> (on hand: <?php echo $m['qty']; ?> <?php echo htmlspecialchars($m['unit']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label required">Movement Type</label>
      <select name="type" class="form-select" required>
        <option value="IN">Stock In (received)</option>
        <option value="OUT">Stock Out (issued to project)</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label required">Quantity</label>
      <input type="number" min="1" name="quantity" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Project (for stock out)</label>
      <select name="project_id" class="form-select">
        <option value="">-- N/A --</option>
        <?php foreach ($projects as $p): ?>
          <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['project_name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Reference / Note</label>
      <input type="text" name="reference" class="form-control" placeholder="e.g. PO-1023 delivery, site usage">
    </div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Movement</button>
    <a href="/ccms/inventory/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
