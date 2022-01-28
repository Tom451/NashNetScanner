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
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

//if there is only one device set the device variable else the device(s) varible will be used
if (count($devices) == 1 ){
    $device = $devices[0];
}

//select all the vulnerbilities
$query = $connection->prepare("SELECT * FROM vulnerabilities JOIN vulnscan ON vulnerabilities.VulnID = vulnscan.VulnID JOIN scan ON vulnscan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result of the vulnerbilities
$vulns = $query->fetchAll(PDO::FETCH_ASSOC);


//API ACCESS
$CVEList = null;

foreach ($vulns as $item) {


    if ($item['VulnCPE'] == "NO CPE"){
        global $JSONObject;

    }
    else{

        //check if the cpe is for the application or if it is for the operating system

        if(str_starts_with($item['VulnCPE'], "cpe:/o")){
            //do something
            print("Ignore === ". $item['VulnCPE']);
            print("\n");
        }
        else{
            global $JSONObject;
            $UPLOADString = "https://services.nvd.nist.gov/rest/json/cves/1.0/?cpeMatchString=".$item['VulnCPE'];
            $JSON = file_get_contents($UPLOADString);
            $JSONObject = json_decode($JSON);

            if($JSONObject -> totalResults != 0){
                $CVEItems = $JSONObject -> result ->CVE_Items;

                foreach($CVEItems as $CVE){

                    $CVEList[] = $CVE;

                }
            }
            else{

            }


        }

    }
}

//set variables for the Page

//vulnerability score
if (count($vulns) == 0){
  $vulnScore = 100;
}
else{
    $vulnScore = count($devices)/count($vulns);
    $vulnScore = $vulnScore*100;
}

//number for wheels
$High = 0;
$Medium = 0;
$Low = 0;
$Unknown = 0;

if (isset($JSONObject)){

    //check there is a list
    if($CVEList != null){
        foreach($CVEList as $CVEItem){
            $count = null;


            if (!empty($CVEItems -> impact->baseMetricV3->cvssV3)) {
                $count = $CVEItem->impact->baseMetricV3->cvssV3->baseSeverity;
                if ($count == "HIGH") {
                    $High = $High + 1;
                } elseif ($count == "MEDIUM") {
                    $Medium = $Medium + 1;
                } elseif ($count == "LOW") {
                    $Low = $Low + 1;
                }
                else{
                    $Unknown = $Unknown + 1;
                }
            }
            else{
                $count = $CVEItem->impact->baseMetricV2->severity;

                if ($count == "HIGH") {
                    $High = $High + 1;
                } elseif ($count == "MEDIUM") {
                    $Medium = $Medium + 1;
                } elseif ($count == "LOW") {
                    $Low = $Low + 1;
                }
                else{
                    $Unknown = $Unknown + 1;
                }

            }



        }

        //get the percentages
        $HighPercentage = round($High / count($CVEList)* 100);
        $MedPercentage = round($Medium / count($CVEList) * 100);
        $LowPercentage = round($Low / count($CVEList) * 100);
        $UnknownPercentage = round($Unknown / count($CVEList) * 100);

    }





}

function getSecurity($device, $CVEList){
    if(!is_null($CVEList)){
        if (count($CVEList) >= 15){
            echo('<section class="highlight-blue" style="background: red;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-times-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">Multiple ssues with' . $device['deviceName'] . ',
                                have been , vulnerabilities will be listed bellow for your information, action will need to be taken and 
                                appropriate mesures are listed bellow</p>
               </div></div></section>');
        }
        else {
            echo('<section class="highlight-blue" style="background: forestgreen;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-check-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No concerning issues with' . $device['deviceName'] . ',
                                the found vulnerabilities will be listed bellow for your information, however your device is currently safe so no
                                extra action will need to be taken,
                                feel free to scan another device </p>
                </div></div></section>');
        }
    }
    else{
        echo('<section class="highlight-blue" style="background: dodgerblue;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-smile-o" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No issues at all with ' . $device['deviceName'] . ',
                                your device is currently safe so no
                                extra action will need to be taken,
                                enjoy your day </p>
                </div></div></section>');

    }

}


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
    <link rel="stylesheet" href="../assets/css/percentages.css">
    <link rel="stylesheet" href="../assets/css/Highlight-Blue.css">
</head>

<body>

    <?php require '../assets/php/navBarLoggedIn.php' ?>


    <div class="container"<?php

    if($scan['ScanType'] != "VulnScan")
        echo'style="display: none" ';

    ?>
    >
        <div class="row">
            <div class="col" style="padding-top: 10px;">
                <h1>Vulnerability Scan for Device:&nbsp;</h1>
                <?php getSecurity($device, $CVEList); ?>

            </div>

        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="col-md-4"><img class="d-lg-flex justify-content-center m-auto" style="padding-bottom: 5px;" src="https://media.istockphoto.com/photos/iphone-11-pro-max-in-silver-color-template-front-view-with-blank-for-picture-id1202959585?k=20&amp;m=1202959585&amp;s=612x612&amp;w=0&amp;h=8DsZSdfyxdzg9OaFS3gOHITfJxjE2gQr6mCJP7ghPiA=" width="100pxpx">
                <ul class="list-group" style="padding-top: 5px;">
                    <li class="list-group-item"><span>Device Name: <?php echo $device['deviceName']?></span></li>
                    <li class="list-group-item"><span>Mac Address: <?php echo $device['deviceMacAddress']?></span></li>
                    <li class="list-group-item"><span>IP: <?php echo $device['deviceIP']?></span></li>
                </ul>
            </div>
            <div class="col-md-8">
                <h2>Overview:</h2>
                <p>Over View of your current security posture.&nbsp;</p><table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>High</th>
                        <th>Medium</th>
                        <th>Low</th>
                        <th>Unknown</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td style="width: 25%">
                            <!-- Chart 2 -->
                            <svg viewBox="0 0 36 36" class="circular-chart" >
                                <path class="circle"

                                      stroke-dasharray="<?php echo ''.$HighPercentage.',100' ?>"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      stroke = 'red'


                                />
                                <text x="18" y="20.35" class="percentage"><?php echo ''.$High ?></text>
                            </svg>

                        </td>
                        <td style="width: 25%">
                            <!-- Chart 3 -->
                            <svg viewBox="0 0 36 36" class="circular-chart" >
                                <path class="circle"

                                      stroke-dasharray="<?php echo ''.$MedPercentage.',100' ?>"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      stroke = 'Orange'

                                />
                                <text x="18" y="20.35" class="percentage"><?php echo ''.$Medium ?></text>
                            </svg></td>
                        <td><!-- Chart 2 -->
                            <svg viewBox="0 0 36 36" class="circular-chart" >
                                <path class="circle"

                                      stroke-dasharray="<?php echo ''.$LowPercentage.',100' ?>"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      stroke = 'Green'

                                />
                                <text x="18" y="20.35" class="percentage"><?php echo ''.$Low ?></text>
                            </svg></td>
                        <td style="width: 25%"><!-- Chart 2 -->
                            <svg viewBox="0 0 36 36" class="circular-chart" >
                                <path class="circle"

                                      stroke-dasharray="<?php echo ''.$UnknownPercentage.',100' ?>"
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      stroke = 'Grey'

                                />
                                <text x="18" y="20.35" class="percentage"><?php echo ''.$Unknown ?></text>
                            </svg></td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="col-md-12">
                <div>
                    <div style="padding-top: 30px">

                        <h2>Vulnerbilities</h2>

                        <table id="Vulnerbilities" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Vulnerbility Name</th>
                                <th>Complexity</th>
                                <th>Port Name</th>
                                <th>Serverty</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            if ($CVEList != null){
                                    //$CVEItems = $JSONObject -> result ->CVE_Items;

                                    if (!empty($CVEList -> impact->baseMetricV3->cvssV3)){

                                        foreach ($CVEList as $CVE) {
                                            echo '<tr>';
                                            echo '<td>' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->attackComplexity . '</td>';
                                            echo '<td>' . $item['VulnName'] . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->baseSeverity . '</td>';
                                            echo '<tr>';
                                        }

                                    }
                                    else{
                                        foreach ($CVEList as $CVE) {
                                            echo '<tr>';
                                            echo '<td>' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV2->cvssV2->accessComplexity . '</td>';
                                            echo '<td>' . $item['VulnName'] . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV2->severity . '</td>';
                                            echo '<tr>';
                                        }
                                    }
                                }

                            else{
                                echo '<tr>';
                                echo '<td colspan="4"> No Vulnerabilities! </td>';
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


    <div class="container"  <?php

    if($scan['ScanType'] != "NetDisc"){

        echo'style="display: none" ';
    }

    ?>
    >
        <div class="row">
            <div class="col" style="padding-top: 10px;">
                <h1>Discovery Scan for Device:&nbsp;</h1>
            </div>
        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="col-md-4"><img class="d-lg-flex justify-content-center m-auto" style="padding-bottom: 5px;" src="https://cdn-icons-png.flaticon.com/512/73/73400.png" width="100pxpx">
                <ul class="list-group" style="padding-top: 5px;">
                    <li class="list-group-item"><span>Number Of Devices: <?php echo count($devices)?></span> </li>
                    <li class="list-group-item"><span>ScanID:&nbsp;<?php echo $scan['ScanID']?></span></li>
                    <li class="list-group-item"><span>Issues: <?php echo "0"?></span></li>
                </ul>
            </div>
            <div class="col-md-8">
                <h2>Overview:</h2>
                <p>Current Devices Attached to Given Network&nbsp;</p><table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Mac</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($devices as $item) {
                        echo '<tr>';
                        echo '<td>' . $item['deviceName'] . '</td>';
                        echo '<td>' . $item['deviceIP'] . '</td>';
                        echo '<td>' . $item['deviceMacAddress'] . '</td>';
                        echo '<tr>';
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
</body>

</html>