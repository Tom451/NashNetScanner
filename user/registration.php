<script src="../assets/js/loginAndRegistrationJS.js"></script>

<?php

session_start();
include('../assets/php/DBConfig.php');

$connection = getConnection();

//if password and/or email is incorrect
function errorCheck(){
    if(isset($_GET['incorrect'])){
        if ($_GET['incorrect'] == "password"){
            echo "Please make your password is: Over 8 characters, lowercase, uppercase and a special character";
        }
        else if ($_GET['incorrect'] == "email"){
            echo "Please make sure your email is correct";
        }
        else if ($_GET['incorrect'] == "firstname"){
            echo "Please make sure your first name is not too long";
        }
        else if ($_GET['incorrect'] == "secondname"){
            echo "Please make sure your second name is not too long";
        }
        else if ($_GET['incorrect'] == "username"){
            echo "Please make sure your username is not too long";
        }

    }

}


if (isset($_POST['register'])) {
    //start by cheching password length
    //get all the details
    $username = $_POST['username'];
    $email = $_POST['email'];
    $firstName =$_POST['firstName'];
    $lastName =$_POST['lastName'];
    $lastSeen = date("Y-m-d H:i:s");

    $number = preg_match('@[0-9]@', $_POST['password']);
    $uppercase = preg_match('@[A-Z]@', $_POST['password']);
    $lowercase = preg_match('@[a-z]@', $_POST['password']);
    $specialChars = preg_match('@[^\w]@', $_POST['password']);



    //check all inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        header("Location:/user/registration.php?incorrect=email");
    }
    if (strlen($_POST['password']) < 8 || !$number || !$uppercase || !$lowercase || !$specialChars){
        header("Location:/user/registration.php?incorrect=password");
    }
    if (strlen($firstName) > 49){
        header("Location:/user/registration.php?incorrect=firstname");
    }
    if (strlen($lastName) > 49){
        header("Location:/user/registration.php?incorrect=secondname");
    }
    if (strlen($username) > 44){
        header("Location:/user/registration.php?incorrect=username");
    }
    else{
        //start by cheching password length
        try {
            $LoginNONCE = random_int(0, PHP_INT_MAX);
        } catch (Exception $e) {
            echo '<script>errorMessagePopUp("Un unexpected error occurred please try again later")</script>';
            header("Location: index.php");
        }

        //get the password hash
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

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
                header('Location: /index.php?UserAccount=Created');
            } else {
                header('Location: /index.php?UserAccount=Error');
            }
        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Register</title>
    <?php require "../assets/php/headerData.php" ?>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/Login-Form-Clean.css">
</head>

<body>
<?php require '../assets/php/navBarLoggedOut.php' ?>

<section class="login-clean" style="width: auto;height: auto;">
    <form  method="post">
        <h2 class="sr-only">Registration Form</h2>
        <div class="illustration"><img src="../assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" width="170" height="150" alt="Logo"></div>
        <p class="text-center" style="display: flex; color: red" id="errorMessage" ><?php errorCheck(); ?></p>
        <div class="form-group"><input class="form-control" type="text" name="firstName" placeholder="First Name"></div>
        <div class="form-group"><input class="form-control" type="text" name="lastName" placeholder="Last Name"></div>
        <div class="form-group"><input class="form-control" type="text" name="username" placeholder="Username"></div>
        <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email"></div>
        <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password"></div>
        <div class="form-group"><button class="btn btn-primary btn-block" type="submit" style="background: var(--blue);" name="register">Register</button></div><a class="forgot" href="#"></a>
    </form>
</section>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>

</body>

</html>
