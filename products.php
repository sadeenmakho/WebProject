<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';
require_once 'Product.class.php';

start_session_once();

function default_product_state() {
    return array(
        'name' => '',
        'max_price' => '',
        'category' => '',
        'sort_col' => 'product_id',
        'sort_dir' => 'ASC',
        'current_page' => 1,
        'per_page' => 10
    );
}

function clean_product_state($state) {
    $default = default_product_state();

    foreach ($default as $key => $value) {
        if (!isset($state[$key])) {
            $state[$key] = $value;
        }
    }

    $state['name'] = trim((string)$state['name']);
    $state['max_price'] = trim((string)$state['max_price']);
    $state['category'] = trim((string)$state['category']);

    if ($state['max_price'] != '' && !is_numeric($state['max_price'])) {
        $state['max_price'] = '';
    }

    if ($state['sort_col'] != 'product_id' && $state['sort_col'] != 'price' && $state['sort_col'] != 'category') {
        $state['sort_col'] = 'product_id';
    }

    if ($state['sort_dir'] != 'ASC' && $state['sort_dir'] != 'DESC') {
        $state['sort_dir'] = 'ASC';
    }

    $state['current_page'] = (int)$state['current_page'];

    if ($state['current_page'] < 1) {
        $state['current_page'] = 1;
    }

    $state['per_page'] = 10;

    return $state;
}

function save_product_state($state) {
    $_SESSION['product_search_state'] = json_encode($state);
}

function load_product_state() {
    $state = default_product_state();

    if (isset($_SESSION['product_search_state']) && $_SESSION['product_search_state'] != '') {
        $decoded = json_decode($_SESSION['product_search_state'], true);

        if (is_array($decoded)) {
            $state = $decoded;
        }
    }

    return clean_product_state($state);
}

function products_url($state, $changes) {
    foreach ($changes as $key => $value) {
        $state[$key] = $value;
    }

    $params = array(
        'name' => $state['name'],
        'max_price' => $state['max_price'],
        'category' => $state['category'],
        'sort_col' => $state['sort_col'],
        'sort_dir' => $state['sort_dir'],
        'page' => $state['current_page']
    );

    return 'products.php?' . http_build_query($params);
}

function bind_search_values($stmt, $state) {
    $stmt->bindValue(':name_empty', $state['name']);
    $stmt->bindValue(':name_like', '%' . $state['name'] . '%');
    $stmt->bindValue(':max_price_empty', $state['max_price']);

    if ($state['max_price'] == '') {
        $stmt->bindValue(':max_price', 0);
    } else {
        $stmt->bindValue(':max_price', $state['max_price']);
    }

    $stmt->bindValue(':category_empty', $state['category']);
    $stmt->bindValue(':category_value', $state['category']);
}

if (isset($_GET['reset'])) {
    unset($_SESSION['product_search_state']);
    redirect_to('products.php');
}

$state = load_product_state();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $state['name'] = post_value('name');
    $state['max_price'] = post_value('max_price');
    $state['category'] = post_value('category');
    $state['current_page'] = 1;

    $state = clean_product_state($state);
    save_product_state($state);
} elseif (count($_GET) > 0) {
    if (isset($_GET['name'])) { $state['name'] = get_value('name'); }
    if (isset($_GET['max_price'])) { $state['max_price'] = get_value('max_price'); }
    if (isset($_GET['category'])) { $state['category'] = get_value('category'); }
    if (isset($_GET['sort_col'])) { $state['sort_col'] = get_value('sort_col'); }
    if (isset($_GET['sort_dir'])) { $state['sort_dir'] = get_value('sort_dir'); }
    if (isset($_GET['page'])) { $state['current_page'] = (int)get_value('page'); }

    $state = clean_product_state($state);
    save_product_state($state);
}

$categoryStmt = $pdo->prepare('SELECT DISTINCT category FROM products ORDER BY category ASC');
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll();

$whereSql = ' FROM products WHERE (:name_empty = \'\' OR LOWER(product_name) LIKE LOWER(:name_like)) AND (:max_price_empty = \'\' OR price <= :max_price) AND (:category_empty = \'\' OR category = :category_value)';

$countStmt = $pdo->prepare('SELECT COUNT(*) AS total' . $whereSql);
bind_search_values($countStmt, $state);
$countStmt->execute();
$totalRowsData = $countStmt->fetch();
$totalRows = (int)$totalRowsData['total'];

$totalPages = (int)ceil($totalRows / 10);

if ($totalPages < 1) {
    $totalPages = 1;
}

if ($state['current_page'] > $totalPages) {
    $state['current_page'] = $totalPages;
}

$limitRows = 10;
$offsetRows = ($state['current_page'] - 1) * 10;

save_product_state($state);

$productSql = 'SELECT product_id AS productId, product_name AS productName, category AS category, description AS description, price AS price, quantity AS quantity, rating AS rating, photo1 AS photo1, photo2 AS photo2, photo3 AS photo3, default_photo AS defaultPhoto' . $whereSql . ' ORDER BY product_id ASC LIMIT :limit_rows OFFSET :offset_rows';

$productStmt = $pdo->prepare($productSql);

bind_search_values($productStmt, $state);

$productStmt->bindValue(':limit_rows', $limitRows, PDO::PARAM_INT);
$productStmt->bindValue(':offset_rows', $offsetRows, PDO::PARAM_INT);

$productStmt->execute();

prepare_layout('Products');

require_once 'header.inc.php';
?>

<div class="page-layout">

  <aside>
    <h2>Filter Products</h2>

    <form action="products.php" method="post" class="filter-form">
      <label for="name">Product Name</label>
      <input type="text" id="name" name="name" placeholder="Product Name" value="<?php echo e($state['name']); ?>">

      <label for="max_price">Maximum Price</label>
      <input type="number" step="0.01" id="max_price" name="max_price" placeholder="Max Price" value="<?php echo e($state['max_price']); ?>">

      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="">Select Category</option>

        <?php foreach ($categories as $cat) { ?>
        <option value="<?php echo e($cat['category']); ?>"<?php echo selected_if($cat['category'], $state['category']); ?>>
          <?php echo e($cat['category']); ?>
        </option>
        <?php } ?>
      </select>

      <button type="submit">Filter</button>
      <a href="products.php?reset=1" class="btn btn-neutral">Reset / Show All</a>
    </form>
  </aside>

  <main class="products-grid">
    <h2 class="products-title">Products</h2>

    <?php
    if ($totalRows == 0) {
        echo '<p class="no-products">No products found.</p>';
    } else {
        while ($product = $productStmt->fetchObject('Product')) {
            echo $product->displayAsCard();
        }
    }
    ?>

    <nav class="pagination" aria-label="Pagination">
      <?php
      if ($state['current_page'] > 1) {
          echo '<a class="btn btn-neutral" href="' . e(products_url($state, array('current_page' => $state['current_page'] - 1))) . '">Previous</a>';
      } else {
          echo '<span class="btn btn-neutral disabled">Previous</span>';
      }

      echo '<span>Page ' . e($state['current_page']) . ' of ' . e($totalPages) . '</span>';

      if ($state['current_page'] < $totalPages) {
          echo '<a class="btn btn-neutral" href="' . e(products_url($state, array('current_page' => $state['current_page'] + 1))) . '">Next</a>';
      } else {
          echo '<span class="btn btn-neutral disabled">Next</span>';
      }
      ?>
    </nav>
  </main>

</div>

<?php
require_once 'footer.inc.php';
?>