<?php
session_name("owner_session");
session_start();

// Cek jika owner sudah login
if (!isset($_SESSION['owner_username'])) {
    header("Location: login.php"); // Alihkan ke halaman login jika belum login
    exit(); // Pastikan tidak ada kode yang dieksekusi setelah header
}
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
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3" style="width: 250px; height: 100vh;">
            <h4>Dashboard Owner</h4>
            <p class="mb-4">Owner</p>
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white"> <i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="input.php" class="nav-link text-white"> <i class="fas fa-home"></i> Data Kost</a>
                </li>
                <li class="nav-item mt-4">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-cogs"></i> Utils</a>
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
