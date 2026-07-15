<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Ember & Oak' : 'Ember & Oak Restaurant' ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="index.php" class="brand">Ember<span> &amp; Oak</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="menu.php">Menu</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <a href="reserve.php" class="nav-cta">Book a Table</a>
  </div>
</nav>
