<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'Cory');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'backendigp');

// Create connection
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$link) {
  die("Error: Connection to the database failed: " . mysqli_connect_error());
}
?>

<?php
require_once('vendor/autoload.php');

$stripe = [
  "secret_key"      => "sk_test_51GuV5oCFWNsys1YIsahFTsEOFJrpa4SDTMtWh4xDRoEqbJk9HOpZYpYMytaTOqtDDSQmoDN0sPRz6lW11VNlXQrP00dxrmQERq",
  "publishable_key" => "pk_test_51GuV5oCFWNsys1YILn2ENhzZ8kxaGbLUJ8XUZMqNGr2IBEfwehXd3lq9jlTpGtxjwjTg1Ze84zXgpHnJqBk2w5Fk002AIvJx8S",
];

\Stripe\Stripe::setApiKey($stripe['secret_key']);
?>