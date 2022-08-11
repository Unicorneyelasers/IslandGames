<?php
session_start();
require "Config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $_SESSION["optedOut"] = true;
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&  $_SESSION["optedOut"] === true) {
            $custID = $_SESSION["customer_id"];
            $query = "UPDATE customers SET data_agreement= 0 WHERE customer_id=$custID";
            if (mysqli_query($link, $query)) {
                echo ("<script>console.log('removed');</script>");
                $sql = "UPDATE customers SET term_date = now() WHERE customer_id=$custID";
                if (mysqli_query($link, $sql)) {
                    $_SESSION["loggedin"] = null;
                    $_SESSION["display_name"] = null;
                    $_SESSION["access_level"] = null;
                    $_SESSION["customer_id"] = null;
                    $_SESSION["acceptedTerms"] = null;

                    header("location: Leaving.php");
                    exit;
                } else {
                    echo ("<script> console.log('timestamp failed');</script>");
                }
            }
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="author" content="Jackie Miner">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Terms Agreement</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="Includes/CSS/common.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="Includes/CSS/terms.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.css" integrity="sha256-2SjB4U+w1reKQrhbbJOiQFARkAXA5CGoyk559PJeG58=" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php include 'NavBar.php'; ?>
    <!--START of the REG form -->
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-body-register">
                        <div id="innerWrapper">
                            <h1 class="data">General Data Protection Regulation</h1>
                            <br>
                            <div class="inputDiv">
                                <div style="height:60vh;width:50vw;background-color: white;border:1px solid #ccc;font:16px/26px Georgia, Garamond, Serif;overflow:auto;">
                                    <br>
                                    <h1>General Data Protection Regulation</h1>

                                    <p><strong>Island Games Portal Privacy Policy</strong></p>
                                    <br>
                                    <p>Island Games Portal is part of the Island Games Portal Group which includes Island Games Portal International and Island Games Portal Direct. This privacy policy will explain how our organization uses the personal data we collect from you when you use our website.</p>
                                    <br>
                                    <p><strong>Topics:</strong></p>
                                    <br>

                                    <p>What data do we collect?</p><br>
                                    <p>How do we collect your data?</p><br>
                                    <p>How will we use your data?</p><br>
                                    <p>How do we store your data?</p><br>
                                    <p>Marketing</p><br>
                                    <p>What are your data protection rights?</p><br>
                                    <p>What are cookies?</p><br>
                                    <p>How do we use cookies?</p><br>
                                    <p>What types of cookies do we use?</p><br>
                                    <p>How to manage your cookies</p><br>
                                    <p>Privacy policies of other websites</p><br>
                                    <p>Changes to our privacy policy</p><br>
                                    <p>How to contact us</p><br>
                                    <p>How to contact the appropriate authorities</p><br>

                                    <br>
                                    <h1>What data do we collect?</h1>
                                    <p>Island Games Portal collects the following data:</p>

                                    <p>Personal identification information (Name, email address, phone number)</p><br>

                                    <br>
                                    <h1>How do we collect your data?</h1>
                                    <p>You directly provide Island Games Portal with most of the data we collect. We collect data and process data when you:</p>

                                    <p>Register online or place an order for any of our products</p><br>
                                    <p>Voluntarily complete a customer survey or provide feedback on any of our message boards</p><br>
                                    <p>Use or view our website via your browser</p><br>

                                    <br>
                                    <h1>How will we use your data?</h1>
                                    <br>
                                    <p>Island Games Portal collects your data so that we can:</p>

                                    <p>Process your order</p><br>
                                    <p>Email you with order confirmations</p><br>

                                    <br>
                                    <p>If you agree, Island Games Portal will share your data with our partner companies so that they may offer you their products and services.</p>

                                    <p>[List organizations that will receive data]</p><br>

                                    <br>
                                    <p>When Island Games Portal processes your order, it may send your data to, and use the resulting information from, credit reference agencies to prevent fraudulent purchases.</p>
                                    <br>
                                    <h1>How do we store your data?</h1>
                                    <p>Island Games Portal securely stores your data at [enter the location and describe security precautions taken].</p>
                                    <br>
                                    <p>Island Games Portal will keep your data for 3 years. Once this time has expired, we will delete your data by deletion.</p>
                                    <br>
                                    <p>Marketing</p>
                                    <p>Island Games Portal would like to send you information about products and services of ours that we think you might like, as well as those of our partner companies.</p>


                                    <br>
                                    <p>If you have agreed to receive marketing, you may always opt out later.</p>
                                    <br>
                                    <p>You have the right at any time to stop Island Games Portal from contacting you for marketing purposes or giving your data to other members of the Island Games Portal Group.</p>
                                    <br>
                                    <p>If you no longer wish to be contacted for marketing purposes, please click here.</p>
                                    <br>
                                    <h1>What are your data protection rights?</h1>
                                    <p>Island Games Portal would like to make sure you are fully aware of all your data protection rights. Every user is entitled to the following:</p>
                                    <br>
                                    <p>The right to access - You have the right to request Island Games Portal for copies of your personal data. We may charge you a small fee for this service.</p>
                                    <br>
                                    <p><strong>The right to rectification </strong>- You have the right to request that Island Games Portal correct any information you believe is inaccurate. You also have the right to request Island Games Portal to complete information you believe is incomplete.</p>
                                </div>
                                <br>
                                <form name="form" method="post">
                                    <br>
                                    <input type="submit" class="btn btn-success" id="submit" name="submit" value="I want to opt out">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

    </script>
    <!-- Wrapper div for the footer -->
    <?php include 'Footer.php'; ?>



    <script src="Includes/JS/admin.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>