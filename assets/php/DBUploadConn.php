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
    $result = $query->fetch(PDO::FETCH_ASSOC);

    //if there is no results then show incorrect credentials
    if (!$result) {
        echo "False";
    } else {

        $post_data = array(
            'scan' => array(
                'userID' => $result['userID'],
                'scanID' => $result['ScanID'],
                'ScanInfo' => $result['ScanInfo'],
                'ScanType' => $result['ScanType'],
                'ScanStatus' => $result['ScanStatus'],
            )
        );

        echo json_encode($post_data);

    }
}


elseif (isset($_POST['UploadWithVerification'])){



    $JSONObject = json_decode($_POST['UploadWithVerification']);

    if (empty($JSONObject->UserName)){
        echo "Not Verified";
    }
    else{

        $USERNONCE = $JSONObject->UserName;

        //select all the users with the given username
        $query = $connection->prepare("SELECT UserID FROM usercredentials WHERE userNonce=:userNonce");
        $query->bindParam("userNonce", $USERNONCE, PDO::PARAM_STR);

        if (!$query->execute()){
            echo "Not verified";
        }

        $User = $JSONObject -> UserName;
        $Vulns = $JSONObject -> scannedVulns;
        $Devices = $JSONObject ->scannedDevices;
        $Scan = $JSONObject ->currentScan;


        //if there are devices upload those
        if ($Devices != null){

            $storedProcedure = 'CALL addDevice(:indeviceIP, :inRTT, :inMacAddress, :inName, :inScanID)';

            $device = json_decode($_POST['UploadWithVerification']);

            foreach($Devices as $mydata){

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

            }

        if ($Vulns != null){

            $storedProcedure = 'CALL addVuln(:inScanID, :inVulnName, :inVulnVersion, :inVulnExtraData, :inVulnProduct, :inPortNumber, :inVulnCPE)';

            foreach($Vulns as $mydata){

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


        }
        else{
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
            echo 'The upload was completed successfully!!';
        }

    }





}



?>