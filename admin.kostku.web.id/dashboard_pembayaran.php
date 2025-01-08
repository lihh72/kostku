<?php
session_name("admin_session");
session_start();

// Cek jika admin belum login
if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

// Data admin bisa diambil dari session jika diperlukan
$admin_username = $_SESSION['admin_username'];

// Konfigurasi database
$host = 'sql306.infinityfree.com';
$user = 'if0_38001806';
$pass = 'TtOqJWP7sAD';
$db = 'if0_38001806_data_kos';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses Verifikasi Pembayaran dan Hapus Data
if (isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];

    // Ambil ID kos terkait
    $query_kos = "SELECT kos_id FROM rentals WHERE rental_id = ?";
    $stmt_kos = $conn->prepare($query_kos);
    $stmt_kos->bind_param("i", $rental_id);
    $stmt_kos->execute();
    $result_kos = $stmt_kos->get_result();
    $kos_id = $result_kos->fetch_assoc()['kos_id'];
    $stmt_kos->close();

    if (isset($_POST['verify'])) {
        // Verifikasi pembayaran: update status rental dan kos menjadi 1
        $conn->begin_transaction();
        try {
            $sql_rental = "UPDATE rentals SET status = 1 WHERE rental_id = ?";
            $stmt_rental = $conn->prepare($sql_rental);
            $stmt_rental->bind_param("i", $rental_id);
            $stmt_rental->execute();

            $sql_kos = "UPDATE kos SET status = 1 WHERE id = ?";
            $stmt_kos_update = $conn->prepare($sql_kos);
            $stmt_kos_update->bind_param("i", $kos_id);
            $stmt_kos_update->execute();

            $conn->commit();
            echo "<script>alert('Pembayaran berhasil diverifikasi.'); window.location.href = 'dashboard_pembayaran.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Terjadi kesalahan saat memverifikasi.'); window.location.href = 'dashboard_pembayaran.php';</script>";
        }
    } elseif (isset($_POST['delete'])) {
        // Hapus data rental: hapus rental dan ubah status kos menjadi 0
        $conn->begin_transaction();
        try {
            $sql_rental = "DELETE FROM rentals WHERE rental_id = ?";
            $stmt_rental = $conn->prepare($sql_rental);
            $stmt_rental->bind_param("i", $rental_id);
            $stmt_rental->execute();

            $sql_kos = "UPDATE kos SET status = 0 WHERE id = ?";
            $stmt_kos_update = $conn->prepare($sql_kos);
            $stmt_kos_update->bind_param("i", $kos_id);
            $stmt_kos_update->execute();

            $conn->commit();
            echo "<script>alert('Data rental berhasil dihapus.'); window.location.href = 'dashboard_pembayaran.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Terjadi kesalahan saat menghapus data.'); window.location.href = 'dashboard_pembayaran.php';</script>";
        }
    }
}

