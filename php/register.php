<?php
// Sambungkan ke database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangkap data dari form
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$userType = trim($_POST['user']);

// Validasi input
if (!empty($username) && !empty($email) && !empty($password) && !empty($userType)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Format email tidak valid.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($userType == "influencer") {
        header("Location: form.php"); // Redirect ke form.php
        exit;
    } elseif ($userType == "business") {
        $sql = "INSERT INTO USER_BISNIS (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Registrasi berhasil sebagai bisnis! Silakan login.";
        } else {
            echo "Terjadi kesalahan: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    echo "Harap isi semua data.";
}

$conn->close();
?>
