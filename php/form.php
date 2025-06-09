<?php
session_start();

// Check if user is logged in and is influencer
if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'influencer') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Database connection
        $host = "localhost";
        $user = "root";
        $password = "";
        $dbname = "borsmen";

        $conn = new mysqli($host, $user, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Handle file upload
        if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto_profile']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);

            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception('Invalid file type. Only jpg, jpeg, png, and gif are allowed.');
            }

            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/' . $new_filename;

            if (!move_uploaded_file($_FILES['foto_profile']['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload file.');
            }
        } else {
            throw new Exception('Profile photo is required.');
        }

        // Sanitize and validate other inputs
        $nama = trim($_POST['nama']);
        $nomor_hp = trim($_POST['nomor_hp']);
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $kota = trim($_POST['kota']);
        $pengenalan = trim($_POST['pengenalan']);
        $konten = trim($_POST['konten']);
        $instagram = trim($_POST['instagram']);
        $tiktok = trim($_POST['tiktok']);
        $youtube = !empty($_POST['youtube']) ? trim($_POST['youtube']) : null;
        $facebook = !empty($_POST['facebook']) ? trim($_POST['facebook']) : null;

        // Validate phone number
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $nomor_hp)) {
            throw new Exception("Invalid phone number format");
        }

        // Update influencer profile
        $sql = "UPDATE user_influencer SET 
                foto_profile = ?,
                nama = ?,
                nomor_hp = ?,
                tanggal_lahir = ?,
                kota = ?,
                pengenalan = ?,
                jenis_konten = ?,
                instagram = ?,
                tiktok = ?,
                youtube = ?,
                facebook = ?
                WHERE username = ?";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssss", 
            $new_filename,
            $nama,
            $nomor_hp,
            $tanggal_lahir,
            $kota,
            $pengenalan,
            $konten,
            $instagram,
            $tiktok,
            $youtube,
            $facebook,
            $_SESSION['username']
        );

        if ($stmt->execute()) {
            $_SESSION['profile_completed'] = true;
            header("Location: dashboard_influencer.php");
            exit();
        } else {
            throw new Exception("Error updating record: " . $stmt->error);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Influencer</title>
    <link rel="stylesheet" href="../CSS/form.css">
</head>
<body>
    <div class="form-container">
        <form action="save_influencer.php" method="POST" enctype="multipart/form-data">
            <div class="flex-container">
                <!-- Data Diri Section -->
                <div class="form-section">
                    <h2>Data Diri</h2>
                    <label for="foto">Foto Profile</label>
                    <input type="file" name="foto_profile" id="foto" accept="image/*" onchange="previewImage(event)" required>
                    <img id="preview" src="#" alt="Preview Foto Profile" class="preview-img"/>

                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" placeholder="Nama" required>

                    <label for="nomor">Phone Number</label>
                    <input type="tel" name="nomor_hp" id="nomor" placeholder="Phone number" required>

                    <label for="tanggal">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal" required>

                    <label for="kota">Kota</label>
                    <select name="kota" id="kota" required>
                        <option value="">Pilih Kota</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Surabaya">Surabaya</option>
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

                    <label for="tiktok">Tiktok</label>
                    <input type="url" name="tiktok" id="tiktok" placeholder="Link Tiktok" required>

                    <label for="youtube">Youtube</label>
                    <input type="url" name="youtube" id="youtube" placeholder="Link Youtube">

                    <label for="facebook">Facebook</label>
                    <input type="url" name="facebook" id="facebook" placeholder="Link Facebook">
                </div>
            </div>

            <button type="submit" class="btn">Kirim</button>
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
