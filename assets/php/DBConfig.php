<?php

function getConnection(){

    if (!defined('USER')){
        define("USER", 'root');
    }
    if (!defined('PASSWORD')){
        define("PASSWORD", 'Z5H&Qc^z!Fz9');
    }
    if (!defined('HOST')){
        define("HOST", 'localhost:3306');
    }
    if (!defined('DATABASE')){
        define("DATABASE", 'nashnetworkdatabase');
    }

    $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);

    return $connection;
}

function agentChecker(){
    $connection = getConnection();
    //select all the users with the given username
    $query = $connection->prepare("SELECT agentOnline FROM user WHERE userID=:UserID");
    $query->bindParam("UserID", $_SESSION['user_id'], PDO::PARAM_STR);
    $query->execute();

    //get the result
    $result = $query->fetch(PDO::FETCH_ASSOC);



    $_SESSION['agentStatus'] = $result['agentOnline'];

    header("index.php");
}

if (isset($_POST['checkAgentStatus'])){
    agentChecker();

}

