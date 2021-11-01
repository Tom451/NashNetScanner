<?php
$connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);

$username = $_POST['username'];
$password = "";

//get the userID
$query = $connection->prepare("SELECT UserID FROM user WHERE userName=:userName");
$query->bindParam("userName", $username, PDO::PARAM_STR);
$query->execute();
$queryReturn = $query->fetch(PDO::FETCH_ASSOC);
$userID = $queryReturn['userID'];

//if username does not exsist
if (!$queryReturn) {
    echo '<script>IncorrectCredentials()</script>';
} else {
    $query = $connection->prepare("SELECT password FROM usercredentials WHERE UserID=:userID");
    $query->bindParam("userID", $userID, PDO::PARAM_STR);
    $query->execute();
    $queryReturn = $query->fetch(PDO::FETCH_ASSOC);
    $password = $queryReturn['password'];
    $userID = $query->fetch(PDO::FETCH_ASSOC);

}
