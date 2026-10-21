<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';
require_once 'Product.class.php';

start_session_once();

$error = '';
$product = false;
$id = get_value('id');

if ($id == '' || !ctype_digit($id)) {
    $error = 'Product ID is missing or invalid.';
} else {
    $stmt = $pdo->prepare('SELECT product_id AS productId, product_name AS productName, category AS category, description AS description, price AS price, quantity AS quantity, rating AS rating, photo1 AS photo1, photo2 AS photo2, photo3 AS photo3, default_photo AS defaultPhoto FROM products WHERE product_id = :product_id');
    $stmt->bindValue(':product_id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetchObject('Product');

    if (!$product) {
        $error = 'Product was not found.';
    }
}

prepare_layout('Product Details');

require_once 'header.inc.php';

if ($error != '') {
?>

<div class="page-layout">
  <main class="main-full product-detail-card">
    <h2>Product Details</h2>
    <p><?php echo e($error); ?></p>
    <p><a class="btn btn-neutral" href="products.php">Back to Products</a></p>
  </main>
</div>

<?php
} else {
    echo $product->displayProductPage();
}

require_once 'footer.inc.php';
?>