<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
$page_title = 'Edit Client';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id=?"); $stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) { set_flash('danger','Client not found.'); redirect('/ccms/clients/list.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($name === '') $errors[] = 'Client name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone !== '' && (!preg_match('/^\+?[0-9\s\-\(\)]{9,15}$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 9)) {
        $errors[] = 'Please enter a valid phone number (9-15 digits).';
    }
    if (!$errors) {
        $pdo->prepare("UPDATE clients SET name=?, email=?, phone=?, address=? WHERE client_id=?")
            ->execute([$name,$email,$phone,$address,$id]);
        audit($pdo,'UPDATE','clients',$id);
        set_flash('success','Client updated.');
        redirect('/ccms/clients/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $client['client_id']; ?>">
    <div class="mb-3"><label class="form-label required">Client Name</label>
      <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? $client['name']); ?>"></div>
    <div class="mb-3"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" placeholder="client@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? $client['email']); ?>"></div>
    <div class="mb-3"><label class="form-label">Phone</label>
      <input type="tel" name="phone" class="form-control" placeholder="e.g. 0771234567 or +94771234567" value="<?php echo htmlspecialchars($_POST['phone'] ?? $client['phone']); ?>"></div>
    <div class="mb-3"><label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? $client['address']); ?></textarea></div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="/ccms/clients/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
