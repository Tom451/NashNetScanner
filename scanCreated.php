<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
} else {

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Features-Clean.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <nav class="navbar navbar-light navbar-expand-md navigation-clean-button">
        <div class="container"><a class="navbar-brand" href="#">NashNetworkScanner</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Network Scanner</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">View Data</a></li>
                    <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Dropdown </a>
                        <div class="dropdown-menu"><a class="dropdown-item" href="#">First Item</a><a class="dropdown-item" href="#">Second Item</a><a class="dropdown-item" href="#">Third Item</a></div>
                    </li>
                </ul><span class="navbar-text actions"> <button class="btn btn-light action-button" type="button">Log Out</button></span>
            </div>
        </div>
    </nav>
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
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>