<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';

start_session_once();
require_employee();

$categories = array(
    'Handcrafted Ceramics',
    'Natural Olive-Oil Products',
    'Traditional Embroidery',
    'Olive Wood Gifts',
    'Textile Gifts',
    'Natural Gifts',
    'Food Gifts',
    'Home Decor'
);

$errors = array();

$productName = '';
$category = '';
$price = '';
$quantity = '';
$rating = '';
$description = '';
$defaultPhotoChoice = '1';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = post_value('product_name');
    $category = post_value('category');
    $price = post_value('price');
    $quantity = post_value('quantity');
    $rating = post_value('rating');
    $description = post_value('description');
    $defaultPhotoChoice = post_value('default_photo');

    if ($productName == '') { $errors[] = 'Product Name is required.'; }
    if ($category == '') { $errors[] = 'Category is required.'; }
    if ($category != '' && !in_array($category, $categories)) { $errors[] = 'Category must be one of the predefined categories.'; }
    if ($price == '' || !is_numeric($price) || (float)$price <= 0) { $errors[] = 'Price must be a positive value.'; }
    if ($quantity == '' || !is_non_negative_integer($quantity)) { $errors[] = 'Quantity must be a non-negative integer.'; }
    if ($rating == '' || !is_numeric($rating) || (float)$rating < 1 || (float)$rating > 5) { $errors[] = 'Rating must be a number from 1 to 5.'; }
    if ($description == '') { $errors[] = 'Description is required.'; }
    if ($defaultPhotoChoice != '1' && $defaultPhotoChoice != '2' && $defaultPhotoChoice != '3') { $errors[] = 'Default Photo must be Photo 1, Photo 2, or Photo 3.'; }

    for ($i = 1; $i <= 3; $i++) {
        $field = 'photo' . $i;

        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] != UPLOAD_ERR_OK) {
            $errors[] = 'Photo ' . $i . ' is required.';
        } elseif ($_FILES[$field]['type'] != 'image/jpeg') {
            $errors[] = 'Photo ' . $i . ' must be image/jpeg.';
        }
    }

    if (count($errors) == 0) {
        $stmt = $pdo->prepare('INSERT INTO products (product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo) VALUES (:product_name, :category, :description, :price, :quantity, :rating, :photo1, :photo2, :photo3, :default_photo)');
        $stmt->bindValue(':product_name', $productName);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(':rating', $rating);
        $stmt->bindValue(':photo1', '');
        $stmt->bindValue(':photo2', '');
        $stmt->bindValue(':photo3', '');
        $stmt->bindValue(':default_photo', '');
        $stmt->execute();

        $productId = $pdo->lastInsertId();

        $photo1 = $productId . '_1.jpeg';
        $photo2 = $productId . '_2.jpeg';
        $photo3 = $productId . '_3.jpeg';

        $defaultPhoto = $photo1;

        if ($defaultPhotoChoice == '2') {
            $defaultPhoto = $photo2;
        }

        if ($defaultPhotoChoice == '3') {
            $defaultPhoto = $photo3;
        }

        $moveOk = true;

        if (!move_uploaded_file($_FILES['photo1']['tmp_name'], 'images/' . $photo1)) { $moveOk = false; }
        if (!move_uploaded_file($_FILES['photo2']['tmp_name'], 'images/' . $photo2)) { $moveOk = false; }
        if (!move_uploaded_file($_FILES['photo3']['tmp_name'], 'images/' . $photo3)) { $moveOk = false; }

        if ($moveOk) {
            $update = $pdo->prepare('UPDATE products SET photo1 = :photo1, photo2 = :photo2, photo3 = :photo3, default_photo = :default_photo WHERE product_id = :product_id');
            $update->bindValue(':photo1', $photo1);
            $update->bindValue(':photo2', $photo2);
            $update->bindValue(':photo3', $photo3);
            $update->bindValue(':default_photo', $defaultPhoto);
            $update->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
            $update->execute();

            redirect_to('view.php?id=' . $productId);
        } else {
            $delete = $pdo->prepare('DELETE FROM products WHERE product_id = :product_id');
            $delete->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
            $delete->execute();

            $errors[] = 'File upload failed. Product was not inserted.';
        }
    }
}

prepare_layout('Add Product');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Add New Product</h2>

    <?php show_errors($errors); ?>

    <form action="add.php" method="post" enctype="multipart/form-data" class="product-form">
      <fieldset>
        <legend>Product Data</legend>

        <div class="form-row">
          <label for="product_name">Product Name</label>
          <input type="text" id="product_name" name="product_name" value="<?php echo e($productName); ?>" required>
        </div>

        <div class="form-row">
          <label for="category">Category</label>
          <select id="category" name="category" required>
            <option value="">Select Category</option>

            <?php foreach ($categories as $cat) { ?>
            <option value="<?php echo e($cat); ?>"<?php echo selected_if($cat, $category); ?>><?php echo e($cat); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="form-row">
          <label for="price">Price</label>
          <input type="number" step="0.01" id="price" name="price" value="<?php echo e($price); ?>" required>
        </div>

        <div class="form-row">
          <label for="quantity">Quantity</label>
          <input type="number" id="quantity" name="quantity" value="<?php echo e($quantity); ?>" required>
        </div>

        <div class="form-row">
          <label for="rating">Rating</label>
          <input type="number" step="0.1" id="rating" name="rating" value="<?php echo e($rating); ?>" required>
        </div>

        <div class="form-row">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="6" required><?php echo e($description); ?></textarea>
        </div>
      </fieldset>

      <fieldset>
        <legend>Product Photos</legend>

        <div class="form-row">
          <label for="photo1">Photo 1</label>
          <input type="file" id="photo1" name="photo1" accept="image/jpeg" required>
        </div>

        <div class="form-row">
          <label for="photo2">Photo 2</label>
          <input type="file" id="photo2" name="photo2" accept="image/jpeg" required>
        </div>

        <div class="form-row">
          <label for="photo3">Photo 3</label>
          <input type="file" id="photo3" name="photo3" accept="image/jpeg" required>
        </div>

        <fieldset class="photo-choice">
          <legend>Default Photo</legend>

          <div class="radio-group">
            <label>
              <input type="radio" name="default_photo" value="1"<?php echo checked_if('1', $defaultPhotoChoice); ?>>
              Photo 1
            </label>

            <label>
              <input type="radio" name="default_photo" value="2"<?php echo checked_if('2', $defaultPhotoChoice); ?>>
              Photo 2
            </label>

            <label>
              <input type="radio" name="default_photo" value="3"<?php echo checked_if('3', $defaultPhotoChoice); ?>>
              Photo 3
            </label>
          </div>
        </fieldset>
      </fieldset>

      <p class="form-actions">
        <button type="submit" class="btn btn-green">Insert</button>
      </p>
    </form>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>