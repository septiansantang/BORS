<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../php/koneksi.php';

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
    'foto_profile' => ''
];
$nama_bisnis = '';
$email = '';
$nomor_telepon = '';
$website = '';
$deskripsi = '';
$foto_profile = '';

// Ambil data bisnis
if ($id_bisnis) {
    $sql = "SELECT nama_bisnis, email, nomor_telepon, website, deskripsi, foto_profile FROM user_bisnis WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($nama_bisnis, $email, $nomor_telepon, $website, $deskripsi, $foto_profile);
    $stmt->fetch();
    $stmt->close();
}

// Proses form edit
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
            $foto_name = uniqid() . '_' . basename($_FILES['foto_profile']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $foto_path)) {
                // Hapus foto lama jika ada
                if ($foto_profile && file_exists($upload_dir . $foto_profile)) {
                    unlink($upload_dir . $foto_profile);
                }
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
            $foto_profile = $foto_profile_new;
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
    <title>Edit Profile - Borsmen</title>
    <link rel="stylesheet" href="../CSS/edit_profile.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #A7E6FF 0%, #ffffff 100%);
        }
        .content-box {
            max-width: 480px;
            margin-top: 48px;
        }
        .edit-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            justify-content: center;
        }
        .edit-header .icon {
            background: #3572EF;
            color: #fff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 2px 8px rgba(53,114,239,0.13);
        }
        .edit-header h4 {
            margin: 0;
            font-size: 1.5rem;
            color: #050C9C;
            font-family: "Oswald", "Lato", Arial, sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .form-group input, .form-group textarea {
            background: #fafdff;
            border: 1.5px solid #bcdcff;
            border-radius: 7px;
            padding: 10px 12px;
            width: 100%;
            font-size: 1em;
            margin-top: 2px;
            margin-bottom: 2px;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #3572EF;
            outline: none;
            background: #f0f7ff;
        }
        .form-group textarea {
            min-height: 70px;
            resize: vertical;
        }
        .field-error {
            color: #c62828;
            font-size: 0.95em;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .optional-label {
            font-size: 0.93em;
            color: #607d8b;
            font-weight: 400;
        }
        .btn {
            min-width: 120px;
            padding: 9px 22px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            margin-right: 6px;
            margin-top: 8px;
            transition: background 0.2s, box-shadow 0.2s, color 0.2s;
            font-family: 'Cal Sans', 'Lato', Arial, sans-serif;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(90deg, #3ABEF9 70%, #3572EF 100%);
            color: #fff;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #3572EF 70%, #3ABEF9 100%);
            color: #fff;
            box-shadow: 0 2px 12px rgba(53,114,239,0.13);
        }
        .btn-secondary {
            background: linear-gradient(90deg, #607d8b 70%, #455a64 100%);
            color: #fff;
        }
        .btn-secondary:hover {
            background: linear-gradient(90deg, #455a64 70%, #607d8b 100%);
            color: #fff;
            box-shadow: 0 2px 12px rgba(96,125,139,0.13);
        }
        .msg-success, .msg-error {
            margin-bottom: 18px;
        }
        .foto-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }
        .foto-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #3572EF;
            background: #fafbfc;
        }
        @media (max-width: 600px) {
            .content-box {
                padding: 14px 4px;
                max-width: 99vw;
            }
            .edit-header h4 {
                font-size: 1.13rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main">
            <div class="content-box" id="content">
                <div class="edit-header">
                    <span class="icon"><i class='bx bxs-user-circle'></i></span>
                    <h4>Edit Profile Bisnis</h4>
                </div>
                <?php if ($success): ?>
                    <div class="msg-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="msg-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-group">
                        <label for="nama_bisnis">Nama Bisnis</label>
                        <input type="text" id="nama_bisnis" name="nama_bisnis" value="<?php echo htmlspecialchars($nama_bisnis); ?>" required>
                        <?php if ($errors['nama_bisnis']): ?>
                            <div class="field-error"><?php echo $errors['nama_bisnis']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if ($errors['email']): ?>
                            <div class="field-error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="nomor_telepon">Nomor Telepon</label>
                        <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($nomor_telepon); ?>" required>
                        <?php if ($errors['nomor_telepon']): ?>
                            <div class="field-error"><?php echo $errors['nomor_telepon']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($website); ?>" required>
                        <?php if ($errors['website']): ?>
                            <div class="field-error"><?php echo $errors['website']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Bisnis</label>
                        <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($deskripsi); ?></textarea>
                        <?php if ($errors['deskripsi']): ?>
                            <div class="field-error"><?php echo $errors['deskripsi']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="foto_profile">Foto Profil (Opsional)</label>
                        <input type="file" id="foto_profile" name="foto_profile" accept="image/*">
                        <?php if ($errors['foto_profile']): ?>
                            <div class="field-error"><?php echo $errors['foto_profile']; ?></div>
                        <?php endif; ?>
                        <?php if ($foto_profile && file_exists(__DIR__ . '/../Uploads/' . $foto_profile)): ?>
                            <div class="foto-preview">
                                <img src="../Uploads/<?php echo htmlspecialchars($foto_profile); ?>" alt="Foto Profil">
                                <span style="font-size:0.97em;color:#607d8b;">Foto Profil Saat Ini</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password Baru <span class="optional-label">(Opsional, minimal 6 karakter)</span></label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        <?php if ($errors['password']): ?>
                            <div class="field-error"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:center;margin-top:18px;">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="pengaturan.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
