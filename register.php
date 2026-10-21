<?php
require_once 'functions.inc.php';
require_once 'dbconfig.inc.php';

start_session_once();

$errors = array();
$success = false;
$newUserId = '';

$firstName = '';
$lastName = '';
$email = '';
$mobile = '';
$dateOfBirth = '';
$flatNo = '';
$street = '';
$city = '';
$country = '';
$postalCode = '';
$role = 'Customer';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = post_value('first_name');
    $lastName = post_value('last_name');
    $email = post_value('email');
    $mobile = post_value('mobile');
    $dateOfBirth = post_value('date_of_birth');
    $flatNo = post_value('flat_no');
    $street = post_value('street');
    $city = post_value('city');
    $country = post_value('country');
    $postalCode = post_value('postal_code');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'Customer';

    if ($firstName == '') { $errors[] = 'First Name is required.'; }
    if ($lastName == '') { $errors[] = 'Last Name is required.'; }
    if ($email == '') { $errors[] = 'Email Address is required.'; }
    if ($mobile == '') { $errors[] = 'Mobile Number is required.'; }
    if ($dateOfBirth == '') { $errors[] = 'Date of Birth is required.'; }
    if ($street == '') { $errors[] = 'Street Name and Number is required.'; }
    if ($city == '') { $errors[] = 'City is required.'; }
    if ($country == '') { $errors[] = 'Country is required.'; }
    if ($postalCode == '') { $errors[] = 'Postal Code is required.'; }
    if ($password == '') { $errors[] = 'Password is required.'; }
    if ($confirmPassword == '') { $errors[] = 'Confirm Password is required.'; }

    if ($email != '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email Address must be in a valid format.';
    }

    if ($email != '') {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $errors[] = 'Email Address already exists.';
        }
    }

    if ($dateOfBirth != '') {
        if (!valid_birth_date($dateOfBirth)) {
            $errors[] = 'Date of Birth must be a valid date.';
        } else {
            $today = strtotime(date('Y-m-d'));
            $birth = strtotime($dateOfBirth);

            if ($birth >= $today) {
                $errors[] = 'Date of Birth must be before today.';
            } elseif (age_from_birth_date($dateOfBirth) < 18) {
                $errors[] = 'User must be at least 18 years old.';
            }
        }
    }

    if ($postalCode != '' && (!ctype_digit($postalCode) || strlen($postalCode) != 6)) {
        $errors[] = 'Postal Code must contain exactly 6 numeric digits.';
    }

    if ($password != $confirmPassword) {
        $errors[] = 'Password and Confirm Password must match.';
    }

    if ($role != 'Customer' && $role != 'Employee') {
        $errors[] = 'Role must be Customer or Employee.';
    }

    if (count($errors) == 0) {
        $newUserId = generate_user_id($pdo);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (user_id, first_name, last_name, email, mobile, date_of_birth, flat_no, street, city, country, postal_code, password_hash, role) VALUES (:user_id, :first_name, :last_name, :email, :mobile, :date_of_birth, :flat_no, :street, :city, :country, :postal_code, :password_hash, :role)');
        $stmt->bindValue(':user_id', $newUserId);
        $stmt->bindValue(':first_name', $firstName);
        $stmt->bindValue(':last_name', $lastName);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':mobile', $mobile);
        $stmt->bindValue(':date_of_birth', $dateOfBirth);
        $stmt->bindValue(':flat_no', $flatNo);
        $stmt->bindValue(':street', $street);
        $stmt->bindValue(':city', $city);
        $stmt->bindValue(':country', $country);
        $stmt->bindValue(':postal_code', $postalCode);
        $stmt->bindValue(':password_hash', $passwordHash);
        $stmt->bindValue(':role', $role);
        $stmt->execute();

        $success = true;
    }
}

prepare_layout('Register');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>User Registration</h2>

    <?php
    if ($success) {
        echo '<section class="success-box">';
        echo '<p>Registration completed successfully.</p>';
        echo '<p>Your 10-digit User ID is: <strong>' . e($newUserId) . '</strong></p>';
        echo '<p><a class="btn btn-green" href="login.php">Go to Login</a></p>';
        echo '</section>';
    } else {
        show_errors($errors);
    ?>

    <form action="register.php" method="post" class="register-form">
      <fieldset>
        <legend>Personal Information</legend>

        <div class="form-row">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name" value="<?php echo e($firstName); ?>" required>
        </div>

        <div class="form-row">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name" value="<?php echo e($lastName); ?>" required>
        </div>

        <div class="form-row">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
        </div>

        <div class="form-row">
          <label for="mobile">Mobile Number</label>
          <input type="text" id="mobile" name="mobile" value="<?php echo e($mobile); ?>" required>
        </div>

        <div class="form-row">
          <label for="date_of_birth">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo e($dateOfBirth); ?>" required>
        </div>
      </fieldset>

      <fieldset>
        <legend>Address</legend>

        <div class="form-row">
          <label for="flat_no">Flat / Unit No</label>
          <input type="text" id="flat_no" name="flat_no" value="<?php echo e($flatNo); ?>">
        </div>

        <div class="form-row">
          <label for="street">Street Name and Number</label>
          <input type="text" id="street" name="street" value="<?php echo e($street); ?>" required>
        </div>

        <div class="form-row">
          <label for="city">City</label>
          <input type="text" id="city" name="city" value="<?php echo e($city); ?>" required>
        </div>

        <div class="form-row">
          <label for="country">Country</label>
          <input type="text" id="country" name="country" value="<?php echo e($country); ?>" required>
        </div>

        <div class="form-row">
          <label for="postal_code">Postal Code</label>
          <input type="text" id="postal_code" name="postal_code" value="<?php echo e($postalCode); ?>" required>
        </div>
      </fieldset>

      <fieldset>
        <legend>Account</legend>

        <div class="form-row">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>

        <div class="form-row">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <fieldset class="role-fieldset">
          <legend>Role</legend>

          <div class="radio-group">
            <label>
              <input type="radio" name="role" value="Customer"<?php echo checked_if('Customer', $role); ?>>
              Customer
            </label>

            <label>
              <input type="radio" name="role" value="Employee"<?php echo checked_if('Employee', $role); ?>>
              Employee
            </label>
          </div>
        </fieldset>
      </fieldset>

      <p class="form-actions">
        <button type="submit" class="btn btn-green">Register</button>
      </p>
    </form>

    <?php } ?>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>