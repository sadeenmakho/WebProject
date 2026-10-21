<?php
function start_session_once() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function is_employee() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] == 'Employee';
}

function is_customer() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] == 'Customer';
}

function redirect_to($url) {
    header('Location: ' . $url);
    exit;
}

function current_relative_url() {
    $url = basename($_SERVER['PHP_SELF']);

    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
        $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    return $url;
}

function prepare_layout($title) {
    $GLOBALS['page_title'] = $title;

    if (is_logged_in()) {
        $fullName = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

        $GLOBALS['header_user_info'] = '<p>Logged in as ' . e($fullName) . ' (' . e($_SESSION['role']) . ')</p>';
        $GLOBALS['header_auth_links'] = '<li><a href="logout.php">Logout</a></li>';
    } else {
        $GLOBALS['header_user_info'] = '';
        $GLOBALS['header_auth_links'] = '<li><a href="register.php">Register</a></li><li><a href="login.php">Login</a></li>';
    }
}

function require_employee() {
    if (!is_employee()) {
        $_SESSION['return_to'] = current_relative_url();
        $_SESSION['needed_role'] = 'Employee';
        redirect_to('login.php');
    }
}

function post_value($name) {
    if (isset($_POST[$name])) {
        return trim($_POST[$name]);
    }

    return '';
}

function get_value($name) {
    if (isset($_GET[$name])) {
        return trim($_GET[$name]);
    }

    return '';
}

function show_errors($errors) {
    if (count($errors) > 0) {
        echo '<section class="error-box">';
        echo '<h2>Errors</h2>';
        echo '<ul>';

        foreach ($errors as $error) {
            echo '<li>' . e($error) . '</li>';
        }

        echo '</ul>';
        echo '</section>';
    }
}

function selected_if($value, $current) {
    if ((string)$value == (string)$current) {
        return ' selected';
    }

    return '';
}

function checked_if($value, $current) {
    if ((string)$value == (string)$current) {
        return ' checked';
    }

    return '';
}

function is_positive_integer($value) {
    return ctype_digit((string)$value) && (int)$value > 0;
}

function is_non_negative_integer($value) {
    return ctype_digit((string)$value) && (int)$value >= 0;
}

function valid_birth_date($dateText) {
    $parts = explode('-', $dateText);

    if (count($parts) != 3) {
        return false;
    }

    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

function age_from_birth_date($dateText) {
    $birthYear = (int)date('Y', strtotime($dateText));
    $birthMonthDay = date('md', strtotime($dateText));
    $todayYear = (int)date('Y');
    $todayMonthDay = date('md');

    $age = $todayYear - $birthYear;

    if ($todayMonthDay < $birthMonthDay) {
        $age--;
    }

    return $age;
}

function generate_user_id($pdo) {
    do {
        $id = (string)rand(1000000000, 9999999999);

        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $id);
        $stmt->execute();

        $exists = $stmt->fetch();
    } while ($exists);

    return $id;
}

function generate_order_id($pdo) {
    do {
        $id = (string)rand(1000000000, 9999999999);

        $stmt = $pdo->prepare('SELECT order_id FROM orders WHERE order_id = :order_id');
        $stmt->bindValue(':order_id', $id);
        $stmt->execute();

        $exists = $stmt->fetch();
    } while ($exists);

    return $id;
}

function basket_total() {
    $total = 0;

    if (isset($_SESSION['basket']) && is_array($_SESSION['basket'])) {
        foreach ($_SESSION['basket'] as $item) {
            $total += ((float)$item['unit_price']) * ((int)$item['quantity']);
        }
    }

    return $total;
}
?>