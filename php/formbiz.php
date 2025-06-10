<?php
session_start();

// Cek apakah pengguna sudah login dan adalah bisnis
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

// Konfigurasi database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

// Inisialisasi variabel error dan sukses
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new mysqli($host, $user, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Koneksi gagal: " . $conn->connect_error);
        }

        // Sanitasi input data
        $nama_bisnis = trim($_POST['nama_bisnis'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validasi input wajib
        if (empty($nama_bisnis) || empty($phone) || empty($description)) {
            throw new Exception("Nama bisnis, nomor telepon, dan deskripsi wajib diisi.");
        }

        // Validasi nomor telepon
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $phone)) {
            throw new Exception("Format nomor telepon tidak valid.");
        }

        // Validasi URL website jika diisi
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            throw new Exception("URL website tidak valid.");
        }

        // Proses unggahan file
        $foto_profile = '';
        if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../Uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $foto_name = uniqid() . '_' . basename($_FILES['foto_profile']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (!move_uploaded_file($_FILES['foto_profile']['tmp_name'], $foto_path)) {
                throw new Exception("Gagal mengunggah foto.");
            }
            $foto_profile = $foto_name;
        } else {
            throw new Exception("Logo bisnis wajib diunggah.");
        }

        // Perbarui data bisnis
        $sql = "UPDATE user_bisnis SET 
                nama_bisnis = ?, 
                nomor_telepon = ?, 
                website = ?, 
                deskripsi = ?, 
                foto_profile = ? 
                WHERE username = ?";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query: " . $conn->error);
        }

        $stmt->bind_param("ssssss", 
            $nama_bisnis, 
            $phone, 
            $website, 
            $description, 
            $foto_profile, 
            $_SESSION['username']
        );

        if ($stmt->execute()) {
            $_SESSION['profile_completed'] = true;
            header("Location: http://localhost/BORS?success=Profil bisnis berhasil diperbarui");
            exit();
        } else {
            throw new Exception("Gagal memperbarui data: " . $stmt->error);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    } finally {
        // Tutup statement hanya jika valid
        if (isset($stmt) && is_object($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Profil Bisnis - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/form.css">
</head>
<body>
    <div class="form-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="flex-container">
                <!-- Data Bisnis Section -->
                <div class="form-section">
                    <h2>Data Bisnis</h2>
                    
                    <label for="foto_profile">Logo Bisnis</label>
                    <input type="file" name="foto_profile" id="foto_profile" accept="image/*" onchange="previewImage(event)" required>
                    <img id="preview" src="#" alt="Preview Logo Bisnis" class="preview-img" style="display: none;"/>

                    <label for="nama_bisnis">Nama Bisnis</label>
                    <input type="text" name="nama_bisnis" id="nama_bisnis" 
                           placeholder="Nama Bisnis" required>

                    <label for="phone">Nomor Telepon</label>
                    <input type="tel" name="phone" id="phone" 
                           placeholder="Nomor Telepon Bisnis" 
                           pattern="[0-9\-\(\)\/\+\s]*" required>
                </div>

                <!-- Informasi Tambahan Section -->
                <div class="form-section">
                    <h2>Informasi Tambahan</h2>

                    <label for="description">Deskripsi Bisnis</label>
                    <textarea name="description" id="description" 
                              placeholder="Ceritakan tentang bisnis Anda" rows="5" required></textarea>

                    <label for="website">Website</label>
                    <input type="url" name="website" id="website" 
                           placeholder="Link website bisnis (opsional)">
                </div>
            </div>

            <button type="submit" class="btn">Simpan Profil</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            preview.style.display = "block";
            preview.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
</body>
</html>