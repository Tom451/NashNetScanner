<script src="assets/js/loginAndRegistrationJS.js"></script>

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
            $_SESSION['key'] = $password;
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
    <nav class="navbar navbar-light navbar-expand-md navigation-clean-button">
        <div class="container"><a class="navbar-brand" href="#">NashNetworkScanner</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">First Item</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Second Item</a></li>
                    <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Dropdown </a>
                        <div class="dropdown-menu"><a class="dropdown-item" href="#">First Item</a><a class="dropdown-item" href="#">Second Item</a><a class="dropdown-item" href="#">Third Item</a></div>
                    </li>
                </ul><span class="navbar-text actions"> <a class="login" href="#">Log In</a><a class="btn btn-light action-button" role="button" href="user/registration.php">Sign Up</a></span>
            </div>
        </div>
    </nav>

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

