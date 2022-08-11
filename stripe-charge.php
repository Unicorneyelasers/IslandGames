<?php
require_once('./config.php');
session_start();

$token  = $_POST['stripeToken'];
$email  = $_POST['stripeEmail'];

$totalamt = $_POST['totalamt'];

// echo "Total amt: $totalamt";
$customer = \Stripe\Customer::create(
  array(
    'email' => $email,
    'source' => $token,
  )
);

$charge = \Stripe\Charge::create(
  array(
    'customer' => $customer->id,
    'amount'   => $totalamt,
    'currency' => 'cad',
  )
);

$amount = number_format(($totalamt / 100), 2);

$customerID = $_SESSION["customer_id"];


if (updateOrderHistory($link, $customerID)) {
  $ordHistID = getMaxOrdID($link, $customerID);
  echo ("<script>console.log('Order History updated');</script>");


  $sql = "select * from cart where customer_id ='$customerID'";
  $result = mysqli_query($link, $sql);

  $receiptProdcts = array();

  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

    $prodID = $row['product_id'];
    $quant = $row['quantity'];

    array_push($receiptProdcts, array($prodID, $quant));

    if (updateOrdProd($link, $ordHistID, $prodID, $quant)) {
      echo ("<script>console.log('OrderProd updated');</script>");
    }
  }

  createReceiptFile($link, $receiptProdcts, $customerID, $ordHistID);

  if (emptyCart($link, $customerID)) {
    echo ("<script>console.log('Cart Empty');</script>");
  }
}




function createReceiptFile($link, $receiptProdcts, $custID, $ordHistID)
{
  $receiptFile = fopen("Receipts/receipt_$ordHistID.txt", "w");

  $grandTotal = 0;
  $orderDate = orderDate($link, $ordHistID);
  $custName = getCustomerName($link, $custID);
  $receiptText = "Dear $custName,\n\nThanks for your recent purchase from Island Games Portal. Here is your receipt for the purchase.\n\n";

  $receiptGames = "";
  for ($i = 0; $i < count($receiptProdcts); $i++) {
    $cur = $receiptProdcts[$i];
    $prodID = $cur[0];
    $prodQuant = $cur[1];
    $gameData = getGameData($link, $prodID);
    $gameTitle = $gameData[0];
    $gamePrice = $gameData[1];
    $total = $gamePrice * $prodQuant;
    $grandTotal = $grandTotal + $total;

    $receiptGames .= "$gameTitle x $prodQuant: $$total CAD\n";
  }

  $receiptText .= $receiptGames;
  $receiptText .= "\nGrand Total: $$grandTotal CAD\n\nOrder Number: $ordHistID\nOrder Date: $orderDate";

  fwrite($receiptFile, $receiptText);
}

function orderDate($link, $ordHistID)
{
  $sql = "SELECT purchase_date FROM order_history WHERE order_history_id=?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $ordHistID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $date);
        if (mysqli_stmt_fetch($stmt)) {
          return $date;
        } else {
          return null;
        }
      }
    }
  }
}

function getGameData($link, $prodID)
{
  $sql = "SELECT title, price FROM products WHERE product_id=?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $prodID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $productName, $productPrice);
        if (mysqli_stmt_fetch($stmt)) {
          return array($productName, $productPrice);
        } else {
          return null;
        }
      }
    }
  }
}

function getCustomerName($link, $custID)
{
  $sql = "SELECT display_name FROM customers WHERE customer_id=?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $custID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $custName);
        if (mysqli_stmt_fetch($stmt)) {
          return $custName;
        } else {
          return null;
        }
      }
    }
  }
}

function updateOrderHistory($link, $customerID)
{
  $insertCustNum = "insert into order_history (customer_id) select distinct customer_id from cart where customer_id = ?";
  if ($stmt = mysqli_prepare($link, $insertCustNum)) {
    mysqli_stmt_bind_param($stmt, "i", $customerID);
    if (mysqli_stmt_execute($stmt)) {
      return true;
    } else {
      echo mysqli_stmt_error($stmt);
      return false;
    }
  }
}

function getProductID($link, $customerID)
{
  $sql = "select product_id from cart where customer_id=?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $customerID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) >= 1) {
        mysqli_stmt_bind_result($stmt, $prodID);
        if (mysqli_stmt_fetch($stmt)) {
          return $prodID;
        } else {
          return null;
        }
      }
    }
  }
}
function getMaxOrdID($link, $customerID)
{
  $sql = "select max(order_history_id) from order_history where customer_id =?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $customerID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $ordHistID);
        if (mysqli_stmt_fetch($stmt)) {
          return $ordHistID;
        } else {
          return null;
        }
      }
    }
  }
}
function getProdQuant($link, $customerID)
{
  $sql = "select quantity from cart where customer_id =?";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $customerID);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $quant);
        if (mysqli_stmt_fetch($stmt)) {
          return $quant;
        } else {
          return null;
        }
      }
    }
  }
}
function updateOrdProd($link, $ordHistID, $prodID, $quant)
{

  $insertOrdProd = "insert into orderproducts values(?,?,?)";
  if ($stmt = mysqli_prepare($link, $insertOrdProd)) {
    mysqli_stmt_bind_param($stmt, "iii", $ordHistID, $prodID, $quant);
    if (mysqli_stmt_execute($stmt)) {
      return true;
    } else {
      echo mysqli_stmt_error($stmt);
      return false;
    }
  }
}



function emptyCart($link, $customerID)
{
  $empty = "delete from cart where customer_id =?";
  if ($stmt = mysqli_prepare($link, $empty)) {
    mysqli_stmt_bind_param($stmt, "i", $customerID);

    if (mysqli_stmt_execute($stmt)) {
      return true;
    } else {
      echo mysqli_stmt_error($stmt);
      return false;
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="author" content="Cory Audette-Tuckwood">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Successful Payment</title>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <link rel="stylesheet" href="Includes/CSS/charge.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" integrity="sha256-46r060N2LrChLLb5zowXQ72/iKKNiw/lAmygmHExk/o=" crossorigin="anonymous" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
  <?php include('NavBar.php') ?>

  <!-- Wrapper div for the main content -->
  <div id="mainContent">
    <div class="container">
      <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-2"></div>
        <div class="col-lg-4">
          <div class="card" style="width: 18rem;">
            <div class="card-body-message">
              <h1 class="card-title-charge">Success!</h1><br>
              <p class="card-text"><?php
                                    echo 'Payment of $' . $amount . ' successfully charged.<br><br> Thank you for shopping with Island Games Portal';
                                    ?></p>
              <div id="searchbar">
                <a href="Index.php" class="btn btn-success" type="submit" value="Search">Home</a>
                <br>
                <a href="Products.php" class="btn btn-success" type="submit" value="Search">Continue Shopping</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Divider -->
        <div class="col-md-2"></div>
        <div class="col-md-2"></div>

      </div>
    </div>
  </div>

  <?php include('Footer.php') ?>

  <script src="Includes/JS/index.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>