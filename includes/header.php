<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - CCMS' : 'CCMS Enterprise System'; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="/ccms/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Fixed top header -->
<nav class="navbar navbar-dark ccms-topbar fixed-top">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center">
      <button class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" onclick="document.querySelector('.ccms-sidebar').classList.toggle('show')">
        <i class="bi bi-list fs-5"></i>
      </button>
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/ccms/dashboard.php">
        <i class="bi bi-buildings-fill text-warning"></i>
        <span>CCMS <span class="badge bg-warning text-dark fs-6 font-monospace ms-1" style="font-size:0.65rem !important">PRO</span></span>
      </a>
    </div>
    <div class="d-flex align-items-center text-white gap-3">
      <div class="d-none d-sm-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-pill border border-light border-opacity-25">
        <i class="bi bi-person-circle fs-6"></i>
        <span class="fw-medium small"><?php echo htmlspecialchars(current_name()); ?></span>
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-1" style="font-size:0.7rem"><?php echo htmlspecialchars(current_role()); ?></span>
      </div>
      <a href="/ccms/logout.php" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 rounded-pill px-3">
        <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
      </a>
    </div>
  </div>
</nav>

