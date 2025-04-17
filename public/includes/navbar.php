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
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button">Admin Tools &#9662;</a>
            <ul class="dropdown-menu">
              <li><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
              <li><a href="<?= BASE_URL ?>/admin/manage_users.php">Manage Users</a></li>
              <li><a href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a></li>
              <li><a href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>

        <?php elseif (is_expert()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button">Expert Tools &#9662;</a>
            <ul class="dropdown-menu">
              <li><a href="<?= BASE_URL ?>/expert/dashboard.php">Dashboard</a></li>
              <li><a href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a></li>
              <li><a href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>

        <?php elseif (is_user()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">QA Session</a></li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<style>
.nav-item.dropdown {
  position: relative;
}
.nav-item .dropdown-menu {
  display: none;
  position: absolute;
  background-color: #333;
  border-radius: 5px;
  top: 100%;
  left: 0;
  padding: 0;
  list-style-type: none;
  min-width: 180px;
  z-index: 1000;
}
.nav-item.dropdown:hover .dropdown-menu {
  display: block;
}
.dropdown-menu li a {
  display: block;
  padding: 10px 15px;
  color: white;
  text-align: left;
  text-decoration: none;
}
.dropdown-menu li a:hover {
  background-color: #4CAF50;
}
</style>
