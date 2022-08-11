<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" integrity="sha256-46r060N2LrChLLb5zowXQ72/iKKNiw/lAmygmHExk/o=" crossorigin="anonymous" />
</head>


<!-- Wrapper div for the nav bar -->
<div id="navBar">
    <nav class="navbar navbar-expand-lg" id="navbar-style">
        <a class="navbar-brand" href="Index.php" style="font-size:30px;"><i class="fas fa-atom"></i> Island Games Portal</a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto"></ul>
            <form class="form-inline my-2 my-lg-0">
                <ul class="navbar-nav mr-auto">
                    <?php
                    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["access_level"] >= 7) {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="AdminGenres.php" style="font-size:20px;">Admin</a>';
                        echo '</li>';
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="Index.php" style="font-size:20px;">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Products.php" style="font-size:20px;">Products</a>
                    </li>
                    <?php
                    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="Cart.php" style="font-size:20px;">Cart</a>';
                        echo '</li>';

                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="Logout.php" style="font-size:20px;">Logout</a>';
                        echo '</li>';
                    } else {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="Register.php" style="font-size:20px;">Register</a>';
                        echo '</li>';

                        echo '<li class="nav-item">';
                        echo '<a class="nav-link" href="Login.php" style="font-size:20px;">Login</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </form>
        </div>
    </nav>
</div>