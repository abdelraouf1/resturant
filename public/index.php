<?php
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Home';

// Pull a few featured / available menu items for the homepage
$pdo = get_db();
$stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY id DESC LIMIT 3");
$featured = $stmt->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Wood-fired flavor,<br>served with warmth.</h1>
      <p>Ember &amp; Oak is a modern grill house on the Nile Corniche — fresh ingredients, open flame, and a menu built around the seasons.</p>
      <a href="reserve.php" class="btn btn-primary">Reserve a Table</a>
      <a href="menu.php" class="btn btn-outline">View Menu</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title">Featured Dishes</h2>
    <p class="section-subtitle">A few favorites from our kitchen this week</p>

    <div class="menu-grid">
      <?php foreach ($featured as $item): ?>
        <div class="menu-card">
          <img src="<?= htmlspecialchars($item['image_url'] ?: 'assets/images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <div class="menu-card-body">
            <div class="menu-card-top">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <span class="price"><?= number_format($item['price'], 2) ?> EGP</span>
            </div>
            <p><?= htmlspecialchars($item['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($featured)): ?>
        <p>Menu coming soon — check back shortly!</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section" style="background:#fff;">
  <div class="container features">
    <div class="feature">
      <div class="icon">🔥</div>
      <h3>Open-Flame Grill</h3>
      <p>Every dish finished over charcoal for real smoky depth.</p>
    </div>
    <div class="feature">
      <div class="icon">🌿</div>
      <h3>Seasonal Ingredients</h3>
      <p>We change the menu with what's freshest each month.</p>
    </div>
    <div class="feature">
      <div class="icon">🍷</div>
      <h3>Curated Pairings</h3>
      <p>A drinks list built specifically to match the grill.</p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
