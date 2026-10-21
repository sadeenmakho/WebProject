<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';

start_session_once();
require_employee();

$errors = array();
$id = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = post_value('product_id');
} else {
    $id = get_value('id');
}

$product = false;

if ($id == '' || !ctype_digit($id)) {
    $errors[] = 'Product ID is missing or invalid.';
} else {
    $stmt = $pdo->prepare('SELECT product_id, product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo FROM products WHERE product_id = :product_id');
    $stmt->bindValue(':product_id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch();

    if (!$product) {
        $errors[] = 'Product was not found.';
    }
}

if ($product) {
    if ($product['default_photo'] == $product['photo2']) {
        $defaultPhotoChoice = '2';
    } elseif ($product['default_photo'] == $product['photo3']) {
        $defaultPhotoChoice = '3';
    } else {
        $defaultPhotoChoice = '1';
    }

    $price = $product['price'];
    $quantity = $product['quantity'];
    $description = $product['description'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $price = post_value('price');
        $quantity = post_value('quantity');
        $description = post_value('description');
        $defaultPhotoChoice = post_value('default_photo');

        if ($price == '' || !is_numeric($price) || (float)$price <= 0) { $errors[] = 'Price must be a positive value.'; }
        if ($quantity == '' || !is_non_negative_integer($quantity)) { $errors[] = 'Quantity must be a non-negative integer.'; }
        if ($description == '') { $errors[] = 'Description is required.'; }
        if ($defaultPhotoChoice != '1' && $defaultPhotoChoice != '2' && $defaultPhotoChoice != '3') { $errors[] = 'Default Photo must be Photo 1, Photo 2, or Photo 3.'; }

        for ($i = 1; $i <= 3; $i++) {
            $field = 'replace_photo' . $i;

            if (isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES[$field]['error'] != UPLOAD_ERR_OK) {
                    $errors[] = 'Replace Photo ' . $i . ' upload failed.';
                } elseif ($_FILES[$field]['type'] != 'image/jpeg') {
                    $errors[] = 'Replace Photo ' . $i . ' must be image/jpeg.';
                }
            }
        }

        if (count($errors) == 0) {
            $photo1 = $product['photo1'];
            $photo2 = $product['photo2'];
            $photo3 = $product['photo3'];

            $moveOk = true;

            if (isset($_FILES['replace_photo1']) && $_FILES['replace_photo1']['error'] == UPLOAD_ERR_OK) {
                $photo1 = $id . '_1.jpeg';

                if (!move_uploaded_file($_FILES['replace_photo1']['tmp_name'], 'images/' . $photo1)) {
                    $moveOk = false;
                }
            }

            if (isset($_FILES['replace_photo2']) && $_FILES['replace_photo2']['error'] == UPLOAD_ERR_OK) {
                $photo2 = $id . '_2.jpeg';

                if (!move_uploaded_file($_FILES['replace_photo2']['tmp_name'], 'images/' . $photo2)) {
                    $moveOk = false;
                }
            }

            if (isset($_FILES['replace_photo3']) && $_FILES['replace_photo3']['error'] == UPLOAD_ERR_OK) {
                $photo3 = $id . '_3.jpeg';

                if (!move_uploaded_file($_FILES['replace_photo3']['tmp_name'], 'images/' . $photo3)) {
                    $moveOk = false;
                }
            }

            if (!$moveOk) {
                $errors[] = 'One or more uploaded images could not be saved.';
            } else {
                $defaultPhoto = $photo1;

                if ($defaultPhotoChoice == '2') {
                    $defaultPhoto = $photo2;
                }

                if ($defaultPhotoChoice == '3') {
                    $defaultPhoto = $photo3;
                }

                $update = $pdo->prepare('UPDATE products SET price = :price, quantity = :quantity, description = :description, photo1 = :photo1, photo2 = :photo2, photo3 = :photo3, default_photo = :default_photo WHERE product_id = :product_id');
                $update->bindValue(':price', $price);
                $update->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
                $update->bindValue(':description', $description);
                $update->bindValue(':photo1', $photo1);
                $update->bindValue(':photo2', $photo2);
                $update->bindValue(':photo3', $photo3);
                $update->bindValue(':default_photo', $defaultPhoto);
                $update->bindValue(':product_id', (int)$id, PDO::PARAM_INT);
                $update->execute();

                redirect_to('view.php?id=' . $id);
            }
        }
    }
}

prepare_layout('Edit Product');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Edit Product</h2>

    <?php show_errors($errors); ?>

    <?php if ($product) { ?>

    <form action="edit.php" method="post" enctype="multipart/form-data" class="product-form">
      <fieldset>
        <legend>Product Data</legend>

        <input type="hidden" name="product_id" value="<?php echo e($id); ?>">

        <div class="form-row">
          <label for="product_id_display">Product ID</label>
          <input type="text" id="product_id_display" value="<?php echo e($product['product_id']); ?>" disabled>
        </div>

        <div class="form-row">
          <label for="product_name_display">Product Name</label>
          <input type="text" id="product_name_display" value="<?php echo e($product['product_name']); ?>" disabled>
        </div>

        <div class="form-row">
          <label for="category_display">Category</label>
          <select id="category_display" disabled>
            <option selected><?php echo e($product['category']); ?></option>
          </select>
        </div>

        <div class="form-row">
          <label for="rating_display">Rating</label>
          <input type="text" id="rating_display" value="<?php echo e($product['rating']); ?>" disabled>
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
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="6" required><?php echo e($description); ?></textarea>
        </div>
      </fieldset>

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

      <fieldset>
        <legend>Replace Photo</legend>

        <div class="replace-photos">
          <div class="replace-photo-item">
            <img src="images/<?php echo e($product['photo1']); ?>" alt="Photo 1">
            <label for="replace_photo1">Replace Photo 1</label>
            <input type="file" id="replace_photo1" name="replace_photo1" accept="image/jpeg">
          </div>

          <div class="replace-photo-item">
            <img src="images/<?php echo e($product['photo2']); ?>" alt="Photo 2">
            <label for="replace_photo2">Replace Photo 2</label>
            <input type="file" id="replace_photo2" name="replace_photo2" accept="image/jpeg">
          </div>

          <div class="replace-photo-item">
            <img src="images/<?php echo e($product['photo3']); ?>" alt="Photo 3">
            <label for="replace_photo3">Replace Photo 3</label>
            <input type="file" id="replace_photo3" name="replace_photo3" accept="image/jpeg">
          </div>
        </div>
      </fieldset>

      <p class="form-actions">
        <button type="submit" class="btn btn-green">Save Changes</button>
        <a class="btn btn-neutral" href="view.php?id=<?php echo e($id); ?>">Cancel</a>
      </p>
    </form>

    <?php } ?>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>