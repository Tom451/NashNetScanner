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

    <?php require "assets/php/headerData.php "?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/Features-Clean.css">
    <link rel="stylesheet" href="assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="assets/css/styles.css">


</head>

<body>

<?php require 'assets/php/navBarLoggedIn.php' ?>

<section class="features-clean">
    <div class="container">
        <div class="intro">
            <h2 class="text-center" id="NameHolder">Welcome UserName,&nbsp;</h2>
            <p class="text-center">Welcome to the NashNetworkDashboard, the first step in simplifying home network security.&nbsp;</p>
        </div>
        <div class="row features">
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-thumbs-o-up icon"></i>
                <h3 class="name">Easy to use</h3>
                <p class="description">Designed with the user in mind this means all the actions on the dashboard are easy for any level of user to use</p>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-clock-o icon"></i>
                <h3 class="name">Quick</h3>
                <p class="description">You specify what scan you want to do, by default a simple scan is started meaning you'll get simple information quickly&nbsp;</p>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-list-alt icon"></i>
                <h3 class="name">Customizable</h3>
                <p class="description">Quick Scan, Vulnerability Scan you pick just the way you would like to go forward.&nbsp;</p>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>
<script type="text/javascript">
        var userName = "<?php echo $_SESSION['userName'];?>"
        var doc = document.getElementById("NameHolder")
        doc.innerText ="Welcome, " + userName;
</script>
