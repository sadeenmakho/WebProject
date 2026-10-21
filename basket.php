<?php
require_once 'functions.inc.php';

start_session_once();
prepare_layout('Shopping Basket');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full basket-card">
    <h2>Shopping Basket</h2>

    <?php if (!isset($_SESSION['basket']) || count($_SESSION['basket']) == 0) { ?>

    <section class="empty-message">
      <p>Your shopping basket is empty.</p>
      <p><a class="btn btn-neutral" href="products.php">Back to Products</a></p>
    </section>

    <?php } else { ?>

    <table class="basket-table">
      <caption>Current basket contents</caption>

      <thead>
        <tr>
          <th scope="col">Product Image</th>
          <th scope="col">Product Name</th>
          <th scope="col">Unit Price</th>
          <th scope="col">Quantity</th>
          <th scope="col">Line Total</th>
          <th scope="col">Remove</th>
        </tr>
      </thead>

      <tbody>
        <?php
        foreach ($_SESSION['basket'] as $productId => $item) {
            $lineTotal = ((float)$item['unit_price']) * ((int)$item['quantity']);

            echo '<tr>';
            echo '<td><img src="images/' . e($item['default_photo']) . '" alt="' . e($item['product_name']) . '"></td>';
            echo '<td>' . e($item['product_name']) . '</td>';
            echo '<td class="num">$' . e(number_format((float)$item['unit_price'], 2)) . '</td>';
            echo '<td class="num">' . e($item['quantity']) . '</td>';
            echo '<td class="num">$' . e(number_format($lineTotal, 2)) . '</td>';
            echo '<td><a class="btn btn-neutral" href="remove_from_basket.php?id=' . e($productId) . '">Remove</a></td>';
            echo '</tr>';
        }
        ?>
      </tbody>
    </table>

    <div class="basket-total">
      Basket Total: $<?php echo e(number_format(basket_total(), 2)); ?>
    </div>

    <div class="basket-actions">
      <a href="products.php" class="btn btn-neutral">Continue Shopping</a>
      <a href="checkout.php" class="btn btn-green">Proceed to Checkout</a>
    </div>

    <?php } ?>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>