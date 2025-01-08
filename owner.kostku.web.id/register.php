<?php
include 'db.php'; // Pastikan file ini benar dan terkoneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json'); // Mengatur tipe konten sebagai JSON

    // Ambil data dari form
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $conn->real_escape_string($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username atau email sudah terdaftar
    $sql = "SELECT * FROM owners WHERE username = '$username' OR email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username atau email sudah terdaftar."]);
    } else {
        // Proses file dokumen pendukung
        $targetDir = "uploads/";

        // Menghasilkan nama file unik berdasarkan ID unik dan ekstensi asli
        $supportDocName = basename($_FILES["support-doc"]["name"]);
        $supportDocName = uniqid() . '.' . pathinfo($supportDocName, PATHINFO_EXTENSION);
        $targetFile = $targetDir . $supportDocName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowedTypes = ["jpg", "jpeg", "png", "pdf"];
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Hanya file JPG, JPEG, PNG, atau PDF yang diperbolehkan."]);
            exit();
        }

        if (move_uploaded_file($_FILES["support-doc"]["tmp_name"], $targetFile)) {
            // Simpan data ke database
            $sql = "INSERT INTO owners (name, username, email, phone, password, support_doc, status) 
                    VALUES ('$name', '$username', '$email', '$phone', '$hashedPassword', '$targetFile', 0)";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["success" => true, "message" => "Registrasi pemilik berhasil!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Gagal menyimpan data ke database."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Gagal mengunggah file dokumen pendukung."]);
        }
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Pemilik Rumah</title>
  <!-- Link Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container-fluid"> <!-- Container lebih lebar -->
    <div class="row justify-content-center mt-5">
      <div class="col-md-8 col-lg-6"> <!-- Lebih besar dari sebelumnya -->
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="text-center mb-4">Daftar Pemilik</h1>
            <form id="register-form" enctype="multipart/form-data" method="POST">
              <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama lengkap Anda" required>
              </div>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required>
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon Anda" pattern="[0-9]{10,15}" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Buat kata sandi" required>
              </div>
              <div class="mb-3">
                <label for="confirm-password" class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Konfirmasi kata sandi Anda" required>
              </div>
              <div class="mb-3">
                <label for="support-doc" class="form-label">Dokumen Pendukung</label>
                <input type="file" class="form-control" id="support-doc" name="support-doc" accept=".jpg,.jpeg,.png,.pdf" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
            <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Masuk</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Fungsi untuk menampilkan alert sederhana
    function showAlert(message, isSuccess) {
      if (isSuccess) {
        alert(message); // Menampilkan pesan sukses
        window.location.href = 'login.php'; // Alihkan ke halaman login setelah sukses
      } else {
        alert(message); // Menampilkan pesan gagal
      }
    }

    document.getElementById("register-form").onsubmit = async function(event) {
      event.preventDefault();

      const formData = new FormData(this);

      try {
        const response = await fetch('register.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        // Tampilkan alert setelah form berhasil dikirim
        showAlert(result.message, result.success);
      } catch (error) {
        console.error("Error:", error);
        showAlert("Terjadi kesalahan saat registrasi. Silakan coba lagi.", false);
      }
    };
  </script>
</body>
</html>
