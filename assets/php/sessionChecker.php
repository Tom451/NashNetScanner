<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: ../index.php');
    exit;
} else {
    //to prevent session fixation log the user out after 15 mins of inactivity
    if(time() - $_SESSION['timestamp'] > 900) { //subtract new timestamp from the old one
        echo"<script>alert('In accordance to OWASP guidelines, You have been signed out due to inactivity');</script>";
        unset($_SESSION['nonce'], $_SESSION['user_id'], $_SESSION['timestamp'], $_SESSION['userName']);
        $_SESSION['logged_in'] = false;
        header("Location: ../index.php?timeout=true");
         //redirect to index.php
        exit;
    } else {
        $_SESSION['timestamp'] = time(); //set new timestamp
        require_once "DBConfig.php";
        agentChecker();

    }

}
