<?php

//start the session
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
} else {
    require '..\assets\php\DBConfig.php';
    $connection = getConnection();
}

//when the user selects the scan, get the post request from that
if (isset($_POST['scanSelected'])) {

    $scanID = $_POST['scanSelected'];
    $query = $connection->prepare("SELECT * FROM scan WHERE ScanID=:scanID");
    $query->bindParam("scanID", $scanID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    $scan = $query->fetch(PDO::FETCH_ASSOC);

}
else {
    header('Location: previousScans.php');
}




//select all the devices with the ip and mac address with scan ID
$query = $connection->prepare("SELECT device.deviceIP, device.deviceMacAddress, device.deviceName, device.deviceLastSeen FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

//select all the vulnerbilities
$query = $connection->prepare("SELECT vulnerabilities.VulnName, vulnerabilities.VulnProduct, vulnerabilities.VulnVersion, vulnerabilities.VulnPortNumber, vulnerabilities.VulnExtraData
FROM vulnerabilities JOIN vulnscan ON vulnerabilities.VulnID = vulnscan.VulnID JOIN scan ON vulnscan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result
$vulns = $query->fetchAll(PDO::FETCH_ASSOC);


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>

    <?php require '../assets/php/navBarLoggedIn.php' ?>

    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Overview&nbsp;</h2>
                <div class="row"></div>
            </div>
            <div class="col-md-6">
                <h2>Scan Information</h2>
                <ul class="list-group">
                    <?php
                    echo '<li class="list-group-item"><span><strong>Scan Type: </strong> ' . $scan['ScanType'] . '&nbsp;</span></li>';
                    echo '<li class="list-group-item"><span><strong>Time Started: </strong> ' . $scan['SessionID'] . '&nbsp;</span></li>';
                    echo '<li class="list-group-item"><span><strong>ScanID: </strong>' . $scan['ScanID'] . '&nbsp;</span></li>'
                    ?>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div><a class="btn btn-primary" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-1" href="#collapse-1" role="button">Show Devices</a>
                    <div class="collapse show" id="collapse-1">
                        <table id="devices" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>IP</th>
                                <th>Mac</th>
                                <th>Last Seen</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($devices as $item) {
                                echo '<tr>';
                                echo '<td>' . $item['deviceName'] . '</td>';
                                echo '<td>' . $item['deviceIP'] . '</td>';
                                echo '<td>' . $item['deviceMacAddress'] . '</td>';
                                echo '<td>' . $item['deviceLastSeen'] . '</td>';
                                echo '<tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <p>&nbsp;</p>

            <div class="col-md-12">

                <div><a class="btn btn-primary" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-2" href="#collapse-2" role="button">Show Vulnerabilities</a>
                    <div class="collapse show" id="collapse-2">
                        <table id="vuln" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Name and Number</th>
                                <th>Product</th>
                                <th>Version</th>
                                <th>Extra Data</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if($vulns == null){
                                echo '<tr>';
                                echo '<td> N/A </td>';
                                echo '<td> N/A </td>';
                                echo '<td> N/A </td>';
                                echo '<td> N/A </td>';
                                echo '<tr>';
                            }
                            foreach ($vulns as $item) {
                                echo '<tr>';
                                echo '<td>' . $item['VulnName'] . " " . $item['VulnPortNumber'] . '</td>';
                                echo '<td>' . $item['VulnProduct'] . '</td>';
                                echo '<td>' . $item['VulnVersion'] . '</td>';
                                echo '<td>' . $item['VulnExtraData'] . '</td>';
                                echo '<tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
</body>

</html>