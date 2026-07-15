<?php
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Reserve a Table';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $date    = trim($_POST['reservation_date'] ?? '');
    $time    = trim($_POST['reservation_time'] ?? '');
    $guests  = (int)($_POST['guests'] ?? 0);
    $notes   = trim($_POST['notes'] ?? '');

    if ($name === '' || $email === '' || $phone === '' || $date === '' || $time === '' || $guests < 1) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            INSERT INTO reservations (full_name, email, phone, reservation_date, reservation_time, guests, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $phone, $date, $time, $guests, $notes]);
        $success = true;
    }
}

require __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container">
    <h2 class="section-title">Reserve a Table</h2>
    <p class="section-subtitle">We'll confirm your reservation by email or phone</p>

    <div class="form-card">
      <?php if ($success): ?>
        <div class="alert alert-success">Thank you! Your reservation request has been received. We'll confirm shortly.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" id="reserveForm">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Phone *</label>
            <input type="tel" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="reservation_date" required min="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label>Time *</label>
            <input type="time" name="reservation_time" required>
          </div>
        </div>
        <div class="form-group">
          <label>Number of Guests *</label>
          <input type="number" name="guests" min="1" max="30" required value="<?= htmlspecialchars($_POST['guests'] ?? 2) ?>">
        </div>
        <div class="form-group">
          <label>Special Requests</label>
          <textarea name="notes" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Confirm Reservation</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
