<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Finance Officer']);
$page_title = 'Project Budgets';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $project_id = (int)$_POST['project_id'];
    $amount = $_POST['allocated_amount'];
    if (!is_numeric($amount) || $amount <= 0) {
        $errors[] = 'Allocated amount must be a positive value.';
    } else {
        $exists = $pdo->prepare("SELECT budget_id FROM budgets WHERE project_id=?");
        $exists->execute([$project_id]);
        if ($exists->fetchColumn()) {
            $pdo->prepare("UPDATE budgets SET allocated_amount=? WHERE project_id=?")->execute([$amount,$project_id]);
        } else {
            $pdo->prepare("INSERT INTO budgets (project_id, allocated_amount) VALUES (?,?)")->execute([$project_id,$amount]);
        }
        audit($pdo,'BUDGET_SET','budgets',$project_id);
        set_flash('success','Budget saved.');
        redirect('/ccms/finance/budgets.php');
    }
}

$rows = $pdo->query("SELECT p.project_id, p.project_name, p.estimated_budget, b.allocated_amount,
                             COALESCE((SELECT SUM(amount) FROM expenses e WHERE e.project_id=p.project_id),0) AS spent
                      FROM projects p LEFT JOIN budgets b ON b.project_id=p.project_id
                      ORDER BY p.created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/page_start.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
<div class="card p-3">
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Project</th><th>Estimated Budget</th><th>Allocated Budget</th><th>Spent</th><th>Remaining</th><th>Set / Update</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $alloc = (float)($r['allocated_amount'] ?? 0); $remaining = $alloc - $r['spent']; ?>
      <tr class="<?php echo $remaining < 0 ? 'table-danger' : ''; ?>">
        <td><?php echo htmlspecialchars($r['project_name']); ?></td>
        <td><?php echo number_format($r['estimated_budget'],2); ?></td>
        <td><?php echo number_format($alloc,2); ?></td>
        <td><?php echo number_format($r['spent'],2); ?></td>
        <td><?php echo number_format($remaining,2); ?></td>
        <td>
          <form method="post" class="d-flex gap-1">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="project_id" value="<?php echo $r['project_id']; ?>">
            <input type="number" step="0.01" min="0.01" name="allocated_amount" class="form-control form-control-sm" style="width:140px" value="<?php echo $alloc; ?>" required>
            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
