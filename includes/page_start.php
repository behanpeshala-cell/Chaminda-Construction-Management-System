<?php
// Expects $page_title to be set before including this file.
require_once __DIR__ . '/header.php';
?>
<div class="ccms-layout">
  <?php require_once __DIR__ . '/sidebar.php'; ?>
  <div class="ccms-main">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
      <h4 class="mb-0"><?php echo htmlspecialchars($page_title ?? ''); ?></h4>
      <?php if (!empty($page_actions)) echo $page_actions; ?>
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash']['type']); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash']['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
