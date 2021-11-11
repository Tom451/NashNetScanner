<?php

// Put your device token here (without spaces):
if (isset($_POST['JSON'])){
    $JSON = $_POST['JSON'];

    $string = $JSON;

    print_r(json_decode($string));

    return "Successful!";
}
elseif (isset($_GET['USERID'])) {
    require 'DBConfig.php';

    //Get PDO connection string
    $connection = getConnection();

    //get the inputted username and the password
    $USERID = $_GET['USERID'];

    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM scan WHERE userID=:userid AND ScanStatus='Pending'");
    $query->bindParam("userid", $USERID, PDO::PARAM_STR);
    $query->execute();

    //get the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    //if there is no results then show incorrect credentials
    if (!$result) {
        echo '<script>IncorrectCredentials()</script>';
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



?>