<?php
function getConnection(){
    define("USER", 'root');
    define("PASSWORD", 'Z5H&Qc^z!Fz9');
    define("HOST", 'localhost:3306');
    define("DATABASE", 'nashnetworkdatabase');

    $connection= new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);

    return $connection;
}

