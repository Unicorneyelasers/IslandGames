<?php
session_start();

require("Config.php");
require("Common.php");

function getRandomProducts($link)
{
	$sql = "SELECT * FROM products LIMIT 3";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}

function getFeaturedProducts($link)
{
	$sql = "SELECT * FROM products WHERE featured=1";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}

function getProductImage($link, $prodID)
{
	$sql = "SELECT image FROM images INNER JOIN productimages USING(image_id) WHERE product_id=? LIMIT 1";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "i", $prodID);

		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_store_result($stmt);
			if (mysqli_stmt_num_rows($stmt) == 1) {
				mysqli_stmt_bind_result($stmt, $image);
				if (mysqli_stmt_fetch($stmt)) {
					return $image;
				} else {
					return null;
				}
			}
		}
	}
}

function getProducts($link)
{
	$featuredProducts = getFeaturedProducts($link);

	if ($featuredProducts == null) {
		$featuredProducts = getRandomProducts($link);
	}

	return $featuredProducts;
}

function getProductsList($link)
{
	if (isset($_GET["type"])) {
		$searchType = $_GET['type'];

		$sql = "SELECT * FROM products LIMIT 3";
		$result = mysqli_query($link, $sql);

		if (mysqli_num_rows($result) == 0) {
			return null;
		} else {
			return $result;
		}
	} else {
		$sql = "SELECT * FROM products LIMIT 8";
		$result = mysqli_query($link, $sql);

		if (mysqli_num_rows($result) == 0) {
			return null;
		} else {
			return $result;
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
	<title>Homepage</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" href="Includes/CSS/indexStyle.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
	<?php include 'NavBar.php'; ?>
	<!-- Wrapper div for the main content -->
	<div id="mainContent">
		<!-- Carousel -->
		<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
			<ol class="carousel-indicators">
				<?php
				$featuredProducts = getProducts($link);

				$loops = 0;
				while ($row = mysqli_fetch_array($featuredProducts, MYSQLI_ASSOC)) {
					if ($loops == 0) {
						echo '<li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>';
					} else {
						echo '<li data-target="#carouselExampleIndicators" data-slide-to="' . $loops . '"></li>';
					}

					$loops++;
				}
				?>
			</ol>
			<div class="carousel-inner">
				<?php
				$featuredProducts = getProducts($link);

				$loopLength = 0;
				while ($row = mysqli_fetch_array($featuredProducts, MYSQLI_ASSOC)) {
					if ($loopLength == 0) {
						echo '<div class="carousel-item active">';
					} else {
						echo '<div class="carousel-item">';
					}

					$banner = getProductImage($link, $row["product_id"]);

					echo '<img src="' . $banner . '" alt="' . $row["title"] . ' Banner">';
					echo '<div class="carousel-caption d-none d-md-block">';
					echo '<h5>' . $row["title"] . '</h5>';
					echo '</div>';
					echo '</div>';

					$loopLength++;
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

		<div id="gameDisplay">
			<!-- Filter section -->


			<!-- Game cards -->
			<div id="gameCards">
				<div class="container">
					<!-- Row 1 -->
					<div class="row">
						<div class="card-deck">
							<?php
							$featuredProducts = getProducts($link);

							while ($row = mysqli_fetch_array($featuredProducts, MYSQLI_ASSOC)) {
								$banner = getProductImage($link, $row["product_id"]);

								echo '<div class="card text-white bg-dark mb-4 text-center">';
								echo '<img class="card-img-top" src="' . $banner . '" alt="' . $row["title"] . ' Banner">';
								echo '<div class="card-body-product">';
								echo '<h5 class="card-title">' . $row["title"] . '</h5>';
								echo '<p class="card-text-desc">' . $row["description"] . '</p>'; ?>
								<!--
								<?php
								if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
									echo '<form action = "AddToCart.php?game=' . $row["product_id"] . '" method="post">';
									echo '<input type="submit" name="add_to_cart" style="margin-top:5px;" class="btn btn-success" value="Add to Cart">';
									echo '</form>';
								} else {
									echo '<form action = "Login.php" method="get">';
									echo '<input type="submit" name="login" style="margin-top:5px;" class="btn btn-success" value="Login">';
									echo '</form>';
								}
								?>
								-->
							<?php
								echo '</div>';
								echo '</div>';
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="productsList">
			<!--////// Game Search Bar Row ///////-->
			<h2 class="product-label">Heres a few of our products</h2>

			<table>
				<!-- Table rows header -->
				<tr>
					<td class="tableImage"></td>
					<td class="tableTitle">Title</td>
					<td class="price">Price</td>
					<td class="purchase">Purchase</td>
				</tr>
				<?php

				$products = getProductsList($link);

				if ($products != null) {
					while ($row = mysqli_fetch_array($products, MYSQLI_ASSOC)) {
						$imageBanner = getProductImage($link, $row["product_id"]);
						echo '<tr>';
						echo '<td class="tableImage"><img src="' . $imageBanner . '" alt=""></td>';
						echo '<td class="tableTitle">' . $row["title"] . '</td>';
						echo '<td>$' . $row["price"] . '</td>';
						echo '<td>';
						if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
							echo '<form action = "AddToCart.php?game=' . $row["product_id"] . '" method="post">';
							echo '<input type="submit" name="add_to_cart" style="margin-top:5px;" class="btn btn-success" value="Add to Cart">';
							echo '</form>';
						} else {
							echo '<form action = "Login.php" method="get">';
							echo '<input type="submit" name="login" style="margin-top:5px;" class="btn btn-success" value="Login to Purchase">';
							echo '</form>';
						}
						echo '</td>';
						echo '</tr>';
					}
				} else {
					echo "<h1>No Products</h1>";
				}

				?>
			</table>
			<br>

			<?php
			if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
				echo '<form action = "Products.php" method="get">';
				echo '<input type="submit" name="add_to_cart" style="margin-top:5px;" class="btn btn-success" value="View More">';
				echo '</form>';
			} else {
				echo '<form action = "Login.php" method="get">';
				echo '<input type="submit" name="login" style="margin-top:5px;" class="btn btn-success" value="Login to View More">';
				echo '</form>';
			}
			?>
		</div>


	</div>
	<?php include 'Footer.php'; ?>
	<script src="Includes/JS/index.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>