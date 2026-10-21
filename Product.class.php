<?php
class Product {
    private $productId;
    private $productName;
    private $category;
    private $description;
    private $price;
    private $quantity;
    private $rating;
    private $photo1;
    private $photo2;
    private $photo3;
    private $defaultPhoto;

    public function __construct(
        $productId = null,
        $productName = null,
        $category = null,
        $description = null,
        $price = null,
        $quantity = null,
        $rating = null,
        $photo1 = null,
        $photo2 = null,
        $photo3 = null,
        $defaultPhoto = null
    ) {
        if (func_num_args() > 0) {
            $this->productId = $productId;
            $this->productName = $productName;
            $this->category = $category;
            $this->description = $description;
            $this->price = $price;
            $this->quantity = $quantity;
            $this->rating = $rating;
            $this->photo1 = $photo1;
            $this->photo2 = $photo2;
            $this->photo3 = $photo3;
            $this->defaultPhoto = $defaultPhoto;
        }
    }

    public function __set($name, $value) {
        if ($name == 'productId') $this->productId = $value;
        if ($name == 'productName') $this->productName = $value;
        if ($name == 'category') $this->category = $value;
        if ($name == 'description') $this->description = $value;
        if ($name == 'price') $this->price = $value;
        if ($name == 'quantity') $this->quantity = $value;
        if ($name == 'rating') $this->rating = $value;
        if ($name == 'photo1') $this->photo1 = $value;
        if ($name == 'photo2') $this->photo2 = $value;
        if ($name == 'photo3') $this->photo3 = $value;
        if ($name == 'defaultPhoto') $this->defaultPhoto = $value;
    }

    private function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    private function imagePath($photo) {
    $photo = trim((string)$photo);

    if ($photo === '') {
        return 'images/logo.png';
    }

    return 'images/' . $photo;
}

    private function badgeClass() {
        return 'badge-' . strtolower(str_replace([' ', '/', '-'], '-', $this->category));
    }

    private function activePhotoClass($photo) {
        return ($this->defaultPhoto == $photo) ? 'active-photo' : '';
    }


   public function displayAsCard() {

    $id = $this->h($this->productId);

    $html  = '<article class="product-card">';

    $html .= '<img class="product-main-img" src="' . $this->imagePath($this->defaultPhoto) . '" alt="">';

    $html .= '<a class="product-id" href="view.php?id=' . $id . '">#' . $id . '</a>';

    $html .= '<div class="product-name">' . $this->h($this->productName) . '</div>';

    $photos = array($this->photo1, $this->photo2, $this->photo3);

    $html .= '<div class="thumbnails">';

    foreach ($photos as $p) {
        $active = ($this->defaultPhoto == $p) ? 'active-photo' : '';
        $html .= '<img src="' . $this->imagePath($p) . '" class="' . $active . '" alt="">';
    }

    $html .= '</div>';

    $html .= '<span class="badge ' . $this->badgeClass() . '">' . $this->h($this->category) . '</span>';

    $html .= '<div class="stock">Qty: ' . $this->h($this->quantity) . ' in stock</div>';

    $html .= '<div class="rating">Rating: ' . $this->h($this->rating) . ' / 5</div>';

    $html .= '<div class="price">$' . number_format((float)$this->price, 2) . '</div>';

    $html .= '<nav class="card-actions">';
    $html .= '<a class="btn btn-neutral" href="view.php?id=' . $id . '">View</a>';
    $html .= '<a class="btn btn-green" href="add_to_basket.php?id=' . $id . '">+ Basket</a>';
    $html .= '</nav>';

    $html .= '</article>';

    return $html;
}


    public function displayInTable() {

    $id = $this->h($this->productId);
    $name = $this->h($this->productName);
    $category = $this->h($this->category);
    $price = number_format((float)$this->price, 2);
    $quantity = $this->h($this->quantity);
    $defaultPhotoPath = $this->imagePath($this->defaultPhoto);

    $html = '<tr>';

    $html .= '<td><img src="' . $defaultPhotoPath . '" alt="' . $name . '" width="100"></td>';
    $html .= '<td>' . $name . '</td>';
    $html .= '<td>' . $category . '</td>';
    $html .= '<td>$' . $price . '</td>';
    $html .= '<td>' . $quantity . '</td>';

    $html .= '<td>';

    $html .= '<a href="view.php?id=' . $id . '">View</a>';

    $role = '';

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $role = $_SESSION['role'];
    }

    if ($role == 'Employee') {
        $html .= ' <a href="edit.php?id=' . $id . '">Edit</a>';
    } else {
        $html .= ' <a href="add_to_basket.php?id=' . $id . '">Add</a>';
    }

    $html .= '</td>';

    $html .= '</tr>';

    return $html;
}


    public function displayProductPage() {

        $id = $this->h($this->productId);

        $html  = '<div class="page-layout">';
        $html .= '<main class="main-full">';
        $html .= '<article class="product-detail-card">';

        $html .= '<h2>Product Details</h2>';

        $html .= '<img src="' . $this->imagePath($this->defaultPhoto) . '" width="300">';

        $html .= '<p><b>Name:</b> ' . $this->h($this->productName) . '</p>';
        $html .= '<p><b>Category:</b> ' . $this->h($this->category) . '</p>';
        $html .= '<p><b>Price:</b> $' . number_format((float)$this->price, 2) . '</p>';
        $html .= '<p><b>Description:</b> ' . $this->h($this->description) . '</p>';

        $html .= '<a class="btn btn-neutral" href="products.php">Back</a>';

        $html .= '</article>';
        $html .= '</main>';
        $html .= '</div>';

        return $html;
    }
}
?>