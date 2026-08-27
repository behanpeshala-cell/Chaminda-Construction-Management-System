<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Procurement Staff']);
$page_title = 'New Supplier';
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
        $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?,?,?,?,?)")
            ->execute([$name,$contact,$phone,$email,$address]);
        audit($pdo,'CREATE','suppliers',(int)$pdo->lastInsertId());
        set_flash('success','Supplier added.');
        redirect('/ccms/suppliers/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <div class="mb-3"><label class="form-label required">Supplier Name</label>
      <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label">Contact Person</label>
      <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>
    <div class="mb-3"><label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea></div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Supplier</button>
    <a href="/ccms/suppliers/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
