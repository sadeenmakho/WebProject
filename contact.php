<?php
require_once 'functions.inc.php';

start_session_once();
prepare_layout('Contact Us');

require_once 'header.inc.php';
?>

<div class="page-layout">
  <main class="main-full form-card">
    <h2>Contact Us</h2>

    <section class="contact-info">
      <p>
        Customers can contact us to ask about products, gift recommendations,
        packaging, prices, and international ordering information.
      </p>

      <address>
        Palestine Imprint Souvenirs<br>
        18 Old Market Street<br>
        Ramallah, Palestine<br>
        Telephone: <a href="tel:+97022990000">+970 2 299 0000</a><br>
        Email: <a href="mailto:support@palestineimprint.example">support@palestineimprint.example</a>
      </address>
    </section>

    <section>
      <h2>Send a Message</h2>

      <form action="mailto:support@palestineimprint.example" method="post" enctype="text/plain" class="contact-form">
        <fieldset>
          <legend>Message Information</legend>

          <div class="form-row">
            <label for="sender_name">Sender Name</label>
            <input type="text" id="sender_name" name="sender_name" placeholder="Example: Sara Ahmad" required>
          </div>

          <div class="form-row">
            <label for="sender_email">Sender Email</label>
            <input type="email" id="sender_email" name="sender_email" placeholder="Example: sara@example.com" required>
          </div>

          <div class="form-row">
            <label for="sender_location">Sender Location City</label>
            <input type="text" id="sender_location" name="sender_location" placeholder="Example: London" required>
          </div>

          <div class="form-row">
            <label for="message_subject">Message Subject</label>
            <input type="text" id="message_subject" name="message_subject" placeholder="Example: Question about shipping" required>
          </div>

          <div class="form-row">
            <label for="message_body">Message Body</label>
            <textarea id="message_body" name="message_body" rows="8" placeholder="Write your message here" required></textarea>
          </div>

          <p class="form-actions">
            <button type="submit" class="btn btn-green">Send Message</button>
            <button type="reset" class="btn btn-neutral">Reset Form</button>
          </p>
        </fieldset>
      </form>
    </section>
  </main>
</div>

<?php
require_once 'footer.inc.php';
?>