<?php
if (isset($_GET['url'])) {
    $imageUrl = $_GET['url'];
    $decodedUrl = urldecode($imageUrl);

    if (filter_var($decodedUrl, FILTER_VALIDATE_URL) === false) {
        echo 'URL tidak valid!';
        exit;
    }

    $parsedUrl = parse_url($decodedUrl);
    $allowedDomains = ['owner.kostku.web.id', 'kostku.web.id']; // Daftar domain yang diizinkan
    if (!in_array($parsedUrl['host'], $allowedDomains)) {
        echo 'Akses ditolak. Hanya URL dari owner.kostku.web.id dan kostku.web.id yang diperbolehkan.';
        exit;
    }

$fileInfo = pathinfo($decodedUrl);
    $extension = strtolower($fileInfo['extension'] ?? ''); // Ambil ekstensi file, default kosong jika tidak ada ekstensi

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Daftar ekstensi yang diizinkan
    if (empty($extension) || !in_array($extension, $allowedExtensions)) {
        echo 'Ekstensi file tidak valid atau tidak diberikan.';
        exit;
    }
    
    $cacheDir = 'cache/';
    $fileInfo = pathinfo($decodedUrl);
    $extension = strtolower($fileInfo['extension'] ?? 'jpg'); // Default ke jpg jika tidak ada ekstensi
    $cacheFile = $cacheDir . md5($decodedUrl) . '.' . $extension;

    if (file_exists($cacheFile)) {
        header('Content-Type: ' . mime_content_type($cacheFile));
        header('Cache-Control: public, max-age=31536000');
        readfile($cacheFile);
        exit;
    }

    $ch = curl_init($decodedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $imageContent = curl_exec($ch);
    if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
        echo 'Gagal mengambil gambar.';
        exit;
    }
    curl_close($ch);

    $imageInfo = getimagesizefromstring($imageContent);
    if ($imageInfo) {
        header('Content-Type: ' . $imageInfo['mime']);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        echo $imageContent;
        if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
        file_put_contents($cacheFile, $imageContent);
    } else {
        echo 'Gambar tidak valid.';
    }
} else {
    echo 'URL tidak diberikan.';
}
?>
