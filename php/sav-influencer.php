<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$nama = trim($_POST['nama']);
$nomor_hp = trim($_POST['nomor_hp']);
$tanggal_lahir = trim($_POST['tanggal_lahir']);
$kota = trim($_POST['kota']);
$pengenalan = trim($_POST['pengenalan']);
$jenis_konten = trim($_POST['jenis_konten']);
$instagram = trim($_POST['instagram']);
$tiktok = trim($_POST['tiktok']);
$youtube = trim($_POST['youtube']);
$facebook = trim($_POST['facebook']);

$sql = "INSERT INTO USER_INFLUENCER (nama, nomor_hp, tanggal_lahir, kota, pengenalan, jenis_konten, instagram, tiktok, youtube, facebook)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $nama, $nomor_hp, $tanggal_lahir, $kota, $pengenalan, $jenis_konten, $instagram, $tiktok, $youtube, $facebook);

if ($stmt->execute()) {
    echo "Data berhasil disimpan!";
    echo "<meta http-equiv='refresh' content='2;url=../index.php'>";
} else {
    echo "Terjadi kesalahan: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
