<?php
require 'DBConfig.php';

//Get PDO connection string
$connection = getConnection();
$storedProcedure = "CALL test(?)";


    $deviceIP = "test";
    $deviceMac = "test";
    $deviceName = "test";
    $RTT = "test";
    $DeviceID = 0;


    $statement = $connection->prepare($storedProcedure);
    $value = 'hello';
    $statement->bindParam(1, $value, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);

    // call the stored procedure
    $statement->execute();

    print "procedure returned $value\n";

