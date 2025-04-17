<?php
require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/config.php'; 
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <ul class="navbar-nav ms-auto">

      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/project.php">Project</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">About</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/display_relationships.php">View Relationships</a></li>

      <?php if (is_logged_in()): ?>
        <?php if (is_admin()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">Admin Tools</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>

        <?php elseif (is_expert()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>

        <?php elseif (is_user()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>logout.php">Logout</a>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
