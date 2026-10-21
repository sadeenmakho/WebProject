<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';

start_session_once();

if (!is_logged_in()) {
    $_SESSION['return_to'] = 'checkout.php';
    redirect_to('login.php');
}

if (!isset($_SESSION['basket']) || count($_SESSION['basket']) == 0) {
    redirect_to('basket.php');
}

$errors = array();
$orderPlaced = false;
$orderId = '';
$orderTotal = basket_total();

$cardholderName = '';
$cardNumber = '';
$expiryMonth = '';
$expiryYear = '';
$cvv = '';
$currentYear = (int)date('Y');

if (!is_customer()) {
    prepare_layout('Checkout');

    require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full checkout-card">
    <h2>Checkout</h2>
    <section class="error-box">
      <p>Only customers can place orders.</p>
    </section>
    <p><a class="btn btn-neutral" href="products.php">Back to Products</a></p>
  </main>
</div>

<?php
    require_once 'footer.inc.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cardholderName = post_value('cardholder_name');
    $cardNumber = post_value('card_number');
    $expiryMonth = post_value('expiry_month');
    $expiryYear = post_value('expiry_year');
    $cvv = post_value('cvv');

    if ($cardholderName == '') { $errors[] = 'Cardholder Name is required.'; }
    if ($cardNumber == '' || !ctype_digit($cardNumber) || strlen($cardNumber) != 16) { $errors[] = 'Card Number must contain exactly 16 digits.'; }
    if ($expiryMonth == '' || !ctype_digit($expiryMonth) || (int)$expiryMonth < 1 || (int)$expiryMonth > 12) { $errors[] = 'Expiry Month must be between 01 and 12.'; }
    if ($expiryYear == '' || !ctype_digit($expiryYear) || (int)$expiryYear < $currentYear || (int)$expiryYear > $currentYear + 10) { $errors[] = 'Expiry Year must be between the current year and current year plus 10.'; }
    if ($cvv == '' || !ctype_digit($cvv) || strlen($cvv) != 3) { $errors[] = 'CVV must contain exactly 3 digits.'; }

    if (count($errors) == 0) {
        foreach ($_SESSION['basket'] as $productId => $item) {
            $stockStmt = $pdo->prepare('SELECT quantity FROM products WHERE product_id = :product_id');
            $stockStmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
            $stockStmt->execute();

            $stock = $stockStmt->fetch();

            if (!$stock || (int)$stock['quantity'] < (int)$item['quantity']) {
                $errors[] = 'The requested quantity for ' . $item['product_name'] . ' is no longer available.';
            }
        }
    }

    if (count($errors) == 0) {
        $orderId = generate_order_id($pdo);

        $insertOrder = $pdo->prepare('INSERT INTO orders (order_id, user_id, order_date, total_amount) VALUES (:order_id, :user_id, NOW(), :total_amount)');
        $insertOrder->bindValue(':order_id', $orderId);
        $insertOrder->bindValue(':user_id', $_SESSION['user_id']);
        $insertOrder->bindValue(':total_amount', $orderTotal);
        $insertOrder->execute();

        $orderDbId = $pdo->lastInsertId();

        foreach ($_SESSION['basket'] as $productId => $item) {
            $lineTotal = ((float)$item['unit_price']) * ((int)$item['quantity']);

            $insertItem = $pdo->prepare('INSERT INTO order_items (order_db_id, product_id, product_name, unit_price, quantity, line_total) VALUES (:order_db_id, :product_id, :product_name, :unit_price, :quantity, :line_total)');
            $insertItem->bindValue(':order_db_id', (int)$orderDbId, PDO::PARAM_INT);
            $insertItem->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
            $insertItem->bindValue(':product_name', $item['product_name']);
            $insertItem->bindValue(':unit_price', $item['unit_price']);
            $insertItem->bindValue(':quantity', (int)$item['quantity'], PDO::PARAM_INT);
            $insertItem->bindValue(':line_total', $lineTotal);
            $insertItem->execute();

            $updateStock = $pdo->prepare('UPDATE products SET quantity = quantity - :quantity_subtract WHERE product_id = :product_id AND quantity >= :quantity_check');
            $updateStock->bindValue(':quantity_subtract', (int)$item['quantity'], PDO::PARAM_INT);
            $updateStock->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
            $updateStock->bindValue(':quantity_check', (int)$item['quantity'], PDO::PARAM_INT);
            $updateStock->execute();
        }

        unset($_SESSION['basket']);

        $orderPlaced = true;
    }
}

