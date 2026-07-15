<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pdo = get_db();
$menuCount = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$pendingRes = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
$todayRes = $pdo->query("SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE()")->fetchColumn();
$unreadMsgs = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

$recentReservations = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5")->fetchAll();

$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>Dashboard</h1>
      <span>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
    </div>

    <div class="stat-cards">
      <div class="stat-card"><div class="num"><?= $menuCount ?></div><div class="label">Menu Items</div></div>
      <div class="stat-card"><div class="num"><?= $pendingRes ?></div><div class="label">Pending Reservations</div></div>
      <div class="stat-card"><div class="num"><?= $todayRes ?></div><div class="label">Reservations Today</div></div>
      <div class="stat-card"><div class="num"><?= $unreadMsgs ?></div><div class="label">Unread Messages</div></div>
    </div>

    <h3 style="margin-bottom:14px;">Recent Reservations</h3>
    <table>
      <thead><tr><th>Name</th><th>Date</th><th>Time</th><th>Guests</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recentReservations as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['reservation_date']) ?></td>
            <td><?= htmlspecialchars(substr($r['reservation_time'],0,5)) ?></td>
            <td><?= (int)$r['guests'] ?></td>
            <td><span class="pill pill-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($recentReservations)): ?>
          <tr><td colspan="5">No reservations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
