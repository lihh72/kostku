<?php
session_name("admin_session");
session_start();

// Cek jika admin sudah login
if (isset($_SESSION['admin_username'])) {
    header("Location: dashboard.php");
    exit();
}

// Koneksi ke database
$pdo = new PDO("mysql:host=sql306.infinityfree.com;dbname=if0_38001806_data_kos", "if0_38001806", "TtOqJWP7sAD");

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query untuk mencari admin berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Login berhasil, set session admin
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card p-4" style="width: 400px;">
            <h4 class="text-center mb-4">Login Admin</h4>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <a href="register.php" class="btn btn-secondary w-100 mt-2">Buat Akun</a>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
