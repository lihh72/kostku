<?php
session_start();

// Hapus semua data sesi
session_unset();
session_destroy();

// Hapus cookie 'username' jika ada
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/"); // Set waktu kadaluarsa di masa lalu untuk menghapus cookie
}

// Redirect ke halaman login
header("Location: login.php");
exit();
?>
