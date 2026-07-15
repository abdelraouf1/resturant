<?php
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Contact';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $success = true;
    }
}

require __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container">
    <h2 class="section-title">Get in Touch</h2>
    <p class="section-subtitle">Questions, feedback, or private events — we'd love to hear from you</p>

    <div class="form-card">
      <?php if ($success): ?>
        <div class="alert alert-success">Your message has been sent. We'll get back to you soon!</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Name *</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Message *</label>
          <textarea name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
