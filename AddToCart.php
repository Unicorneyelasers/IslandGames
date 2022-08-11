<?php
session_start();

require('Config.php');
$hide = false;
$gameID = $customerID = -1;
$mainTitle = $infoText = "";

if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
    $mainTitle = "Error";
    $infoText = "You must be logged in to add a product to your cart.";
    $hide = true;
} else {
    if (isset($_GET['game'])) {
        $gameID = $_GET['game'];
    }

    if ($gameID == -1) {
        $mainTitle = "Error";
        $infoText = "There was an issue adding to the cart";
    } else {
        if (!validProduct($link, $gameID)) {
            $mainTitle = "Error";
            $infoText = "That isn't a valid product ID";
        } else {
            if (!isset($_SESSION["customer_id"])) {
                $mainTitle = "Error";
                $infoText = "There was an error finding your customer ID";
            } else {
                $customerID = $_SESSION["customer_id"];
            }

            if (alreadyAdded($link, $customerID, $gameID)) {
                $mainTitle = "Error";
                $infoText = "This product is already in your cart!";
            } else {
                $success = addToCart($link, $customerID, $gameID);
                if ($success) {
                    $mainTitle = "Success";
                    $infoText = "The product has been added to your cart!";
                } else {
                    $mainTitle = "Error";
                    $infoText = "There was an error adding the product to your cart";
                }
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

function alreadyAdded($link, $customerID, $prodID)
{
    $sql = "SELECT * FROM cart WHERE customer_id=? AND product_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $customerID, $prodID);

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

function addToCart($link, $customerID, $prodID)
{
    $sql = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $customerID, $prodID);

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
    <title>Cart</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/cart.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" integrity="sha256-46r060N2LrChLLb5zowXQ72/iKKNiw/lAmygmHExk/o=" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include('NavBar.php') ?>
    <?php if ($hide) {
        echo ("<script>$(document).ready(function () { 
            $('.btn.btn-success').css('display', 'none');
            });</script>");
    } ?>
    <!-- Wrapper div for the main content -->
    <div id="mainContent">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-2"></div>
                <div class="col-lg-4">
                    <div class="card" style="width: 18rem;">
                        <div class="card-body-message">
                            <h2 class="card-title-cart"><?php echo $mainTitle ?></h2><br>
                            <p class="card-text"><?php echo $infoText ?></p>
                            <div id="searchbar">
                                <a href="Cart.php" class="btn btn-success" type="submit" value="Search">Go to cart.</a>
                                <a href="Products.php" class="btn btn-success" type="submit" value="Search">Continue browsing</a>
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