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
    $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
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
        $allowedTypes = ["jpg", "jpeg", "png"];
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Hanya file JPG, JPEG, atau PNG yang diperbolehkan."]);
            exit();
        }

        if (move_uploaded_file($_FILES["support-doc"]["tmp_name"], $targetFile)) {
            // Simpan data ke database
            $sql = "INSERT INTO users (name, username, email, phone, password, support_doc, status) 
                    VALUES ('$name', '$username', '$email', '$phone', '$hashedPassword', '$targetFile', 0)";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["success" => true, "message" => "Registrasi berhasil!"]);
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
  <title>Daftar - KostKu</title>
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #FFFDF9;
      color: #0A0A0A;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow: auto;
    }

    .container {
      width: 100%;
      max-width: 800px; /* Memanfaatkan horizontal */
      padding: 20px;
    }

    .register-box {
      background-color: #FFFFFF;
      border: 4px solid #0A0A0A;
      border-radius: 16px;
      box-shadow: 10px 10px 0px 0px #0A0A0A;
      padding: 25px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .title {
      font-size: 28px;
      font-weight: bold;
      text-align: center;
      color: #005F73;
    }

    .subtitle {
      font-size: 14px;
      text-align: center;
      color: #333333;
    }

    form {
      display: grid;
      grid-template-columns: repeat(2, 1fr); /* Dua kolom */
      gap: 20px;
      align-items: start;
    }

    label {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 5px;
      display: block;
    }

    input {
      font-size: 14px;
      padding: 10px;
      border: 3px solid #0A0A0A;
      border-radius: 10px;
      background-color: #F5F5F5;
      width: 100%;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    input:focus {
      outline: none;
      border-color: #005F73;
      background-color: #FFFFFF;
      transform: scale(1.02);
    }

    .full-row {
      grid-column: span 2; /* Memanfaatkan penuh untuk elemen besar */
    }

    .input-group {
      position: relative;
    }

    .input-group button {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      background-color: transparent;
      border: none;
      font-size: 14px;
      cursor: pointer;
      color: #005F73;
      font-weight: bold;
      line-height: 1;
    }

    .input-group button:hover {
      transform: translateY(-50%) scale(1.1);
    }

    .register-btn {
      font-size: 16px;
      font-weight: bold;
      color: #FFFFFF;
      background-color: #EE9B00;
      padding: 10px;
      border: 3px solid #0A0A0A;
      border-radius: 12px;
      cursor: pointer;
      grid-column: span 2; /* Tombol penuh */
      text-align: center;
    }

    .register-btn:hover {
      transform: translateY(-3px);
      background-color: #FF7A00;
    }

    .footer-text {
      font-size: 12px;
      text-align: center;
      grid-column: span 2;
    }

    .footer-text a {
      color: #005F73;
      text-decoration: none;
      font-weight: bold;
      position: relative;
    }

    .footer-text a::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 2px;
      background-color: #005F73;
      left: 0;
      bottom: -2px;
      transform: scaleX(0);
      transform-origin: left;
    }

    .footer-text a:hover::after {
      transform: scaleX(1);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="register-box">
      <h1 class="title">Daftar</h1>
      <p class="subtitle">Bergabunglah dengan kami dan temukan Kost berkualitas!</p>
      <form id="register-form" enctype="multipart/form-data" method="POST">
        <div>
          <label for="name">Nama Lengkap</label>
          <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap Anda" required>
        </div>
        
        <div>
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>
        
        <div>
          <label for="email">Alamat Email</label>
          <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
        </div>
        
        <div>
          <label for="phone">Nomor Telepon</label>
          <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon Anda" pattern="[0-9]{10,15}" required>
        </div>

        <div>
          <label for="password">Kata Sandi</label>
          <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Buat kata sandi" required>
            <button type="button" onclick="togglePassword('password')">👁</button>
          </div>
        </div>
        
        <div>
          <label for="confirm-password">Konfirmasi Kata Sandi</label>
          <div class="input-group">
            <input type="password" id="confirm-password" name="confirm-password" placeholder="Konfirmasi kata sandi Anda" required>
            <button type="button" onclick="togglePassword('confirm-password')">👁</button>
          </div>
        </div>
        
        <div class="full-row">
          <label for="support-doc">Dokumen Pendukung</label>
          <input type="file" id="support-doc" name="support-doc" accept=".jpg,.jpeg,.png" required>
        </div>

        <button type="submit" class="register-btn">Daftar</button>
      </form>
      <p class="footer-text">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </div>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }

    function validatePassword() {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm-password').value;

      // Aturan validasi kata sandi
      const passwordRules = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

      if (!passwordRules.test(password)) {
        alert('Kata sandi harus memiliki minimal 1 huruf kapital, 1 angka, dan 8 karakter.');
        return false;
      }

      if (password !== confirmPassword) {
        alert('Kata sandi dan konfirmasi kata sandi tidak cocok.');
        return false;
      }

      return true;
    }

    document.getElementById("register-form").onsubmit = async function(event) {
      event.preventDefault();

      if (!validatePassword()) {
        return;
      }

      const formData = new FormData(this);
      const submitButton = this.querySelector('.register-btn');
      submitButton.disabled = true; // Nonaktifkan tombol sementara

      try {
        const response = await fetch('register.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        // Tampilkan popup menggunakan alert() untuk pesan respon
        if (result.success) {
          alert(result.message);
          window.location.href = 'login.php'; // Alihkan ke halaman login setelah registrasi sukses
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error("Error:", error);
        alert("Terjadi kesalahan saat registrasi. Silakan coba lagi.");
      } finally {
        submitButton.disabled = false; // Aktifkan tombol kembali
      }
    };

  </script>
</body>
</html>
