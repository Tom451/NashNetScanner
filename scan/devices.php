<?php

require '..\assets\php\sessionChecker.php';
require '..\assets\php\DBConfig.php';

$USERID = $_SESSION['user_id'];
$connection = getConnection();

//select all the devices that have been discovered by the loged in user
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.UserID=:userid AND scan.ScanType = 'NetDisc' GROUP by device.deviceIP");
$query->bindParam("userid", $USERID, PDO::PARAM_STR);

$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

if(count($devices) == 0){
    header('Location: tutorial.php');
}

//select all the devices that have been discovered by the loged in user
$query = $connection->prepare("SELECT device.deviceID, scan.ScanID, scan.ScanTime FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'VulnScan' AND scan.userID = :userid");

$query->bindParam("userid", $USERID, PDO::PARAM_STR);

$query->execute();

//get the result
$scannedDevices = $query->fetchAll(PDO::FETCH_ASSOC);

function getNewestScan($deviceID, $scannedDevices){

    $scans = null;

    foreach ($scannedDevices as $item){

        if ($item['deviceID'] == $deviceID){

            $scans[$deviceID]['deviceID'] = $item['deviceID'];
            $scans[$deviceID]['ScanID'] = $item['ScanID'];
            $scans[$deviceID]['ScanTime'] = $item['ScanTime'];

        }

    }

    if (count($scans) == 1){

        return $scans[$deviceID]['ScanID'];
    }
    else if(count($scans) > 1){

        $value = max($scans[$deviceID]['ScanTime']);

        $key = array_search($value, $scans);
        return $key;

    }






}

