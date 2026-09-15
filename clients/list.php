<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
$page_title = 'Clients';
$page_actions = '<a href="/ccms/clients/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Client</a>';

$search = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM clients WHERE name LIKE ? OR email LIKE ? OR nic_number LIKE ? ORDER BY created_at DESC");
$like = "%$search%";
$stmt->execute([$like, $like, $like]);
$clients = $stmt->fetchAll();

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search by name, email or NIC" value="<?php echo htmlspecialchars($search); ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Search</button></div>
  </form>
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Name</th><th>NIC</th><th>Gender / DOB</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (!$clients): ?><tr><td colspan="7" class="text-center text-muted py-4">No clients found.</td></tr><?php endif; ?>
    <?php foreach ($clients as $c): ?>
      <tr>
        <td><?php echo htmlspecialchars($c['name']); ?></td>
        <td><code><?php echo htmlspecialchars($c['nic_number'] ?? '—'); ?></code></td>
        <td>
          <?php echo htmlspecialchars($c['gender'] ?? '—'); ?>
          <?php if (!empty($c['date_of_birth'])): ?>
            <br><small class="text-muted"><?php echo htmlspecialchars($c['date_of_birth']); ?></small>
          <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($c['email']); ?></td>
        <td><?php echo htmlspecialchars($c['phone']); ?></td>
        <td><?php echo $c['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
        <td>
          <a href="/ccms/clients/edit.php?id=<?php echo $c['client_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <form action="/ccms/clients/toggle_status.php" method="post" class="d-inline" data-confirm="Change status of this client?">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $c['client_id']; ?>">
            <button class="btn btn-sm btn-outline-warning"><i class="bi bi-power"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
