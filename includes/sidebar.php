<?php
$role = current_role();
$current_script = $_SERVER['PHP_SELF'];
function nav_active($dir_or_file, $current_script) {
    return (strpos($current_script, $dir_or_file) !== false) ? 'active' : '';
}
?>
<div class="ccms-sidebar">
  <ul class="nav flex-column">

    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('dashboard.php',$current_script); ?>" href="/ccms/dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>

    <?php if (in_array($role, ['Administrator'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/users/',$current_script); ?>" href="/ccms/users/list.php"><i class="bi bi-person-badge"></i> Users</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/clients/',$current_script); ?>" href="/ccms/clients/list.php"><i class="bi bi-person-lines-fill"></i> Clients</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager','Site Staff','Client'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/projects/',$current_script); ?>" href="/ccms/projects/list.php"><i class="bi bi-kanban"></i> Projects</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/employees/',$current_script); ?>" href="/ccms/employees/list.php"><i class="bi bi-people"></i> Employees</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Site Staff','Project Manager'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/materials/',$current_script); ?>" href="/ccms/materials/list.php"><i class="bi bi-box-seam"></i> Materials</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/inventory/',$current_script); ?>" href="/ccms/inventory/list.php"><i class="bi bi-clipboard-data"></i> Inventory / Stock</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/material_requests/',$current_script); ?>" href="/ccms/material_requests/list.php"><i class="bi bi-truck"></i> Material Requests</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Finance Officer'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/finance/budgets.php',$current_script); ?>" href="/ccms/finance/budgets.php"><i class="bi bi-wallet2"></i> Budgets</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/finance/expenses.php',$current_script); ?>" href="/ccms/finance/expenses.php"><i class="bi bi-receipt"></i> Expenses</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/finance/payments.php',$current_script); ?>" href="/ccms/finance/payments.php"><i class="bi bi-credit-card"></i> Payments</a>
    </li>
    <?php endif; ?>

    <?php if (in_array($role, ['Administrator','Procurement Staff'])): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/suppliers/',$current_script); ?>" href="/ccms/suppliers/list.php"><i class="bi bi-truck-front"></i> Suppliers</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/purchase_orders/',$current_script); ?>" href="/ccms/purchase_orders/list.php"><i class="bi bi-cart-check"></i> Purchase Orders</a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('/reports/',$current_script); ?>" href="/ccms/reports/index.php"><i class="bi bi-bar-chart-line"></i> Reports</a>
    </li>

  </ul>
</div>

