<?php
include 'db.php';

// Menangani aksi berdasarkan parameter `action`
$action = $_GET['action'] ?? '';

if ($action == 'fetch') {
    $unverifiedUsers = $conn->query("SELECT * FROM users WHERE status = 0")->fetch_all(MYSQLI_ASSOC);
    $verifiedUsers = $conn->query("SELECT * FROM users WHERE status = 1")->fetch_all(MYSQLI_ASSOC);
    
    // Mengubah path file dokumen menjadi URL yang dapat diakses dari folder ../uploads/
    foreach ($unverifiedUsers as &$user) {
        $user['support_doc'] = '/uploads/' . basename($user['support_doc']);
    }
    foreach ($verifiedUsers as &$user) {
        $user['support_doc'] = '/uploads/' . basename($user['support_doc']);
    }

    echo json_encode(['unverified' => $unverifiedUsers, 'verified' => $verifiedUsers]);
    exit; // Menghentikan eksekusi HTML
} elseif ($action == 'verify') {
    $userId = $_GET['id'];
    $conn->query("UPDATE users SET status = 1 WHERE id = $userId");
    exit;
} elseif ($action == 'reject' || $action == 'delete') {
    $userId = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $userId");
    exit;
}

// Tutup koneksi setelah menangani aksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
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
                    <a href="#" class="nav-link text-white"> <i class="fas fa-home"></i> Data Kamar</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-users"></i> Data Anggota Kost</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-money-bill"></i> Transaksi Pembayaran</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-receipt"></i> Riwayat Pembayaran</a>
                </li>
                <li class="nav-item mt-4">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-cogs"></i> Utils</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white"> <i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="p-4" style="flex: 1;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Data Pengguna Belum Terverifikasi</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telp</th>
                            <th>Dokumen Pendukung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="unverified-users">
                        <!-- Data akan dimuat oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal untuk Menampilkan Dokumen -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Dokumen Pendukung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="documentViewer" class="text-center">
                        <!-- Konten modal (gambar atau PDF) akan dimuat di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <script>
        // Fungsi untuk memuat data pengguna yang belum terverifikasi
        function loadUnverifiedUsers() {
            fetch('verifikasi_pengguna1.php?action=fetch')
                .then(response => response.json())
                .then(data => {
                    const unverifiedTable = document.getElementById('unverified-users');
                    unverifiedTable.innerHTML = '';  // Bersihkan tabel sebelum menambah data

                    data.unverified.forEach((user, index) => {
                        const row = unverifiedTable.insertRow();
                        row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td><a href="tel:${user.phone}" class="text-decoration-none"><i class="fas fa-phone"></i> ${user.phone}</a></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="openDocument('${user.support_doc}')"><i class="fas fa-eye"></i> Lihat Dokumen</button>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm" onclick="verifyUser(${user.id})"><i class="fas fa-check"></i> Verifikasi</button>
                                <button class="btn btn-danger btn-sm" onclick="rejectUser(${user.id})"><i class="fas fa-times"></i> Tolak</button>
                            </td>
                        `;
                    });
                })
                .catch(error => console.error('Error fetching unverified users:', error));
        }

        // Fungsi untuk memverifikasi pengguna
        function verifyUser(userId) {
            fetch(`verifikasi_pengguna1.php?action=verify&id=${userId}`, { method: 'POST' })
                .then(() => loadUnverifiedUsers())
                .catch(error => console.error('Error verifying user:', error));
        }

        // Fungsi untuk menolak pengguna
        function rejectUser(userId) {
            fetch(`verifikasi_pengguna1.php?action=reject&id=${userId}`, { method: 'POST' })
                .then(() => loadUnverifiedUsers())
                .catch(error => console.error('Error rejecting user:', error));
        }

        // Fungsi untuk membuka dokumen
        function openDocument(documentUrl) {
            const modal = new bootstrap.Modal(document.getElementById('documentModal'));
            const documentViewer = document.getElementById('documentViewer');
            
            // Menampilkan dokumen (Gambar atau PDF)
            if (documentUrl.endsWith('.pdf')) {
                documentViewer.innerHTML = `<embed src="${documentUrl}" width="100%" height="400px" />`;
            } else {
                documentViewer.innerHTML = `<img src="${documentUrl}" class="img-fluid" />`;
            }
            modal.show();
        }

        // Panggil fungsi untuk memuat data pengguna yang belum terverifikasi saat halaman dimuat
        loadUnverifiedUsers();
    </script>
</body>
</html>
