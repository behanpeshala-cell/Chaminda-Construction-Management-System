<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$role = current_role();
$page_title = 'Reports';

require_once __DIR__ . '/../includes/page_start.php';
?>
<div class="row g-3">

  <?php if (in_array($role, ['Administrator','Project Manager','Site Staff','Client'])): ?>
  <div class="col-md-6 col-lg-3">
    <div class="card p-3 h-100">
      <h6><i class="bi bi-kanban"></i> Project Progress</h6>
      <p class="small text-muted">Status and completion percentage for all projects.</p>
      <a href="/ccms/reports/export_csv.php?type=projects" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Site Staff','Project Manager'])): ?>
  <div class="col-md-6 col-lg-3">
    <div class="card p-3 h-100">
      <h6><i class="bi bi-clipboard-data"></i> Inventory</h6>
      <p class="small text-muted">Current stock levels for every material.</p>
      <a href="/ccms/reports/export_csv.php?type=inventory" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
  <div class="col-md-6 col-lg-3">
    <div class="card p-3 h-100">
      <h6><i class="bi bi-receipt"></i> Financial</h6>
      <p class="small text-muted">Budget vs. actual expenditure per project.</p>
      <a href="/ccms/reports/export_csv.php?type=financial" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['Administrator','Procurement Staff'])): ?>
  <div class="col-md-6 col-lg-3">
    <div class="card p-3 h-100">
      <h6><i class="bi bi-truck-front"></i> Supplier / Purchase Orders</h6>
      <p class="small text-muted">All purchase orders and their status.</p>
      <a href="/ccms/reports/export_csv.php?type=purchase_orders" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
  </div>
  <?php endif; ?>

</div>

<div class="card p-3 mt-3">
  <p class="small text-muted mb-0">
    <i class="bi bi-info-circle"></i> Reports export to CSV directly from your browser. A print-to-PDF option is
    available via your browser's print dialog (Ctrl/Cmd+P &rarr; Save as PDF) once a report is open, satisfying the
    PDF/CSV export requirement in Section 4.1 of the SRS without requiring a separate PDF library.
  </p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
