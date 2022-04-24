<?php
require_once 'DBConfig.php';
require_once 'DBFunctions.php';

//Get PDO connection string
$connection = getConnection();

// Show Scans user has
if (isset($_GET['USERID'])) {

    //get the inputted username and the password
    $USERNONCE = $_GET['USERID'];

    $result = getAllUsersByUserNonce($connection, $USERNONCE);

    $result = getAllUserPendingScans($connection, $result['UserID']);

    //if there is no results then show incorrect credentials
    if (!$result) {
        echo "False";
    } else {

        $post_data = null;

        foreach ($result as $item) {

            $post_data[] = array(
                'scan' => array(
                    'userID' => $item['userID'],
                    'scanID' => $item['ScanID'],
                    'ScanInfo' => $item['ScanInfo'],
                    'ScanType' => $item['ScanType'],
                    'ScanStatus' => $item['ScanStatus'],
                )
            );

        }


        echo json_encode($post_data);

    }
}

// Upload Scans and Vulnerability
elseif (isset($_POST['UploadWithVerification'])) {

    $JSONObject = json_decode($_POST['UploadWithVerification']);

    //check if user is verified
    if (empty($JSONObject->userName)) {
        echo "Not Verified";
    } else {

        // if a mac address is provided in the scan info section then it is a Scan update
        if (!empty($JSONObject->scanInfo)) {



            $inDeviceScanned = $JSONObject->ScanStatus;
            $inIPAddress = "Null";
            $inMacAddress = "Null";

            if (filter_var($JSONObject->scanInfo, FILTER_VALIDATE_MAC)) {

                $inMacAddress = $JSONObject->scanInfo;


            }
            else if (filter_var($JSONObject->scanInfo, FILTER_VALIDATE_IP)){

                $inIPAddress = $JSONObject->scanInfo;


            }

            if (setDeviceStatus($connection, $inMacAddress, $inMacAddress, $inDeviceScanned)) {
                echo "Updated Scan";
            } else {
                echo "Error";
            }
            return;
        }


        $USERNONCE = $JSONObject->userName;

        if (!getAllUsersByUserNonce($connection, $USERNONCE)) {
            echo "Not verified";
        }

        $User = $JSONObject->userName;

        $Devices = $JSONObject->scannedDevices;

        $Scan = $JSONObject->currentScan;


        //if there are devices upload those
        if ($Devices != null) {

            $Vulns = $JSONObject->scannedVulns;

            $storedProcedure = 'CALL addDevice(:indeviceIP, :inRTT, :inMacAddress, :inName, :inScanID)';

            $device = json_decode($_POST['UploadWithVerification']);

            foreach ($Devices as $mydata) {

                $deviceIP = $mydata->ipAddress;
                $deviceMac = $mydata->macAddress;
                $deviceName = $mydata->name;
                $RTT = $mydata->RTT;
                $ScanID = $mydata->ScanID;


                $statement = $connection->prepare($storedProcedure);

                $statement->bindParam(':indeviceIP', $deviceIP, PDO::PARAM_STR);
                $statement->bindParam(':inMacAddress', $deviceMac, PDO::PARAM_STR);
                $statement->bindParam(':inName', $deviceName, PDO::PARAM_STR);
                $statement->bindParam(':inRTT', $deviceName, PDO::PARAM_STR);
                $statement->bindParam(':inScanID', $ScanID, PDO::PARAM_STR);
                $statement->execute();

                //get the userID of new user to be added.
                $query = $connection->prepare("SELECT deviceID FROM device WHERE deviceMacAddress=:deviceMacAddress AND deviceIP = :deviceIP");
                $query->bindParam("deviceMacAddress", $deviceMac, PDO::PARAM_STR);
                $query->bindParam("deviceIP", $deviceIP, PDO::PARAM_STR);
                $query->execute();

                $deviceIDResult = $query->fetchColumn();

                echo 'Updated Device';

            }

            //if there is vulnerabilities
            $vulnCount = 0;

            if ($Vulns != null) {

                $storedProcedure = 'CALL addVuln(:inScanID, :inVulnName, :inVulnVersion, :inVulnExtraData, :inVulnProduct, :inPortNumber, :inVulnCPE)';


                foreach ($Vulns as $item){
                    $VulnCPE = $item->VulnCPE;

                    if ($VulnCPE != "NO CPE" AND !str_starts_with($VulnCPE, "cpe:/o")) {
                        //Create the API String to query the NVD Database
                        $UPLOADString = "https://services.nvd.nist.gov/rest/json/cves/1.0/?cpeMatchString=" . $item->VulnCPE;
                        //Get the return JSON object
                        $JSON = file_get_contents($UPLOADString);
                        $JSONObject = json_decode($JSON);
                        $vulnCount = $vulnCount + $JSONObject->totalResults;
                        //if there is CPEs check they are not OS
                    }else{
                        echo 'STR Skip';


                    }
                }
                foreach ($Vulns as $mydata) {

                    $vulnName = $mydata->VulnName;
                    $vulnVersion = $mydata->VulnVersion;
                    $vulnExtraData = $mydata->VulnExtraData;
                    $vulnProduct = $mydata->VulnProduct;
                    $vulnPortNumber = $mydata->VulnPortNumber;
                    $vulnCPE = $mydata->VulnCPE;
                    $ScanID = $mydata->scanID;

                    $statement = $connection->prepare($storedProcedure);

                    $statement->bindParam(':inVulnName', $vulnName, PDO::PARAM_STR);
                    $statement->bindParam(':inVulnVersion', $vulnVersion, PDO::PARAM_STR);
                    $statement->bindParam(':inVulnExtraData', $vulnExtraData, PDO::PARAM_STR);
                    $statement->bindParam(':inVulnProduct', $vulnProduct, PDO::PARAM_STR);
                    $statement->bindParam(':inPortNumber', $vulnPortNumber, PDO::PARAM_STR);
                    $statement->bindParam(':inVulnCPE', $vulnCPE, PDO::PARAM_STR);
                    $statement->bindParam(':inScanID', $ScanID, PDO::PARAM_STR);
                    $statement->execute();

                }

                //if there IS CVEs tehn mark the device as vulnerable
                $sql = 'UPDATE device JOIN devicescan on device.deviceID= devicescan.deviceID SET device.deviceScanned = :NewStatus WHERE devicescan.ScanID = :scanID';
                // prepare statement
                $statement = $connection->prepare($sql);

                if ($vulnCount != 0) {
                    // bind params
                    $NewStatus = "Yes: Vulnerable";

                } else {
                    $NewStatus = "Yes: Safe";
                }

                $statement->bindParam(':NewStatus', $NewStatus, PDO::PARAM_STR);
                $statement->bindParam(':scanID', $ScanID);

                if (!$statement->execute()) {
                    echo "Unsuccessful";
                    return;
                }

            } else if ($Scan->scanType == 'NetDisc') {
                echo "No Update";

            }else{
                $NewStatus = "Yes: Safe";

                //if there IS CVEs tehn mark the device as vulnerable
                $sql = 'UPDATE device JOIN devicescan on device.deviceID= devicescan.deviceID SET device.deviceScanned = :NewStatus WHERE devicescan.ScanID = :scanID';
                // prepare statement
                $statement = $connection->prepare($sql);

                $statement->bindParam(':NewStatus', $NewStatus, PDO::PARAM_STR);
                $statement->bindParam(':scanID', $ScanID);

                if (!$statement->execute()) {
                    echo "Unsuccessful";
                    return;
                }

            }



            //update the device scanned area

        }


        $ScanStatus = $Scan->ScanStatus;
        $ScanID = $Scan->scanID;


        $sql = 'UPDATE scan SET ScanStatus = :NewStatus WHERE ScanID = :ScanID';

        // prepare statement
        $statement = $connection->prepare($sql);


        // bind params
        $statement->bindParam(':NewStatus', $ScanStatus, PDO::PARAM_STR);
        $statement->bindParam(':ScanID', $ScanID);

        // execute the UPDATE statment
        if ($statement->execute()) {
            echo 'Successful';
        }
        else{
            echo 'unsuccessful';
        }

    }
}

//Set the agent status
elseif(isset($_POST['AgentStatus'])){
    $JSONObject = json_decode($_POST['AgentStatus']);

    //check if user is verified
    if (empty($JSONObject->userNONCE)) {
        echo "Not Verified";
    } else {

        $query = $connection->prepare("UPDATE user LEFT JOIN usercredentials ON usercredentials.UserID = user.UserID SET user.agentOnline = :agentStatus WHERE usercredentials.userNonce = :userNonce");
        $query->bindParam("userNonce", $JSONObject->userNONCE, PDO::PARAM_STR);
        $query->bindParam("agentStatus", $JSONObject->agentStatus, PDO::PARAM_STR);

        if (!$query->execute()) {
            echo "Not verified";
        }
        else{
            echo'success';
        }

    }

}
