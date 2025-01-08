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

// Tangkap query pencarian dan filter
$query = isset($_GET['q']) ? $_GET['q'] : '';
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (int)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (int)$_GET['price_max'] : null;
$filter_facilities = isset($_GET['facilities']) ? $_GET['facilities'] : [];

// Cek apakah pengguna sudah login
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Menyiapkan parameter pencarian dasar
$sql = "SELECT * FROM kos WHERE (name LIKE ? OR description LIKE ?) AND status != 1";

$params = ["ss"];
$values = ["%$query%", "%$query%"];

// Menambahkan filter harga jika ada input
if ($price_min !== null) {
    $sql .= " AND price >= ?";
    $params[0] .= "i";
    $values[] = $price_min;
}
if ($price_max !== null) {
    $sql .= " AND price <= ?";
    $params[0] .= "i";
    $values[] = $price_max;
}

// Menambahkan filter fasilitas jika ada input
if (!empty($filter_facilities)) {
    foreach ($filter_facilities as $facility) {
        $sql .= " AND facilities LIKE ?";
        $params[0] .= "s";
        $values[] = "%$facility%";
    }
}

// Menjalankan query
$stmt = $conn->prepare($sql);
$stmt->bind_param(...array_merge([$params[0]], $values));
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - KostKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
/* Filter Section */
.filter-container {
    display: flex;
    justify-content: left;
    margin: 20px 0;
}

.filter-container button {
    font-size: 16px;
    font-weight: bold;
    color: #FFFFFF;
    background-color: #EE9B00;
    padding: 10px 20px;
    border: 3px solid #0A0A0A;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s ease, background-color 0.3s ease;
}

.filter-container button:hover {
    transform: translateY(-3px);
    background-color: #FF7A00;
}

/* Modal Styles */
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    padding-top: 40px;
    animation: fadeIn 0.3s ease-in-out;
}

.modal-content {
    background-color: #fff;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 20px;
    border-radius: 12px;
    border: 3px solid #0A0A0A;
    box-shadow: 6px 6px 0px 0px #0A0A0A;
    width: 85%;
    max-width: 450px;
    max-height: 75vh;
    overflow-y: auto;
    transition: transform 0.3s ease-in-out;
}

/* Modal Close Button */
.modal-close {
    color: #333;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: #FF7A00;
}

/* Modal Header */
.modal h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 16px;
}

/* Form Inputs */
.modal form input[type="number"], 
.modal form input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 16px;
    border-radius: 6px;
    border: 2px solid #EEE;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.modal form input[type="number"]:focus, 
.modal form input[type="text"]:focus {
    border-color: #FF7A00;
    outline: none;
}

/* Checkbox */
.modal form label {
    display: block;
    margin: 8px 0;
    font-size: 14px;
}

.modal form input[type="checkbox"] {
    margin-right: 6px;
}

/* Submit Button */
.modal form button {
    width: 100%;
    padding: 10px;
   
    background-color: #EE9B00;
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal form button:hover {
    background-color: #FF7A00;
}

/* Animation for Modal Fade In */
@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

/* Responsif untuk layar kecil */
@media (max-width: 768px) {
    .modal-content {
        width: 90%;
    }
}

.facilities-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2px;
    margin: 4px 0;
}

.facility-item {
    font-size: 14px;
    line-height: 1.2;
}

</style>

</head>
<body>
    <div class="container">
    <header>
    <div class="logo">
        <a href="index.php" style="text-decoration: none; color: inherit;">KostKu</a>
    </div>
            <div class="search-bar">
                <form action="search.php" method="get">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Cari kos..." 
                        value="<?= htmlspecialchars($query) ?>" 
                        required>
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

<!-- Filter Section -->
<div class="filter-container">
    <button id="filter-btn">Filter</button>
</div>

<!-- Modal Filter -->
<div id="filter-modal" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3>Filter</h3>
        <form action="search.php" method="get">
            <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
            <div>
                <label for="price_min">Harga Minimum:</label>
                <input type="number" id="price_min" name="price_min" placeholder="Contoh: 1000000" value="<?= htmlspecialchars($price_min ?? '') ?>">
            </div>
            <div>
                <label for="price_max">Harga Maksimum:</label>
                <input type="number" id="price_max" name="price_max" placeholder="Contoh: 5000000" value="<?= htmlspecialchars($price_max ?? '') ?>">
            </div>
            <div class="facilities-container">
    <div class="facility-item">
        <label><input type="checkbox" name="facilities[]" value="AC" <?= in_array('AC', $filter_facilities) ? 'checked' : '' ?>> AC</label>
    </div>
    <div class="facility-item">
        <label><input type="checkbox" name="facilities[]" value="Kamar Mandi Dalam" <?= in_array('Kamar Mandi Dalam', $filter_facilities) ? 'checked' : '' ?>> Kamar Mandi Dalam</label>
    </div>
    <div class="facility-item">
        <label><input type="checkbox" name="facilities[]" value="Wi-Fi" <?= in_array('Wi-Fi', $filter_facilities) ? 'checked' : '' ?>> Wi-Fi</label>
    </div>
    <div class="facility-item">
        <label><input type="checkbox" name="facilities[]" value="Laundry" <?= in_array('Laundry', $filter_facilities) ? 'checked' : '' ?>> Laundry</label>
    </div>
    <div class="facility-item">
        <label><input type="checkbox" name="facilities[]" value="Dapur Umum" <?= in_array('Dapur Umum', $filter_facilities) ? 'checked' : '' ?>> Dapur Umum</label>
    </div>
</div>

            <button type="submit">Terapkan Filter</button>
        </form>
    </div>
</div>



        <!-- Search Results -->
        <div class="search-results">
            <h2>Hasil Pencarian</h2>
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
                    <p>Tidak ada hasil untuk "<strong><?= htmlspecialchars($query) ?></strong>".</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <p>&copy; 2024 KostKu. All rights reserved. <a href="#">Privacy Policy</a></p>
        </footer>
    </div>
</body>
</html>

    <script>
        const modal = document.getElementById('filter-modal');
        const filterBtn = document.getElementById('filter-btn');
        const closeModal = document.querySelector('.modal-close');

        filterBtn.onclick = () => modal.style.display = 'block';
        closeModal.onclick = () => modal.style.display = 'none';
        window.onclick = (event) => {
            if (event.target == modal) modal.style.display = 'none';
        };
    </script>
</body>
</html>
<?php
// Tutup koneksi
$conn->close();
?>
