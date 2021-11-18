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
                <li class="nav-item"><a class="nav-link active" href="selectScan.php">Network Scanner</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Items: </a>
                    <div class="dropdown-menu"><a class="dropdown-item" href="selectScan.php">Scan Selector</a><a class="dropdown-item" href="#">Second Item</a><a class="dropdown-item" href="#">Third Item</a></div>
                </li>
            </ul><span class="navbar-text actions"> <a class="btn btn-light action-button" role="button" href="logOut.php">Log Out</a></span>
        </div>
    </div>
</nav>
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
