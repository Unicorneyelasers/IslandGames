<?php
session_start();

require "Config.php";
require "Common.php";

if (!isLoggedin()) {
    exit;
}

if (!termsAccepted()) {
    exit;
}

$customerID = $_SESSION["customer_id"];

if (isset($_GET["action"])) {
    if ($_GET["action"] == "delete") {
        if (isset($_GET["id"])) {
            $prodID = $_GET["id"];
            if (validProduct($link, $prodID)) {
                if (isInCart($link, $prodID, $customerID)) {
                    if (removeProduct($link, $prodID, $customerID)) {
                        echo '
                        <script type="text/javascript">
                            alert("The product has been removed from your cart!"); 
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    } else {
                        echo '
                        <script type="text/javascript">
                            alert("There was an error removing the product for your cart."); 
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    }
                }
            }
        }
    }

    if ($_GET["action"] == "add") {
        if (isset($_GET["id"])) {
            $prodID = $_GET["id"];
            if (validProduct($link, $prodID)) {
                if (isInCart($link, $prodID, $customerID)) {
                    if (updateCart($link, $prodID, $customerID, "add")) {
                        echo '
                        <script type="text/javascript">
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    } else {
                        echo '
                        <script type="text/javascript">
                            alert("There was an error updating the quantity for that product."); 
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    }
                }
            }
        }
    }

    if ($_GET["action"] == "minus") {
        if (isset($_GET["id"])) {
            $prodID = $_GET["id"];
            if (validProduct($link, $prodID)) {
                if (isInCart($link, $prodID, $customerID)) {
                    if (updateCart($link, $prodID, $customerID, "remove")) {
                        echo '
                        <script type="text/javascript">
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    } else {
                        echo '
                        <script type="text/javascript">
                            alert("There was an error updating the quantity for that product."); 
                            window.location.href = "Cart.php";
                        </script>';
                        exit;
                    }
                }
            }
        }
    }
}

function updateCart($link, $prodID, $customerID, $type)
{
    $quantity = getCurQuantity($link, $prodID, $customerID);
    if ($quantity == null) {
        return false;
    } else {
        $sql = "";
        $newQuantity = -1;

        if ($type == "add") {
            $newQuantity = ++$quantity;
        } else if ($type == "remove") {
            $newQuantity = --$quantity;
        }

        if ($newQuantity == 0) {
            return removeProduct($link, $prodID, $customerID);
        } else {
            $sql = "UPDATE cart SET quantity=? WHERE customer_id=? AND product_id=?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $newQuantity, $customerID, $prodID);

                if (mysqli_stmt_execute($stmt)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}

function getCurQuantity($link, $prodID, $customerID)
{
    $sql = "SELECT quantity FROM cart WHERE product_id=? AND customer_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $prodID, $customerID);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $quantity);
                if (mysqli_stmt_fetch($stmt)) {
                    return $quantity;
                }
            } else {
                return null;
            }
        }
    }
}

function removeProduct($link, $prodID, $customerID)
{
    $sql = "DELETE FROM cart WHERE customer_id=? AND product_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $customerID, $prodID);

        if (mysqli_stmt_execute($stmt)) {
            return true;
        } else {
            return false;
        }
    }
}

function isInCart($link, $prodID, $customerID)
{
    $sql = "SELECT product_id FROM cart WHERE product_id=? AND customer_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $prodID, $customerID);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function validProduct($link, $prodID)
{
    $sql = "SELECT product_id FROM products WHERE product_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $prodID);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function getCart($link)
{
    $customerID = $_SESSION["customer_id"];
    $sql = "SELECT * FROM cart WHERE customer_id=$customerID";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) == 0) {
        return null;
    } else {
        return $result;
    }
}

function getGameData($link, $productID)
{
    $sql = "SELECT product_id, title, price FROM products WHERE product_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $productID);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $productID, $title, $price);
                if (mysqli_stmt_fetch($stmt)) {
                    return array("productID" => $productID, "title" => $title, "price" => $price);
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
    <meta name="author" content="Cory Audette-Tuckwood">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cart</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/cart.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" integrity="sha256-46r060N2LrChLLb5zowXQ72/iKKNiw/lAmygmHExk/o=" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include('NavBar.php') ?>

    <!-- Wrapper div for the main content -->
    <div id="mainContent">
        <div id="contentWrapper">
            <div class="container">
                <div class="row">
                    <div class="col-md-1" id="blank"></div>

                    <div class="col-md-10" id="cart">
                        <h1> Shopping Cart </h1>
                        <br>
                        <table class="cart-table table-bordered">
                            <tr>
                                <th width="30%" class="tableTitle">Item</th>
                                <th width="20%" class="quantity">Quantity</th>
                                <th width="20%" class="price">Price</th>
                                <th width="15%" class="total">Total</th>
                                <th width="5%" class="action">Action</th>
                            </tr>
                            <?php
                            $cart = getCart($link);

                            $total = 0;
                            if ($cart != null) {
                                while ($row = mysqli_fetch_array($cart, MYSQLI_ASSOC)) {
                                    $gameData = getGameData($link, $row["product_id"]);

                                    echo '<tr>';
                                    echo '<td class="tableTitle">' . $gameData["title"] . '</td>';
                                    echo '<td>';
                                    echo '<div class="quantity">';
                                    echo '<a href="Cart.php?action=minus&id=' . $row["product_id"] . '"<i class="fa fa-minus" aria-hidden="true"></i></a>';
                                    echo '<span class="quantityText">' . $row["quantity"] . '</span>';
                                    echo '<a href="Cart.php?action=add&id=' . $row["product_id"] . '"<i class="fa fa-plus" aria-hidden="true"></i></a>';
                                    echo '</div>';
                                    echo '</td>';
                                    echo '<td>$' . $gameData["price"] . '</td>';
                                    echo '<td>$' . number_format($row["quantity"] * $gameData["price"], 2) . '</td>';

                                    echo '<td><a href="Cart.php?action=delete&id=' . $row["product_id"] . '"<span class="text-danger">Remove</span></a></td>';
                                    echo '</tr>';

                                    $total = $total + ($gameData["price"] * $row["quantity"]);
                                }
                            } else {

                                echo "<h1>No Products</h1>";
                            }
                            ?>
                            <tr>
                                <td colspan="3" align="right">Total</td>
                                <td align="right">$<?php echo number_format($total, 2); ?></td>
                                <td></td>
                            </tr>
                        </table>
                        <br>

                        <?php
                        $customerID = $_SESSION["customer_id"];
                        $sql = "SELECT * FROM cart WHERE customer_id=$customerID";
                        $result = mysqli_query($link, $sql);

                        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && mysqli_num_rows($result) > 0) { ?>
                            <form action="stripe-charge.php" method="post">
                                <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="pk_test_51GuV5oCFWNsys1YILn2ENhzZ8kxaGbLUJ8XUZMqNGr2IBEfwehXd3lq9jlTpGtxjwjTg1Ze84zXgpHnJqBk2w5Fk002AIvJx8S" data-description="<?php echo 'Payment Checkout'; ?>" data-amount="<?php echo $total * 100; ?>" data-locale="auto"></script>
                                <input class="stripe-button" type="hidden" name="totalamt" value="<?php echo $total * 100; ?>" />
                            </form> <?php }  ?>
                    </div>
                    <div class="col-md-1" id="blank"></div>
                </div>
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