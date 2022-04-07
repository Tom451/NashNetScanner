<?php

//start the session
require '..\assets\php\sessionChecker.php';

require_once '..\assets\php\DBConfig.php';
$connection = getConnection();

//when the user selects the scan, get the post request from that
if (isset($_POST['DiscoverySelected'])) {

    $scanID = $_POST['VulnScanSelected'];
    $query = $connection->prepare("SELECT * FROM scan WHERE ScanID=:scanID");
    $query->bindParam("scanID", $scanID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    $scan = $query->fetch(PDO::FETCH_ASSOC);

}
else {
    print($_POST['VulnScanSelected']);
}




//select all the devices with the ip and mac address with scan ID
$query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

//select all the vulnerbilities
$query = $connection->prepare("SELECT * FROM vulnerabilities JOIN vulnscan ON vulnerabilities.VulnID = vulnscan.VulnID JOIN scan ON vulnscan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
$query->bindParam("scanID", $scanID, PDO::PARAM_STR);
$query->execute();

//get the result of the vulnerbilities
$vulns = $query->fetchAll(PDO::FETCH_ASSOC);


if (count($vulns) == 0){
    $vulnScore = 100;
}
else{
    $vulnScore = count($devices)/count($vulns);
    $vulnScore = $vulnScore*100;
}



?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/percentages.css">
</head>

<body>

<?php require '../assets/php/navBarLoggedIn.php' ?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>Scan Information</h2>
            <ul class="list-group">
                <?php
                echo '<li class="list-group-item"><span><strong>Scan Type: </strong> ' . $scan['ScanType'] . '&nbsp;</span></li>';
                echo '<li class="list-group-item"><span><strong>Time Started: </strong> ' . $scan['SessionID'] . '&nbsp;</span></li>';
                echo '<li class="list-group-item"><span><strong>ScanID: </strong>' . $scan['ScanID'] . '&nbsp;</span></li>'
                ?>
                <li class="list-group-item">
                        <span>
                        <a class="btn btn-primary" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-1" href="#collapse-1" role="button">Show Devices</a>
                        <a class="btn btn-primary" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-2" href="#collapse-2" role="button">Show Ports</a>
                        </span>
                </li>

            </ul>
        </div>

        <div class="col-md-6"
            <?php

            if($scan['ScanType'] == "VulnScan")

                echo'style="visibility: shown"';
            else{
                echo'style="visibility: hidden" ';
            }

            ?>
        >
            <div class="infoarea">
                <h2 title="This is calculated by the number of the devices divided by the number of vulnerabilities">Vulnerability Score</h2>
                <p>Higher Number the more secure you are, hover on the title to get an explanation</p>
            </div>



            <div class="row">


                <!-- Chart 2 -->
                <svg viewBox="0 0 36 36" class="circular-chart" >
                    <path class="circle"

                          stroke-dasharray="<?php echo ''.$vulnScore.',100' ?>"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          stroke =
                          <?php
                          //sets colour

                          if ($vulnScore >= 70){
                              echo 'green';
                          }
                          else if($vulnScore >=40){
                              echo 'orange';
                          }
                          else{
                              echo 'red';
                          }

                          ?>

                    />
                    <text x="18" y="20.35" class="percentage"><?php echo ''.$vulnScore.'%' ?></text>
                </svg>

            </div>


        </div>

    </div>
</div>

<div class="container" style="padding-top: 3px;padding-bottom: 3px;">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col"><img class="d-lg-flex m-auto align-items-lg-center" src="/assets/images/NND.png" style="height: 20px" alt="Logo"></div>
            </div>
            <div class="row">
                <div class="col">
                    <ul class="list-group">
                        <li class="list-group-item"><span><strong>Device Name:&nbsp;</strong></span></li>
                        <li class="list-group-item"><span><strong>Number of Issues:&nbsp;</strong></span></li>
                        <li class="list-group-item"><span><strong>Device Type:&nbsp;</strong>&nbsp;</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6"></div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row" >
                <div class="col">
                    <p>You Have</p>
                    <h3 id="numOfVulns">0</h3>
                    <p>Vulnerbilities</p>
                </div>
                <div class="col">

                    <p> Of these tis percentage need looking at: </p>
                    <!-- Chart 2 -->
                    <svg viewBox="0 0 36 36" class="circular-chart" >
                        <path class="circle"

                              stroke-dasharray="<?php echo ''.$vulnScore.',100' ?>"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              stroke =
                              <?php
                              //sets colour

                              if ($vulnScore >= 70){
                                  echo 'green';
                              }
                              else if($vulnScore >=40){
                                  echo 'orange';
                              }
                              else{
                                  echo 'red';
                              }

                              ?>

                        />
                        <text x="18" y="20.35" class="percentage"><?php echo ''.$vulnScore.'%' ?></text>
                    </svg>

                </div>
                <div class="col">


                </div>

            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div>
                <div class="collapse hide" id="collapse-1" style="padding-top: 30px">

                    <h2>Devices</h2>

                    <table id="devices" class="table table-striped table-bordered">
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


            <div>
                <div class="collapse hide" id="collapse-2">
                    <h2>Ports</h2>

                    <table id="vuln" class="table table-striped table-bordered">
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

        <div class="col-md-12">
            <div>
                <div class="collapse hide" id="collapse-1" style="padding-top: 30px">

                    <h2>Vulnerbilities</h2>

                    <table id="Vulnerbilities" class="table table-striped table-bordered">
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

                        foreach ($vulns as $item) {

                            if (!$item['VulnCPE'] == "NO CPE"){

                                //check if the cpe is for the application or if it is for the operating system

                                if(str_starts_with($item['VulnCPE'], "cpe:/o")){
                                    //do something
                                    print("Ignore === ". $item['VulnCPE']);
                                    print("\n");
                                }
                                else{

                                    $UPLOADString = "https://services.nvd.nist.gov/rest/json/cves/1.0/?cpeMatchString=".$item['VulnCPE'];
                                    echo $UPLOADString;
                                    $JSON = file_get_contents($UPLOADString);
                                    $JSONObject = json_decode($JSON);





                                    if ($JSONObject -> totalResults != 0){

                                        $CVEItems = $JSONObject -> result ->CVE_Items;

                                        if (!empty($CVEItems -> impact->baseMetricV3->cvssV3)){

                                            foreach ($CVEItems as $CVE) {
                                                echo '<tr>';
                                                echo '<td>' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
                                                echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->attackComplexity . '</td>';
                                                echo '<td>' . $item['VulnName'] . '</td>';
                                                echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->baseSeverity . '</td>';
                                                echo '<tr>';
                                            }

                                        }
                                        else{
                                            foreach ($CVEItems as $CVE) {
                                                echo '<tr>';
                                                echo '<td>' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
                                                echo '<td>' . $CVE ->impact->baseMetricV2->cvssV2->accessComplexity . '</td>';
                                                echo '<td>' . $item['VulnName'] . '</td>';
                                                echo '<td>' . $CVE ->impact->baseMetricV2->severity . '</td>';
                                                echo '<tr>';
                                            }
                                        }




                                    }


                                }




                            }



                        }

                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p>&nbsp;</p>


    </div>
</div>


<script src="../../assets/js/jquery.min.js"></script>
<script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
</body>

</html>