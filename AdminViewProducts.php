<?php
session_start();

if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["access_level"] >= 7)) {
	echo "not allowed";
	echo '
		<script type="text/javascript">
			alert("You cannot access this page."); 
			window.location.href = "Index.php";
		</script>';
	exit;
}

require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$_SESSION["edit_id"] = $_POST["prodID"];
	header ("location: AdminEditProduct.php");
}

function cleanInput($link, $data)
{
	if (empty(trim($data))) {
		return null;
	} else {
		$data = trim($data);
		$data = strip_tags($data);
		$data = mysqli_real_escape_string($link, $data);
		if (empty(trim($data))) {
			return null;
		} else {
			return $data;
		}
	}
}

function getProducts($link)
{
	$sql = "SELECT * FROM products order by title";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta name="author" content="Tyson Hoops">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin Add Product</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="Includes/CSS/adminView.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.css" integrity="sha256-2SjB4U+w1reKQrhbbJOiQFARkAXA5CGoyk559PJeG58=" crossorigin="anonymous" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
	<!-- Wrapper div for the nav bar -->
	<?php include 'NavBar.php'; ?>


	<div id="contentWrapper">
		<!-- Wrapper div for the side bar -->
		<div id="sidebar">
			<h2 class="sidebarTitle">Admin Panel</h2>
			<div class="sidebarItem collapsible">
				<a>Product Managment<span class="collapsibleIcon"><i class="fas fa-arrow-down"></i></span></a>
				<div class="dropdownData" id="collapseContent">
					<span class="sidebarDropdownItem"><a href="AdminAddProducts.php">Add Product</a></span>
					<span class="sidebarDropdownItem"><a href="AdminViewProducts.php">Manage Products</a></span>
				</div>
			</div>

			<div class="sidebarItem collapsible">
				<a>Genres<span class="collapsibleIcon"><i class="fas fa-arrow-down"></i></span></a>
				<div class="dropdownData" id="collapseContent">
					<span class="sidebarDropdownItem"><a href="AdminGenres.php">Add Genre</a></span>
				</div>
			</div>
		</div>

		<!-- Wrapper div for the main content -->
		<div id="backgroundMain"></div>

		<div id="mainContent">
			<div id="content">
				<div id="formWrapper">
					<h1>Products</h1>

					<table>
						<?php
							$products = getProducts($link);

							if ($products != null) {
								while ($row = mysqli_fetch_array($products, MYSQLI_ASSOC)) {
									$imageBanner = getProductImage($link, $row["product_id"]);

									echo '<tr>';
										 echo '<td class="tableImage"><img src="' . $imageBanner . '" alt=""></td>';
										echo '<td>'.$row["title"].'</td>';
										echo '<td>';
											echo '<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">';
												echo '<input type="hidden" id="" name="prodID" value="'.$row["product_id"].'">';
												echo '<input type="submit" name="" style="margin-top:5px;" class="btn btn-success" value="Edit">';
											echo '</form>';
										echo'</td>';
									echo '</tr>';
								}
							} else {
								echo "<h3>No Products</h3>";
							}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>


	<!-- Wrapper div for the footer -->
	<?php include 'Footer.php'; ?>
	</div>

	<script src="Includes/JS/adminAdd.js"></script>
	<script src="Includes/JS/adminSidebar.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>