<?php
$pageTitle = 'About';
require __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container about-grid">
    <img src="assets/images/about.jpg" alt="Restaurant interior" onerror="this.src='assets/images/placeholder.jpg'">
    <div>
      <h2>Our Story</h2>
      <p style="margin-top:16px;">
        Ember &amp; Oak opened its doors with one idea: food tastes better cooked over real fire.
        Our chefs work with local farmers and fishermen to bring in the freshest ingredients
        every morning, then let the charcoal grill do the rest.
      </p>
      <p style="margin-top:16px;">
        Whether you're joining us for a quiet dinner or a celebration with the whole family,
        our team is here to make it memorable.
      </p>
      <a href="reserve.php" class="btn btn-primary" style="margin-top:24px;">Book Your Visit</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
