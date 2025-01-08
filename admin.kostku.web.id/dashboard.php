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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kost</title>
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

        <!-- Main Content -->
        <div class="p-4" style="flex: 1;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard</h2>
                <button type="button" class="btn btn-danger" onclick="window.location.href='logout.php';">Logout</button>

            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">RP.355.349.000</h5>
                            <p class="card-text">Total Pemasukan Kost Tahun 2024</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">RP.75.650.000</h5>
                            <p class="card-text">Total Pemasukan (Tunai) Kost Tahun 2024</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-secondary">
                        <div class="card-body">
                            <h5 class="card-title">RP.279.699.000</h5>
                            <p class="card-text">Total Pemasukan (Transfer) Kost Tahun 2024</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">47 Orang</h5>
                            <p class="card-text">Total Anggota Kost Aktif</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">37 Kamar</h5>
                            <p class="card-text">Total Kamar Tersedia</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">47 Kamar</h5>
                            <p class="card-text">Total Kamar Dipakai</p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="#" class="text-white">More Info</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
