<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/ccms/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your username/email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && $user['status'] !== 'active') {
            $error = 'Invalid username or password.'; // do not reveal deactivated status
        } elseif ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
            $error = "Account temporarily locked due to multiple failed attempts. Try again in {$mins} minute(s).";
        } elseif ($user && password_verify($password, $user['password_hash'])) {
            // success: reset failed attempts, start session
            $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE user_id = ?")
                ->execute([$user['user_id']]);

            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['last_activity'] = time();

            audit($pdo, 'LOGIN', 'users', $user['user_id']);
            redirect('/ccms/dashboard.php');
        } else {
            // failed attempt -- generic message either way (username enumeration protection)
            $error = 'Invalid username or password.';
            if ($user) {
                $attempts = $user['failed_attempts'] + 1;
                $locked_until = null;
                if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                    $locked_until = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'));
                    $error = 'Too many failed attempts. Your account has been temporarily locked for ' . LOCKOUT_MINUTES . ' minutes.';
                }
                $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE user_id = ?")
                    ->execute([$attempts, $locked_until, $user['user_id']]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - CCMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="/ccms/assets/css/style.css" rel="stylesheet">
</head>
<body class="ccms-auth-wrapper">
  <div class="card ccms-auth-card">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <i class="bi bi-buildings" style="font-size:2.5rem;color:var(--ccms-primary)"></i>
        <h4 class="mt-2 mb-0">CCMS</h4>
        <small class="text-muted">Chaminda Construction Company</small>
      </div>

      <?php if (!empty($_GET['timeout'])): ?>
        <div class="alert alert-warning py-2">You were logged out due to inactivity.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="mb-3">
          <label class="form-label required">Username / Email</label>
          <input type="text" name="identifier" class="form-control" required autofocus
                 value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>">
        </div>
        <div class="mb-2">
          <label class="form-label required">Password</label>
          <input type="password" name="password" id="loginPassword" class="form-control" required>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="showPass" data-toggle-password="#loginPassword">
          <label class="form-check-label" for="showPass">Show password</label>
        </div>
        <button type="submit" class="btn btn-success w-100" style="background:var(--ccms-primary);border-color:var(--ccms-primary)">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
        <div class="text-center mt-3">
          <a href="/ccms/forgot_password.php" class="small text-muted">Forgot Password?</a>
        </div>
      </form>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ccms/assets/js/script.js"></script>
</body>
</html>
