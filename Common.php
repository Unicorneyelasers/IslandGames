<?php
    function termsAccepted() {
        if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["acceptedTerms"] == 1)) {
            echo '
            <script type="text/javascript">
                alert("You must first accept our terms of service."); 
               
            </script>';
        } else {
            return true;
        }
    }

    function isLoggedin() {
        if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
            echo '
            <script type="text/javascript">
                alert("You must first login to continue."); 
                window.location.href = "Login.php";
            </script>';
        } else {
            return true;
        }
    }