prepare_layout('Checkout');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full checkout-card">
    <h2>Checkout</h2>

    <?php if ($orderPlaced) { ?>

    <section class="success-box">
      <p>Order placed successfully.</p>
      <p>Your 10-digit Order ID is: <strong><?php echo e($orderId); ?></strong></p>
      <p>Order Total: <strong>$<?php echo e(number_format($orderTotal, 2)); ?></strong></p>
      <p><a class="btn btn-green" href="products.php">Back to Products</a></p>
    </section>

    <?php } else { ?>

    <?php show_errors($errors); ?>

    <table class="basket-table">
      <caption>Basket summary before order placement</caption>

      <thead>
        <tr>
          <th scope="col">Product Image</th>
          <th scope="col">Product Name</th>
          <th scope="col">Unit Price</th>
          <th scope="col">Quantity</th>
          <th scope="col">Line Total</th>
        </tr>
      </thead>

      <tbody>
        <?php
        foreach ($_SESSION['basket'] as $item) {
            $lineTotal = ((float)$item['unit_price']) * ((int)$item['quantity']);

            echo '<tr>';
            echo '<td><img src="images/' . e($item['default_photo']) . '" alt="' . e($item['product_name']) . '"></td>';
            echo '<td>' . e($item['product_name']) . '</td>';
            echo '<td class="num">$' . e(number_format((float)$item['unit_price'], 2)) . '</td>';
            echo '<td class="num">' . e($item['quantity']) . '</td>';
            echo '<td class="num">$' . e(number_format($lineTotal, 2)) . '</td>';
            echo '</tr>';
        }
        ?>
      </tbody>
    </table>

    <div class="basket-total">
      Order Total: $<?php echo e(number_format($orderTotal, 2)); ?>
    </div>

    <form action="checkout.php" method="post" class="checkout-form">
      <fieldset>
        <legend>Credit Card Information</legend>

        <div class="form-row">
          <label for="cardholder_name">Cardholder Name</label>
          <input type="text" id="cardholder_name" name="cardholder_name" value="<?php echo e($cardholderName); ?>" required>
        </div>

        <div class="form-row">
          <label for="card_number">Card Number</label>
          <input type="text" id="card_number" name="card_number" value="<?php echo e($cardNumber); ?>" required>
        </div>

        <div class="form-row">
          <label for="expiry_month">Expiry Month</label>
          <select id="expiry_month" name="expiry_month" required>
            <option value="">Select Month</option>

            <?php for ($m = 1; $m <= 12; $m++) { $monthText = sprintf('%02d', $m); ?>
            <option value="<?php echo e($monthText); ?>"<?php echo selected_if($monthText, $expiryMonth); ?>><?php echo e($monthText); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="form-row">
          <label for="expiry_year">Expiry Year</label>
          <select id="expiry_year" name="expiry_year" required>
            <option value="">Select Year</option>

            <?php for ($y = $currentYear; $y <= $currentYear + 10; $y++) { ?>
            <option value="<?php echo e($y); ?>"<?php echo selected_if($y, $expiryYear); ?>><?php echo e($y); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="form-row">
          <label for="cvv">CVV</label>
          <input type="text" id="cvv" name="cvv" value="<?php echo e($cvv); ?>" required>
        </div>

        <p class="form-actions">
          <button type="submit" class="btn btn-green">Place Order</button>
        </p>
      </fieldset>
    </form>

    <?php } ?>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>