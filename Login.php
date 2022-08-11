<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
	header("location: Index.php");
	exit;
}


require_once "Config.php";

$email = $password = "";
$email_err = $password_err = $general_err = $agreement_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = cleanInput($link, $_POST["email"]);
	if ($email == null) {
		$email_err = "Please enter a valid email";
	} else {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$email_err = "Invalid email format";
		}
	}

	$password = cleanInput($link, $_POST["password"]);
	if ($password == null) {
		$password_err = "Please enter a valid password";
	}

	if (empty($email_err) && empty($password_err)) {
		$sql = "SELECT customer_ID, display_name, password, access_level, data_agreement FROM customers WHERE email=?";

		if ($stmt = mysqli_prepare($link, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $email);

			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) == 1) {
					mysqli_stmt_bind_result($stmt, $customer_ID, $displayName, $hashed_pass, $accessLevel, $agreement);
					if (mysqli_stmt_fetch($stmt)) {
						if (password_verify($password, $hashed_pass)) {
							$_SESSION["loggedin"] = true;
							$_SESSION["display_name"] = $displayName;
							$_SESSION["access_level"] = $accessLevel;
							$_SESSION["customer_id"] = $customer_ID;
							$_SESSION["acceptedTerms"] = $agreement;

							if ($agreement == 1) {
								$sql = "UPDATE customers SET last_login=now() WHERE customer_id=$customer_ID";
								if (mysqli_query($link, $sql)) {
									header("location: Index.php");
								}
							} else {
								header("location: AcceptTerms.php");
							}
						} else {
							$password_err = "The password you entered was not valid.";
						}
					} else {
						return null;
					}
				} else {
					$email_err = "No account found with that email.";
				}
			} else {
				$general_err = "Oops! Something went wrong while processing your request. Please try again later.";
			}
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta name="author" content="Tyson Hoops">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Login</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="Includes/CSS/register.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.css" integrity="sha256-2SjB4U+w1reKQrhbbJOiQFARkAXA5CGoyk559PJeG58=" crossorigin="anonymous" />
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
							<h1>Login</h1>
							<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
								<div id="email" class="inputSection">
									<input type="text" name="email" id="email" placeholder="Email" value="<?php echo $email; ?>" required>
									<br>
									<span class="help-block"><?php echo $email_err ?></span>
								</div>
								<div id="password" class="inputSection">
									<input type="password" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>" required>
									<br>
									<span class="help-block"><?php echo $password_err ?></span>
								</div>
								<div id="submit" class="inputSection">
									<input type="submit" class="btn btn-primary" value="Login" id="loginButton">
								</div>
								<div>
									<p>Our terms are changing, please accept to continue <a href="Terms.php">here</a></p>
									<span class="help-block"><?php echo $agreement_err ?></span>
								</div>
								<div>
									<p>Don't have an account? Sign up <a href="Register.php">here</a></p>
								</div>
								<span class="help-block"><?php echo $general_err ?></span>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php include('Footer.php') ?>

	<!--<script src="Includes/JS/adminAdd.js"></script>-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>