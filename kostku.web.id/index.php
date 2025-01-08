<?php
// Mulai sesi
session_start();

// Konfigurasi database
$host = 'sql306.infinityfree.com';
$user = 'if0_38001806';
$pass = 'TtOqJWP7sAD';
$db = 'if0_38001806_data_kos';

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mendapatkan kos terbaru (maksimal 3)

$sql = "SELECT * FROM kos WHERE status = 0 ORDER BY id DESC LIMIT 3";

$result = $conn->query($sql);

// Cek apakah pengguna sudah login
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Penyewaan Kos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
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

    <!-- Hero Section -->
    <div class="hero">
        <h1>Temukan Kost Berkualitas</h1>
        <p>Beragam pilihan kost dengan fasilitas terbaik, harga terjangkau, dan lokasi strategis.</p>
        <button>Jelajahi Sekarang</button>
    </div>

    <!-- Section Tawaran Terbaru -->
    <div class="latest-offers">
        <h2>Tawaran Terbaru</h2>
        <p>Jangan lewatkan kos terbaru kami yang memiliki lokasi strategis, harga terjangkau, dan fasilitas lengkap.</p>
    </div>

    <!-- Kos Listings -->
    <div class="listing">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <a href="produk.php?id=<?= $row['id'] ?>" style="text-decoration: none; color: inherit;">
                        <img src="proxy.php?url=https://owner.kostku.web.id/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">

                        <div class="info">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <p><?= htmlspecialchars($row['description']) ?></p>
                            <span class="price">Rp <?= number_format($row['price'], 0, ',', '.') ?>/bulan</span>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada kos yang tersedia saat ini.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 KostKu. All rights reserved. <a href="#">Privacy Policy</a></p>
    </footer>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const profile = document.getElementById("profile");
        const profilePopup = document.getElementById("profilePopup");

        profile.addEventListener("mouseenter", () => {
            profilePopup.classList.add("active");
        });

        profile.addEventListener("mouseleave", () => {
            profilePopup.classList.remove("active");
        });
    });
</script>
</body>
</html>

<?php
// Tutup koneksi
$conn->close();
?>
