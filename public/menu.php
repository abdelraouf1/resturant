<?php
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Menu';
$pdo = get_db();

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$items = $pdo->query("
    SELECT m.*, c.name AS category_name
    FROM menu_items m
    LEFT JOIN categories c ON c.id = m.category_id
    ORDER BY c.sort_order, m.name
")->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container">
    <h2 class="section-title">Our Menu</h2>
    <p class="section-subtitle">Every image is served straight from our S3 media bucket</p>

    <div class="menu-filters">
      <button class="active" data-category="all">All</button>
      <?php foreach ($categories as $cat): ?>
        <button data-category="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="menu-grid">
      <?php foreach ($items as $item): ?>
        <div class="menu-card" data-category="<?= htmlspecialchars($item['category_name'] ?? '') ?>">
          <img src="<?= htmlspecialchars($item['image_url'] ?: 'assets/images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
          <div class="menu-card-body">
            <div class="menu-card-top">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <span class="price"><?= number_format($item['price'], 2) ?> EGP</span>
            </div>
            <p><?= htmlspecialchars($item['description']) ?></p>
            <?php if (!$item['is_available']): ?>
              <span class="badge-unavailable">Currently unavailable</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <p>No menu items yet. Add some from the admin dashboard.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
