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

// Define variables and initialize with empty values
$title = $description = $price = $productID = "";
$images = $genres = $productGenres = array();
$added = true;

$description_err = $images_err = $game_err = $genres_err = $title_err = $price_err = "";

if (isset($_SESSION["edit_id"])) {
	$prodID = $_SESSION["edit_id"];
	$data = getGameData($link, $prodID);
	$title = cleanInput($link, $data["title"]);
	$description = cleanInput($link, $data["description"]);
	$price = $data["price"];
	$featured = $data["featured"];

	$imageIDS = getGameImages($link, $_SESSION["edit_id"]);
	if ($imageIDS != null) {
		while ($row = mysqli_fetch_array($imageIDS, MYSQLI_ASSOC)) {
			$imageID = $row["image_id"];
			$url = getImageURL($link,$imageID);
			array_push($images, $url);
		}
	}

	$gameGenres = getGameGenres($link, $prodID);
	if ($gameGenres != null) {
		while ($row = mysqli_fetch_array($gameGenres, MYSQLI_ASSOC)) {
			$genreID = $row["genre_id"];
			array_push($productGenres, $genreID);
		}
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$description = cleanInput($link, $_POST["description"]);
	if ($description == null) {
		$description_err = "Please provide a description for the game.";
	}

	$images = $_POST["images"];
	if (empty(trim($images))) {
		$images_err = "Please provide at least one image for the game.";
	} else {
		$images = explode("\n", $images);
	}

	if (isset($_POST['genres'])) {
		$genres = $_POST['genres'];
	} else {
		$genres_err = "No genres selected";
	}

	$title = cleanInput($link, $_POST["gameTitle"]);
	if ($title == null) {
		$title_err = "Please provide a title for the game";
	}

	$price = cleanInput($link, $_POST["price"]);
	if ($price == null) {
		$price_err = "Please provide a price for the game";
	}

	if (isset($_POST["featured"])) {
		$featured = $_POST["featured"];
	} else {
		$featured = 0;
	}

	if (empty($description_err) && empty($images_err) && empty($genres_err) && empty($title_err) && empty($price_err)) {
		$productID = getProductID($link, $title);

		if ($productID == null) {
			$game_err = "Game cannot be found!";
			$added = false;
		} else {
			$success = updateProduct($link, $title, $description, $price, $productID, $featured);
			if ($success) {
				$success = removeProductImages($link, $productID);
				if ($success) {
					//Loop through the images and add them to the DB
					for ($i = 0; $i <= count($images) - 1; $i++) {
						$cur = $images[$i];

						$cur = cleanInput($link, $cur);

						if ($cur != null) {
							$success = insertImage($link, $cur);
							if ($success) {
								$imageID = getImageID($link, $cur);
								if ($imageID != null) {
									$success = insertImageHandler($link, $imageID, $productID);
									if ($success) {
									} else {
										print "There was an error inserting to the image handler";
										$added = false;
									}
								} else {
									print "There was an error getting the image ID";
									$added = false;
								}
							} else {
								print "There was an error adding the image";
								$added = false;
							}
						}
					}

					//Remove product genres
					$success = removeProductGenres($link, $productID);
					if ($success) {
						//Through Genres
						for ($i = 0; $i <= count($genres) - 1; $i++) {
							$cur = $genres[$i];
							$genreID = getGenreID($link, $cur);

							if ($genreID != null) {
								$success = insertGameGenre($link, $genreID, $productID);
								if ($success) {
								} else {
									print "There was a problem adding the game to the genre";
									$added = false;
								}
							} else {
								print "There was a problem getting the genre ID";
								$added = false;
							}
						}
					} else {
						$game_err = "There was a problem removing the games genres!";
						$added = false;
					}
				} else {
					$game_err = "There was a problem removing the games images!";
					$added = false;
				}
			} else {
				print "There was a problem inserting the product";
				$added = false;
			}
		}

		if ($added) {
			echo '
				<script type="text/javascript">
					alert("Product updated successfully!"); 
					window.location.href = "AdminViewProducts.php";
				</script>';
		}
	}
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

function getProductID($link, $productName)
{
	$sql = "SELECT product_id FROM products WHERE title like (?)";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $productName);

		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_store_result($stmt);
			if (mysqli_stmt_num_rows($stmt) == 1) {
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

function getImageID($link, $imagePath)
{
	$sql = "SELECT image_id FROM images WHERE image=?";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $imagePath);

		if (mysqli_stmt_execute($stmt)) {
			mysqli_stmt_store_result($stmt);
			if (mysqli_stmt_num_rows($stmt) == 1) {
				mysqli_stmt_bind_result($stmt, $imageID);
				if (mysqli_stmt_fetch($stmt)) {
					return $imageID;
				} else {
					return null;
				}
			}
		}
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

function getGenreID($link, $genre)
{
	$sql = "SELECT genre_id FROM genre WHERE genre=? ";
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

function getGameData($link, $prodID) {
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

function getGameImages($link, $prodID) {
	$sql = "SELECT * FROM productimages WHERE product_id=$prodID";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}

function getImageURL($link, $imageID) {
	$sql = "SELECT image FROM images WHERE image_id=?";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "i", $imageID);

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

function getGameGenres($link, $prodID) {
	$sql = "SELECT * FROM productgenres WHERE product_id=$prodID";
	$result = mysqli_query($link, $sql);

	if (mysqli_num_rows($result) == 0) {
		return null;
	} else {
		return $result;
	}
}

function insertGameGenre($link, $genreID, $productID)
{
	$sql = "INSERT INTO productgenres (genre_id, product_id) VALUES (?, ?)";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "ii", $genreID, $productID);

		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			echo mysqli_stmt_error($stmt);
			return false;
		}
	}
}

function updateProduct($link, $title, $description, $price, $prodID, $featured)
{
	$sql = "UPDATE products SET title=?, description=?, price=?, featured=? WHERE product_id=$prodID";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "ssdi", $title, $description, $price, $featured);

		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			print mysqli_stmt_error($stmt);
			return false;
			//printf("Error: %s.\n", mysqli_stmt_error($stmt));
		}
	}
}

