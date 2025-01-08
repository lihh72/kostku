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
    // Mengambil data owner yang belum diverifikasi
    $unverifiedOwners = $conn->query("SELECT * FROM owners WHERE status = 0")->fetch_all(MYSQLI_ASSOC);
    $verifiedOwners = $conn->query("SELECT * FROM owners WHERE status = 1")->fetch_all(MYSQLI_ASSOC);
    
    // Mengubah path file dokumen menjadi URL yang dapat diakses dari folder ../uploads/
    foreach ($unverifiedOwners as &$owner) {
        $owner['support_doc'] = 'proxy.php?url=https://owner.kostku.web.id/uploads/' . basename($owner['support_doc']);
    }
    foreach ($verifiedOwners as &$owner) {
        $owner['support_doc'] = 'proxy.php?url=https://owner.kostku.web.id/uploads/' . basename($owner['support_doc']);
    }

    echo json_encode(['unverified' => $unverifiedOwners, 'verified' => $verifiedOwners]);
    exit; // Menghentikan eksekusi HTML
} elseif ($action == 'verify') {
    $ownerId = $_GET['id'];
    $conn->query("UPDATE owners SET status = 1 WHERE id = $ownerId");
    exit;
} elseif ($action == 'reject' || $action == 'delete') {
    $ownerId = $_GET['id'];
    $conn->query("DELETE FROM owners WHERE id = $ownerId");
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
                <h2>Data Owner Belum Terverifikasi</h2>
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
                    <tbody id="unverified-owners">
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
        function loadUnverifiedOwners() {
            fetch('verifikasi_owner1.php?action=fetch')  // Arahkan ke file yang benar
                .then(response => response.json())
                .then(data => {
                    const unverifiedTable = document.getElementById('unverified-owners');
                    unverifiedTable.innerHTML = '';  // Bersihkan tabel sebelum menambah data

                    data.unverified.forEach((owner, index) => {
                        const row = unverifiedTable.insertRow();
                        row.innerHTML = `
                            <td>${owner.id}</td>
                            <td>${owner.username}</td>
                            <td>${owner.name}</td>
                            <td>${owner.email}</td>
                            <td><a href="tel:${owner.phone}" class="text-decoration-none"><i class="fas fa-phone"></i> ${owner.phone}</a></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="openDocument('${owner.support_doc}')"><i class="fas fa-eye"></i> Lihat Dokumen</button>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm" onclick="verifyOwner(${owner.id})"><i class="fas fa-check"></i> Verifikasi</button>
                                <button class="btn btn-danger btn-sm" onclick="rejectOwner(${owner.id})"><i class="fas fa-times"></i> Tolak</button>
                            </td>
                        `;
                    });
                })
                .catch(error => console.error('Error fetching unverified owners:', error));
        }

        // Fungsi untuk memverifikasi pengguna
        function verifyOwner(ownerId) {
            fetch(`verifikasi_owner1.php?action=verify&id=${ownerId}`, { method: 'POST' })
                .then(() => loadUnverifiedOwners())
                .catch(error => console.error('Error verifying user:', error));
        }

        // Fungsi untuk menolak pengguna
        function rejectOwner(ownerId) {
            fetch(`verifikasi_owner1.php?action=reject&id=${ownerId}`, { method: 'POST' })
                .then(() => loadUnverifiedOwners())
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
        loadUnverifiedOwners();
    </script>
</body>
</html>
