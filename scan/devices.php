<?php

session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');

    exit;
} else {

}
require '..\assets\php\DBConfig.php';

$USERID = $_SESSION['user_id'];
$connection = getConnection();

//select all the devices that have been discovered by the loged in user
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'NetDisc' AND scan.userID = :userid");
$query->bindParam("userid", $USERID, PDO::PARAM_STR);

$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

//select all the devices that have been discovered by the loged in user
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'VulnScan' AND scan.userID = :userid");
$query->bindParam("userid", $USERID, PDO::PARAM_STR);

$query->execute();

//get the result
$scannedDevices = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../assets/css/Highlight-Blue.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/css/swiper.min.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php require '../assets/php/navBarLoggedIn.php' ?>

<section class="features-clean">
    <div class="container">
        <div class="intro">
            <h2 class="text-center">Devices</h2>
            <p class="text-center">Here are all your vulnerabilities of all the devices on the network&nbsp;</p>
        </div>
        <div class="row features">
            <?php
            $i = 1;
            foreach ($devices as $item) {
            echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon"></i>';
                echo'<ul class="list-unstyled">';
                    echo '<h3 class="name">'. $i .'</h3>';
                    echo '<li><strong>Name:</strong>'.$item['deviceName'].'</li>';
                    echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                    echo '<li><strong>Status:</strong>'.$item['deviceIP'].'</li>';
                    echo '<li><strong>Scanned:</strong>'.$item['deviceIP'].'</li>';
                    echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">View Scan</button> </td>';

                    $i++;
                }
                ?>



                    <li><strong>IP:</strong>&nbsp;Item 2</li>
                    <li><strong>Status:</strong>&nbsp;Item 3</li>
                    <li><strong>Scanned:&nbsp;</strong>Item 4</li>
                </ul><button class="btn btn-primary" type="button">Button</button>
                <ul class="list-group"></ul>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-mobile-phone icon"></i>
                <h3 class="name">Device Two</h3>
                <ul class="list-unstyled">
                    <li><strong>Name:</strong>&nbsp;Item 1</li>
                    <li><strong>IP:</strong>&nbsp;Item 2</li>
                    <li><strong>Status:</strong>&nbsp;Item 3</li>
                    <li><strong>Scanned:&nbsp;</strong>Item 4</li>
                </ul><button class="btn btn-primary" type="button">Button</button>
                <ul class="list-group"></ul>
            </div>
            <div class="col-sm-6 col-lg-4 item"><i class="fa fa-question icon"></i>
                <h3 class="name">Device Three</h3>
                <ul class="list-unstyled">
                    <li><strong>Name:</strong>&nbsp;Item 1</li>
                    <li><strong>IP:</strong>&nbsp;Item 2</li>
                    <li><strong>Status:</strong>&nbsp;Item 3</li>
                    <li><strong>Scanned:</strong>Item 4</li>
                </ul>
                <ul class="list-group"></ul><button class="btn btn-primary" type="button">Button</button>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/js/swiper.jquery.min.js"></script>
<script src="assets/js/Simple-Slider.js"></script>
</body>

</html>
