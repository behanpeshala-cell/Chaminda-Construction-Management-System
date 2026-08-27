<?php
$role = current_role();
$current = basename($_SERVER['PHP_SELF']);
function nav_active($file, $current) { return $file === $current ? 'active' : ''; }
?>
<div class="ccms-sidebar">
  <ul class="nav flex-column">

    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('dashboard.php',$current); ?>" href="/ccms/dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>

    <?php if (in_array($role, ['Administrator'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/users/list.php"><i class="bi bi-person-badge"></i> Users</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/clients/list.php"><i class="bi bi-person-lines-fill"></i> Clients</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager','Site Staff','Client'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/projects/list.php"><i class="bi bi-kanban"></i> Projects</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/employees/list.php"><i class="bi bi-people"></i> Employees</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Site Staff','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/materials/list.php"><i class="bi bi-box-seam"></i> Materials</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/inventory/list.php"><i class="bi bi-clipboard-data"></i> Inventory / Stock</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/material_requests/list.php"><i class="bi bi-truck"></i> Material Requests</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/finance/budgets.php"><i class="bi bi-wallet2"></i> Budgets</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/finance/expenses.php"><i class="bi bi-receipt"></i> Expenses</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/finance/payments.php"><i class="bi bi-credit-card"></i> Payments</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Procurement Staff'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/suppliers/list.php"><i class="bi bi-truck-front"></i> Suppliers</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/ccms/purchase_orders/list.php"><i class="bi bi-cart-check"></i> Purchase Orders</a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
      <a class="nav-link" href="/ccms/reports/index.php"><i class="bi bi-bar-chart-line"></i> Reports</a>
    </li>

  </ul>
</div>
