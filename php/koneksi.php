<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>