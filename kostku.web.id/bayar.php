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

$error_message = ''; // Inisialisasi variabel error_message agar bisa ditangani dengan benar.
$kos_name = '';
$kos_image = '';
$rental_start = null;
$rental_end = null;
$total_price = 0;
$durasi_sewa = 0;
$qris_url = ''; 

if ($rental_id) {
    // Cek apakah pengguna sudah login dan mendapatkan data rental untuk rental_id tersebut
    if ($is_logged_in) {
        $user_id = $_SESSION['id']; // Ambil user_id dari session

        // Query untuk mengambil data penyewaan berdasarkan rental_id dan status belum dibayar (status = 0)
        $sql = "SELECT r.rental_id, r.kos_id, r.rental_start, r.rental_end, r.total_price, r.status, r.payment_proof, k.name AS kos_name, k.image AS kos_image, r.user_id AS rental_user_id
                FROM rentals r
                INNER JOIN kos k ON r.kos_id = k.id
                WHERE r.rental_id = ? AND r.user_id = ? AND r.status = 0"; // Pastikan hanya yang status = 0 yang diambil
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

            // Menghitung durasi sewa dalam bulan
            $interval = $rental_start->diff($rental_end);
            $durasi_sewa = $interval->m + ($interval->y * 12); // Menghitung total bulan

            // Menghitung pajak 12% dan biaya admin
            $pajak = $total_price * 0.12; // Pajak 12%
            $biaya_admin = 50000; // Biaya admin tetap Rp 50.000
            $subtotal = $total_price + $pajak + $biaya_admin; // Subtotal mencakup semuanya
            $harga_asli = $total_price / $durasi_sewa;

            // **Memperbarui kolom payment_amount dengan nilai subtotal**
            $update_payment_sql = "UPDATE rentals SET payment_amount = ? WHERE rental_id = ?";
            $update_payment_stmt = $conn->prepare($update_payment_sql);
            $update_payment_stmt->bind_param("di", $subtotal, $rental_id); // Bind subtotal dan rental_id
            $update_payment_stmt->execute();

            // Cek apakah total_price sudah dihitung sebelumnya
            if ($total_price == 0) { // Jika total_price masih 0, berarti belum dihitung
                // Update total_price dengan nilai subtotal yang sudah dihitung
                $update_sql = "UPDATE rentals SET total_price = ? WHERE rental_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("di", $subtotal, $rental_id); // Bind subtotal dan rental_id
                $update_stmt->execute();
            }


            // QRIS URL statis (misalnya Anda sudah menyiapkan QRIS di sistem pembayaran Anda)
            $qris_url = "qr1_ID1023262341291_26.12.24_173521687_1735216903262.jpeg"; // Ganti dengan URL QRIS yang sesuai

            // Update status di tabel kos menjadi 1 setelah ID terbentuk
            $update_kos_status_sql = "UPDATE kos SET status = 1 WHERE id = ?";
            $update_kos_stmt = $conn->prepare($update_kos_status_sql);
            $update_kos_stmt->bind_param("i", $rental['kos_id']);
            $update_kos_stmt->execute();

            
        } else {
            $error_message = "Data penyewaan tidak ditemukan atau sudah dibayar, atau Anda mencoba mengakses data orang lain.";
        }
    } else {
        $error_message = "Anda harus login terlebih dahulu.";
    }
} else {
    $error_message = "ID penyewaan tidak valid.";
}
// Proses Upload Bukti Pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $upload_dir = 'uploads/';
    // Menghasilkan nama file unik berdasarkan ID unik dan ekstensi asli
    $file_name = basename($_FILES['bukti_pembayaran']['name']);
    $file_name = uniqid() . '.' . pathinfo($file_name, PATHINFO_EXTENSION); // Membuat nama file unik
    $target_file = $upload_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_type, $allowed_types)) {
        // Cek apakah bukti pembayaran sudah ada di database
        $sql_check = "SELECT payment_proof FROM rentals WHERE rental_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $rental_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $existing_payment_proof = $result_check->fetch_assoc()['payment_proof'];
            // Jika bukti pembayaran sudah ada, tampilkan pesan kesalahan
            if (!empty($existing_payment_proof)) {
                $error_message = "Bukti pembayaran sudah diunggah sebelumnya.";
            } else {
                // Proses unggah file
                if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
                    // Update database dengan path file
                    $sql = "UPDATE rentals SET payment_proof = ? WHERE rental_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $target_file, $rental_id);
                    $stmt->execute();
                    $success_message = "Bukti pembayaran berhasil diunggah.";
                } else {
                    $error_message = "Terjadi kesalahan saat mengunggah bukti pembayaran.";
                }
            }
        } else {
            $error_message = "Data penyewaan tidak ditemukan.";
        }
    } else {
        $error_message = "Format file tidak valid. Hanya file JPG, JPEG, PNG, GIF, PDF yang diperbolehkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles3.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Detail Section -->
            <div class="col-md-6">
                <div class="detail-box">
                    <h5 class="section-title">Detail Kost</h5>
                    
                    <!-- Menampilkan Nama Kos -->
                    <p><strong>Nama Kost:</strong> <?= !empty($kos_name) ? htmlspecialchars($kos_name) : 'Kos tidak ditemukan' ?></p>
                    <p><strong>Harga Kost/Bulan:</strong> Rp <?= number_format($harga_asli, 0, ',', '.') ?></p>
                    <p><strong>Durasi:</strong> <?= $durasi_sewa > 0 ? $durasi_sewa . ' bulan' : 'Durasi tidak valid' ?></p>
                    <hr>
                    <p><strong>Biaya Sewa Kost:</strong> 
    <span id="biaya-kost">
        Rp <?= number_format($harga_asli, 0, ',', '.') ?> * <?= $durasi_sewa ?> bulan = Rp <?= number_format($total_price, 0, ',', '.') ?>
    </span>
</p>

                    <!-- Keterangan Pajak dan Biaya Admin -->
                    <p><strong>Pajak (12%):</strong> Rp <?= number_format($pajak, 0, ',', '.') ?></p>
                    <p><strong>Biaya Admin:</strong> Rp <?= number_format($biaya_admin, 0, ',', '.') ?></p>
                    <hr>
                    <p><strong>Subtotal:</strong> <span style="font-size: 1.2rem;" id="subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></span></p>
                    <!-- Tombol Bayar Disini -->
                    <button type="button" class="btn-upload w-100" data-bs-toggle="modal" data-bs-target="#qrModal">Bayar Disini</button>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="qrModalLabel">QRIS Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img src="<?= $qris_url ?>" alt="QRIS Code" class="img-fluid">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="col-md-6">
                <div class="payment-box">
                    <h5 class="section-title">Rincian Pembayaran</h5>
                    <p><strong>Metode Pembayaran:</strong> QRIS</p>
                    <p><strong>Subtotal:</strong> <span id="total-biaya">Rp <?= number_format($subtotal, 0, ',', '.') ?></span></p> 
                    <p><label for="start-date"><strong>Tanggal Mulai Ngekos:</strong> <?= $rental_start ? htmlspecialchars($rental_start->format('d-m-Y')) : 'Tidak ada tanggal' ?></label></p>
                    <p><label for="end-date"><strong>Tanggal Akhir Ngekos:</strong> <?= $rental_end ? htmlspecialchars($rental_end->format('d-m-Y')) : 'Tidak ada tanggal' ?></label></p>

                    <h6>Unggah Bukti Pembayaran</h6>
                    <form action="bayar.php?id=<?= $rental_id ?>" method="POST" enctype="multipart/form-data">
                        <input class="form-control-file" type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png" required>
                        <button type="submit" class="btn-upload w-100">Unggah Bukti Pembayaran</button>
                    </form>

                    <!-- Display error or success message -->
                    <?php if (!empty($error_message)) { echo "<div class='alert alert-danger mt-3'>{$error_message}</div>"; } ?>
                    <?php if (isset($success_message)) { echo "<div class='alert alert-success mt-3'>{$success_message}</div>"; } ?>
                    
                    <!-- Link untuk konfirmasi pembayaran -->
                    <p class="mt-3">Sudah melakukan pembayaran? <a href="konfirmasi.php?id=<?= $rental_id ?>" target="_blank">Klik atau simpan link ini</a> untuk mengetahui apakah pembayaran Anda sudah diverifikasi.</p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 KostKu. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

<!-- DECLARE db_connection AS DatabaseConnection
DECLARE rental_id AS INTEGER
DECLARE user_id AS INTEGER
DECLARE kos_name AS STRING
DECLARE kos_image AS STRING
DECLARE rental_start AS DateTime
DECLARE rental_end AS DateTime
DECLARE total_price AS FLOAT
DECLARE durasi_sewa AS INTEGER
DECLARE pajak AS FLOAT
DECLARE biaya_admin AS FLOAT
DECLARE subtotal AS FLOAT
DECLARE qris_url AS STRING
DECLARE error_message AS STRING
DECLARE success_message AS STRING

// Mulai sesi dan koneksi database
START SESSION
CONNECT TO database

// Cek apakah pengguna sudah login
IF user_is_logged_in THEN
    user_id = get_logged_in_user_id()
    
    // Ambil rental_id dari URL
    rental_id = GET rental_id_from_url

    // Validasi apakah rental_id valid
    IF rental_id EXISTS THEN
        // Ambil data penyewaan berdasarkan rental_id dan user_id
        EXECUTE QUERY "SELECT * FROM rentals r INNER JOIN kos k ON r.kos_id = k.id WHERE r.rental_id = rental_id AND r.user_id = user_id AND r.status = 0"
        IF data_exists THEN
            // Ambil data penyewaan
            kos_name = rental_data['kos_name']
            kos_image = rental_data['kos_image']
            rental_start = rental_data['rental_start']
            rental_end = rental_data['rental_end']
            total_price = rental_data['total_price']
            
            // Hitung durasi sewa (dalam bulan)
            durasi_sewa = calculate_duration_in_months(rental_start, rental_end)
            
            // Hitung pajak 12% dan biaya admin
            pajak = total_price * 0.12
            biaya_admin = 50000
            subtotal = total_price + pajak + biaya_admin
            
            // Update pembayaran dengan subtotal
            EXECUTE QUERY "UPDATE rentals SET payment_amount = subtotal WHERE rental_id = rental_id"
            
            // Tentukan URL QRIS (statis atau berdasarkan sistem pembayaran)
            qris_url = "qr1_...jpeg"
            
            // Update status kos menjadi 1 setelah ID terbentuk
            EXECUTE QUERY "UPDATE kos SET status = 1 WHERE id = rental_data['kos_id']"
        ELSE
            error_message = "Data penyewaan tidak ditemukan atau sudah dibayar."
        END IF
    ELSE
        error_message = "ID penyewaan tidak valid."
    END IF
ELSE
    error_message = "Anda harus login terlebih dahulu."
END IF

// Proses Unggah Bukti Pembayaran
IF POST REQUEST received AND bukti_pembayaran_file_exists THEN
    // Tentukan direktori dan nama file tujuan
    upload_dir = 'uploads/'
    file_name = generate_unique_filename()
    target_file = upload_dir + file_name
    
    // Validasi format file
    IF file_format_valid THEN
        // Cek apakah bukti pembayaran sudah ada di database
        EXECUTE QUERY "SELECT payment_proof FROM rentals WHERE rental_id = rental_id"
        IF payment_proof_exists THEN
            error_message = "Bukti pembayaran sudah diunggah sebelumnya."
        ELSE
            // Proses unggah file
            IF move_uploaded_file(file, target_file) THEN
                // Update database dengan path file
                EXECUTE QUERY "UPDATE rentals SET payment_proof = target_file WHERE rental_id = rental_id"
                success_message = "Bukti pembayaran berhasil diunggah."
            ELSE
                error_message = "Terjadi kesalahan saat mengunggah bukti pembayaran."
            END IF
        END IF
    ELSE
        error_message = "Format file tidak valid. Hanya file JPG, JPEG, PNG, GIF, PDF yang diperbolehkan."
    END IF
END IF

// Tampilkan pesan error atau sukses jika ada
IF error_message EXISTS THEN
    DISPLAY error_message
ELSE IF success_message EXISTS THEN
    DISPLAY success_message
END IF

// Tampilkan QRIS Pembayaran
DISPLAY QRIS modal with image from qris_url

END SESSION
CLOSE DATABASE CONNECTION
-->