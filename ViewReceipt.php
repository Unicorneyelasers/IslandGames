<?php
session_start();

require('Config.php');
require("Common.php");

if (isLoggedin() && !termsAccepted()) {
    exit;
}

$customerName = "";

if (isset($_GET["id"])) {
    $orderID = $_GET['id'];

    $orderData = isValidReceipt($link, $orderID);
    if ($orderData != null) {
        $curCustomerID = $_SESSION["customer_id"];
        if ($curCustomerID == $orderData["customerID"]) {
            $customerName = getCustomerName($link, $curCustomerID);
        } else {
            echo '
            <script type="text/javascript">
                alert("You can\'t view this receipt"); 
                window.location.href = "OrderHistory.php";
            </script>';
        }
    } else {
        echo '
        <script type="text/javascript">
            alert("Invalid Receipt ID"); 
            window.location.href = "OrderHistory.php";
        </script>';
    }
} else {
    echo '
    <script type="text/javascript">
        alert("Receipt ID not found"); 
        window.location.href = "OrderHistory.php";
    </script>';
}

function isValidReceipt($link, $orderID)
{
    $sql = "SELECT * FROM order_history WHERE order_history_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $orderID);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) >= 1) {
                mysqli_stmt_bind_result($stmt, $order_ID, $purchase_date, $customer_ID);
                if (mysqli_stmt_fetch($stmt)) {
                    return array("orderID" => $order_ID, "purchaseDate" => $purchase_date, "customerID" => $customer_ID);
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

function getOrderItems($link, $orderID)
{
    $sql = "SELECT * FROM orderproducts WHERE order_history_id=$orderID";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) == 0) {
        return null;
    } else {
        return $result;
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





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="author" content="Tyson Hoops">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Receipt View</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/receipt.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" integrity="sha256-46r060N2LrChLLb5zowXQ72/iKKNiw/lAmygmHExk/o=" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include('NavBar.php') ?>
    <!-- Wrapper div for the main content -->
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-body-register">
                        <div id="innerWrapper">
                            <h1>Receipt #<?php echo $orderID ?></h1>
                            <h3>Dear <?php echo $customerName ?>,</h3><br>
                            <p>Thanks for your recent purchase from Island Games Portal. Please save this receipt for your records.</p><br><br>
                            <table>
                                <tr>
                                    <th class="prodName">Product Name</th>
                                    <th class="prodQuant">Product Quantity</th>
                                    <th class="price">Price</th>
                                </tr>
                                <?php
                                $items = getOrderItems($link, $orderID);

                                while ($row = mysqli_fetch_array($items, MYSQLI_ASSOC)) {
                                    $gameData = getGameData($link, $row["product_id"]);
                                    $price = $gameData[1] * $row["quantity"];

                                    echo '<tr>';
                                    echo '<td> ' . $gameData[0] . ' </td>';
                                    echo '<td class="prodQuant"> ' . $row["quantity"] . ' </td>';
                                    echo '<td>$' . $price . ' </td>';
                                    echo '</tr>';
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('Footer.php') ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>