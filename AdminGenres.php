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

require "Config.php";

// Define variables and initialize with empty values
$genre = "";
$genre_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty(trim($_POST["genre"]))) {
		$genre_err = "Please provide a genre name.";
	} else {
		$genre = $_POST["genre"];
	}

	//If there are no errors, continue
	if (empty($genre_err)) {
		$genreID = getgenreID($link, $genre);

		if ($genreID != null) {
			$genre_err = "genre already exists!";
		} else {
			$success = insertgenre($link, $genre);
			if ($success) {
				echo '
					<script type="text/javascript">
						alert("genre added successfully!"); 
						window.location.href = "AdminGenres.php";
					</script>';
				exit;
			} else {
				print "There was a problem inserting the genre";
			}
		}
	}
}


function getgenreID($link, $genre)
{
	$sql = "SELECT genre_id FROM genre WHERE genre=?";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $genre);

		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_store_result($stmt);
			if (mysqli_stmt_num_rows($stmt) == 1) {
				mysqli_stmt_bind_result($stmt, $genreID);
				if (mysqli_stmt_fetch($stmt)) {
					return $genreID;
				} else {
					return null;
				}
			}
		}
	}
}

function insertgenre($link, $genre)
{
	$sql = "INSERT INTO genre (genre) VALUES (?)";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $genre);

		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			return false;
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
	<title>Admin Page</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="Includes/CSS/adminGeneres.css" type="text/css" media="screen" />
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

			<!--
			<p class="sidebarItem sidebarActive collapsible">Product Managment</p>
			<div class="dropdownData" id="collapseShit">
				<span class="sidebarDropdownItem"><a class="nav-link" href="AdminGenres.php">Add Genre</a></span>
				<span class="sidebarDropdownItem"><a class="nav-link" href="AdminAddProducts.php">Add Products</a></span>
			</div>
			-->
		</div>

		<!-- Wrapper div for the main content -->
		<div id="backgroundMain"></div>

		<div id="mainContent">
			<div id="content">
				<div id="modal">
					<div id="popupLoading">
						<h1 class="text-center" id="loadingText">Please wait while we try to import that game...</h1>
						<div class="loader">
						</div>
					</div>

					<div id="apiQueryResult">
						<div id="apiQueryResultFound">
							<img src="" alt="image" id="queryFoundImage">
							<h3 id="queryFoundGameTitle"></h3>
							<p>We found a game with a matching title. Is this the game you want to add?</p>
							<button id="queryFoundYesButton" class="queryFoundButton">Yes</button>
							<button id="queryFoundNoButton" class="queryFoundButton">No</button>
						</div>
						<div id="apiQueryResultNotFound">
							<P>We couldn't find any games matching that title. Please double check your spelling or enter the data manually.</P>
							<button id="queryNotFoundCloseButton">Close</button>
						</div>
					</div>
				</div>

				<div id="formWrapper">
					<h1>New Genre</h1>
					<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
						<div id="innerWrapper">
							<div class="inputDiv">

								<input type="text" placeholder="genre Title" class="inputItem" id="genre" name="genre" value="<?php echo $genre; ?>" required>
								<input type="submit" class="btn btn-primary" value="Add" id="submitButton">
								<br>
								<span class="help-block"><?php echo $genre_err; ?></span>
							</div>
						</div>
					</form>
				</div>

				<div id="genreWrapper">
					<h1>Current Genre List</h1><br>
					<table>
						<?php
						$sql = "SELECT genre_id, genre FROM genre";
						$result = mysqli_query($link, $sql);

						if (mysqli_num_rows($result) == 0) {
							echo "<h1 class='col-xs-1 text-center'>No Genres</h1>";
						} else {
							echo '<tr>';
							echo '<td>ID</td>';
							echo '<td>Genre</td>';
							echo '</tr>';

							while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
								echo '<tr>';
								echo '<td>' . $row["genre_id"] . '</td>';
								echo '<td>' . $row["genre"] . '</td>';
								echo '</tr>';
							}
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>


	<!-- Wrapper div for the footer -->
	<?php include 'Footer.php'; ?>

	<script src="Includes/JS/adminAdd.js"></script>
	<script src="Includes/JS/adminSidebar.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>