<?php

const USER = 'root';
const PASSWORD = 'Z5H&Qc^z!Fz9';
const HOST = 'localhost:3306';
const DATABASE = 'nashnetworkingdashboard';

try {
    $connection = new PDO("mysql:host=".HOST.";dbname=".DATABASE, USER, PASSWORD);
} catch (PDOException $e) {
    exit("Error: " . $e->getMessage());
}
?>
