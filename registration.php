<script src="assets/js/loginAndRegistrationJS.js"></script>

<?php
session_start();
include('assets/php/DBConfig.php');

$connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);

if (isset($_POST['register'])) {

    require('assets/php/generateKeys.php');
    //get all the details
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $firstName =$_POST['firstName'];
    $lastName =$_POST['lastName'];

    //get the password hash
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    //check if username already exsits
    $query = $connection->prepare("SELECT * FROM usercredentials WHERE userName=:username");
    $query->bindParam("username", $username, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo '<script>usernameInUse()</script>';;
    }
    if ($query->rowCount() == 0) {


        //start with adding the user
        $query = $connection->prepare("INSERT INTO user(firstName,lastName,email) VALUES (:firstName,:lastName,:email)");
        $query->bindParam("firstName", $firstName, PDO::PARAM_STR);
        $query->bindParam("lastName", $lastName, PDO::PARAM_STR);
        $query->bindParam("email", $email, PDO::PARAM_STR);
        $result = $query->execute();

        //get the userID of new user to be added.
        $query = $connection->prepare("SELECT userID FROM user WHERE email=:email");
        $query->bindParam("email", $email, PDO::PARAM_STR);
        $query->execute();
        $userIDResult = $query->fetchColumn();

        //then add the credentials
        $query = $connection->prepare("INSERT INTO usercredentials(userName,password,UserID,EncryptionSalt) VALUES (:username,:password_hash,:userID,:EncryptionSalt)");
        $query->bindParam("username", $username, PDO::PARAM_STR);
        $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
        $query->bindParam("userID", $userIDResult, PDO::PARAM_STR);
        $query->bindParam("EncryptionSalt", $username, PDO::PARAM_STR);


        $result = $query->execute();

        if ($result) {
            echo '<script></script>';;
        } else {
            echo '<script>ErrorWithForm()</script>';;
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
</head>

<body>
<nav class="navbar navbar-light navbar-expand-md navigation-clean-button">
    <div class="container"><a class="navbar-brand" href="#">NashNetworkScanner</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navcol-1">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link active" href="#">First Item</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Second Item</a></li>
                <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Dropdown </a>
                    <div class="dropdown-menu"><a class="dropdown-item" href="#">First Item</a><a class="dropdown-item" href="#">Second Item</a><a class="dropdown-item" href="#">Third Item</a></div>
                </li>
            </ul><span class="navbar-text actions"> <a class="login" href="#">Log In</a></span>
        </div>
    </div>
</nav>

<section class="login-clean" style="width: auto;height: auto;">
    <form  method="post">
        <h2 class="sr-only">Login Form</h2>
        <div class="illustration"><img src="assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" width="170" height="150" alt="Logo"></div>
        <div class="form-group"><input class="form-control" type="text" name="firstName" placeholder="First Name"></div>
        <div class="form-group"><input class="form-control" type="text" name="lastName" placeholder="Last Name"></div>
        <div class="form-group"><input class="form-control" type="text" name="username" placeholder="Username"></div>
        <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email"></div>
        <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password"></div>
        <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" name="register">Register</button></div><a class="forgot" href="#"></a>
    </form>
</section>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>

</body>

</html>
