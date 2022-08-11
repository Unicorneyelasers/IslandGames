<?php
session_start();

require('Config.php');
$hide = false;
$gameID = $customerID = -1;

$displayName = $_SESSION["display_name"];

function getGameData($link, $customer_id)
{
	$sql = "select purchase_date, title, price, quantity from order_history inner join orderproducts using (order_history_id) inner join products using(product_id) where customer_id =?";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "i", $customer_id);

		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_store_result($stmt);
			if (mysqli_stmt_num_rows($stmt) >= 1) {
				mysqli_stmt_bind_result($stmt, $productID, $title, $price, $quant);
				if (mysqli_stmt_fetch($stmt)) {
					return array("productID" => $productID, "title" => $title, "price" => $price, "quantity" => $quant);
				} else {
					return null;
				}
			}
		}
	}
}

function getHistory($link, $customer_id)
{
	$sql = "SELECT order_history_id, purchase_date FROM order_history WHERE customer_ID=$customer_id ORDER BY purchase_date";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}

function orderContents($link, $orderID)
{
	$sql = "SELECT * FROM orderproducts WHERE order_history_id=$orderID";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}


function orderQuantity($link, $orderID)
{
	$sql = "SELECT * FROM orderproducts WHERE order_history_id=$orderID";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		$quantity = 0;
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$quantity += $row["quantity"];
		}
		return $quantity;
	}
}
/*
function getHistory($link, $customerID)
{
	$sql = "select * from order_history inner join orderproducts using (order_history_id) inner join products using(product_id) where customer_id = ('$customerID')";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}
*/

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta name="author" content="Cory Audette-Tuckwood">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Order History</title>

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
		<div id="contentWrapper">
			<div class="container">
				<div class="row">
					<div class="col-md-1" id="blank"></div>

					<div class="col-md-10" id="cart">
						<h1 class="card-title-cart">Showing order history for <u><?php echo $displayName ?></u></h1>
						<br>
						<table class="cart-table table-bordered">
							<tr>
								<th width="30%" class="price">Receipt #</th>
								<th width="20%" class="total">Product count</th>
								<th width="50%" class="purchaseDate">Purchase Date</th>
							</tr>
							<?php
							$customerID = $_SESSION["customer_id"];
							$history = getHistory($link, $customerID);

							$total = 0;
							if ($history != null) {
								while ($row = mysqli_fetch_array($history, MYSQLI_ASSOC)) {
									$orderQuantity = orderQuantity($link, $row["order_history_id"]);

									echo '<tr>';
									echo '<td>' . $row["order_history_id"] . '</td>';
									echo '<td>' . $orderQuantity . '</td>';
									echo '<td>' . $row["purchase_date"] . '</td>';

									echo '<td>';
									echo '<form action = "ViewReceipt.php?id=' . $row["order_history_id"] . '" method="post">';
									echo '<input type="submit" name="add_to_cart" style="margin-top:5px;" class="btn btn-success" value="View">';
									echo '</form>';
									echo '</td>';
									echo '</tr>';
								}
							}
							?>
						</table>
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