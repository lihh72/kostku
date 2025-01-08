<?php
session_start();
// Konfigurasi database
$host = 'sql306.infinityfree.com';
$user = 'if0_38001806';
$pass = 'TtOqJWP7sAD';
$db = 'if0_38001806_data_kos';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID produk dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query untuk mendapatkan detail kos berdasarkan ID, pastikan status tidak 1 (sudah disewa atau tidak tersedia)
$sql = "SELECT * FROM kos WHERE id = ? AND status != 1"; // Menambahkan filter status != 1
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kos = $result->fetch_assoc();

if (!$kos) {
    echo "<script>alert('Produk tidak ditemukan atau sudah tidak tersedia.'); window.location.href = 'index.php';</script>";
    exit; // Menghentikan eksekusi lebih lanjut jika produk tidak ditemukan
}

// Cek apakah pengguna sudah login
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Proses penyewaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rental_start'], $_POST['duration'])) {
    if ($is_logged_in) {
        $rental_start = $_POST['rental_start'];
        $duration = $_POST['duration'];
        $today = date('Y-m-d');

        if ($rental_start < $today) {
            echo "<script>alert('Tanggal mulai tidak boleh sebelum hari ini.');</script>";
        } elseif ($rental_start && $duration) {
            // Hitung total harga berdasarkan durasi
            $start = new DateTime($rental_start);
            $end = clone $start;
            $interval = new DateInterval('P' . $duration . 'M');
            $end->add($interval);
            $total_price = $duration * $kos['price'];

            // Simpan data penyewaan ke database
            $sql = "INSERT INTO rentals (user_id, kos_id, rental_start, rental_end, total_price) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $rental_end = $end->format('Y-m-d');
            $stmt->bind_param("iissd", $_SESSION['id'], $id, $rental_start, $rental_end, $total_price);

            if ($stmt->execute()) {
                $rental_id = $conn->insert_id; // Ambil ID rental yang baru dibuat
                echo "<script>window.location.href = 'bayar.php?id=" . $rental_id . "';</script>";
                exit;
            } else {
                echo "<script>alert('Terjadi kesalahan. Silakan coba lagi.');</script>";
            }
        }
    } else {
        echo "<script>alert('Anda harus login terlebih dahulu untuk menyewa.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kos['name']) ?> - KostKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles2.css">
</head>
<body>
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
                </a>
            </div>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="section">
        <div class="product">
            <div class="image-section">
           <img class="main-image" src="proxy.php?url=https://owner.kostku.web.id/<?= htmlspecialchars($kos['image']) ?>" alt="<?= htmlspecialchars($kos['name']) ?>">

            </div>
            <div class="details">
                <h1><?= htmlspecialchars($kos['name']) ?></h1>
                <p class="price">Rp <?= number_format($kos['price'], 0, ',', '.') ?>/bulan</p>

                <?php if ($is_logged_in): ?>
                    <div class="rent-button">
                        <button id="openModal">Mulai Penyewaan</button>
                    </div>

                    <!-- Modal -->
                    <div id="rentalModal" class="modal">
                        <div class="modal-content">
                            <span class="close" id="closeModal">&times;</span>
                            <h2>Penyewaan Kos</h2>
                            <form method="post">
                                <label for="rental_start">Tanggal Mulai:</label>
                               <input type="date" name="rental_start" id="rental_start" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">


                                <label for="duration">Durasi Sewa:</label>
                                <select name="duration" id="duration" required>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                    <option value="18">18 Bulan</option>
                                    <option value="24">24 Bulan</option>
                                </select>
                                <button type="submit">Sewa Sekarang</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Silakan <a href="login.php">login</a> untuk menyewa.</p>
                <?php endif; ?>

                <div class="facilities-container">
    <h3>Fasilitas:</h3>
    <div class="facilities">
        <?php
            $facilities = explode(',', $kos['facilities']);
            foreach ($facilities as $index => $facility) {
                echo '<div class="facility-item">' . htmlspecialchars(trim($facility)) . '</div>';
                if (($index + 1) % 2 == 0) {
                    echo '<div class="clear"></div>'; // Setiap 2 fasilitas, beri clear
                }
            }
        ?>
    </div>
</div>

        </div>
    </div>

    <div class="section">
        <h3>Lokasi:</h3>
        <div class="map">
            <iframe src="<?= htmlspecialchars($kos['map_location']) ?>" allowfullscreen=""></iframe>
        </div>

        <h2>Deskripsi Lengkap</h2>
        <p><?= nl2br(htmlspecialchars($kos['full_description'])) ?></p>
    </div>
</div>

<footer>
    &copy; 2024 KostKu. All rights reserved.
</footer>

<script>
// Ambil elemen modal dan tombol
var modal = document.getElementById("rentalModal");
var openModalButton = document.getElementById("openModal");
var closeModalButton = document.getElementById("closeModal");

document.querySelector('form').addEventListener('submit', function(e) {
    const rentalStartInput = document.getElementById('rental_start');
    const selectedDate = new Date(rentalStartInput.value);
    const today = new Date();

    // Atur waktu untuk memastikan hanya membandingkan tanggal
    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        e.preventDefault();
        alert('Tanggal mulai tidak boleh sebelum hari ini.');
    }
});

// Ketika tombol "Mulai Penyewaan" diklik, buka modal
openModalButton.onclick = function() {
    modal.classList.add("show");
    modal.style.display = "block";
    setTimeout(function() {
        modal.style.opacity = "1";
    }, 10); // Penundaan kecil untuk memulai animasi
}

// Ketika tombol close (X) diklik, tutup modal
closeModalButton.onclick = function() {
    modal.classList.remove("show");
    modal.style.opacity = "0";
    setTimeout(function() {
        modal.style.display = "none";
    }, 300); // Penundaan agar animasi selesai
}

// Ketika pengguna mengklik di luar modal, tutup modal
window.onclick = function(event) {
    if (event.target == modal) {
        modal.classList.remove("show");
        modal.style.opacity = "0";
        setTimeout(function() {
            modal.style.display = "none";
        }, 300); // Penundaan agar animasi selesai
    }
}

</script>
</body>
</html>

<?php
$conn->close();
?>
