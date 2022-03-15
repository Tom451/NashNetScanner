<?php
require 'DBConfig.php';
//Get PDO connection string
$connection = getConnection();

//If the post Value is JSON for the upload start the upload of the devices
if (isset($_GET['USERID'])) {

    //get the inputted username and the password
    $USERNONCE = $_GET['USERID'];

    //select all the users with the given username
    $query = $connection->prepare("SELECT UserID FROM usercredentials WHERE userNonce=:userNonce");
    $query->bindParam("userNonce", $USERNONCE, PDO::PARAM_STR);
    $query->execute();

    //get the userID result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM scan WHERE userID=:userid AND ScanStatus='Pending'");
    $query->bindParam("userid", $result['UserID'], PDO::PARAM_STR);
    $query->execute();

    //get the result
    $result = $query->fetchAll(PDO::FETCH_ASSOC);


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

elseif (isset($_POST['UploadWithVerification'])) {

    $JSONObject = json_decode($_POST['UploadWithVerification']);

    //check if user if varified
    if (empty($JSONObject->userName)) {
        echo "Not Verified";
    } else {
        //check if its a mac address being provided if it is then its a device scan
        if (!empty($JSONObject->scanInfo)) {

            $storedProcedure = 'CALL setDeviceStatus(:inMacAddress, :inIPAddress, :inDeviceScanned)';

            $statement = $connection->prepare($storedProcedure);

            $statement->bindParam(':inDeviceScanned', $JSONObject->ScanStatus, PDO::PARAM_STR);

            if (filter_var($JSONObject->scanInfo, FILTER_VALIDATE_MAC)) {

                $IP = "Null";
                $statement->bindParam(':inMacAddress', $JSONObject->scanInfo, PDO::PARAM_STR);
                $statement->bindParam(':inIPAddress', $IP, PDO::PARAM_STR);

            }
            else if (filter_var($JSONObject->scanInfo, FILTER_VALIDATE_IP)){
                $MAC = "Null";
                $statement->bindParam(':inMacAddress', $MAC, PDO::PARAM_STR);
                $statement->bindParam(':inIPAddress', $JSONObject->scanInfo, PDO::PARAM_STR);


            }

            if ($statement->execute()) {
                echo "Updated Scan";
            } else {
                echo "Error";
            }
            return;
        }

        $USERNONCE = $JSONObject->userName;

        //select all the users with the given username
        $query = $connection->prepare("SELECT UserID FROM usercredentials WHERE userNonce=:userNonce");
        $query->bindParam("userNonce", $USERNONCE, PDO::PARAM_STR);

        if (!$query->execute()) {
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
            if ($Vulns != null) {

                $storedProcedure = 'CALL addVuln(:inScanID, :inVulnName, :inVulnVersion, :inVulnExtraData, :inVulnProduct, :inPortNumber, :inVulnCPE)';
                $vulnCount = 0;

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
                };

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


            } else {
                echo "No Data Provided";
            }
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

elseif(isset($_POST['AgentStatus'])){
    $JSONObject = json_decode($_POST['AgentStatus']);

    //check if user if verified
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



?>