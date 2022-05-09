<?php
//start the session
session_start();
if(isset($_SESSION['user_id'])){
    header('Location: homepage.php');
    exit;
}
if(isset($_GET['login'])){

}

function checkError(){
    if(isset($_GET['timeout'])){
        if($_GET['timeout'] == true){
            echo 'You have been logged out in accordance with OWASP guidelines to prevent session fixation';
        }
        else{
            echo 'Timed Out';
        }
    }
    else if(isset($_GET['UserAccount'])){
        if($_GET['UserAccount'] == "Created"){
            echo 'Account created Successfully!';
        }
        else if($_GET['UserAccount'] == "Error"){
            echo 'Account creation error please try again!';
        }
    }
    if (isset($_GET['login'])){
        if ($_GET['login']=="incorrect"){
            echo 'Login Incorrect Please try again';
        }

    }

}


require_once 'assets\php\database\DBConfig.php';
require_once 'assets\php\database\DBFunctions.php';

//if the login is complete then log the user in
if (isset($_POST['login'])) {

    //Get PDO connection string
    $connection = getConnection();

    //get the inputted username and the password
    $username = $_POST['username'];
    $password = $_POST['password'];



    //get the result
    $result = getUserByUserName($connection, $username);

    //if there is no results then show incorrect credentials
    if (!$result) {
        header('Location: \user\registration.php');

    } else {
        //compare the password inputted to the password hash
        if (password_verify($password, $result['password'])) {

            //if the given password is correct then, get the derived key from the
            // password and store that in the session variable for later encryption use
            $_SESSION['nonce'] = $result['userNonce'];
            $_SESSION['user_id'] = $result['UserID'];
            $_SESSION['userName'] = $username;
            $_SESSION['timestamp'] = time();


            agentChecker();

            //set the users last seen
            // send the user to the home page
            header('Location: homepage.php');
        } else {
            // if the password was not correct then don't let the user sign in
            header('Location: index.php?login=incorrect');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="icon" href="faviconIcon.ico" type='image/x-icon'/>
</head>



<body>

    <?php require 'assets/php/navBar/navBarLoggedOut.php' ?>

    <section class="login-clean">
        <form method="post">
            <h2 class="sr-only">Login Form</h2>
            <div class="illustration"><img src="assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" width="170" height="170" alt="Logo"></div>
            <p style="display: flex; color: black" ><b><?php checkError();?></b></p>
            <div class="form-group"><input class="form-control" type="text" name="username" placeholder="Username"></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password"></div>
            <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" value="login" name="login">Login</button></div><a class="forgot" href="#"></a>
        </form>
    </section>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>