// Query untuk mengambil pembayaran yang belum diverifikasi (status = 0)
$sql = "SELECT r.rental_id, r.kos_id, r.total_price, r.payment_amount, r.status, r.rental_start, r.rental_end, r.payment_proof, k.name AS kos_name, k.image AS kos_image, u.name AS user_name, u.phone AS user_phone
        FROM rentals r
        INNER JOIN kos k ON r.kos_id = k.id
        INNER JOIN users u ON r.user_id = u.id
        WHERE r.status = 0"; // Status 0 berarti belum diverifikasi
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembayaran - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<body>
<div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3" style="width: 250px; height: 100vh;">
            <h4>Dashboard Admin</h4>
            <p class="mb-4">Admin</p>
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white"> <i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="verifikasi_pengguna1.php" class="nav-link text-white"> <i class="fas fa-user-circle"></i> Pengguna Belum Diverifikasi</a>
                </li>
                <li class="nav-item">
                    <a href="verifikasi_pengguna2.php" class="nav-link text-white"> <i class="fas fa-user-circle"></i> Pengguna Terverifikasi</a>
                </li>
                <li class="nav-item">
                    <a href="verifikasi_owner1.php" class="nav-link text-white"> <i class="fas fa-user-circle"></i> Owner Belum Diverifikasi</a>
                </li>
                <li class="nav-item">
                    <a href="verifikasi_owner2.php" class="nav-link text-white"> <i class="fas fa-user-circle"></i> Owner Terverifikasi</a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_pembayaran.php" class="nav-link text-white"> <i class="fas fa-money-bill"></i> Verifikasi Pembayaran</a>
                </li>
                <li class="nav-item">
                    <a href="verifikasi1_pembayaran.php" class="nav-link text-white"> <i class="fas fa-receipt"></i> Riwayat Pembayaran</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-white"> <i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

    <div class="container mt-4">
        <h2 class="text-center">Dashboard Pembayaran - Admin</h2>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kost</th>
                    <th>Durasi Sewa (Bulan)</th>
                    <th>Total Biaya</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Akhir</th>
                    <th>Gambar Kos</th>
                    <th>Status Pembayaran</th>
                    <th>Bukti Pembayaran</th>
                    <th>Nama Penyewa</th>
                    <th>Nomor HP Penyewa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        $rental_id = $row['rental_id'];
                        $kos_name = $row['kos_name'];
                        $kos_image = $row['kos_image'];
                        $rental_start = new DateTime($row['rental_start']);
                        $rental_end = new DateTime($row['rental_end']);
                        $payment_amount = $row['payment_amount'];
                        $payment_proof = $row['payment_proof'];
                        $status = $row['status'];
                        $user_name = $row['user_name'];
                        $user_phone = $row['user_phone'];

                        // Menghitung durasi sewa dalam bulan
                        $interval = $rental_start->diff($rental_end);
                        $durasi_sewa = $interval->m + ($interval->y * 12); // Menghitung total bulan

                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$kos_name}</td>
                                <td>{$durasi_sewa} bulan</td>
                                <td>Rp " . number_format($payment_amount, 0, ',', '.') . "</td>
                                <td>" . $rental_start->format('d-m-Y') . "</td>
                                <td>" . $rental_end->format('d-m-Y') . "</td>";

                        // Cek jika ada gambar kos
                        if ($kos_image) {
                            echo "<td><img src='proxy.php?url=https://owner.kostku.web.id/{$kos_image}' alt='Kos Image' width='100'></td>";

                            echo "<td><span class='badge bg-warning'>Belum Diverifikasi</span></td>";
                            echo "<td>";
                            // Cek jika ada bukti pembayaran
                            if ($payment_proof) {
                                echo "<a href='proxy.php?url=https://kostku.web.id/{$payment_proof}' target='_blank' class='btn btn-info btn-sm'>Lihat Bukti</a>";
                            } else {
                                echo "<span class='badge bg-danger'>Gambar belum di-upload</span>";
                            }
                            echo "</td>";
                            echo "<td>{$user_name}</td>
                                  <td>{$user_phone}</td>
                                  <td>
                                      <form action='dashboard_pembayaran.php' method='POST'>
                                          <input type='hidden' name='rental_id' value='{$rental_id}'>
                                          <button type='submit' name='verify' class='btn btn-success btn-sm'>Verifikasi Pembayaran</button>
                                      </form>
                                      <form action='dashboard_pembayaran.php' method='POST' class='mt-2'>
                                          <input type='hidden' name='rental_id' value='{$rental_id}'>
                                          <button type='submit' name='delete' class='btn btn-danger btn-sm' onclick='return confirm(\"Anda yakin ingin menghapus data ini?\")'>Hapus</button>
                                      </form>
                                  </td>";
                        } else {
                            echo "<td><span class='badge bg-secondary'>Tidak Ada Gambar</span></td>";
                            echo "<td colspan='5'>Tidak ada gambar yang di-upload. Tidak ada aksi.</td>";
                        }
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='12' class='text-center'>Tidak ada pembayaran yang belum diverifikasi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
