<?php
// Sambungkan ke database
$host = "localhost"; // Ganti dengan host database kamu
$user = "root";      // Ganti dengan username database kamu
$password = "";      // Ganti dengan password database kamu
$dbname = "borsmen"; // Ganti dengan nama database kamu

$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangkap data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Validasi input
if (!empty($username) && !empty($password)) {
    // Cek username dan password di database
    if (empty($username) || empty($password)) {
        echo "Harap isi semua data.";
        exit;
    }
    $sql = "SELECT * FROM USER_INFLUENCER WHERE user_name = ? AND Password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login berhasil
        echo "Login berhasil! Selamat datang, $username.";
    } else {
        // Login gagal
        echo "Username atau password salah.";
    }
    $stmt->close();
} else {
    echo "Harap isi semua data.";
}

$conn->close();
?>
