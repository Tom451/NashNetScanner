<script src="../../assets/js/errorMessagePopUp.js"></script>

<?php
require '..\..\assets\php\sessionChecker.php';

if(isset($_POST['previousScans'])){
    header('Location: previousScans.php');
}


if (isset($_POST['createScan'])) {

    //get the dbconfig file
    require_once '..\..\assets\php\DBConfig.php';

    //get the user ID from the session
    $USERID = $_SESSION['user_id'];

    //set the scan status as pending
    $ScanStatus = "Pending";

    //get the PDO connection
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
        $username = $_SESSION['user_id'];


        // if the post is a network disc then add the info for that
        if ($_POST['createScan'] == "NetDisc"){
            $SessionID = date('d-m-y h:i:s:u') ."-". rand(0,1000);
            $scanInfo = "N/A";
            $scanType = "NetDisc";
            $scanStatus = "Pending";
            $groupID = "Pending";

        }
        //else if it is a vulnerability scan then
        elseif($_POST['createScan'] == "VulnScan"){
            $SessionID = date('d-m-y h:i:s:u') ."-". rand(0,1000);
            $scanInfo = $_POST['IPADDRESS'];
            $scanType = "VulnScan";
            $scanStatus = "Pending";

        }
        //if the post value contains an IP address it means that the user has already selected a device
        elseif(filter_var($_POST['createScan'], FILTER_VALIDATE_IP)){
            $scanInfo = $_POST['createScan'];
            $scanType = "VulnScan";
            $scanStatus = "Pending";
        }
        elseif($_POST['createScan'] == "FULLSCAN"){

            //select all the users with the given username
            $query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
            JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'NetDisc' AND scan.userID = :userid GROUP BY device.deviceID");

            $storedProcedure = 'CALL createFullScan(:userID,:SessionID,:scanInfo,:scanType,:scanStatus,:scanTime,:deviceID)';

            $query->bindParam("userid", $USERID, PDO::PARAM_STR);
            $query->execute();

            $devices = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($devices as $item){

                $SessionID = date('d-m-y h:i:s:u');

                $scanInfo = $item['deviceIP'];
                $scanType = "VulnScan";
                $scanStatus = "Pending";

                $date = date('Y-m-d H:i:s');

                $statement = $connection->prepare($storedProcedure);


                $statement->bindParam("userID", $username, PDO::PARAM_STR);
                $statement->bindParam("SessionID", $SessionID, PDO::PARAM_STR);
                $statement->bindParam("scanInfo", $scanInfo, PDO::PARAM_STR);
                $statement->bindParam("scanType", $scanType, PDO::PARAM_STR);
                $statement->bindParam("scanStatus", $scanStatus, PDO::PARAM_STR);
                $statement->bindParam("scanTime", $date, PDO::PARAM_STR);
                $statement->bindParam("deviceID", $item['deviceID'], PDO::PARAM_STR);

                $statement->execute();






            }
            header('Location: scanCreated.php');

            return;



        }
        $SessionID = date('d-m-y h:i:s:u');
        $date = date('Y-m-d H:i:s');

        $query = $connection->prepare("INSERT INTO scan(userID,SessionID,ScanInfo,scanType,scanStatus,scanTime)
        VALUES (:userID,:SessionID,:scanInfo,:scanType,:scanStatus,:scanTime)");
        $query->bindParam("userID", $username, PDO::PARAM_STR);
        $query->bindParam("SessionID", $SessionID, PDO::PARAM_STR);
        $query->bindParam("scanInfo", $scanInfo, PDO::PARAM_STR);
        $query->bindParam("scanType", $scanType, PDO::PARAM_STR);
        $query->bindParam("scanStatus", $scanStatus, PDO::PARAM_STR);
        $query->bindParam("scanTime", $date, PDO::PARAM_STR);

        $result = $query->execute();

        header('Location: scanCreated.php');

    } else {

        echo '<script>errorMessagePopUp("Scan waiting please run current scan")</script>';
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <?php require "../../assets/php/headerData.php" ?>
    <title>Create Scan</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body>
    <?php require '../../assets/php/navBarLoggedIn.php' ?>

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
                        <form method="post"><button class="btn btn-primary" type="submit" name="createScan" value="NetDisc">Run Discovery</button></form>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5 col-lg-4 item">
                    <div class="box"><i class="fa fa-laptop icon"></i>
                        <h3 class="name">Vulnerability Scan&nbsp;</h3>
                        <p class="description">Vulnerability Scan, you can select a device you would like to preform the scan on .&nbsp;</p>

                        <form method="post">

                            <label for="IPADDRESS">Address: </label>
                            <input type="text" id="IPADDRESS" name="IPADDRESS">

                            <button class="btn btn-primary" type="submit" name="createScan" value="VulnScan">Run Discovery</button>

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
    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>

