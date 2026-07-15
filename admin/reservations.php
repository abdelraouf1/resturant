<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pdo = get_db();

if (isset($_GET['status'], $_GET['id'])) {
    $allowed = ['pending', 'confirmed', 'cancelled'];
    if (in_array($_GET['status'], $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$_GET['status'], (int)$_GET['id']]);
    }
    header('Location: reservations.php');
    exit;
}

$reservations = $pdo->query("SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC")->fetchAll();
$active = 'reservations';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reservations | Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Reservations</h1></div>

    <table>
      <thead><tr><th>Name</th><th>Contact</th><th>Date</th><th>Time</th><th>Guests</th><th>Notes</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($reservations as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?><br><?= htmlspecialchars($r['phone']) ?></td>
            <td><?= htmlspecialchars($r['reservation_date']) ?></td>
            <td><?= htmlspecialchars(substr($r['reservation_time'], 0, 5)) ?></td>
            <td><?= (int)$r['guests'] ?></td>
            <td><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
            <td><span class="pill pill-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td class="actions">
              <a href="?status=confirmed&id=<?= $r['id'] ?>" class="edit">Confirm</a>
              <a href="?status=cancelled&id=<?= $r['id'] ?>" class="delete">Cancel</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?>
          <tr><td colspan="8">No reservations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
