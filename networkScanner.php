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

    $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
    echo $_SESSION['key'];
    echo "#######";
    //get the logged in user
    require "assets/php/encryptData.php";
    $username = $_SESSION['user_id'];
    $SessionID = generateRandom();
    $scanInfo = encryptData("nmap -sP -oX C:\Users\Public\Documents\NMAPNetworkScan.xml", $_SESSION['key']);
    $scanType = encryptData("NetDisc", $_SESSION['key']);
    $scanStatus = encryptData("Pending", $_SESSION['key']);

    $query = $connection->prepare("INSERT INTO scan(userID,SessionID,ScanInfo,scanType,scanStatus) 
    VALUES (:userID,:SessionID,:scanInfo,:scanType,:scanStatus)");
    $query->bindParam("userID", $username, PDO::PARAM_STR);
    $query->bindParam("SessionID", $SessionID, PDO::PARAM_STR);
    $query->bindParam("scanInfo", $scanInfo, PDO::PARAM_STR);
    $query->bindParam("scanType", $scanType, PDO::PARAM_STR);
    $query->bindParam("scanStatus", $scanStatus, PDO::PARAM_STR);

    $result = $query->execute();

    echo $SessionID;
}

?>


<form method="post">
    <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" name="pingScan">Login</button></div><a class="forgot" href="#"></a>
</form>
