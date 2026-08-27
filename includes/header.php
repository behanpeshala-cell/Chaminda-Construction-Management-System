<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - CCMS' : 'CCMS'; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="/ccms/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Fixed top header -->
<nav class="navbar navbar-dark ccms-topbar fixed-top">
  <div class="container-fluid">
    <button class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" onclick="document.querySelector('.ccms-sidebar').classList.toggle('show')">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand fw-bold" href="/ccms/dashboard.php"><i class="bi bi-buildings"></i> CCMS</a>
    <div class="d-flex align-items-center text-white">
      <span class="me-3 d-none d-sm-inline">
        <i class="bi bi-person-circle"></i>
        <?php echo htmlspecialchars(current_name()); ?>
        <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars(current_role()); ?></span>
      </span>
      <a href="/ccms/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>
