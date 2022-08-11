<?php
session_start();

require "Config.php";
require "Common.php";

if (isLoggedin() && !termsAccepted()) {
    exit;
}

$searchType = $result = "";


// Define variables and initialize with empty values
$add = "";
$add_err = "";

$customerID = $productID = $Quantity = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET["type"])) {
        $searchType = $_GET['type'];

        $sql = "select * from products inner join productgenres using(product_id) inner join genre using (genre_id) where genre like ('$searchType');";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 0) {
            $products = null;
        } else {
            $products = $result;
        }
    } else if (isset($_GET["game"])) {
        //If there are no errors, continue
        if (empty($add_err)) {
            $addID = insertToCart($link, $customerID, $productID, $Quantity);

            if ($addID != null) {
                $add_err = "This item has already been added.";
            } else {
                $success = insertToCart($link, $customerID, $productID, $Quantity);
                if ($success) {
                    echo '
                        <script type="text/javascript">
                            alert("add_to_cart added successfully!"); 
                            window.location.href = "Products.php";
                        </script>';
                    exit;
                } else {
                    print "There was a problem inserting the item to the cart.";
                }
            }
        }
    } else {
        $sql = "SELECT * FROM products";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 0) {
            $products = null;
        } else {
            $products = $result;
        }
    }
}

function insertToCart($link, $customerID, $productID, $Quantity)
{
    $sql = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iii", $customerID, $productID, $Quantity);

        if (mysqli_stmt_execute($stmt)) {
            return true;
        } else {
            echo mysqli_stmt_error($stmt);
            return false;
        }
    }
}

function getProducts($link)
{
    if (isset($_GET["type"])) {
        $searchType = $_GET['type'];

        $sql = "select * from products inner join productgenres using(product_id) inner join genre using (genre_id) where genre like ('$searchType') order by title;";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 0) {
            return null;
        } else {
            return $result;
        }
    } else {
        $sql = "SELECT * FROM products order by title";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 0) {
            return null;
        } else {
            return $result;
        }
    }
}

function getProductImage($link, $prodID)
{
    $sql = "SELECT image FROM images WHERE image_id in (SELECT image_id FROM productimages WHERE product_id=$prodID)";

    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) == 0) {
        return null;
    } else {
        $row = $result->fetch_row();
        $value = $row[0];

        return $value;
    }
}

function getGenres($link)
{
    $sql = "SELECT * FROM genre ORDER BY genre asc";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) == 0) {
        return null;
    } else {
        return $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="author" content="Cory Audette-Tuckwood">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Products</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/products.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include('NavBar.php') ?>

    <!-- Wrapper div for the main content -->
    <div id="mainContent">
        <div id="contentWrapper">
            <div class="container">
                <div class="row">
                    <div class="col-md-9" id="productsList">
                    <h2 class="product-label">Browse Our Products</h2>
                        <!--////// Game Search Bar Row ///////
                        
                        <div id="searchbar">

                            <input type="text" name="genreName" id="genre-filter" placeholder="What are you looking for?" size="30">
                            <input class="btn btn-success" type="submit" value="Search">
                        </div>
                       ///// End Game Search Bar Row ///////-->
                        <table>
                            <!-- Table rows header -->
                            <tr>
                                <td class="tableImage"></td>
                                <td class="tableTitle">Title</td>
                                <td class="price">Price</td>
                                <td class="purchase">Purchase</td>
                            </tr>
                            <?php

                            $products = getProducts($link);

                            if ($products != null) {
                                while ($row = mysqli_fetch_array($products, MYSQLI_ASSOC)) {
                                    $imageBanner = getProductImage($link, $row["product_id"]);
                                    echo '<tr>';
                                    echo '<td class="tableImage"><img src="' . $imageBanner . '" alt=""></td>';
                                    echo '<td class="tableTitle"> <a href="ViewProduct.php?game='.$row["product_id"].'">'.$row["title"].'</a></td>';
                                    echo '<td>$' . $row["price"] . '</td>';
                                    echo '<td>';
                                    echo '<form action = "AddToCart.php?game=' . $row["product_id"] . '" method="post">';
                                    echo '<input type="submit" name="add_to_cart" style="margin-top:5px;" class="btn btn-success" value="Add to Cart">';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo "<h1>No Products</h1>";
                            }

                            ?>
                        </table>
                    </div>
                    <!-- Divider -->
                    <div class="col-md-1"></div>
                    <div class="col-md-2" id="sortingTab">
                        <table>
                            <tr>
                                <td class="tableTitle">Select Genres</td>
                            </tr>
                            <tr>
                                <td class="tableTitle">
                                    <form action="Products.php" method="post">
                                        <input class="genreButton" type="submit" name="type" value="All">
                                    </form>
                                </td>
                            </tr>
                            <?php
                            $genres = getGenres($link);

                            if ($genres != null) {
                                while ($row = mysqli_fetch_array($genres, MYSQLI_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td class="tableTitle">';
                                    echo '<form action = "Products.php?type=' . $row["genre"] . '" method="post">';
                                    echo '<input class="genreButton" type="submit" name="type" value="' . $row["genre"] . '">';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo "<h3>No Genres</h3>";
                            }
                            ?>
                        </table>
                    </div>
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