<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pdo = get_db();

if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([(int)$_GET['read']]);
    header('Location: messages.php');
    exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$active = 'messages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages | Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Contact Messages</h1></div>

    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td style="max-width:280px;"><?= htmlspecialchars($m['message']) ?></td>
            <td><?= htmlspecialchars($m['created_at']) ?></td>
            <td><?= $m['is_read'] ? '<span class="pill pill-confirmed">Read</span>' : '<span class="pill pill-pending">New</span>' ?></td>
            <td class="actions">
              <?php if (!$m['is_read']): ?><a href="?read=<?= $m['id'] ?>" class="edit">Mark Read</a><?php endif; ?>
              <a href="?delete=<?= $m['id'] ?>" class="delete" onclick="return confirm('Delete this message?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
          <tr><td colspan="6">No messages yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
