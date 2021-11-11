<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
} else {

}

if (isset($_POST['pingScan'])) {


    require 'assets\php\DBConfig.php';
    require 'assets\php\randomSessionCreator.php';

    $USERID = $_SESSION['user_id'];
    $ScanStatus = "Pending";
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
        require "assets/php/encryptData.php";
        $username = $_SESSION['user_id'];
        $SessionID = generateRandom();
        $scanInfo = "nmap -sP -oX";
        $scanType = "NetDisc";
        $scanStatus = "Pending";

        $query = $connection->prepare("INSERT INTO scan(userID,SessionID,ScanInfo,scanType,scanStatus) 
    VALUES (:userID,:SessionID,:scanInfo,:scanType,:scanStatus)");
        $query->bindParam("userID", $username, PDO::PARAM_STR);
        $query->bindParam("SessionID", $SessionID, PDO::PARAM_STR);
        $query->bindParam("scanInfo", $scanInfo, PDO::PARAM_STR);
        $query->bindParam("scanType", $scanType, PDO::PARAM_STR);
        $query->bindParam("scanStatus", $scanStatus, PDO::PARAM_STR);

        $result = $query->execute();

        echo $SessionID;
    } else {

        echo "Scan Waiting to be run please run previous scan";
    }




}

?>


<form method="post">
    <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" name="pingScan">Login</button></div><a class="forgot" href="#"></a>
</form>
