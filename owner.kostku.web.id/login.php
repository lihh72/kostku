<?php
session_name("owner_session");
session_start();

// Cek jika owner sudah login
if (isset($_SESSION['owner_username'])) {
    header("Location: dashboard.php"); // Alihkan ke dashboard jika sudah login
    exit();  // Pastikan tidak ada kode yang dieksekusi setelah header
}

include 'db.php'; // Pastikan file ini terhubung ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Cek apakah username ada di database
    $sql = "SELECT * FROM owners WHERE username = '$username' AND status = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $owner = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $owner['password'])) {
            // Set session jika login berhasil
            $_SESSION['owner_username'] = $owner['username'];
            $_SESSION['owner_id'] = $owner['id'];
            header("Location: dashboard.php"); // Alihkan ke dashboard
            exit(); // Pastikan tidak ada kode yang dieksekusi setelah header
        } else {
            $error_message = "Password salah.";
        }
    } else {
        $error_message = "Username tidak ditemukan atau akun belum diaktifkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Pemilik Rumah</title>
  <!-- Link Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="text-center mb-4">Login Pemilik</h1>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3">Belum punya akun? <a href="register.php">Daftar</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
