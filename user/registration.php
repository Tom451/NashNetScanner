<script src="../assets/js/loginAndRegistrationJS.js"></script>

<?php

session_start();
include('../assets/php/DBConfig.php');

$connection = getConnection();

if (isset($_POST['register'])) {

    //get all the details
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $firstName =$_POST['firstName'];
    $lastName =$_POST['lastName'];
    $lastSeen = date("Y-m-d H:i:s");
    try {
        $LoginNONCE = random_int(0, PHP_INT_MAX);
    } catch (Exception $e) {
        echo '<script>errorMessagePopUp("Un unexpected error occurred please try again later")</script>';
        header("Location: index.php");
    }

    //get the password hash
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    //check if username already exsits
    $query = $connection->prepare("SELECT * FROM usercredentials WHERE userName=:username");
    $query->bindParam("username", $username, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo '<script>usernameInUse()</script>';
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
        $query = $connection->prepare("INSERT INTO usercredentials(userName,password,UserID,lastSeen,userNonce) VALUES (:username,:password_hash,:userID,:lastSeen,:LoginNonce)");
        $query->bindParam("username", $username, PDO::PARAM_STR);
        $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
        $query->bindParam("userID", $userIDResult, PDO::PARAM_STR);
        $query->bindParam("lastSeen", $lastSeen, PDO::PARAM_STR);
        $query->bindParam("LoginNonce", $LoginNONCE, PDO::PARAM_STR);


        $result = $query->execute();

        if ($result) {
            header('Location: index.php');
        } else {
            echo '<script>ErrorWithForm()</script>';
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
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/Login-Form-Clean.css">
</head>

<body>
<?php require '../assets/php/navBarLoggedOut.php' ?>

<section class="login-clean" style="width: auto;height: auto;">
    <form  method="post">
        <h2 class="sr-only">Login Form</h2>
        <div class="illustration"><img src="../assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" width="170" height="150" alt="Logo"></div>
        <div class="form-group"><label><input class="form-control" type="text" name="firstName" placeholder="First Name"></label></div>
        <div class="form-group"><label><input class="form-control" type="text" name="lastName" placeholder="Last Name"></label></div>
        <div class="form-group"><label><input class="form-control" type="text" name="username" placeholder="Username"></label></div>
        <div class="form-group"><label><input class="form-control" type="email" name="email" placeholder="Email"></label></div>
        <div class="form-group"><label><input class="form-control" type="password" name="password" placeholder="Password"></label></div>
        <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" name="register">Register</button></div><a class="forgot" href="#"></a>
    </form>
</section>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>

</body>

</html>
