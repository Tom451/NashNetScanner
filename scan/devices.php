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
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'NetDisc' AND scan.userID = :userid GROUP BY device.deviceID");
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


function getNewestScan($deviceID, $scannedDevices){

    foreach ($scannedDevices as $item){
        if ($item['deviceID'] == $deviceID){
            return $item['ScanID'];
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<meta http-equiv="refresh" content="20" />

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
            <?php
            echo '<form action="/scan/createScan.php" method="post">';
            echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="FULLSCAN" id="FULLSCAN">Start Scan</button> </td>';
            echo '</form>';

            ?>
        </div>

            <?php
            $i = 1;
            $NeedsAttention = null;
            $Secure = null;
            $Other = null;
            foreach ($devices as $item){
                if($item['deviceScanned']=="Yes: Vulnerable"){
                    $NeedsAttention[] =$item;
                }
                elseif ($item['deviceScanned'] == "No"){
                    $Other[] = $item;
                }
                elseif ($item['deviceScanned'] == "Yes: Safe"){
                    $Secure[] = $item;
                }
                else{
                    $Other[] = $item;
                }
            }
            echo'<h1 style="padding-bottom: 10px">Needs Attention: </h1>';
            echo '<div class="row features">';
            foreach ($NeedsAttention as $item){
                echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon" style="color: red"></i>';
                echo'<ul class="list-unstyled">';
                echo '<h3 class="name">Device: '. $item['deviceName'] .'</h3>';
                echo '<li><strong>Mac:</strong>'.$item['deviceMacAddress'].'</li>';
                echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                echo '<li><strong>Scanned:</strong>'.$item['deviceScanned'].'</li>';

                if ($item['deviceScanned'] != "No"){
                    echo'<form action="/scan/viewScan.php" method="post">';
                    echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . getNewestScan($item['deviceID'], $scannedDevices) . '" id="'.getNewestScan($item['deviceID'], $scannedDevices).'">View Scan</button> </td>';
                    echo'</form>';
                }

                else if($item['deviceScanned'] == "No"){
                    echo '<form action="/scan/createScan.php" method="post">';
                    echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">Start Scan</button> </td>';
                    echo '</form>';
                }
                elseif ($item['deviceScanned'] != "Scanning"){

                }



                echo'</ul>';

                echo'</div>';
            }
            echo '</div>';
            echo'<h1 style="padding-bottom: 10px">Safe: </h1>';
            echo '<div class="row features">';
            foreach ($Secure as $item){
                echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon" style="color: Green"></i>';
                echo'<ul class="list-unstyled">';
                echo '<h3 class="name">Device: '. $item['deviceName'] .'</h3>';
                echo '<li><strong>Mac:</strong>'.$item['deviceMacAddress'].'</li>';
                echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                echo '<li><strong>Scanned:</strong>'.$item['deviceScanned'].'</li>';

                if ($item['deviceScanned'] != "No"){
                    echo'<form action="/scan/viewScan.php" method="post">';
                    echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . getNewestScan($item['deviceID'], $scannedDevices) . '" id="'.getNewestScan($item['deviceID'], $scannedDevices).'">View Scan</button> </td>';
                    echo'</form>';
                }

                else if($item['deviceScanned'] == "No"){
                    echo '<form action="/scan/createScan.php" method="post">';
                    echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">Start Scan</button> </td>';
                    echo '</form>';
                }
                elseif ($item['deviceScanned'] != "Scanning"){

                }



                echo'</ul>';

                echo'</div>';
            }
            echo '</div>';
            echo'<h1 style="padding-bottom: 10px">Other: </h1>';
            echo '<div class="row features">';
            foreach ($Other as $item){
                echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon" style="color: grey"></i>';

                echo'<ul class="list-unstyled">';
                echo '<h3 class="name">Device: '. $item['deviceName'] .'</h3>';
                echo '<li><strong>Mac:</strong>'.$item['deviceMacAddress'].'</li>';
                echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                echo '<li><strong>Scanned:</strong>'.$item['deviceScanned'].'</li>';

                if ($item['deviceScanned'] != "No"){
                    echo'<form action="/scan/viewScan.php" method="post">';
                    echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . getNewestScan($item['deviceID'], $scannedDevices) . '" id="'.getNewestScan($item['deviceID'], $scannedDevices).'">View Scan</button> </td>';
                    echo'</form>';
                }

                else if($item['deviceScanned'] == "No"){
                    echo '<form action="/scan/createScan.php" method="post">';
                    echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">Start Scan</button> </td>';
                    echo '</form>';
                }
                elseif ($item['deviceScanned'] != "Scanning"){

                }



                echo'</ul>';

                echo'</div>';
            }
            echo '</div>';

            echo'</div>';
            echo'</div>';
            ?>

    </div>
</section>
<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/js/swiper.jquery.min.js"></script>
<script src="assets/js/Simple-Slider.js"></script>
</body>

</html>
