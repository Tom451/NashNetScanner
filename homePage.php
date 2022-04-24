<?php
require 'assets\php\sessionChecker.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <?php require "assets/php/navBar/headerData.php " ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Home Page</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Features-Clean.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/scanOverlayAndAccoridion.css">


</head>

<body>

<?php require 'assets/php/navBar/navBarLoggedIn.php' ?>

<!-- The overlay -->
<div id="myNav" class="overlay">

    <!-- Button to close the overlay navigation -->
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
        <h2 class="text-center">Here is our Transparency Report</h2>
        <p class="text-center">Data stored about you:  </p>
        <p class="text-center"><b>Network Data: </b>IP Address, MAC Address, Device Name</p>
        <p class="text-center"><b>Personal Data: </b>First Name, Last Name, Email, Hashed Password</p>
    </div>

</div>

<section class="features-clean" style="padding-bottom: 0" >
    <div class="container">

        <div class="intro">
            <h2 class="text-center" id="NameHolder">Welcome UserName,&nbsp;</h2>
            <p class="text-center">Welcome to the NashNetworkDashboard, the first step in simplifying home network security. Please do scroll down to read more: </p>
        </div>

        <div class="row features-clean" style="padding-bottom: 0">
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-thumbs-o-up icon"></i>
                <h3 class="name">Easy to use</h3>
                <p class="description">Designed with the user in mind this means all the actions on the dashboard are easy for any level of user to use</p>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-clock-o icon"></i>
                <h3 class="name">Quick</h3>
                <p class="description">You specify what scan you want to do, by default a simple scan is started meaning you'll get simple information quickly&nbsp;</p>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-lock icon"></i>
                <h3 class="name">Transparent</h3>
                <p>Transparent with the user, always are and always will be. Press <a style="color: dodgerblue" onclick="openNav()"><u>Here</u></a> to find out. NashNetworkScanner will tell you all the data it stores</p>
            </div>
        </div>


    </div>
</section>

<section class="py-4 py-xl-5" style="padding-top: 0">
    <div class="container">
        <div class="bg-light border rounded border-light d-flex flex-column justify-content-between flex-lg-row p-4 p-md-5">
            <div class="pb-2 pb-lg-1">
                <h2 class="font-weight-bold mb-2">Confused as to where to begin?</h2>
                <p class="mb-0">Click here to get started, your journey to complete security begins.</p>
            </div>
            <div class="my-2"><a class="btn btn-primary btn-lg mr-2 py-2 px-4" role="button" href="/scan/View/Devices.php">Begin</a></div>
        </div>
    </div>
</section>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>
<script type="text/javascript">
        let userName = "<?php echo ucfirst($_SESSION['userName']);?>"
        let doc = document.getElementById("NameHolder")
        doc.innerText ="Welcome, " + userName;

        function openNav() {
            document.getElementById("myNav").style.width = "100%";
        }

        function closeNav() {
            document.getElementById("myNav").style.width = "0%";
        }
</script>
