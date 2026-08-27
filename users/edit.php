<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator']);
$page_title = 'Edit User';
$roles = ['Administrator','Project Manager','Finance Officer','Procurement Staff','Site Staff','Client'];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { set_flash('danger','User not found.'); redirect('/ccms/users/list.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $role      = $_POST['role'] ?? '';
    $newpass   = $_POST['password'] ?? '';

    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (!in_array($role, $roles, true)) $errors[] = 'Please select a valid role.';
    if ($newpass !== '' && strlen($newpass) < 8) $errors[] = 'New password must be at least 8 characters.';

    if (!$errors) {
        if ($newpass !== '') {
            $hash = password_hash($newpass, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, password_hash=? WHERE user_id=?")
                ->execute([$full_name, $email, $role, $hash, $id]);
        } else {
            $pdo->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE user_id=?")
                ->execute([$full_name, $email, $role, $id]);
        }
        audit($pdo, 'UPDATE', 'users', $id);
        set_flash('success', 'User updated.');
        redirect('/ccms/users/list.php');
    }
}

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $user['user_id']; ?>">
    <div class="mb-3">
      <label class="form-label required">Full Name</label>
      <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name']); ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label required">Email</label>
      <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>">
    </div>
    <div class="mb-3">
      <label class="form-label required">Role</label>
      <select name="role" class="form-select" required>
        <?php foreach ($roles as $r): ?>
          <option value="<?php echo $r; ?>" <?php echo (($_POST['role'] ?? $user['role']) === $r) ? 'selected' : ''; ?>><?php echo $r; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Reset Password (optional)</label>
      <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
    </div>
    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="/ccms/users/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
