<?php

$host = "db";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Connessione al server MySQL
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}
?>
