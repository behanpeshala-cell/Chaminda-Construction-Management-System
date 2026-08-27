<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Finance Officer']);
$page_title = 'Payments';

$projects = $pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $project_id = (int)$_POST['project_id'];
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['payment_type'] ?? '';
    $date = $_POST['payment_date'] ?? '';

    if (!$project_id) $errors[] = 'Please select a project.';
    if (!is_numeric($amount) || $amount <= 0) $errors[] = 'Amount must be a positive value.';
    if (!$date) $errors[] = 'Payment date is required.';
    if (!in_array($type, ['Client Payment','Supplier Payment','Other'], true)) $errors[] = 'Please select a payment type.';

    if (!$errors) {
        $pdo->prepare("INSERT INTO payments (project_id, amount, payment_type, payment_date, recorded_by) VALUES (?,?,?,?,?)")
            ->execute([$project_id,$amount,$type,$date,current_user_id()]);
        audit($pdo,'CREATE','payments',(int)$pdo->lastInsertId());
        set_flash('success','Payment recorded.');
        redirect('/ccms/finance/payments.php');
    }
}

$payments = $pdo->query("SELECT pay.*, p.project_name, u.full_name FROM payments pay
                          JOIN projects p ON p.project_id=pay.project_id
                          JOIN users u ON u.user_id=pay.recorded_by
                          ORDER BY pay.created_at DESC LIMIT 100")->fetchAll();

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h6>Record Payment</h6>
      <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="mb-2">
          <label class="form-label small required">Project</label>
          <select name="project_id" class="form-select form-select-sm" required>
            <option value="">-- Select --</option>
            <?php foreach ($projects as $p): ?><option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['project_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small required">Payment Type</label>
          <select name="payment_type" class="form-select form-select-sm">
            <option>Client Payment</option><option>Supplier Payment</option><option>Other</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small required">Amount (LKR)</label>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm" required>
        </div>
        <div class="mb-2">
          <label class="form-label small required">Payment Date</label>
          <input type="date" name="payment_date" class="form-control form-control-sm" required>
        </div>
        <button class="btn btn-sm btn-success w-100"><i class="bi bi-check-lg"></i> Record Payment</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card p-3">
      <h6>Recent Payments</h6>
      <div class="table-responsive">
      <table class="table table-sm table-hover align-middle">
        <thead><tr><th>Date</th><th>Project</th><th>Type</th><th>Amount</th><th>By</th></tr></thead>
        <tbody>
        <?php if (!$payments): ?><tr><td colspan="5" class="text-center text-muted py-4">No payments recorded yet.</td></tr><?php endif; ?>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?php echo $p['payment_date']; ?></td>
            <td><?php echo htmlspecialchars($p['project_name']); ?></td>
            <td><?php echo htmlspecialchars($p['payment_type']); ?></td>
            <td><?php echo number_format($p['amount'],2); ?></td>
            <td><?php echo htmlspecialchars($p['full_name']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
