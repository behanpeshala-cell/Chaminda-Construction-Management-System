<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$page_title = 'Dashboard';

$role = current_role();

// ---- KPI queries (guarded so the dashboard still renders on an empty DB) ----
function count_rows(PDO $pdo, string $sql, array $params = []) {
    try { $s = $pdo->prepare($sql); $s->execute($params); return (int)$s->fetchColumn(); }
    catch (Exception $e) { return 0; }
}
function sum_col(PDO $pdo, string $sql, array $params = []) {
    try { $s = $pdo->prepare($sql); $s->execute($params); return (float)$s->fetchColumn(); }
    catch (Exception $e) { return 0.0; }
}

$totalProjects   = count_rows($pdo, "SELECT COUNT(*) FROM projects");
$ongoingProjects = count_rows($pdo, "SELECT COUNT(*) FROM projects WHERE status='Ongoing'");
$totalClients    = count_rows($pdo, "SELECT COUNT(*) FROM clients WHERE status='active'");
$lowStockCount   = count_rows($pdo, "SELECT COUNT(*) FROM inventory i JOIN materials m ON m.material_id=i.material_id WHERE i.quantity_on_hand <= m.reorder_level");
$pendingRequests = count_rows($pdo, "SELECT COUNT(*) FROM material_requests WHERE status='Pending'");
$pendingPOs      = count_rows($pdo, "SELECT COUNT(*) FROM purchase_orders WHERE status IN ('Pending','Approved')");
$totalBudget     = sum_col($pdo, "SELECT COALESCE(SUM(allocated_amount),0) FROM budgets");
$totalExpense    = sum_col($pdo, "SELECT COALESCE(SUM(amount),0) FROM expenses");

try {
    $projByStatus = $pdo->query("SELECT status, COUNT(*) c FROM projects GROUP BY status")->fetchAll();
} catch (Exception $e) { $projByStatus = []; }
try {
    $expByCategory = $pdo->query("SELECT category, SUM(amount) total FROM expenses GROUP BY category")->fetchAll();
} catch (Exception $e) { $expByCategory = []; }

try {
    $myProjects = [];
    if ($role === 'Client') {
        $stmt = $pdo->prepare("SELECT p.* FROM projects p JOIN clients c ON c.client_id=p.client_id
                                WHERE c.email = (SELECT email FROM users WHERE user_id=?)");
        $stmt->execute([current_user_id()]);
        $myProjects = $stmt->fetchAll();
    } elseif ($role === 'Project Manager') {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_manager_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([current_user_id()]);
        $myProjects = $stmt->fetchAll();
    } else {
        $myProjects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5")->fetchAll();
    }
} catch (Exception $e) { $myProjects = []; }

require_once __DIR__ . '/includes/page_start.php';
?>

<div class="row g-3 mb-2">
  <?php if (in_array($role, ['Administrator','Project Manager','Client','Site Staff'])): ?>
  <div class="col-6 col-md-3">
    <div class="card ccms-stat-card bg-ccms-1 p-3">
      <div class="small">Total Projects</div>
      <div class="stat-value"><?php echo $totalProjects; ?></div>
      <div class="small opacity-75"><?php echo $ongoingProjects; ?> ongoing</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
  <div class="col-6 col-md-3">
    <div class="card ccms-stat-card bg-ccms-2 p-3">
      <div class="small">Active Clients</div>
      <div class="stat-value"><?php echo $totalClients; ?></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Site Staff','Project Manager'])): ?>
  <div class="col-6 col-md-3">
    <div class="card ccms-stat-card bg-ccms-3 p-3">
      <div class="small">Low Stock Materials</div>
      <div class="stat-value"><?php echo $lowStockCount; ?></div>
      <div class="small opacity-75"><?php echo $pendingRequests; ?> pending requests</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
  <div class="col-6 col-md-3">
    <div class="card ccms-stat-card bg-ccms-4 p-3">
      <div class="small">Budget vs Spent (LKR)</div>
      <div class="stat-value" style="font-size:1.2rem"><?php echo number_format($totalExpense,0); ?> / <?php echo number_format($totalBudget,0); ?></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Procurement Staff'])): ?>
  <div class="col-6 col-md-3">
    <div class="card ccms-stat-card bg-ccms-5 p-3">
      <div class="small">Open Purchase Orders</div>
      <div class="stat-value"><?php echo $pendingPOs; ?></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="row g-3 mt-1">
  <?php if (in_array($role, ['Administrator','Project Manager','Site Staff'])): ?>
  <div class="col-md-6">
    <div class="card p-3">
      <h6>Projects by Status</h6>
      <canvas id="projStatusChart" height="180"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
  <div class="col-md-6">
    <div class="card p-3">
      <h6>Expenses by Category</h6>
      <canvas id="expCategoryChart" height="180"></canvas>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card p-3 mt-3">
  <h6><?php echo $role === 'Client' ? 'Your Projects' : 'Recent Projects'; ?></h6>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Project</th><th>Type</th><th>Status</th><th>Progress</th></tr></thead>
      <tbody>
      <?php if (!$myProjects): ?>
        <tr><td colspan="4" class="text-muted text-center py-3">No projects to display yet.</td></tr>
      <?php else: foreach ($myProjects as $p): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['project_name']); ?></td>
          <td><?php echo htmlspecialchars($p['project_type']); ?></td>
          <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['status']); ?></span></td>
          <td style="min-width:140px">
            <div class="progress"><div class="progress-bar bg-success" style="width:<?php echo (int)$p['progress_percent']; ?>%"></div></div>
            <small><?php echo (int)$p['progress_percent']; ?>%</small>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
if (typeof Chart !== 'undefined') {
  Chart.defaults.color = '#cbd5e1';
  Chart.defaults.borderColor = 'rgba(51, 65, 85, 0.4)';
}
<?php if (in_array($role, ['Administrator','Project Manager','Site Staff'])): ?>
new Chart(document.getElementById('projStatusChart'), {
  type: 'doughnut',
  data: {
    labels: <?php echo json_encode(array_column($projByStatus,'status')); ?>,
    datasets: [{ data: <?php echo json_encode(array_column($projByStatus,'c')); ?>,
      backgroundColor: ['#10b981','#06b6d4','#f59e0b','#3b82f6','#f43f5e'] }]
  },
  options: { plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1' } } } }
});
<?php endif; ?>

<?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
new Chart(document.getElementById('expCategoryChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode(array_column($expByCategory,'category')); ?>,
    datasets: [{ label: 'LKR', data: <?php echo json_encode(array_column($expByCategory,'total')); ?>,
      backgroundColor: '#10b981' }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(51, 65, 85, 0.4)' } },
      y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(51, 65, 85, 0.4)' } }
    }
  }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
