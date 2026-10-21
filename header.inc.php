<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($page_title); ?> - Palestine Imprint Souvenirs</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
<header>
  <div class="header-brand">
    <img src="./images/logo.png" alt="Palestine Imprint Souvenirs logo">
    <div>
      <h1>Palestine Imprint Souvenirs</h1>
      <p>Authentic Palestinian gifts carrying the imprint of heritage.</p>
    </div>
  </div>

  <nav aria-label="Main navigation">
    <ul>
      <li><a href="index.php">Assignment 3 Home</a></li>
      <li><a href="products.php">Products</a></li>
      <?php echo $header_auth_links; ?>
      <li><a href="basket.php">Basket</a></li>
      <li><a href="contact.php">Contact Us</a></li>
    </ul>
  </nav>

  <div class="user-info">
    <?php echo $header_user_info; ?>
  </div>
</header>