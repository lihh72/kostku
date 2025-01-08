<?php
$servername = "sql306.infinityfree.com";
$username = "if0_38001806";      // Sesuaikan dengan username MySQL Anda
$password = "TtOqJWP7sAD";          // Sesuaikan dengan password MySQL Anda
$dbname = "if0_38001806_data_kos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
