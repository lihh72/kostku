<?php
session_start();
include 'db.php';

// Fetch user data from session
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');

    // Get data from the form
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $support_doc = $user['support_doc'];

    // Jika password diubah, hash kembali
    if (!empty($password)) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  } else {
      $hashed_password = $user['password']; // Pertahankan password lama jika tidak diubah
  }

    // Update data in database
    $sql = "UPDATE users SET name='$name', username='$username', email='$email', phone='$phone', password='$hashed_password', support_doc='$support_doc' WHERE id=$user_id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Laman Profil Berhasil di Perbarui!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal Memperbarui Laman Profil."]);
    }
    exit();
}


$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil - Rental Rumah</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
  <style>
    form#profile-form {
    display: none; /* Hide form initially */
    flex-direction: column;
    gap: 15px;
}


  </style>
</head>
<body>
  

  <div class="container">
    <!-- Header -->
    <header>
    <div class="logo">
        <a href="index.php" style="text-decoration: none; color: inherit;">KostKu</a>
    </div>
    <div class="search-bar">
        <form action="search.php" method="get">
            <input type="text" name="q" placeholder="Cari kos...">
            <button type="submit">Cari</button>
        </form>

        </div>
        <div class="actions">
        <?php if ($is_logged_in): ?>
            <div class="profile">
                <a href="profile.php" class="profile-link">
                <span class="profile-icon">👤</span>
                <div class="profile-popup">
                    <p><strong>Halo, <?= htmlspecialchars($username) ?></strong></p>
                    <p>Email: <?= htmlspecialchars($_SESSION['email'] ?? 'Tidak tersedia') ?></p>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>
    <div class="profile-box">
      <h1 class="title">Profil</h1>
      <p class="subtitle">Laman Profil</p>
      
      <div class="profile-info" id="profile-info">
        <div><span>Nama Lengkap:</span> <span><?php echo $user['name']; ?></span></div>
        <div><span>Username:</span> <span><?php echo $user['username']; ?></span></div>
        <div><span>Alamat Email:</span> <span><?php echo $user['email']; ?></span></div>
        <div><span>Nomor Telepon:</span> <span><?php echo $user['phone']; ?></span></div>
        <div><span>Kata Sandi:</span> <span><?php echo str_repeat('*', 8); ?></span></div>
      </div>
      
      <form id="profile-form" enctype="multipart/form-data" method="POST">
        <div>
          <label for="name">Nama Lengkap</label>
          <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" placeholder="Masukkan nama lengkap" required>
        </div>
        
        <div>
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" placeholder="Masukkan username" required>
        </div>
        
        <div>
          <label for="email">Alamat Email</label>
          <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" placeholder="Masukkan alamat email" required>
        </div>

        <div>
          <label for="phone">Nomor Telepon</label>
          <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" placeholder="Masukkan nomor telpon" required>
        </div>

        <div>
          <label for="password">Kata Sandi (Kosongkan jika tidak ingin mengubah)</label>
          <input type="password" id="password" name="password" placeholder="Masukkan kata sandi baru">
        </div>

        <button type="submit" class="profile-btn">Update Profile</button>
      </form>

      <button class="edit-btn" id="edit-btn">Edit Profile</button>
      </div>
    </div>
  </div>

  <script>
    // Toggle between viewing and editing profile
    const editBtn = document.getElementById('edit-btn');
    const profileForm = document.getElementById('profile-form');
    const profileInfo = document.getElementById('profile-info');

    editBtn.addEventListener('click', () => {
      profileForm.style.display = profileForm.style.display === 'flex' ? 'none' : 'flex';
      profileInfo.style.display = profileForm.style.display === 'flex' ? 'none' : 'flex';
      editBtn.textContent = profileForm.style.display === 'flex' ? 'Cancel Edit' : 'Edit Profile';
    });

    // Handle form submission
    profileForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(profileForm);

      fetch('', {
        method: 'POST',
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          location.reload();  // Reload the page to show updated profile
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        alert('An error occurred while updating the profile.');
      });
    });
  </script>
</body>
</html>
