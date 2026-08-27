<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager','Site Staff','Client']);
$page_title = 'Projects';
$role = current_role();
$page_actions = in_array($role, ['Administrator','Project Manager'])
  ? '<a href="/ccms/projects/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Project</a>' : '';

$search = trim($_GET['q'] ?? '');
if ($role === 'Client') {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS client_name FROM projects p JOIN clients c ON c.client_id=p.client_id
                            WHERE c.email = (SELECT email FROM users WHERE user_id=?) AND p.project_name LIKE ?
                            ORDER BY p.created_at DESC");
    $stmt->execute([current_user_id(), "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS client_name FROM projects p JOIN clients c ON c.client_id=p.client_id
                            WHERE p.project_name LIKE ? ORDER BY p.created_at DESC");
    $stmt->execute(["%$search%"]);
}
$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search projects" value="<?php echo htmlspecialchars($search); ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
  </form>
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Project</th><th>Client</th><th>Type</th><th>Location</th><th>Budget (LKR)</th><th>Status</th><th>Progress</th><th></th></tr></thead>
    <tbody>
    <?php if (!$projects): ?><tr><td colspan="8" class="text-center text-muted py-4">No projects found.</td></tr><?php endif; ?>
    <?php foreach ($projects as $p): ?>
      <tr>
        <td><?php echo htmlspecialchars($p['project_name']); ?></td>
        <td><?php echo htmlspecialchars($p['client_name']); ?></td>
        <td><?php echo htmlspecialchars($p['project_type']); ?></td>
        <td><?php echo htmlspecialchars($p['location']); ?></td>
        <td><?php echo number_format($p['estimated_budget'],2); ?></td>
        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['status']); ?></span></td>
        <td style="min-width:120px">
          <div class="progress"><div class="progress-bar bg-success" style="width:<?php echo (int)$p['progress_percent']; ?>%"></div></div>
          <small><?php echo (int)$p['progress_percent']; ?>%</small>
        </td>
        <td>
          <a href="/ccms/projects/view.php?id=<?php echo $p['project_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
          <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
          <a href="/ccms/projects/edit.php?id=<?php echo $p['project_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
