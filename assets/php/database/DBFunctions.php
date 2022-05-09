<?php

function getDiscoveredDevicesFromDB($connection, $USERID) : array{
    //select all the devices that have been discovered by the logged-in user
    //This is all the networked scanned devices, and due to the grouping will mean that the devices only appear once
    //even if they have been found multiple times
    $query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
    JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.UserID=:userid AND scan.ScanType = 'NetDisc' GROUP by device.deviceIP");
    //bind the variables
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);

    //get the result
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);

}

function getScannedDevicesFromDB($connection, $USERID) : array{

    //get all the devices that have been vulnerability assessed.
    $query = $connection->prepare("SELECT device.deviceID, scan.ScanID, scan.ScanTime, scan.ScanStatus FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
    JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanType = 'VulnScan' AND scan.userID = :userid");
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    return $query->fetchAll(PDO::FETCH_ASSOC);

}

function getAllPendingScansFromDB($connection, $USERID) : array{


    //get all the devices that are currently pending a scan
    $query = $connection->prepare("SELECT * FROM scan WHERE scan.userID = :userid AND ScanStatus = 'Pending'" );
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getScan($connection, $scanID) : mixed {

    $query = $connection->prepare("SELECT * FROM scan WHERE ScanID=:scanID");
    $query->bindParam("scanID", $scanID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    return $query->fetch(PDO::FETCH_ASSOC);

}

function getAllDevicesOnScan($connection, $scanID) : mixed {

    $query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
    JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
    $query->bindParam("scanID", $scanID, PDO::PARAM_STR);
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getAllVulnerabilities($connection, $scanID) : mixed {
    //select all the vulnerabilities
    $query = $connection->prepare("SELECT * FROM vulnerabilities JOIN vulnscan ON vulnerabilities.VulnID = vulnscan.VulnID JOIN scan ON vulnscan.ScanID = scan.ScanID WHERE scan.ScanID = :scanID");
    $query->bindParam("scanID", $scanID, PDO::PARAM_STR);
    $query->execute();

    //get the result of the vulnerabilities
    return $query->fetchAll(PDO::FETCH_ASSOC);

}

function getOtherScansForDevice($deviceID): bool|array
{


    $connection = getConnection();

    $query = $connection->prepare("SELECT * FROM device JOIN deviceScan ON device.deviceID = deviceScan.DeviceID
    JOIN scan ON deviceScan.ScanID = scan.ScanID WHERE device.deviceID = :deviceID AND scan.ScanType = 'VulnScan'");
    $query->bindParam("deviceID", $deviceID, PDO::PARAM_STR);

    $query->execute();

    //return the result get the result
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getUserByUserNonce($connection, $USERNONCE): array {

    //select all the users with the given username
    $query = $connection->prepare("SELECT UserID FROM usercredentials WHERE userNonce=:userNonce");
    $query->bindParam("userNonce", $USERNONCE, PDO::PARAM_STR);
    $query->execute();

    //get the userID result
    return $query->fetch(PDO::FETCH_ASSOC);

}

function getUserByUserName($connection, $USERNAME): mixed{
    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM usercredentials WHERE userName=:username ");
    $query->bindParam("username", $USERNAME, PDO::PARAM_STR);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);

}

function getAllUsersByEmail($connection, $EMAIL): mixed{
    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM usercredentials WHERE email=:email ");
    $query->bindParam("email", $EMAIL, PDO::PARAM_STR);
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);

}

function getAllUserPendingScans($connection, $userID): array {

    //select all the users with the given username and with scans
    $query = $connection->prepare("SELECT * FROM scan WHERE userID=:userid AND ScanStatus='Pending'");
    $query->bindParam("userid", $userID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function setDeviceStatus($connection, $inMacAddress, $inIPaddress, $inDeviceScanned): mixed{

    $storedProcedure = 'CALL setDeviceStatus(:inMacAddress, :inIPAddress, :inDeviceScanned)';
    $statement = $connection->prepare($storedProcedure);
    $statement->bindParam(':inDeviceScanned', $inDeviceScanned, PDO::PARAM_STR);
    $statement->bindParam(':inMacAddress', $inMacAddress, PDO::PARAM_STR);
    $statement->bindParam(':inIPAddress', $inIPaddress, PDO::PARAM_STR);

    return $statement->execute();
}

function addUser($connection, $firstName, $lastName, $email, $username, $password_hash, $lastSeen, $LoginNONCE):mixed{
    //start with adding the user
    $query = $connection->prepare("INSERT INTO user(firstName,lastName,email) VALUES (:firstName,:lastName,:email)");
    $query->bindParam("firstName", $firstName, PDO::PARAM_STR);
    $query->bindParam("lastName", $lastName, PDO::PARAM_STR);
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();

    //get the userID of new user to be added.
    $query = $connection->prepare("SELECT userID FROM user WHERE email=:email");
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();
    $userIDResult = $query->fetchColumn();

    //then add the credentials
    $query = $connection->prepare("INSERT INTO usercredentials(userName,password,UserID,lastSeen,userNonce) VALUES (:username,:password_hash,:userID,:lastSeen,:LoginNonce)");
    $query->bindParam("username", $username, PDO::PARAM_STR);
    $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
    $query->bindParam("userID", $userIDResult, PDO::PARAM_STR);
    $query->bindParam("lastSeen", $lastSeen, PDO::PARAM_STR);
    $query->bindParam("LoginNonce", $LoginNONCE, PDO::PARAM_STR);


    return $query->execute();
}
