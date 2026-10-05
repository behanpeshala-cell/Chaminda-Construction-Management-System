    </div><!-- /.ccms-main -->
  </div><!-- /.ccms-layout -->

<!-- Floating Toast Notification Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090; margin-top: 65px;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($_SESSION['flash'])): ?>
<script>
  window.SESSION_TOAST = <?php echo json_encode($_SESSION['flash']); ?>;
</script>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<script src="/ccms/assets/js/script.js"></script>
</body>
</html>
