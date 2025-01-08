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

// Cek apakah pengguna sudah login
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Ambil rental_id dari URL menggunakan parameter 'id'
$rental_id = isset($_GET['id']) ? $_GET['id'] : null;
$error_message = ''; // Inisialisasi variabel error_message

// Variabel rincian pembayaran
$kos_name = '';
$kos_image = '';
$rental_start = null;
$rental_end = null;
$total_price = 0;
$durasi_sewa = 0;
$status_verifikasi = '';

// Pastikan rental_id valid
if ($rental_id) {
    // Cek apakah pengguna sudah login dan mendapatkan data rental untuk rental_id tersebut
    if ($is_logged_in) {
        $user_id = $_SESSION['id']; // Ambil user_id dari session

        // Query untuk mengambil data penyewaan berdasarkan rental_id dan status
        $sql = "SELECT r.rental_id, r.kos_id, r.rental_start, r.rental_end, r.total_price, r.payment_amount, r.status, r.payment_proof, k.name AS kos_name, k.image AS kos_image
                FROM rentals r
                INNER JOIN kos k ON r.kos_id = k.id
                WHERE r.rental_id = ? AND r.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $rental_id, $user_id); // Bind rental_id dan user_id
        $stmt->execute();
        $result = $stmt->get_result();

        // Periksa apakah query berhasil dan ada hasil
        if ($result->num_rows > 0) {
            $rental = $result->fetch_assoc(); // Ambil data penyewaan pertama
            $kos_name = $rental['kos_name'];
            $kos_image = $rental['kos_image'];
            $rental_start = new DateTime($rental['rental_start']);
            $rental_end = new DateTime($rental['rental_end']);
            $total_price = $rental['total_price'];
            $payment_amount = $rental['payment_amount'];
            $status_verifikasi = $rental['status'] == 0 ? 'memverifikasi' : 'success';

            // Menghitung durasi sewa dalam bulan
            $interval = $rental_start->diff($rental_end);
            $durasi_sewa = $interval->m + ($interval->y * 12); // Menghitung total bulan
        } else {
            $error_message = "Data penyewaan tidak ditemukan atau Anda mencoba mengakses data orang lain.";
        }
    } else {
        $error_message = "Anda harus login terlebih dahulu.";
    }
} else {
    $error_message = "ID penyewaan tidak valid.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    overflow-x: hidden;
    padding: 20px;
}

        .container {
            margin-top: 50px;
        }
        .verification-container, .success-container {
            
            width: 100%;
            max-width: 600px;
            padding: 20px;
            margin-left: auto;
            margin-right: auto;
            background: #FFFFFF;
            border-radius: 16px;
            border: 4px solid #0A0A0A;
            box-shadow: 10px 10px 0px 0px #0A0A0A;

        }
        .verification-header, .success-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .verification-header h1, .success-header h1 {
            font-size: 2rem;
        }
        .verification-header h1 {
            color: #FF9800;
        }
        .success-header h1 {
            color: #4CAF50;
        }
        .verification-header p, .success-header p {
            color: #666;
        }
        .btn-view {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($status_verifikasi == 'memverifikasi'): ?>
            <!-- Tampilan Menunggu Verifikasi -->
            <div class="verification-container" id="waiting-verification">
                <div class="verification-header">
                    <h1>⏳ Menunggu Verifikasi</h1>
                    <p>Bukti pembayaran Anda sedang diperiksa oleh admin. Mohon tunggu.</p>
                </div>
                <div class="mb-3">
                    <strong>Rincian Pembayaran:</strong>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Nama Kost:</strong> <?= htmlspecialchars($kos_name) ?></li>
                        <li class="list-group-item"><strong>Durasi:</strong> <?= $durasi_sewa ?> bulan</li>
                        <li class="list-group-item"><strong>Tanggal Mulai Sewa:</strong> <?= $rental_start->format('d-m-Y') ?></li>
                        <li class="list-group-item"><strong>Tanggal Selesai Sewa:</strong> <?= $rental_end->format('d-m-Y') ?></li>
                        <li class="list-group-item"><strong>Biaya Sewa:</strong> Rp <?= number_format($total_price, 0, ',', '.') ?></li>
                        <li class="list-group-item"><strong>Total Biaya (Sewa + Pajak + Admin):</strong> Rp <?= number_format($payment_amount, 0, ',', '.') ?></li>
                        
                    </ul>
                </div>
            </div>
        <?php elseif ($status_verifikasi == 'success'): ?>
            <!-- Tampilan Setelah Verifikasi Berhasil -->
            <div class="success-container" id="success-verification">
                <div class="success-header">
                    <h1>✔️ Pembayaran Berhasil!</h1>
                    <p>Terima kasih telah melakukan pembayaran.</p>
                </div>
                <div class="mb-3">
                    <strong>Rincian Pembayaran:</strong>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Nama Kost:</strong> <?= htmlspecialchars($kos_name) ?></li>
                        <li class="list-group-item"><strong>Durasi:</strong> <?= $durasi_sewa ?> bulan</li>
                        <li class="list-group-item"><strong>Tanggal Mulai Sewa:</strong> <?= $rental_start->format('d-m-Y') ?></li>
                        <li class="list-group-item"><strong>Tanggal Selesai Sewa:</strong> <?= $rental_end->format('d-m-Y') ?></li>
                        <li class="list-group-item"><strong>Biaya Sewa:</strong> Rp <?= number_format($total_price, 0, ',', '.') ?></li>
                        <li class="list-group-item"><strong>Total Biaya (Sewa + Pajak + Admin):</strong> Rp <?= number_format($payment_amount, 0, ',', '.') ?></li>
                        
                    </ul>
                </div>
                <div class="text-center btn-view">
                    <a href="https://wa.me/6289637001713" class="btn btn-success">Kontak Owner</a>
                    <a href="index.php" class="btn btn-primary">Kembali ke Index</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
