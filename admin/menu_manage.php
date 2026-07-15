<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/s3.php';
require_admin_login();

$pdo = get_db();
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$flash = '';
$flashType = 'success';

// ---- Handle create / update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    if (!csrf_check()) {
        $flash = 'Invalid request, please retry.'; $flashType = 'error';
    } else {
        $id          = $_POST['id'] ?? '';
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        $imageKey = null; $imageUrl = null;

        // Handle new image upload (optional on edit)
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = s3_upload_menu_image($_FILES['image']['tmp_name'], $_FILES['image']['name']);
            if ($uploaded) {
                $imageKey = $uploaded['key'];
                $imageUrl = $uploaded['url'];
            } else {
                $flash = 'Image upload to S3 failed — item saved without changing the image.';
                $flashType = 'error';
            }
        }

        if ($id) {
            // Update existing item
            if ($imageKey) {
                // Fetch + delete old S3 object before replacing
                $old = $pdo->prepare("SELECT image_key FROM menu_items WHERE id = ?");
                $old->execute([$id]);
                $oldKey = $old->fetchColumn();

                $stmt = $pdo->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, image_key=?, image_url=?, is_available=? WHERE id=?");
                $stmt->execute([$categoryId, $name, $description, $price, $imageKey, $imageUrl, $isAvailable, $id]);

                if ($oldKey) s3_delete_object($oldKey);
            } else {
                $stmt = $pdo->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, is_available=? WHERE id=?");
                $stmt->execute([$categoryId, $name, $description, $price, $isAvailable, $id]);
            }
        } else {
            // Create new item
            $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, image_key, image_url, is_available) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$categoryId, $name, $description, $price, $imageKey, $imageUrl, $isAvailable]);
        }

        if (!$flash) { $flash = 'Menu item saved successfully.'; }
    }
}

// ---- Handle delete ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_key FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $key = $stmt->fetchColumn();

    $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    if ($key) s3_delete_object($key);

    header('Location: menu_manage.php');
    exit;
}

// ---- Load item to edit (if any) ----
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$items = $pdo->query("
    SELECT m.*, c.name AS category_name FROM menu_items m
    LEFT JOIN categories c ON c.id = m.category_id
    ORDER BY m.id DESC
")->fetchAll();

$active = 'menu';
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Menu Items | Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Menu Items</h1></div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flashType === 'error' ? 'error' : 'success' ?>" style="max-width:640px;"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:32px;">
      <h3 style="margin-bottom:16px;"><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <?php if ($editItem): ?><input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>"><?php endif; ?>

        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" rows="2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Price (EGP)</label>
            <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($editItem['price'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Category</label>
            <select name="category_id">
              <option value="">— None —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (($editItem['category_id'] ?? null) == $cat['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Image <?= $editItem ? '(leave empty to keep current image)' : '' ?></label>
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
          <?php if (!empty($editItem['image_url'])): ?>
            <img src="<?= htmlspecialchars($editItem['image_url']) ?>" class="thumb" style="margin-top:8px;">
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label><input type="checkbox" name="is_available" <?= (!$editItem || $editItem['is_available']) ? 'checked' : '' ?> style="width:auto; margin-right:8px;"> Available on menu</label>
        </div>

        <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update Item' : 'Add Item' ?></button>
        <?php if ($editItem): ?><a href="menu_manage.php" class="btn btn-outline" style="color:#333;border-color:#333;">Cancel</a><?php endif; ?>
      </form>
    </div>

    <table>
      <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><img src="<?= htmlspecialchars($item['image_url'] ?: '../public/assets/images/placeholder.jpg') ?>" class="thumb"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['category_name'] ?? '—') ?></td>
            <td><?= number_format($item['price'], 2) ?> EGP</td>
            <td><?= $item['is_available'] ? '<span class="pill pill-confirmed">Available</span>' : '<span class="pill pill-cancelled">Hidden</span>' ?></td>
            <td class="actions">
              <a href="?edit=<?= $item['id'] ?>" class="edit">Edit</a>
              <a href="?delete=<?= $item['id'] ?>" class="delete" onclick="return confirm('Delete this item and its S3 image?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
