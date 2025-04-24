<?php
require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/config.php';
?>

<style>
/* Dropdown container */
.navbar .dropdown {
    position: relative;
}

.navbar .dropdown-content {
    display: none;
    position: absolute;
    background-color: #333;
    min-width: 180px;
    z-index: 1;
    top: 100%;
    left: 0;
}

.navbar .dropdown-content a {
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    border-bottom: 1px solid #444;
}

.navbar .dropdown-content a:hover {
    background-color: #4CAF50;
}

.navbar .dropdown:hover .dropdown-content {
    display: block;
}

.navbar .dropdown:hover > .nav-link::after {
    content: " ▼";
    font-size: 12px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <ul class="navbar-nav ms-auto">

      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/project.php">Project</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">About</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/display_relationships.php">Relationships</a></li>

      <?php if (is_logged_in()): ?>
        <?php if (is_admin()): ?>
          <li class="dropdown nav-item">
            <a class="nav-link" href="#">Admin Tools</a>
            <div class="dropdown-content">
              <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
              <a href="<?= BASE_URL ?>/admin/manage_users.php">Manage Users</a>
              <a href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a>
              <a href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a>
            </div>
          </li>
        <?php elseif (is_expert()): ?>
          <li class="dropdown nav-item">
            <a class="nav-link" href="#">Expert Tools</a>
            <div class="dropdown-content">
              <a href="<?= BASE_URL ?>/expert/dashboard.php">Dashboard</a>
              <a href="<?= BASE_URL ?>/expert/manage_terms.php">Manage Terms</a>
              <a href="<?= BASE_URL ?>/expert/manage_rules.php">Manage Rules</a>
            </div>
          </li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/qa_session.php">Q&A</a></li>

        <li class="dropdown nav-item">
          <a class="nav-link" href="#">
            <?= htmlspecialchars($_SESSION['username']) ?> | <?= ucfirst($_SESSION['role']) ?>
          </a>
          <div class="dropdown-content">
            <a href="#">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
            <a href="#">🛡️ <?= ucfirst($_SESSION['role']) ?></a>
            <a href="<?= BASE_URL ?>/user/change_password.php">Change Password</a>
            <a href="<?= BASE_URL ?>/logout.php">Logout</a>
          </div>
        </li>
      <?php else: ?>
        <li class="dropdown nav-item">
          <a class="nav-link" href="#">Account</a>
          <div class="dropdown-content">
            <a href="<?= BASE_URL ?>/login.php">Login</a>
            <a href="<?= BASE_URL ?>/register.php">Register</a>
          </div>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
