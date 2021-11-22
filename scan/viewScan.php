<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');

    exit;
} else {

}

if (isset($_POST['scanSelected'])) {

    $scanID = $_POST['scanSelected'];

}
else {
    header('Location: previousScans.php');
}

require 'assets\php\DBConfig.php';

//Get PDO connection string
$connection = getConnection();



//select all the users with the given username
$query = $connection->prepare("SELECT device.deviceIP, device.deviceMacAddress, device.deviceName, device.deviceLastSeen FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result
$result = $query->fetchAll(PDO::FETCH_ASSOC);


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
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div><a class="btn btn-primary" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-1" href="#collapse-1" role="button">Show Content</a>
                    <div class="collapse show" id="collapse-1"><table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
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

        foreach ($result as $item) {
            echo '<tr>';
            echo '<td>' . $item['deviceName'] . '</td>';
            echo '<td>' . $item['deviceIP'] . '</td>';
            echo '<td>' . $item['deviceMacAddress'] . '</td>';
            echo '<td>' . $item['deviceLastSeen'] . '</td>';
            echo '<tr>';
        }
        ?>

        </tbody>
    </table></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6"></div>
        </div>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
</body>

</html>