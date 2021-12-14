<script src="assets/js/loginAndRegistrationJS.js"></script>
<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon"/>

<?php
session_start();
if(isset($_SESSION['user_id'])){
    header('Location: homepage.php');
    exit;
} else {

}

require 'assets\php\DBConfig.php';


if (isset($_POST['login'])) {

    //Get PDO connection string
    $connection = getConnection();

    //get the inputted username and the password
    $username = $_POST['username'];
    $password = $_POST['password'];

    //select all the users with the given username
    $query = $connection->prepare("SELECT * FROM usercredentials WHERE userName=:username");
    $query->bindParam("username", $username, PDO::PARAM_STR);
    $query->execute();

    //get the result
    $result = $query->fetch(PDO::FETCH_ASSOC);

    //if there is no results then show incorrect credentials
    if (!$result) {
        echo '<script>IncorrectCredentials()</script>';
    } else {
        //comapare the password inputted to the password hash
        if (password_verify($password, $result['password'])) {

            //if the given password is correct then, get the derived key from the
            // password and store that in the session variable for later encryption use
            $_SESSION['nonce'] = $result['userNonce'];
            $_SESSION['user_id'] = $result['UserID'];
            $_SESSION['userName'] = $username;
            // send the user to the home page
            header('Location: homepage.php');
        } else {
            // if the password was not correct then don't let the user sign in
            echo '<script>IncorrectCredentials()</script>';
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
    <?php require 'assets/php/navBarLoggedOut.php' ?>

    <section class="login-clean" style="width: auto;height: auto;">
        <form method="post">
            <h2 class="sr-only">Login Form</h2>
            <div class="illustration"><img src="assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" width="170" height="150" alt="Logo"></div>
            <div class="form-group"><input class="form-control" type="text" name="username" placeholder="Username"></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password"></div>
            <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" value="login" name="login">Login</button></div><a class="forgot" href="#"></a>
        </form>
    </section>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>

