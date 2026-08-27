<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
$page_title = 'Edit Employee';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id=?"); $stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) { set_flash('danger','Employee not found.'); redirect('/ccms/employees/list.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['full_name'] ?? '');
    $role_title = trim($_POST['role_title'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name === '') $errors[] = 'Employee name is required.';
    if (!$errors) {
        $pdo->prepare("UPDATE employees SET full_name=?, role_title=?, phone=?, email=? WHERE employee_id=?")
            ->execute([$name,$role_title,$phone,$email,$id]);
        audit($pdo,'UPDATE','employees',$id);
        set_flash('success','Employee updated.');
        redirect('/ccms/employees/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $emp['employee_id']; ?>">
    <div class="mb-3"><label class="form-label required">Full Name</label>
      <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? $emp['full_name']); ?>"></div>
    <div class="mb-3"><label class="form-label">Role / Title</label>
      <input type="text" name="role_title" class="form-control" value="<?php echo htmlspecialchars($_POST['role_title'] ?? $emp['role_title']); ?>"></div>
    <div class="mb-3"><label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? $emp['phone']); ?>"></div>
    <div class="mb-3"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $emp['email']); ?>"></div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="/ccms/employees/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
