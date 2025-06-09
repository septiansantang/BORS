<?php
session_start();

// Check if user is logged in and is business
if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'business') {
    header("Location: login.php");
    exit();
}

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

// Initialize error variable
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = new mysqli($host, $user, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Sanitize input data
        $business_type = trim($_POST['business_type']);
        $business_address = trim($_POST['business_address']);
        $phone = trim($_POST['phone']);
        $website = trim($_POST['website']);
        $description = trim($_POST['description']);
        
        // Validate phone number
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $phone)) {
            throw new Exception("Invalid phone number format");
        }
        
        // Validate website if provided
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid website URL");
        }

        // Update business data
        $sql = "UPDATE user_bisnis SET 
                jenis_bisnis = ?, 
                alamat = ?, 
                no_telp = ?, 
                website = ?, 
                deskripsi = ? 
                WHERE username = ?";
                
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssss", 
            $business_type, 
            $business_address, 
            $phone, 
            $website, 
            $description, 
            $_SESSION['username']
        );

        if ($stmt->execute()) {
            $_SESSION['profile_completed'] = true;
            header("Location: dashboard_bisnis.php");
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
    <title>Lengkapi Profil Bisnis - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/form.css">
</head>
<body>
    <div class="form-container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="flex-container">
                <!-- Data Bisnis Section -->
                <div class="form-section">
                    <h2>Data Bisnis</h2>
                    
                    <label for="foto">Logo Bisnis</label>
                    <input type="file" name="foto_profile" id="foto" accept="image/*" onchange="previewImage(event)" required>
                    <img id="preview" src="#" alt="Preview Logo Bisnis" class="preview-img"/>

                    <label for="business_type">Jenis Bisnis</label>
                    <select name="business_type" id="business_type" required>
                        <option value="">Pilih Jenis Bisnis</option>
                        <option value="Retail">Retail</option>
                        <option value="F&B">F&B</option>
                        <option value="Jasa">Jasa</option>
                        <option value="Teknologi">Teknologi</option>
                    </select>

                    <label for="phone">Nomor Telepon</label>
                    <input type="tel" name="phone" id="phone" 
                           placeholder="Nomor Telepon Bisnis" 
                           pattern="[0-9\-\(\)\/\+\s]*" required>

                    <label for="business_address">Alamat Bisnis</label>
                    <textarea name="business_address" id="business_address" 
                              placeholder="Alamat lengkap bisnis" rows="3" required></textarea>
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

                    <label for="instagram">Instagram Bisnis</label>
                    <input type="url" name="instagram" id="instagram" 
                           placeholder="Link Instagram bisnis">

                    <label for="facebook">Facebook Bisnis</label>
                    <input type="url" name="facebook" id="facebook" 
                           placeholder="Link Facebook bisnis">
                </div>
            </div>

            <button type="submit" class="btn">Simpan Profil</button>
        </form>
    </div>

    <script>
    function previewImage(event) {
        var preview = document.getElementById('preview');
        preview.style.display = "block";
        preview.src = URL.createObjectURL(event.target.files[0]);
    }
    </script>
</body>
</html>