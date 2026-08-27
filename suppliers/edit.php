<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff']);
$page_title = 'Edit Supplier';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id=?"); $stmt->execute([$id]);
$sup = $stmt->fetch();
if (!$sup) { set_flash('danger','Supplier not found.'); redirect('/ccms/suppliers/list.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($name === '') $errors[] = 'Supplier name is required.';
    if (!$errors) {
        $pdo->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE supplier_id=?")
            ->execute([$name,$contact,$phone,$email,$address,$id]);
        audit($pdo,'UPDATE','suppliers',$id);
        set_flash('success','Supplier updated.');
        redirect('/ccms/suppliers/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $sup['supplier_id']; ?>">
    <div class="mb-3"><label class="form-label required">Supplier Name</label>
      <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? $sup['name']); ?>"></div>
    <div class="mb-3"><label class="form-label">Contact Person</label>
      <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? $sup['contact_person']); ?>"></div>
    <div class="mb-3"><label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? $sup['phone']); ?>"></div>
    <div class="mb-3"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $sup['email']); ?>"></div>
    <div class="mb-3"><label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? $sup['address']); ?></textarea></div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="/ccms/suppliers/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
