<div class="sidebar">
  <h2>Ember &amp; Oak</h2>
  <a href="dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
  <a href="menu_manage.php" class="<?= ($active ?? '') === 'menu' ? 'active' : '' ?>">Menu Items</a>
  <a href="reservations.php" class="<?= ($active ?? '') === 'reservations' ? 'active' : '' ?>">Reservations</a>
  <a href="messages.php" class="<?= ($active ?? '') === 'messages' ? 'active' : '' ?>">Messages</a>
  <a href="../public/index.php" target="_blank">View Site ↗</a>
  <a href="logout.php" class="logout-link">Logout</a>
</div>
