<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff']);
$page_title = 'Suppliers';
$page_actions = '<a href="/ccms/suppliers/create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Supplier</a>';

$search = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE name LIKE ? ORDER BY created_at DESC");
$stmt->execute(["%$search%"]);
$suppliers = $stmt->fetchAll();
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-3">
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search suppliers" value="<?php echo htmlspecialchars($search); ?>"></div>
    <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
  </form>
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (!$suppliers): ?><tr><td colspan="6" class="text-center text-muted py-4">No suppliers found.</td></tr><?php endif; ?>
    <?php foreach ($suppliers as $s): ?>
      <tr>
        <td><?php echo htmlspecialchars($s['name']); ?></td>
        <td><?php echo htmlspecialchars($s['contact_person']); ?></td>
        <td><?php echo htmlspecialchars($s['phone']); ?></td>
        <td><?php echo htmlspecialchars($s['email']); ?></td>
        <td><?php echo $s['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
        <td>
          <a href="/ccms/suppliers/edit.php?id=<?php echo $s['supplier_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <form action="/ccms/suppliers/toggle_status.php" method="post" class="d-inline" data-confirm="Change status of this supplier?">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $s['supplier_id']; ?>">
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
