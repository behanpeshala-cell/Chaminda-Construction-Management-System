<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Administrator','Project Manager']);
$page_title = 'New Employee';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name       = trim($_POST['full_name'] ?? '');
    $nic_number = strtoupper(trim($_POST['nic_number'] ?? ''));
    $role_title = trim($_POST['role_title'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($name === '') $errors[] = 'Employee name is required.';
    
    // NIC Validation
    if ($nic_number !== '') {
        if (!validate_sri_lankan_nic($nic_number)) {
            $errors[] = 'Please enter a valid Sri Lankan NIC (9 digits + V/X or 12 digits).';
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE nic_number=?");
            $dup->execute([$nic_number]);
            if ($dup->fetchColumn() > 0) {
                $errors[] = 'That NIC number is already assigned to another employee.';
            }
        }
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone !== '' && (!preg_match('/^\+?[0-9\s\-\(\)]{9,15}$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 9)) {
        $errors[] = 'Please enter a valid phone number (9-15 digits).';
    }

    if (!$errors) {
        $pdo->prepare("INSERT INTO employees (full_name, nic_number, role_title, phone, email) VALUES (?,?,?,?,?)")
            ->execute([$name, $nic_number !== '' ? $nic_number : null, $role_title, $phone, $email]);
        
        $empId = (int)$pdo->lastInsertId();
        audit($pdo, 'CREATE', 'employees', $empId);
        
        create_notification(
            $pdo,
            'New Employee Registered',
            "Employee '$name' " . ($nic_number ? "(NIC: $nic_number) " : "") . "added successfully as $role_title.",
            'success',
            '/ccms/employees/list.php'
        );

        redirect('/ccms/employees/list.php');
    }
}
require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="card p-4" style="max-width:560px">
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    
    <div class="mb-3">
      <label class="form-label required">Full Name</label>
      <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">NIC Number</label>
      <input type="text" name="nic_number" class="form-control" placeholder="e.g. 199512345678 or 123456789V" maxlength="12" value="<?php echo htmlspecialchars($_POST['nic_number'] ?? ''); ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Role / Title</label>
      <input type="text" name="role_title" class="form-control" placeholder="e.g. Mason, Site Supervisor" value="<?php echo htmlspecialchars($_POST['role_title'] ?? ''); ?>">
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="tel" name="phone" class="form-control" placeholder="e.g. 0771234567 or +94771234567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="employee@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>
    </div>

    <button class="btn btn-success"><i class="bi bi-check-lg"></i> Save Employee</button>
    <a href="/ccms/employees/list.php" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
