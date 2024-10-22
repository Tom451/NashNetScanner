<?php
require '..\..\assets\php\sessionChecker.php';



require_once '..\..\assets\php\database\DBConfig.php';

$USERID = $_SESSION['user_id'];
$connection = getConnection();

//select all the users with the given username
$query = $connection->prepare("SELECT * FROM scan WHERE userID=:userid");
$query->bindParam("userid", $USERID, PDO::PARAM_STR);
$query->execute();

//get the result
$result = $query->fetchAll(PDO::FETCH_ASSOC);

//get all the devices that have had a vulnerbility scan
//select all the devices with the ip and mac address with scan ID
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'VulnScan' AND scan.userID = :userid");
$query->bindParam("userid", $USERID, PDO::PARAM_STR);

$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);


?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Previous Scans</title>
    <?php require "../../assets/php/navBar/headerData.php" ?>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/visStyling.css">
</head>

<body>

    <?php require '../../assets/php/navBar/navBarLoggedIn.php' ?>

    <script src="../../assets/js/visDrawing.js"></script>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

    <script>

        function convert(){
            <?php

            $js_array = json_encode($devices);

            echo "var javascript_array = ". $js_array . ";";
            ?>

             return javascript_array;

        }
        window.addEventListener(

            "load", () =>
            {
                let items = convert();

                draw(items);


            }


        );




    </script>




    <section class="features-clean">
        <div class="container">
            <div class="intro">
                <h2 class="text-center">Previous Scans</h2>
            </div>

            <div class="row" style="padding-bottom: 10px">
                <div class="col-md-12"><p class="text-center">Here you can see all your previous scans, this is where you can select what scan you would like to view&nbsp;</p></div>
            </div>

            <form action="/scan/View/viewScan.php" method="post">
            <div class="row features">
                <?php
                if(count($result) == 0){
                    header('Location: tutorial.php');
                }

                foreach (array_reverse($result) as $item) {

                    if ($item['ScanType'] == "VulnScan"){
                        echo '<div class="col-sm-6 col-lg-4 item"><i class="fa fa-search icon"></i>';
                        echo '<h3 class="name">Vulnerability Scan</h3>';
                    }
                    elseif ($item['ScanType'] == "NetDisc"){
                        echo '<div class="col-sm-6 col-lg-4 item"><i class="fa fa-eye icon"></i>';
                        echo '<h3 class="name">Network Discovery</h3>';

                    }
                    echo '<ul class="list-unstyled">';
                    echo'<li><strong>Scan Type: </strong>'.$item['ScanType'].'</li>';
                    echo'<li><strong>Scan Status:</strong>'.$item['ScanStatus'].'</li>';
                    echo'<li><strong>Scan ID: </strong>'.$item['ScanID'].'</li>';
                    echo'<li><strong>Date: </strong>'.$item['SessionID'].'</li>';
                    if ($item['ScanStatus'] == "Pending"){

                        echo'<li></li>';
                    }
                    //check if it is errored
                    elseif ($item['ScanStatus'] == "Error"){
                        echo'<li></li>';
                    }
                    else {
                        echo '</ul><button class="btn btn-primary bg-secondary d-lg-flex" name="scanSelected" value="' . $item['ScanID'] . '" id="'.$item['ScanID'].'">View Scan</button>';
                    }
                    echo'</div>';

                }

                ?>
            </div>

            </form>
        </div>
    </section>


    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
</body>

</html>