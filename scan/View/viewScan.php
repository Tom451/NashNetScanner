<?php

//start the session
require '..\..\assets\php\sessionChecker.php';
require_once '..\..\assets\php\DBConfig.php';

require "..\Loading.php";

$connection = getConnection();
$scanID = null;

//when the user selects the scan, get the post request from that
if (isset($_POST['refresh'])) {
    echo $_POST['refresh'];
}

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

//
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
//Create a new variable for the CVE list which will be created
$CVEList = null;

foreach ($vulns as $item) {


    if ($item['VulnCPE'] == "NO CPE"){
        global $JSONObject;

    }
    else{

        //check if the cpe is for the application or if it is for the operating system

        if(str_starts_with($item['VulnCPE'], "cpe:/o")){
            //if the CPE is an Opering system one due to the large number of errors and vulnerabilities
            // Leave it out and move onto the application vulnerabilities
            print("Ignore === ". $item['VulnCPE']);
            print("\n");
        }
        else{
            //make the JSON object global to allow it to be accessed from the HTML
            global $JSONObject;

            //Create the API String to query the NVD Database
            $UPLOADString = "https://services.nvd.nist.gov/rest/json/cves/1.0/?cpeMatchString=".$item['VulnCPE'];

            //Get the return JSON object
            $JSON = file_get_contents($UPLOADString);
            $JSONObject = json_decode($JSON);

            //if there is no CPE then
            if($JSONObject -> totalResults != 0){
                $CVEItems = $JSONObject -> result ->CVE_Items;

                foreach($CVEItems as $CVE){
                    $CVEList[] = $CVE;
                }

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
$Critical = 0;
$Unknown = 0;

if (isset($JSONObject)){

    //check there is a list
    if($CVEList != null){
        foreach($CVEList as $CVEItem){
            $count = null;

            if (!empty($CVEItem->impact->baseMetricV3->cvssV3)) {

                $count = $CVEItem->impact->baseMetricV3->cvssV3->baseSeverity;
                if ($count == "HIGH" ) {
                    $High = $High + 1;
                }elseif($count == "CRITICAL"){
                    $Critical = $Critical + 1;
                }
                elseif ($count == "MEDIUM") {
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
        $HighPercentage = round($High / count($CVEList)* 100, 2);
        $MedPercentage = round($Medium / count($CVEList) * 100, 2);
        $LowPercentage = round($Low / count($CVEList) * 100, 2);
        $CriticalPercentage = round($Critical / count($CVEList) * 100, 2);
        $UnknownPercentage = round($Unknown / count($CVEList) * 100, 2);

    }





}

$devicesScans = getOtherScans($device['deviceID']);
$countOfScans = count($devicesScans);

$value = max($devicesScans);
$key = array_search($value, $devicesScans);

$currentScan = true;

if($devicesScans[$key]['ScanID'] != $scanID){
    $currentScan = false;
}

//this fucntion is called bt the page to show the banner at the top to give the user a quick overview on their currrent
// Security status
function getSecurity($device, $CVEList, $currentScan){
    //As long and the CVE list is not null then it will calculate
    if (!$currentScan){
        //High ammount of issues found
        echo('<section class="highlight-section" style="background: grey;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-warning" style="transform: scale(2);"></i></h2>
                            <p class="text-center">You are viewing an historical record of ' . $device['deviceName'] . ',
                                to view more up to date information please visit the "Devices" Page </p>
               </div></div></section>');
    }

    if(!is_null($CVEList)){
        if (count($CVEList) >= 5){
            //High ammount of issues found
            echo('<section class="highlight-section" style="background: red;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-times-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">Multiple issues with ' . $device['deviceName'] . ',
                                have been found, vulnerabilities will be listed bellow for your information, action will need to be taken and 
                                appropriate mesures will also be listed below</p>
               </div></div></section>');
        }
        else {
            //Low amount of issues
            echo('<section class="highlight-section" style="background: forestgreen;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-check-circle" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No concerning issues with' . $device['deviceName'] . ',
                                the found vulnerabilities will be listed bellow for your information, however your device is currently safe so no
                                extra action will need to be taken,
                                feel free to scan another device </p>
                </div></div></section>');
        }
    }
    //else if the device is down
    else if ($device['deviceScanned'] == "Host Down"){
        //No issues
        echo('<section class="highlight-section" style="background: orange;"> <div class="container"> <div class="intro">
                <h2 class="text-center"> <i class="fa fa-unlink" style="transform: scale(2);"></i></h2>
                            <p class="text-center">The device: ' . $device['deviceName'] . ',
                                was not online during this scan, please visit the devices page to start a new scan</p>
               </div></div></section>');
    }
    else{
        //No issues found
        echo('<section class="highlight-section" style="background: green;"> <div class="container"> <div class="intro">
                <h2 class="text-center"><i class="fa fa-smile-o" style="transform: scale(2);"></i></h2>
                            <p class="text-center">No issues at all with ' . $device['deviceName'] . ',
                                your device is currently safe so no
                                extra action will need to be taken,
                                enjoy your day! </p>
                </div></div></section>');

    }

}

function getOtherScans($deviceID): bool|array
{


    $connection = getConnection();

    $query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
    JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE device.deviceID = :deviceID AND scan.ScanType = 'VulnScan'");
    $query->bindParam("deviceID", $deviceID, PDO::PARAM_STR);

    $query->execute();

    //return the result get the result
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

require '../../assets/php/VulnHelp.php';
?>



<!DOCTYPE html>
<html lang="en">

<script>
    window.onload = function() {
        document.getElementById('loading').style.display = "none";
    }
</script>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>View Scan</title>
    <?php require "../../assets/php/headerData.php" ?>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/Highlight-Section.css">
</head>

<body>

    <!-- Get the nav bar for a logged in page -->
    <?php require '../../assets/php/navBarLoggedIn.php' ?>



    <div class="container"<?php
    //If the scan type is vulnerability then:
    if($scan['ScanType'] != "VulnScan")
        echo'style="display: none" ';
    ?>
    >
        <div class="row">
            <div class="col" style="padding-top: 10px;">
                <h1>Vulnerability Scan for Device:&nbsp;</h1>
                <?php getSecurity($device, $CVEList, $currentScan); ?>

            </div>

        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="col-md-4">

                <img class="d-lg-flex justify-content-center m-auto" style="padding-bottom: 5px;" src="https://media.istockphoto.com/photos/iphone-11-pro-max-in-silver-color-template-front-view-with-blank-for-picture-id1202959585?k=20&amp;m=1202959585&amp;s=612x612&amp;w=0&amp;h=8DsZSdfyxdzg9OaFS3gOHITfJxjE2gQr6mCJP7ghPiA=" width="100px" alt="iPhone">
                <ul class="list-group" style="padding-top: 5px;">
                    <li class="list-group-item"><span>Device Name: <?php echo $device['deviceName']?></span></li>
                    <li class="list-group-item"><span>Mac Address: <?php echo $device['deviceMacAddress']?></span></li>
                    <li class="list-group-item"><span>IP: <?php echo $device['deviceIP']?></span></li>
                    <li class="list-group-item"><span>History: </span>
                        <form name="scanSelected" action="viewScan.php" method="post">
                            <label for="scans"><select name="scanSelected" id="scans" onchange="this.form.submit()">
                                    <?php

                                    $count = count($devicesScans);
                                    $scanNumber = 0;

                                    foreach ($devicesScans as $scans){
                                        if ($scanNumber == $count - 1){
                                            break;
                                        }
                                        else{
                                            echo '<option name="scanSelected" value="'.$scans['ScanID'].'"><b>Previous: </b>'.$scans['ScanTime'].'</option>';
                                            $scanNumber++;
                                        }

                                    }
                                    echo '<option name="scanSelected" value="'.$devicesScans[$count-1]['ScanID'].'"><b>Newest: </b>'.$devicesScans[$count-1]['ScanTime'].'</option>';

                                    ?>
                            </select></label>
                            <button class="btn btn-primary" type="submit" onclick="this.form.submit()">Submit</button>
                        </form>

                    </li>
                </ul>
            </div>
            <div class="col-md-8">
                <h2>Overview:</h2>
                <p>Over View of your current security posture.&nbsp;</p>
                <p>Here is a break down of the vulnerbilties on the device.</p>

                <figure>
                    <ul class="list-group" style="padding-top: 7px;">
                        <li class="list-group-item">
                            <h3>Current Outlook:</h3>
                            <p>Hover to view percentages: </p>
                            <div class="graphic" >
                                <div class="row">
                                    <div class="chart" style="border-radius: 5px; border-color: grey; max-height: 5%">

                                        <span class="block" title="You have: <?php echo $CriticalPercentage?>% Critical Issues" style="width: <?php echo $CriticalPercentage?>%; background-color: darkred">
                                    <span class="value"><?php echo $CriticalPercentage?>%</span>

                                </span>
                                <span class="block" title="You have: <?php echo $HighPercentage?>% High Issues" style="width: <?php echo $HighPercentage?>%; background-color: red">
                                    <span class="value"><?php echo $HighPercentage?>%</span>

                                </span>
                                        <span class="block" title="You have: <?php echo $MedPercentage?>% Medium Issues" style="width: <?php echo $MedPercentage?>%; background-color: orange">
                                    <span class="value"><?php echo $MedPercentage?>%</span>

                                </span>
                                        <span class="block" title="You have: <?php echo $LowPercentage?>% Low Issues" style="width: <?php echo $LowPercentage?>%; background-color: green;  overflow: hidden;">
                                    <span class="value"><?php echo $LowPercentage?>%</span>

                                </span>
                                        <span class="block" title="Safe" style="width: <?php if($Low == 0 AND $Medium == 0 AND $High == 0){echo '100%';}else{echo'0%';}?>; background-color: green">
                                    <span class="value">No Vulnerabilities</span>
                                </span>

                                    </div>
                                </div>
                                <div class="x-axis">
                                    <ul class="legend">
                                        <li><i class="fa fa-square" style="padding-right: 3px; color: darkred"></i>Critical</li>
                                        <li><i class="fa fa-square" style="padding-right: 3px; color: red"></i>High</li>
                                        <li><i class="fa fa-square" style="padding-right: 3px; color: orange"></i>Medium</li>
                                        <li><i class="fa fa-square" style="padding-right: 3px; color: green"></i>Low</li>
                                    </ul>
                                </div>
                            </div>
                            <h3>At a glance:</h3>

                            <p><?php
                                if ($High + $Critical + $Medium + $Low === 0){
                                echo 'No issues found on your device, please visit the devices tab to view your other devices';
                                }
                                else {
                                    echo 'You currently have: ' . $High + $Critical . ' number of important issues and: ' . $Medium + $Low .
                                        ' lesser issues, it is advised you look at these important ones first, these are labeled in the red colours 
                                        for ease of viewing';
                                }
                                ?>
                            </p>


                        </li>
                    </ul>


                </figure>
            </div>

            <div class="col-md-12">
                <h2 style="padding-top: 2%">Vulnerbilities</h2>

                <table class="table table-hover table table-striped table-bordered" id="vulnTable">
                    <thead>
                    <tr>
                        <th>Weakness Name</th>
                        <th>Type Of Vulnerbility</th>
                        <th>Port Affected</th>
                        <th aria-sort="ascending">Serverty</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    require_once 'VulnerbilitySection.php';
                    if (empty($item)){
                        $item = false;
                    }

                    createTableArea($CVEList, $item);


                    ?>

                    </tbody>

                </table>

                <script src="../../assets/js/tableSort.js"></script>
                <script>sortTable()</script>

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
            <div class="col-md-4"><img class="d-lg-flex justify-content-center m-auto" style="padding-bottom: 5px;" src="https://cdn-icons-png.flaticon.com/512/73/73400.png" width="100pxpx" alt="Icon">
                <ul class="list-group" style="padding-top: 5px;">
                    <li class="list-group-item"><span>Number Of Devices: <?php echo count($devices)?></span> </li>
                    <li class="list-group-item"><span>ScanID:&nbsp;<?php echo $scan['ScanID']?></span></li>
                    <li class="list-group-item"><span>Issues: <?php echo "0"?></span></li>
                </ul>
            </div>
            <div class="col-md-8">
                <h2>Overview:</h2>
                <p>Current Devices Attached to Given Network&nbsp;</p><table id="example" class="table table-striped table-bordered"  >
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Mac</th>
                        <th>Start Scan</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($devices as $item) {
                        echo '<tr>';
                        echo '<td>' . $item['deviceName'] . '</td>';
                        echo '<td>' . $item['deviceIP'] . '</td>';
                        echo '<td>' . $item['deviceMacAddress'] . '</td>';
                        echo '<form action="/scan/Create/CreateScan.php" method="post">';
                        echo '<td> <button class="btn btn-primary bg-secondary d-lg-flex" name="createScan" value="' . $item['deviceIP'] . '" id="'.$item['deviceIP'].'">View Scan</button> </td>';
                        echo '</form>';
                        echo '<tr>';
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>



</body>

</html>