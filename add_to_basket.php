<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';
require_once 'Product.class.php';

start_session_once();

if (!is_logged_in()) {
    $_SESSION['return_to'] = current_relative_url();
    redirect_to('login.php');
}

$errors = array();
$product = false;
$quantityRequested = '1';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = post_value('product_id');
    $quantityRequested = post_value('quantity');
} else {
    $id = get_value('id');
}

if (!is_customer()) {
    prepare_layout('Add to Basket');

    require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Add to Basket</h2>

    <section class="error-box">
      <p>Only customers can add products to the shopping basket.</p>
    </section>

    <p>
      <a class="btn btn-neutral" href="products.php">Back to Products</a>
    </p>
  </main>
</div>

<?php
    require_once 'footer.inc.php';
    exit;
}

if ($id == '' || !ctype_digit($id)) {
    $errors[] = 'Product ID is missing or invalid.';
} else {
    $stmt = $pdo->prepare('SELECT product_id AS productId, product_name AS productName, category AS category, description AS description, price AS price, quantity AS quantity, rating AS rating, photo1 AS photo1, photo2 AS photo2, photo3 AS photo3, default_photo AS defaultPhoto FROM products WHERE product_id = :product_id');
    $stmt->bindValue(':product_id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetchObject('Product');

    if (!$product) {
        $errors[] = 'Product was not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $product && count($errors) == 0) {

    if (!is_positive_integer($quantityRequested)) {
        $errors[] = 'Quantity must be a positive integer.';
    } else {

        $stockStmt = $pdo->prepare('SELECT quantity FROM products WHERE product_id = :product_id');
        $stockStmt->bindValue(':product_id', (int)$id, PDO::PARAM_INT);
        $stockStmt->execute();

        $stock = $stockStmt->fetch();
        $available = (int)$stock['quantity'];

        $alreadyInBasket = 0;

        if (isset($_SESSION['basket'][$id])) {
            $alreadyInBasket = (int)$_SESSION['basket'][$id]['quantity'];
        }

        $newTotalQuantity = $alreadyInBasket + (int)$quantityRequested;

        if ($newTotalQuantity > $available) {
            $errors[] = 'Requested quantity exceeds available stock.';
        } else {

            if (!isset($_SESSION['basket']) || !is_array($_SESSION['basket'])) {
                $_SESSION['basket'] = array();
            }

            if (isset($_SESSION['basket'][$id])) {
                $_SESSION['basket'][$id]['quantity'] = $newTotalQuantity;
            } else {
                $_SESSION['basket'][$id] = array(
                    'product_id' => $product->getProductId(),
                    'product_name' => $product->getProductName(),
                    'unit_price' => $product->getPrice(),
                    'quantity' => (int)$quantityRequested,
                    'default_photo' => $product->getDefaultPhoto()
                );
            }

            redirect_to('basket.php');
        }
    }
}

prepare_layout('Add to Basket');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Add Item to Shopping Basket</h2>

    <?php show_errors($errors); ?>

    <?php if ($product) { ?>

    <form action="add_to_basket.php" method="post" class="basket-form">
      <fieldset>
        <legend>Basket Confirmation</legend>

        <input type="hidden" name="product_id" value="<?php echo e($product->getProductId()); ?>">

        <div class="form-row">
          <label>Product ID</label>
          <input type="text" value="<?php echo e($product->getProductId()); ?>" disabled>
        </div>

        <div class="form-row">
          <label>Product Name</label>
          <input type="text" value="<?php echo e($product->getProductName()); ?>" disabled>
        </div>

        <div class="form-row">
          <label>Unit Price</label>
          <input type="text" value="<?php echo e(number_format((float)$product->getPrice(), 2)); ?>" disabled>
        </div>

        <div class="form-row">
          <label for="quantity">Quantity</label>
          <input type="number" id="quantity" name="quantity" value="<?php echo e($quantityRequested); ?>" required>
        </div>

        <p class="form-actions">
          <button type="submit" class="btn btn-green">Add to Basket</button>
        </p>

      </fieldset>
    </form>

    <?php } ?>

    <p>
      <a class="btn btn-neutral" href="products.php">Back to Products</a>
    </p>

  </main>
</div>

<?php
require_once 'footer.inc.php';
?>