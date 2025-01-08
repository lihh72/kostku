<?php
// Menghancurkan sesi dan menghapus semua data sesi
session_name("owner_session"); // Pastikan nama sesi sesuai dengan yang digunakan
session_start();
session_unset(); // Menghapus semua data sesi
session_destroy(); // Menghancurkan sesi

// Redirect ke halaman login setelah logout
header("Location: login.php");
exit();
?>
