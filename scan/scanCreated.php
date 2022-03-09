<?php
require '..\assets\php\sessionChecker.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php require '../assets/php/navBarLoggedIn.php' ?>

    <section class="features-clean">
        <div class="container">
            <div class="intro">
                <h2 class="text-center">Scan Created!</h2>
                <p class="text-center">Your scan has been created, please run this from the NND Agent.</p>
            </div>
            <div class="row features">
                <div class="col-sm-6 col-lg-4 item"><i class="fa fa-download icon"></i>
                    <h3 class="name">Download the Agent</h3>
                    <p class="description">Download the agent from the agent download page, and run the NND Agent File&nbsp;</p>
                </div>
                <div class="col-sm-6 col-lg-4 item"><i class="fa fa-mouse-pointer icon"></i>
                    <h3 class="name">Click, Click!</h3>
                    <p class="description">Right click the icon down in your task bar, and then in the context menu select "run scan"</p>
                </div>
                <div class="col-sm-6 col-lg-4 item"><i class="fa fa-birthday-cake icon"></i>
                    <h3 class="name">Celebrate!</h3>
                    <p class="description">You are now one step closer to being Cyber Secure! Good work, your scan will be available to view shortly.&nbsp;</p>
                </div>
            </div>
        </div>
    </section>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>