<?php require_once('./config.php'); ?>

<?php

    $total = 100.00;
    echo "<h3>The test total is: ".$total."</h3>";
    ?>

<form action="stripe-charge.php" method="post">
  <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="pk_test_51GuV5oCFWNsys1YILn2ENhzZ8kxaGbLUJ8XUZMqNGr2IBEfwehXd3lq9jlTpGtxjwjTg1Ze84zXgpHnJqBk2w5Fk002AIvJx8S"
          data-description="<?php echo 'Payment Checkout'; ?>"
          data-amount="<?php echo $total*100; ?>"
          data-locale="auto"></script>
            <input type="hidden" name="totalamt" value="<?php echo $total*100; ?>" />
</form>