function getVulns($NeedsAttention, $Secure, $Other, $Scanning){
    //As long and the CVE list is not null then it will calculate
    if(!empty($Scanning)) {
        if (count($Scanning) != 0) {
            //No issues found
            echo('<section class="highlight-blue" style="background: darkorange;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-hourglass-2" style="transform: scale(2);"></i></h2>
                            <p class="text-center">There is a scan currently under way, please press this button bellow to see how many devices are left to scan
                             <br><b><span id="scanProg">' . count($Scanning) . '</span> devices left to scan </b>
                             </p>
                             <button style="padding: 10px " class="btn btn-secondary " id="new">Refresh <i class="fa fa-refresh" style="transform: scale(1);"></i></button>
                </div></div></section>');

        }
    }
    elseif (is_null($NeedsAttention) AND is_null($Secure)){
        echo('<section class="highlight-blue" style="background: dodgerblue;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-birthday-cake" style="transform: scale(2);"></i></h2>
                            <p class="text-center">Congratulations on your network Scan! Now you currently have scanned no
                             devices for vulnerabilities, please start a full scan from the menu section to begin!</p>
               </div></div></section>');
    }
    else if(!is_null($NeedsAttention)){
        if (count($NeedsAttention) >= 5){
            //High ammount of issues found
            echo('<section class="highlight-blue" style="background: red;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-times-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">Your Network contains '.count($NeedsAttention).' that will need attention they will 
                             be listed bellow for your information</p>
               </div></div></section>');
        }
        else {
            //Low amount of issues
            echo('<section class="highlight-blue" style="background: forestgreen;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-check-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No concerning issues with,
                                the found vulnerabilities will be listed bellow for your information, however your device is currently safe so no
                                extra action will need to be taken,
                                feel free to scan another device </p>
                </div></div></section>');
        }
    }
    else{
        //No issues found
        echo('<section class="highlight-blue" style="background: dodgerblue;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-smile-o" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No issues at all with,
                                your device is currently safe so no
                                extra action will need to be taken,
                                enjoy your day! </p>
                </div></div></section>');

    }

}


if (isset($_POST['callFunc1'])) {
    //select all the devices that have been discovered by the loged in user
    $query = $connection->prepare("SELECT * FROM scan WHERE scan.userID = :userid AND ScanStatus = 'Pending'" );
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    $devicesToScan = $query->fetchAll(PDO::FETCH_ASSOC);
    $countDevicesToScan = count($devicesToScan);
    if ($countDevicesToScan == 0){

        $countDevicesToScan = 1;
    }


    echo $countDevicesToScan;

    header("Refresh:0");

    return;



}


?>

<!DOCTYPE html>
<html lang="en">
<meta http-equiv="refresh" content="60" />

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Devices</title>
    <?php require "../assets/php/headerData.php"?>
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
    <link rel="stylesheet" href="../assets/css/scanOverlayAndAccoridion.css">
</head>

<body>

<?php require '../assets/php/navBarLoggedIn.php' ?>


<!-- The overlay -->
<div id="myNav" class="overlay">
    <!-- Button to close the overlay navigation -->
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
    <section class="features-clean">
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
                        <form action="/scan/createScan.php" method="post"><button class="btn btn-primary" type="submit" name="createScan" value="NetDisc">Run Discovery</button></form>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-laptop icon"></i>
                        <h3 class="name">Full Scan</h3>
                        <p class="description">Scan All the currently known devices on the network. Note this will not find new devices but
                            rather scan the ones currently known&nbsp;</p>

                        <form action="/scan/createScan.php" method="post">';
                            <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="FULLSCAN" id="FULLSCAN">Start Scan</button>

                        </form>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-history icon"></i>
                        <h3 class="name">History</h3>
                        <p class="description">View your latest and your oldest scans all in one area!&nbsp;</p>

                        <form method="post"><button class="btn btn-primary" type="submit" name="previousScans" value="PrevScan">View Scans</button></form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

</div>


<section class="features-clean">
    <div class="container">
        <div class="intro">
            <h2 class="text-center">Devices</h2>
        </div>


        <script>
            $(document).ready(function(){
                $("#new").click(function(){
                    $.ajax({
                        url: 'devices.php',
                        type: 'post',
                        data: { "callFunc1": "1"},
                        success: function(response) {
                            document.getElementById("scanProg").innerText = "There are: " + response + " left";
                        }
                    });
                });
                $.ajax({
                    url: 'devices.php',
                    type: 'post',
                    data: { "callFunc1": "1"},
                    success: function(response) {
                        document.getElementById("scanProg").innerText = "There are: " + response + " left";
                    }
                });
            });

        </script>



        <div id="menuBox">
            <a onclick="openNav()">Menu</a>
        </div>

            <?php
            $i = 1;
            $NeedsAttention = null;
            $Secure = null;
            $Other = null;
            $Scanning = null;


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
                elseif ($item['deviceScanned'] == "Scanning"){
                    $Scanning[] = $item;
                }
                else{
                    $Other[] = $item;
                }
            }

            getVulns($NeedsAttention, $Secure, $Other, $Scanning);
            if (!empty($Scanning) ){
                echo'<div id="scanning"><button class="accordion" data-toggle="collapse" data-target="#scanningdata">Currently Scanning: <span id="scanProg"></span></button>';
                echo '<div id = "scanningdata" class="collapse show"><div class="row features" style="padding-top: 10px;">';


                foreach ($Scanning as $item){
                    echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-upload icon" style="color: deepskyblue"></i>';
                    echo'<ul class="list-unstyled">';
                    echo '<h3 class="name">Device: '. $item['deviceName'] .'</h3>';
                    echo '<li><strong>Mac:</strong>'.$item['deviceMacAddress'].'</li>';
                    echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                    echo '<li><strong>Scanned:</strong>'.$item['deviceScanned'].'</li>';



                    echo'</ul>';

                    echo'</div>';
                }
                echo '</div></div></div>';
            }

            //needs attention area
            echo'<div id="needsattention"><button class="accordion" data-toggle="collapse" data-target="#needsattentiondata">Needs Attention:</button>';
            echo '<div id = "needsattentiondata" class="collapse show"><div class="row features" style="padding-top: 10px;">';
            if (!empty($NeedsAttention) ){
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
                echo '</div></div></div>';
            }
            else{
                echo '<div><span>Nother to show</span></span></div></div></div></div>';
            }




                echo '<div id="safe"><button class="accordion collapsed" data-toggle="collapse" data-target="#safedata">Safe:</button>';
                echo '<div id="safedata" class="collapse"><div  class="row features">';
            if (!empty($Secure) ) {
                foreach ($Secure as $item) {
                    echo '<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon" style="color: Green"></i>';
                    echo '<ul class="list-unstyled">';
                    echo '<h3 class="name">Device: ' . $item['deviceName'] . '</h3>';
                    echo '<li><strong>Mac:</strong>' . $item['deviceMacAddress'] . '</li>';
                    echo '<li><strong>IP:</strong>' . $item['deviceIP'] . '</li>';
                    echo '<li><strong>Scanned:</strong>' . $item['deviceScanned'] . '</li>';

                    if ($item['deviceScanned'] != "No") {
                        echo '<form action="/scan/viewScan.php" method="post">';
                        echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . getNewestScan($item['deviceID'], $scannedDevices) . '" id="' . getNewestScan($item['deviceID'], $scannedDevices) . '">View Scan</button> </td>';
                        echo '</form>';
                    } else if ($item['deviceScanned'] == "No") {
                        echo '<form action="/scan/createScan.php" method="post">';
                        echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="' . $item['deviceIP'] . '">Start Scan</button> </td>';
                        echo '</form>';
                    } elseif ($item['deviceScanned'] != "Scanning") {

                    }


                    echo '</ul>';

                    echo '</div>';
                }
                echo '</div></div></div>';
            }
            else{
                echo '<div><span>Nother to show</span></span></div></div></div></div>';
            }


            echo'<div id="other"><button class="accordion collapsed" data-toggle="collapse" data-target="#otherdata">Other:</button>';
            echo '<div id="otherdata" class="collapse"><div  class="row features">';
            if (!empty($Other) ){
            foreach ($Other as $item){
                echo'<div class="col-sm-6 col-lg-4 item"><i class="fa fa-desktop icon" style="color: grey"></i>';

                echo'<ul class="list-unstyled">';
                echo '<h3 class="name">Device: '. $item['deviceName'] .'</h3>';
                echo '<li><strong>Mac:</strong>'.$item['deviceMacAddress'].'</li>';
                echo '<li><strong>IP:</strong>'.$item['deviceIP'].'</li>';
                echo '<li><strong>Scanned:</strong>'.$item['deviceScanned'].'</li>';

                if (str_contains($item['deviceScanned'], "Yes")){
                    echo'<form action="/scan/viewScan.php" method="post">';
                    echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . getNewestScan($item['deviceID'], $scannedDevices) . '" id="'.getNewestScan($item['deviceID'], $scannedDevices).'">View Scan</button> </td>';
                    echo'</form>';
                }

                else if($item['deviceScanned'] == "No" | $item['deviceScanned'] == "Host Down" ){
                    echo '<form action="/scan/createScan.php" method="post">';
                    echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">Start Scan</button> </td>';
                    echo '</form>';
                }
                elseif ($item['deviceScanned'] != "Scanning"){
                    echo '';

                }



                echo'</ul>';

                echo'</div>';
            }
            echo '<div><span>Nother to show</span></span></div></div></div></div>';
            }




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
<script>
    function openNav() {
        document.getElementById("myNav").style.width = "100%";
    }

    function closeNav() {
        document.getElementById("myNav").style.width = "0%";
    }


</script>

</body>

</html>