function insertImage($link, $imagePath)
{
	$sql = "INSERT INTO images (image) VALUES (?)";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $imagePath);

		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			return false;
		}
	}
}

function insertImageHandler($link, $imageID, $productID)
{
	$sql = "INSERT INTO productimages (product_id, image_id) VALUES (?, ?)";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "ii", $productID, $imageID);
		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			return false;
			//printf("Error: %s.\n", mysqli_stmt_error($stmt));
		}
	}
}

function removeProductImages($link, $prodID) {
    $sql = "DELETE i.*,p.* FROM images i INNER JOIN productimages p USING (image_id) WHERE product_id=?;";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "i", $prodID);
		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			return false;
			//printf("Error: %s.\n", mysqli_stmt_error($stmt));
		}
	}
}

function removeProductGenres($link, $prodID) {
    $sql = "DELETE FROM productgenres WHERE product_id=?";
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_bind_param($stmt, "i", $prodID);
		if (mysqli_stmt_execute($stmt)) {
			return true;
		} else {
			return false;
			//printf("Error: %s.\n", mysqli_stmt_error($stmt));
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
	<title>Admin Add Product</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="Includes/CSS/adminAdd.css" type="text/css" media="screen" />
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
					<h1>Edit Product</h1>


					<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
						<div class="container">
							<div class="row">
								<div class="col-md-9">
									<div id="innerWrapper">
										<div class="inputDiv">
											<label>Game Title</label>
											<input type="text" placeholder="Game Title" class="inputItem" id="gameTitle" name="gameTitle" value="<?php echo $title; ?>" required>
											<br>
											<span class="help-block"><?php echo $title_err; ?></span>
										</div>
										<div class="inputDiv">
											<label>Description</label>
											<textarea name="description" id="gameDesc" cols="50" rows="10"><?php echo $description; ?></textarea>
											<br>
											<span class="help-block"><?php echo $description_err; ?></span>
										</div>
										<div class="inputDiv">
											<label>Price</label>
											<input type="text" placeholder="Price" class="inputItem" id="gamePrice" name="price" value="<?php echo $price; ?>" required>
											<br>
											<span class="help-block"><?php echo $price_err; ?></span>
										</div>
										<div class="inputDiv">
											<label>Screenshots (One image per line)</label>
											<textarea name="images" id="gameImages" cols="50" rows="10"><?php
												if (isset($images)) {
													for ($i = 0; $i < count($images); $i++) {
														$cur = $images[$i];
														if ($i == 0) {
															echo $cur;
														} else {
															echo "\n$cur";
														}
													}
												}
												?></textarea>
											<br>
											<span class="help-block"><?php echo $images_err; ?></span>
										</div>
									</div>
								</div>
								<div class="col-md-1">

								</div>
								<div class="col-md-2">
									<div class="inputDiv">
										<label>Genres</label>
										<br>
										<span class="help-block"><?php echo $genres_err; ?></span>
										<table>
											<?php
											$genres = getGenres($link);

											if ($genres != null) {
												while ($row = mysqli_fetch_array($genres, MYSQLI_ASSOC)) {
													$genreID = getGenreID($link, $row["genre"]);
													echo '<tr>';
													echo '<td class="tableCheckbox"><input type="checkbox" name="genres[]" value="'.$row["genre"].'"';
													if (in_array($genreID, $productGenres)) {
														echo "checked";
													}
													echo '></td>';
													echo '<td class="tableTitle">' . $row["genre"] . '</td>';
													echo '</tr>';
												}
											} else {
												echo "<h3>No Genres</h3>";
											}
											?>
										</table>
									</div>
									<div class="inputDiv">
										<label>Featured</label>
										<table>
											<tr>
												<td class="tableCheckbox">
													<input type="checkbox" name="featured" value="1" <?php if ($featured == 1) {echo "checked";}?>>
													Featured Product
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>

						<div id="submitButtonWrapper">
							<input type="submit" value="Submit" id="submitButton">
							<br>
							<span class="help-block"><?php echo $game_err; ?></span>
						</div>
					</form>
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