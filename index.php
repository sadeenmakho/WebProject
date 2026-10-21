<?php
require_once 'functions.inc.php';

start_session_once();
prepare_layout('Assignment 3 Home');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full home-card">
    <section>
      <h2>Assignment 3: CSS Styling</h2>

      <p>
        Palestine Imprint Souvenirs is a dynamic Palestinian souvenir store using PHP,
        MySQL, PDO, sessions, forms, and server-side validation. In Assignment 3,
        the application is styled using one external CSS file, CSS Grid, and Flexbox.
      </p>

      <p>
        <a class="btn btn-green" href="products.php">Open Product Listing</a>
      </p>

      <figure class="home-figure">
        <img src="images/logo.png" alt="Palestine Imprint Souvenirs logo">
        <figcaption>
          Palestinian souvenirs inspired by ceramics, embroidery, olive trees, and handmade gifts.
        </figcaption>
      </figure>
    </section>

    <section>
      <h2>Testing Usernames and Passwords</h2>

      <table class="basket-table">
        <caption>Use these accounts for testing after importing students_1230865.sql.</caption>

        <thead>
          <tr>
            <th scope="col">Role</th>
            <th scope="col">Username / Email</th>
            <th scope="col">Password</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>Customer</td>
            <td>customer1@example.com</td>
            <td>Customer123</td>
          </tr>

          <tr>
            <td>Customer</td>
            <td>customer2@example.com</td>
            <td>Customer456</td>
          </tr>

          <tr>
            <td>Employee</td>
            <td>employee@example.com</td>
            <td>Employee123</td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>