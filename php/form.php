<?php
session_start();

// Cek apakah pengguna sudah login dan adalah influencer
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'influencer') {
    header("Location: login.php");
    exit();
}

// Inisialisasi variabel
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Koneksi database
        $host = "localhost";
        $user = "root";
        $password = "";
        $dbname = "borsmen";

        $conn = new mysqli($host, $user, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Koneksi gagal: " . $conn->connect_error);
        }

        // Tangani unggahan file
        $new_filename = '';
        if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_profile']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);

            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception('Jenis file tidak valid. Hanya jpg, jpeg, png, dan gif yang diizinkan.');
            }

            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/' . $new_filename;

            if (!is_dir('../Uploads/')) {
                mkdir('../Uploads/', 0755, true);
            }

            if (!move_uploaded_file($_FILES['foto_profile']['tmp_name'], $upload_path)) {
                throw new Exception('Gagal mengunggah file.');
            }
        } else {
            throw new Exception('Foto profil wajib diunggah.');
        }

        // Sanitasi dan validasi input
        $nama = trim($_POST['nama'] ?? '');
        $nomor_hp = trim($_POST['nomor_hp'] ?? '');
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
        $kota = trim($_POST['kota'] ?? '');
        $pengenalan = trim($_POST['pengenalan'] ?? '');
        $konten = trim($_POST['konten'] ?? '');
        $link_ig = trim($_POST['instagram'] ?? '');
        $link_tiktok = trim($_POST['tiktok'] ?? '');
        $link_youtube = !empty($_POST['youtube']) ? trim($_POST['youtube']) : null;
        $link_fb = !empty($_POST['facebook']) ? trim($_POST['facebook']) : null;

        // Validasi input wajib
        if (empty($nama) || empty($nomor_hp) || empty($tanggal_lahir) || empty($kota) || empty($pengenalan) || empty($konten) || empty($link_ig) || empty($link_tiktok)) {
            throw new Exception("Semua field wajib diisi kecuali YouTube dan Facebook.");
        }

        // Validasi nomor telepon
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $nomor_hp)) {
            throw new Exception("Format nomor telepon tidak valid.");
        }

        // Validasi URL jika diisi
        foreach ([$link_ig, $link_tiktok, $link_youtube, $link_fb] as $url) {
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception("Salah satu URL tidak valid.");
            }
        }

        // Perbarui profil influencer
        $sql = "UPDATE user_influencer SET 
                foto_profile = ?,
                name = ?,
                nomor_hp = ?,
                tanggal_lahir = ?,
                kota = ?,
                pengenalan = ?,
                konten = ?,
                link_ig = ?,
                link_tiktok = ?,
                link_youtube = ?,
                link_fb = ?
                WHERE username = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssss", 
            $new_filename,
            $nama,
            $nomor_hp,
            $tanggal_lahir,
            $kota,
            $pengenalan,
            $konten,
            $link_ig,
            $link_tiktok,
            $link_youtube,
            $link_fb,
            $_SESSION['username']
        );

        if ($stmt->execute()) {
            $_SESSION['profile_completed'] = true;
            header("Location: http://localhost/BORS?success=Profil influencer berhasil diperbarui");
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
    <title>Lengkapi Profil Influencer - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/Form.css">
</head>
<body>
    <div class="form-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="flex-container">
                <!-- Data Diri Section -->
                <div class="form-section">
                    <h2>Data Diri</h2>
                    <label for="foto_profile">Foto Profil</label>
                    <input type="file" name="foto_profile" id="foto_profile" accept="image/*" onchange="previewImage(event)" required>
                    <img id="preview" src="#" alt="Preview Foto Profil" class="preview-img" style="display: none;"/>

                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" placeholder="Nama" required>

                    <label for="nomor_hp">Nomor Telepon</label>
                    <input type="tel" name="nomor_hp" id="nomor_hp" placeholder="Nomor telepon" required>

                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" required>

                    <label for="kota">Kota</label>
                    <select name="kota" id="kota" required>
                        <option value="">Pilih Kota</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Surabaya">Surabaya</option>
                        <option value="Medan">Medan</option>
                        <option value="Yogyakarta">Yogyakarta</option>
                        <option value="Bali">Bali</option>  
                        <option value="Makassar">Makassar</option>
                        <option value="Semarang">Semarang</option>
                        <option value="Mataram">Mataram</option>
                        <option value="Batam">Batam</option>
                        <option value="Malang">Malang</option>
                        <option value="Bandar Lampung">Bandar Lampung</option>
                        <option value="Pekanbaru">Pekanbaru</option>
                    </select>
                </div>

                <!-- Konten Section -->
                <div class="form-section">
                    <h2>Konten</h2>
                    <label for="pengenalan">Pengenalan Diri</label>
                    <textarea name="pengenalan" id="pengenalan" placeholder="Ceritakan tentang diri Anda" rows="5" required></textarea>

                    <label for="konten">Jenis Konten</label>
                    <select name="konten" id="konten" required>
                        <option value="">Pilih Jenis Konten</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Teknologi">Teknologi</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Travel">Travel</option>
                        <option value="Kuliner">Kuliner</option>
                    </select>

                    <label for="instagram">Instagram</label>
                    <input type="url" name="instagram" id="instagram" placeholder="Link Instagram" required>

                    <label for="tiktok">TikTok</label>
                    <input type="url" name="tiktok" id="tiktok" placeholder="Link TikTok" required>

                    <label for="youtube">YouTube</label>
                    <input type="url" name="youtube" id="youtube" placeholder="Link YouTube">

                    <label for="facebook">Facebook</label>
                    <input type="url" name="facebook" id="facebook" placeholder="Link Facebook">
                </div>
            </div>

            <button type="submit" class="btn">Simpan Profil</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>