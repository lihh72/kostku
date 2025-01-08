<?php
session_name("owner_session");
session_start();

// Cek jika owner sudah login
if (!isset($_SESSION['owner_username'])) {
    header("Location: login.php"); // Alihkan ke halaman login jika belum login
    exit(); // Pastikan tidak ada kode yang dieksekusi setelah header
}


require 'db.php';

// Fungsi untuk menangani upload gambar
function uploadImage($file) {
    $targetDir = "uploads/";

    // Menghasilkan nama file unik berdasarkan ID unik dan ekstensi asli
    $fileName = uniqid() . '.' . pathinfo($file["name"], PATHINFO_EXTENSION);
    $targetFile = $targetDir . $fileName;

    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validasi file
    if (getimagesize($file["tmp_name"]) === false || $file["size"] > 5000000 || 
        !in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        return false;
    }

    // Pindahkan file yang diupload ke folder tujuan
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    }
    return false;
}


$result = $conn->query("SELECT * FROM kos");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $facilities = isset($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';
        $map_location = $_POST['map_location'];
        $full_description = $_POST['full_description'];
        $imagePath = uploadImage($_FILES['image']);

        if ($imagePath) {
            $sql = "INSERT INTO kos (name, description, price, image, facilities, map_location, full_description) 
                    VALUES ('$name', '$description', '$price', '$imagePath', '$facilities', '$map_location', '$full_description')";
            $conn->query($sql);
        }

        // Redirect to refresh page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $facilities = isset($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';
        $map_location = $_POST['map_location'];
        $full_description = $_POST['full_description'];
        $imagePath = $_POST['current_image'];

        if ($_FILES['image']['name'] != "") {
            $uploadedPath = uploadImage($_FILES['image']);
            if ($uploadedPath) $imagePath = $uploadedPath;
        }

        $sql = "UPDATE kos SET name = '$name', description = '$description', price = '$price', 
                image = '$imagePath', facilities = '$facilities', map_location = '$map_location', 
                full_description = '$full_description' WHERE id = $id";
        $conn->query($sql);

        // Redirect to refresh page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM rentals WHERE kos_id = $id");
    $conn->query("DELETE FROM kos WHERE id = $id");

    // Redirect to refresh page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kos</title>
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
                <h2>Daftar Kos</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKosModal">+ Tambah Kos Baru</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Harga/Bulan</th>
                            <th>Gambar</th>
                            <th>Fasilitas</th>
                            <th>Lokasi Peta</th>
                            <th>Deskripsi Lengkap</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                                <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="Gambar Kos" width="100"></td>
                                <td><?= htmlspecialchars($row['facilities']) ?></td>
                                <td><a href="<?= htmlspecialchars($row['map_location']) ?>" target="_blank">Lihat Lokasi</a></td>
                                <td><?= htmlspecialchars($row['full_description']) ?></td>
                                <td>
                                <button class="btn btn-primary btn-sm" onclick="editKos(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kos -->
    <div class="modal fade" id="addKosModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kos Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Nama:</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi:</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Harga:</label>
                            <input type="number" name="price" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Fasilitas:</label>
                            <div>
                                <label><input type="checkbox" name="facilities[]" value="AC"> AC</label>
                                <label><input type="checkbox" name="facilities[]" value="Wi-Fi"> Wi-Fi</label>
                                <label><input type="checkbox" name="facilities[]" value="Dapur Umum"> Dapur Umum</label>
                                <label><input type="checkbox" name="facilities[]" value="Laundry"> Laundry</label>
                                <label><input type="checkbox" name="facilities[]" value="Kamar Mandi Dalam"> Kamar Mandi Dalam</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Lokasi Peta:</label>
                            <input type="text" name="map_location" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi Lengkap:</label>
                            <textarea name="full_description" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Gambar:</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kos -->
    <div class="modal fade" id="editKosModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Kos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        <div class="mb-3">
                            <label>Nama:</label>
                            <input type="text" name="name" id="editName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi:</label>
                            <input type="text" name="description" id="editDescription" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Harga:</label>
                            <input type="number" name="price" id="editPrice" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Fasilitas:</label>
                            <div>
                                <label><input type="checkbox" name="facilities[]" value="AC" id="editFacilityAC"> AC</label>
                                <label><input type="checkbox" name="facilities[]" value="Wi-Fi" id="editFacilityWiFi"> Wi-Fi</label>
                                <label><input type="checkbox" name="facilities[]" value="Dapur Umum" id="editFacilityDapurUmum"> Dapur Umum</label>
                                <label><input type="checkbox" name="facilities[]" value="Laundry" id="editFacilityLaundry"> Laundry</label>
                                <label><input type="checkbox" name="facilities[]" value="Kamar Mandi Dalam" id="editKamarMandiDalam"> Kamar Mandi Dalam</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Lokasi Peta:</label>
                            <input type="text" name="map_location" id="editMapLocation" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi Lengkap:</label>
                            <textarea name="full_description" id="editFullDescription" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Gambar (Upload baru jika ingin diubah):</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editKos(data) {
            document.getElementById('editId').value = data.id;
            document.getElementById('editName').value = data.name;
            document.getElementById('editDescription').value = data.description;
            document.getElementById('editPrice').value = data.price;
            document.getElementById('editMapLocation').value = data.map_location;
            document.getElementById('editFullDescription').value = data.full_description;
            document.getElementById('editCurrentImage').value = data.image;

            document.getElementById('editFacilityAC').checked = data.facilities.includes("AC");
            document.getElementById('editFacilityWiFi').checked = data.facilities.includes("Wi-Fi");
            document.getElementById('editFacilityDapurUmum').checked = data.facilities.includes("Dapur Umum");
            document.getElementById('editFacilityLaundry').checked = data.facilities.includes("Laundry");
            document.getElementById('editKamarMandiDalam').checked = data.facilities.includes("Kamar Mandi Dalam");

            const modal = new bootstrap.Modal(document.getElementById('editKosModal'));
            modal.show();
        }
    </script>
</body>
</html>

<!-- DECLARE db_connection AS DatabaseConnection
DECLARE query_result AS Result
DECLARE user_action AS STRING
DECLARE input_data AS Object

IF db_connection IS NOT NULL THEN
    OUTPUT "Koneksi ke database berhasil"
    
    INPUT user_action // Aksi pengguna: 'add', 'edit', 'delete', 'view'

    IF user_action = 'add' THEN
        INPUT input_data.name, input_data.description, input_data.price, input_data.facilities, input_data.map_location, input_data.full_description, input_data.image
        EXECUTE QUERY 'INSERT INTO kos (name, description, price, facilities, map_location, full_description, image) VALUES (input_data.name, input_data.description, input_data.price, input_data.facilities, input_data.map_location, input_data.full_description, input_data.image)'
        OUTPUT "Data berhasil ditambahkan"
    
    ELSE IF user_action = 'edit' THEN
        INPUT input_data.id, input_data.name, input_data.description, input_data.price, input_data.facilities, input_data.map_location, input_data.full_description, input_data.image
        EXECUTE QUERY 'UPDATE kos SET name = input_data.name, description = input_data.description, price = input_data.price, facilities = input_data.facilities, map_location = input_data.map_location, full_description = input_data.full_description, image = input_data.image WHERE id = input_data.id'
        OUTPUT "Data berhasil diperbarui"
    
    ELSE IF user_action = 'delete' THEN
        INPUT input_data.id
        EXECUTE QUERY 'DELETE FROM kos WHERE id = input_data.id'
        EXECUTE QUERY 'DELETE FROM rentals WHERE kos_id = input_data.id'
        OUTPUT "Data berhasil dihapus"
    
    ELSE IF user_action = 'view' THEN
        EXECUTE QUERY 'SELECT * FROM kos'
        FOR EACH row IN query_result DO
            OUTPUT row // Menampilkan data kos
        END FOR
    
    ELSE
        OUTPUT "Aksi tidak valid"
    ENDIF

ELSE
    OUTPUT "Gagal koneksi ke database"
ENDIF
-->