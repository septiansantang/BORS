<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php'; // Menggunakan koneksi.php untuk konsistensi

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            throw new Exception("Username dan password wajib diisi.");
        }

        // Daftar tabel dan halaman tujuan
        $tables = [
            'user_bisnis' => ['user_type' => 'business', 'redirect' => 'beranda_business.php'],
            'user_influencer' => ['user_type' => 'influencer', 'redirect' => 'dashboard_influencer.php'],
            'user_admin' => ['user_type' => 'admin', 'redirect' => 'dashboard_admin.php']
        ];

        $user_found = false;
        $user_data = null;

        // Periksa setiap tabel
        foreach ($tables as $table => $info) {
            $sql = "SELECT id, username, password FROM $table WHERE username = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Gagal mempersiapkan query untuk tabel $table: " . $conn->error);
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $user_found = true;
                $user_data['user_type'] = $info['user_type'];
                $user_data['redirect'] = $info['redirect'];
                $stmt->close();
                break; // Keluar dari loop jika pengguna ditemukan
            }
            $stmt->close();
        }

        if ($user_found && password_verify($password, $user_data['password'])) {
            // Simpan data sesi
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_type'] = $user_data['user_type'];

            // Redirect ke halaman yang sesuai
            header("Location: " . $user_data['redirect']);
            exit();
        } else {
            $error = $user_found ? "Password salah." : "Pengguna tidak ditemukan.";
        }

        $conn->close();

    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/register.css?t=<?php echo time(); ?>">
</head>
<body>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h1>Login</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" 
                       placeholder="Username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" 
                       placeholder="Password" 
                       required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <button type="submit" class="btn">Login</button>

            <div class="register-link">
                <p>Belum punya akun? 
                    <a href="register.php">Daftar</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>
