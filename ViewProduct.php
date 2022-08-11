<?php
session_start();

require "Config.php";
require "Common.php";

if (isLoggedin() && !termsAccepted()) {
    exit;
}

$gameTitle = $description = "";
$price = 0;
$images = array();

if (isset($_GET["game"])) {
    $gameID = $_GET['game'];

    if (validProduct($link, $gameID)) {
        $gameData = getGameData($link, $gameID);
        $gameTitle = $gameData["title"];

        $prodImages = getProductImages($link, $gameID);
        if ($prodImages != null) {
            while ($row = mysqli_fetch_array($prodImages, MYSQLI_ASSOC)) {
                array_push($images, $row["image"]);
            }
        }

        $description = $gameData["description"];

        $price = $gameData["price"];
    } else {
        echo '<script type="text/javascript">
            alert("Invalid product!");
            window.location.href = "Products.php";
        </script>';
    }
} else {
    echo '<script type="text/javascript">
        alert("Product not found!");
        window.location.href = "Products.php";
    </script>';
}

function validProduct($link, $prodID)
{
    $sql = "SELECT title FROM products WHERE product_id=?";
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

function getGameData($link, $prodID)
{
    $sql = "SELECT product_id, title, description, featured, price FROM products WHERE product_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $prodID);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $product_id, $title, $description, $featured, $price);
                if (mysqli_stmt_fetch($stmt)) {
                    return array("productID" => $product_id, "title" => $title, "description" => $description, "featured" => $featured, "price" => $price);
                } else {
                    return null;
                }
            }
        }
    }
}

function getProductImages($link, $prodID)
{
    //$sql = "SELECT image FROM images WHERE image_id in (SELECT image_id FROM productimages WHERE product_id=$prodID)";
    $sql = "SELECT image FROM images INNER JOIN productimages USING (image_id) WHERE product_id = $prodID";
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
    <meta name="author" content="Tyson Hoops">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Products</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/ProductPage.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include('NavBar.php') ?>

    <!-- Wrapper div for the main content -->
    <div id="mainContent">
        <div id="contentWrapper">
            <!-- Title -->
            <h2 class="card-title-product"><?php echo $gameTitle ?></h2>
            <!-- Carousel -->
            <div id="carouselExampleIndicators" class="carousel-slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <?php
                    for ($i = 0; $i < count($images); $i++) {
                        if ($i == 0) {
                            echo '<li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>';
                        } else {
                            echo '<li data-target="#carouselExampleIndicators" data-slide-to="' . $i . '"></li>';
                        }
                    }
                    ?>
                </ol>
                <div class="carousel-inner">
                    <?php
                    for ($i = 0; $i < count($images); $i++) {
                        if ($i == 0) {
                            echo '<div class="carousel-item active">';
                        } else {
                            echo '<div class="carousel-item">';
                        }

                        echo '<img class="d-block w-100" src="' . $images[$i] . '" alt="First slide">';
                        echo '</div>';
                    }
                    ?>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
            <!-- Description -->
            <p class="card-text-desc"><?php echo $description ?></p>
        </div>
    </div>

    <?php //include('Footer.php') 
    ?>

    <script src="Includes/JS/index.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>