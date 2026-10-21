<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';

start_session_once();

$errors = array();
$email = '';
$alreadyLogged = false;

if ($_SERVER['REQUEST_METHOD'] != 'POST' && is_logged_in()) {
    $alreadyLogged = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = post_value('email');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email == '') { $errors[] = 'Email is required.'; }
    if ($password == '') { $errors[] = 'Password is required.'; }

    if (count($errors) == 0) {
        $stmt = $pdo->prepare('SELECT user_id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            if (isset($_SESSION['needed_role']) && $_SESSION['needed_role'] != '' && $user['role'] != $_SESSION['needed_role']) {
                $errors[] = 'Please log in using an account with role: ' . $_SESSION['needed_role'] . '.';
            } else {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];

                $returnTo = 'products.php';

                if (isset($_SESSION['return_to']) && $_SESSION['return_to'] != '') {
                    $returnTo = $_SESSION['return_to'];
                    unset($_SESSION['return_to']);
                }

                unset($_SESSION['needed_role']);

                redirect_to($returnTo);
            }
        }
    }
}

prepare_layout('Login');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Login</h2>

    <?php
    show_errors($errors);

    if ($alreadyLogged) {
        echo '<section class="success-box">';
        echo '<p>You are already logged in as ' . e($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) . ' (' . e($_SESSION['role']) . ').</p>';

        if (isset($_SESSION['needed_role']) && $_SESSION['needed_role'] != '' && $_SESSION['role'] != $_SESSION['needed_role']) {
            echo '<p>This page requires role: ' . e($_SESSION['needed_role']) . '. Please logout and login with the correct account.</p>';
        }

        echo '<p><a class="btn btn-neutral" href="logout.php">Logout</a></p>';
        echo '</section>';
    } else {
    ?>

    <form action="login.php" method="post" class="login-form">
      <fieldset>
        <legend>User Credentials</legend>

        <div class="form-row">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
        </div>

        <div class="form-row">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>

        <p class="form-actions">
          <button type="submit" class="btn btn-green">Login</button>
        </p>
      </fieldset>
    </form>

    <?php } ?>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>