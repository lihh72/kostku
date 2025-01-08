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

include 'db.php';

// Menangani aksi berdasarkan parameter `action`
$action = $_GET['action'] ?? '';

if ($action == 'fetch') {
    $unverifiedUsers = $conn->query("SELECT * FROM users WHERE status = 0")->fetch_all(MYSQLI_ASSOC);
    $verifiedUsers = $conn->query("SELECT * FROM users WHERE status = 1")->fetch_all(MYSQLI_ASSOC);
    
    // Menambahkan path folder target ke dokumen
    foreach ($unverifiedUsers as &$user) {
        $user['support_doc'] = 'proxy.php?url=https://kostku.web.id/uploads/' . basename($user['support_doc']);
    }
    foreach ($verifiedUsers as &$user) {
        $user['support_doc'] = 'proxy.php?url=https://kostku.web.id/uploads/' . basename($user['support_doc']);
    }

    echo json_encode(['unverified' => $unverifiedUsers, 'verified' => $verifiedUsers]);
    exit; // Menghentikan eksekusi HTML
} elseif ($action == 'verify') {
    $userId = $_GET['id'];
    $conn->query("UPDATE users SET status = 1 WHERE id = $userId");
    exit;
} elseif ($action == 'reject') {
    $userId = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $userId");
    exit;
} elseif ($action == 'delete') {
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
                <h2>Data Pengguna Terverifikasi</h2>
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
                    <tbody id="verified-users">
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
        // Fungsi untuk memuat data pengguna yang sudah terverifikasi
        function loadVerifiedUsers() {
            fetch('verifikasi_pengguna2.php?action=fetch')
                .then(response => response.json())
                .then(data => {
                    const verifiedTable = document.getElementById('verified-users');
                    verifiedTable.innerHTML = '';  // Bersihkan tabel sebelum menambah data

                    data.verified.forEach((user, index) => {
                        const row = verifiedTable.insertRow();
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
                                <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})"><i class="fas fa-trash"></i> Hapus</button>
                            </td>
                        `;
                    });
                })
                .catch(error => console.error('Error fetching verified users:', error));
        }

        // Fungsi untuk menghapus pengguna
        function deleteUser(userId) {
            fetch(`verifikasi_pengguna2.php?action=delete&id=${userId}`, { method: 'POST' })
                .then(() => loadVerifiedUsers())
                .catch(error => console.error('Error deleting user:', error));
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

        // Panggil fungsi untuk memuat data pengguna yang sudah terverifikasi saat halaman dimuat
        loadVerifiedUsers();
    </script>
</body>
</html>
