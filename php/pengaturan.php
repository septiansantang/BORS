<?php
session_start();

// Cek apakah pengguna sudah login dan adalah bisnis
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../php/koneksi.php';

// Inisialisasi variabel
$id_bisnis = $_SESSION['user_id'] ?? 0;
$success = '';
$error = '';
$errors = [
    'nama_bisnis' => '',
    'email' => '',
    'nomor_telepon' => '',
    'website' => '',
    'deskripsi' => '',
    'password' => '',
    'foto_profile' => '' // Menambahkan kunci foto_profile untuk mencegah peringatan
];
$nama_bisnis = '';
$email = '';
$nomor_telepon = '';
$website = '';
$deskripsi = '';
$foto_profile = '';

// Ambil data bisnis dari database
if ($id_bisnis) {
    $sql = "SELECT nama_bisnis, email, nomor_telepon, website, deskripsi, foto_profile FROM user_bisnis WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($nama_bisnis, $email, $nomor_telepon, $website, $deskripsi, $foto_profile);
    if ($stmt->fetch()) {
        $data_bisnis_ditemukan = true;
    } else {
        $data_bisnis_ditemukan = false;
    }
    $stmt->close();
}

// Proses form jika metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_bisnis = trim(filter_input(INPUT_POST, 'nama_bisnis', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $nomor_telepon = trim(filter_input(INPUT_POST, 'nomor_telepon', FILTER_SANITIZE_STRING));
    $website = trim(filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL));
    $deskripsi = trim(filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING));
    $password = trim($_POST['password'] ?? '');
    $valid = true;

    // Validasi input
    if (!$nama_bisnis) {
        $errors['nama_bisnis'] = 'Nama bisnis wajib diisi.';
        $valid = false;
    }
    if (!$email) {
        $errors['email'] = 'Email wajib diisi.';
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
        $valid = false;
    }
    if (!$nomor_telepon) {
        $errors['nomor_telepon'] = 'Nomor telepon wajib diisi.';
        $valid = false;
    } elseif (!preg_match('/^[0-9+\- ]+$/', $nomor_telepon)) {
        $errors['nomor_telepon'] = 'Nomor telepon tidak valid.';
        $valid = false;
    }
    if (!$website) {
        $errors['website'] = 'Website wajib diisi.';
        $valid = false;
    } elseif (!filter_var($website, FILTER_VALIDATE_URL)) {
        $errors['website'] = 'Format website tidak valid.';
        $valid = false;
    }
    if (!$deskripsi) {
        $errors['deskripsi'] = 'Deskripsi wajib diisi.';
        $valid = false;
    }
    if ($password && strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
        $valid = false;
    }

    // Validasi upload foto
    $foto_profile_new = $foto_profile;
    if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = $_FILES['foto_profile']['type'];
        $file_size = $_FILES['foto_profile']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $errors['foto_profile'] = 'Format file harus JPG, PNG, atau GIF.';
            $valid = false;
        } elseif ($file_size > $max_size) {
            $errors['foto_profile'] = 'Ukuran file maksimal 2MB.';
            $valid = false;
        } else {
            // Perbaiki: basename harus pakai tanda kurung tutup
            $foto_name = uniqid() . '_' . basename($_FILES['foto_profile']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $foto_path)) {
                $foto_profile_new = $foto_name;
            } else {
                $errors['foto_profile'] = 'Gagal mengupload foto.';
                $valid = false;
            }
        }
    }

    // Simpan perubahan ke database
    if ($valid) {
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE user_bisnis SET nama_bisnis = ?, email = ?, nomor_telepon = ?, website = ?, deskripsi = ?, password = ?, foto_profile = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $nama_bisnis, $email, $nomor_telepon, $website, $deskripsi, $password_hash, $foto_profile_new, $id_bisnis);
        } else {
            $sql = "UPDATE user_bisnis SET nama_bisnis = ?, email = ?, nomor_telepon = ?, website = ?, deskripsi = ?, foto_profile = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $nama_bisnis, $email, $nomor_telepon, $website, $deskripsi, $foto_profile_new, $id_bisnis);
        }
        if ($stmt && $stmt->execute()) {
            $success = 'Data berhasil diperbarui.';
            $_SESSION['username'] = $nama_bisnis;
            // Hapus foto lama jika ada foto baru
            if ($foto_profile_new !== $foto_profile && $foto_profile && file_exists(__DIR__ . '/../Uploads/' . $foto_profile)) {
                unlink(__DIR__ . '/../Uploads/' . $foto_profile);
            }
        } else {
            $error = 'Gagal memperbarui data: ' . ($stmt ? $stmt->error : 'Koneksi database gagal.');
        }
        if ($stmt) $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - Borsmen</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/beranda.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="header">
                <div class="list-item">
                    <a href="#">
                        <i class='bx bxs-coffee-bean'></i>
                        <span class="description-header">Borsmen</span>
                    </a>
                </div>
                <div class="illustration">
                    <video class="animation-video" playsinline muted loop autoplay style="width: 50%; height: 50%; object-fit: cover;">
                        <source type="video/mp4" src="../assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story (1).mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <div class="tab">
                <div class="list-item" data-target="dashboard">
                    <a href="beranda_business.php" id="menu-dashboard">
                        <i class='bx bxs-dashboard'></i>
                        <span class="description-header">Dashboard</span>
                    </a>
                </div>
                <div class="list-item" data-target="kampanye">
                    <a href="kampanye_saya.php" id="menu-kampanye">
                        <i class='bx bxs-megaphone'></i>
                        <span class="description-header">Kampanye Saya</span>
                    </a>
                </div>
                <div class="list-item" data-target="analitik">
                    <a href="analitik.php" id="menu-analitik">
                        <i class='bx bxs-bar-chart-alt-2'></i>
                        <span class="description-header">Analitik</span>
                    </a>
                </div>
                <div class="list-item" data-target="escrow">
                    <a href="escrow.php" id="menu-escrow">
                        <i class='bx bxs-wallet'></i>
                        <span class="description-header">Escrow</span>
                    </a>
                </div>
                <div class="list-item" data-target="pengaturan">
                    <a href="pengaturan.php" id="menu-pengaturan">
                        <i class='bx bxs-cog'></i>
                        <span class="description-header">Pengaturan</span>
                    </a>
                </div>
                <div class="list-item">
                    <a href="api.php?action=logout">
                        <i class='bx bxs-log-out'></i>
                        <span class="description-header">Logout</span>
                    </a>
                </div>
            </div>
            <div class="profile">
                <?php if ($foto_profile && file_exists(__DIR__ . '/../Uploads/' . $foto_profile)): ?>
                    <img src="../Uploads/<?php echo htmlspecialchars($foto_profile); ?>" alt="Foto Profil" class="profile-img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                    </div>
                <?php endif; ?>
                <div class="ms-2">
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Bisnis</div>
                </div>
            </div>
        </div>
        <div class="main">
            <div class="content-box" id="content">
                <h4>Pengaturan Akun Bisnis</h4>
                <div class="profile-info">
                    <?php if (!$data_bisnis_ditemukan): ?>
                        <div class="msg-error">Data bisnis tidak ditemukan.</div>
                    <?php else: ?>
                        <div><b>Nama Bisnis:</b> <?php echo htmlspecialchars($nama_bisnis); ?></div>
                        <div><b>Email:</b> <?php echo htmlspecialchars($email); ?></div>
                        <div><b>Telepon:</b> <?php echo htmlspecialchars($nomor_telepon); ?></div>
                        <div><b>Website:</b> <?php echo htmlspecialchars($website); ?></div>
                        <div><b>Deskripsi:</b> <?php echo nl2br(htmlspecialchars($deskripsi)); ?></div>
                        <?php if ($foto_profile && file_exists(__DIR__ . '/../Uploads/' . $foto_profile)): ?>
                            <div>
                                <b>Foto Profil:</b>
                                <img src="../Uploads/<?php echo htmlspecialchars($foto_profile); ?>" alt="Foto Profil" class="profile-img">
                            </div>
                        <?php endif; ?>
                        <div style="margin-top:18px;">
                            <a href="edit_profile.php" class="btn btn-secondary btn-sm">Edit Profile</a>
                        </div>
                    <?php endif; ?>
                </div>
                <form class="form-setting" method="post" autocomplete="off">
                </form>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
</body>
</html>