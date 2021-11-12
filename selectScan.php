<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
} else {

}

if (isset($_POST['pingScan'])) {


    require 'assets\php\DBConfig.php';
    require 'assets\php\randomSessionCreator.php';

    $USERID = $_SESSION['user_id'];
    $ScanStatus = "Pending";
    $connection = getConnection();
    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM scan WHERE userID=:userid AND ScanStatus = :ScanStatus");
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);
    $query->bindParam("ScanStatus", $ScanStatus, PDO::PARAM_STR);
    $query->execute();

//get the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

//if there is no results then show incorrect credentials
    if (!$result) {
        require "assets/php/encryptData.php";
        $username = $_SESSION['user_id'];
        $SessionID = generateRandom();
        $scanInfo = "nmap -sP -oX";
        $scanType = "NetDisc";
        $scanStatus = "Pending";

        $query = $connection->prepare("INSERT INTO scan(userID,SessionID,ScanInfo,scanType,scanStatus)
VALUES (:userID,:SessionID,:scanInfo,:scanType,:scanStatus)");
        $query->bindParam("userID", $username, PDO::PARAM_STR);
        $query->bindParam("SessionID", $SessionID, PDO::PARAM_STR);
        $query->bindParam("scanInfo", $scanInfo, PDO::PARAM_STR);
        $query->bindParam("scanType", $scanType, PDO::PARAM_STR);
        $query->bindParam("scanStatus", $scanStatus, PDO::PARAM_STR);

        $result = $query->execute();

        header('Location: scanCreated.php');

    } else {

        echo "Scan Waiting to be run please run previous scan";
    }




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
    <section class="features-boxed">
        <div class="container">
            <div class="intro">
                <h2 class="text-center">Please select what you would like to do</h2>
                <p class="text-center">Here we have a list of the scans you can run on your network,</p>
            </div>
            <div class="row justify-content-center features">
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-question icon"></i>
                        <h3 class="name">Network Discovery</h3>
                        <p class="description">Basic Network Discovery, this will allow you to view the devices on your current network</p>
                        <form method="post"><button class="btn btn-primary" type="submit" name="pingScan">Run Discovery</button></form>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-laptop icon"></i>
                        <h3 class="name">Vulnerability Scan&nbsp;</h3>
                        <p class="description">Vulnerability Scan, you can select a device you would like to preform the scan on .&nbsp;</p><a class="learn-more" href="#">Coming Soon »</a>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-history icon"></i>
                        <h3 class="name">History</h3>
                        <p class="description">View your latest and your oldest scans all in one area!&nbsp;</p><a class="learn-more" href="#">Coming Soon »</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>