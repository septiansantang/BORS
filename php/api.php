```
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'borsmen';
$dbuser = 'root';
$dbpass = '';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    sendResponse(500, 'Koneksi database gagal: ' . $conn->connect_error);
}

function sendResponse($status, $message, $data = null) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) || empty($url);
}

$request_method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan POST.');
        }

        $username = htmlspecialchars($_POST['username'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_type = $_POST['user_type'] ?? '';
        $name = htmlspecialchars($_POST['name'] ?? '');
        $nama_bisnis = htmlspecialchars($_POST['nama_bisnis'] ?? '');

        if (empty($username) || empty($email) || empty($password) || empty($user_type)) {
            sendResponse(400, 'Semua field wajib diisi.');
        }

        if (!validateEmail($email)) {
            sendResponse(400, 'Email tidak valid.');
        }

        $check_query = "SELECT username FROM user_$user_type WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            sendResponse(400, 'Username atau email sudah terdaftar.');
        }
        $check_stmt->close();

        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($user_type === 'business') {
            $table = 'user_bisnis';
            $query = "INSERT INTO $table (username, email, password, nama_bisnis) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $username, $email, $password_hashed, $nama_bisnis);
        } elseif ($user_type === 'influencer') {
            $table = 'user_influencer';
            $query = "INSERT INTO $table (username, email, password, name) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $username, $email, $password_hashed, $name);
        } else {
            sendResponse(400, 'Tipe pengguna tidak valid.');
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_type'] = $user_type;
            sendResponse(201, 'Registrasi berhasil.', ['user_type' => $user_type]);
        } else {
            sendResponse(500, 'Gagal mendaftar: ' . $stmt->error);
        }

        $stmt->close();
        break;

    case 'login':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan POST.');
        }

        $username = htmlspecialchars($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_type = $_POST['user_type'] ?? '';

        if (empty($username) || empty($password) || empty($user_type)) {
            sendResponse(400, 'Semua field wajib diisi.');
        }

        $table = $user_type === 'admin' ? 'user_admin' : ($user_type === 'business' ? 'user_bisnis' : 'user_influencer');
        $query = "SELECT id, username, password, email FROM $table WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            sendResponse(401, 'Username tidak ditemukan.');
        }

        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user_type;
            sendResponse(200, 'Login berhasil.', ['user_type' => $user_type]);
        } else {
            sendResponse(401, 'Password salah.');
        }

        $stmt->close();
        break;

    case 'update_influencer':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan POST.');
        }

        if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'influencer') {
            sendResponse(401, 'Tidak diizinkan. Silakan login sebagai influencer.');
        }

        $nama = htmlspecialchars($_POST['nama'] ?? '');
        $nomor_hp = htmlspecialchars($_POST['nomor_hp'] ?? '');
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
        $kota = htmlspecialchars($_POST['kota'] ?? '');
        $pengenalan = htmlspecialchars($_POST['pengenalan'] ?? '');
        $konten = htmlspecialchars($_POST['konten'] ?? '');
        $instagram = htmlspecialchars($_POST['instagram'] ?? '');
        $tiktok = htmlspecialchars($_POST['tiktok'] ?? '');
        $youtube = htmlspecialchars($_POST['youtube'] ?? '');
        $facebook = htmlspecialchars($_POST['facebook'] ?? '');

        if (empty($nama) || empty($nomor_hp) || empty($tanggal_lahir) || empty($kota) || empty($pengenalan) || empty($konten) || empty($instagram) || empty($tiktok)) {
            sendResponse(400, 'Semua field wajib diisi kecuali youtube dan facebook.');
        }

        if (!validateUrl($instagram) || !validateUrl($tiktok) || !validateUrl($youtube) || !validateUrl($facebook)) {
            sendResponse(400, 'URL media sosial tidak valid.');
        }

        $foto_profile = null;
        if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../Uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $foto_name = uniqid() . '_' . basename($_FILES['foto_profile']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $foto_path)) {
                $foto_profile = $foto_name;
            } else {
                sendResponse(400, 'Gagal mengupload foto.');
            }
        }

        $username = $_SESSION['username'];
        $query = "UPDATE user_influencer SET name = ?, foto_profile = ?, nomor_hp = ?, tanggal_lahir = ?, kota = ?, pengenalan = ?, konten = ?, link_ig = ?, link_tiktok = ?, link_youtube = ?, link_fb = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssssss", $nama, $foto_profile, $nomor_hp, $tanggal_lahir, $kota, $pengenalan, $konten, $instagram, $tiktok, $youtube, $facebook, $_SESSION['user_id']);

        if ($stmt->execute()) {
            sendResponse(200, 'Profil influencer berhasil diperbarui.');
        } else {
            sendResponse(500, 'Gagal memperbarui profil: ' . $stmt->error);
        }

        $stmt->close();
        break;

    case 'update_business':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan POST.');
        }

        if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
            sendResponse(401, 'Tidak diizinkan. Silakan login sebagai bisnis.');
        }

        $nama_bisnis = htmlspecialchars($_POST['nama_bisnis'] ?? '');
        $website = htmlspecialchars($_POST['website'] ?? '');
        $nomor_telepon = htmlspecialchars($_POST['nomor_telepon'] ?? '');
        $deskripsi = htmlspecialchars($_POST['deskripsi'] ?? '');

        if (empty($nama_bisnis)) {
            sendResponse(400, 'Nama bisnis wajib diisi.');
        }

        if (!validateUrl($website)) {
            sendResponse(400, 'URL website tidak valid.');
        }

        $foto_profile = null;
        if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../Uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $foto_name = uniqid() . '_' . basename($_FILES['foto_profile']['name']);
            $foto_path = $upload_dir . $foto_name;
            if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $foto_path)) {
                $foto_profile = $foto_name;
            } else {
                sendResponse(400, 'Gagal mengupload foto.');
            }
        }

        $username = $_SESSION['username'];
        $query = "UPDATE user_bisnis SET nama_bisnis = ?, foto_profile = ?, website = ?, nomor_telepon = ?, deskripsi = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $nama_bisnis, $foto_profile, $website, $nomor_telepon, $deskripsi, $_SESSION['user_id']);

        if ($stmt->execute()) {
            sendResponse(200, 'Profil bisnis berhasil diperbarui.');
        } else {
            sendResponse(500, 'Gagal memperbarui profil: ' . $stmt->error);
        }

        $stmt->close();
        break;

    case 'create_collaboration':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan POST.');
        }

        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['business', 'influencer'])) {
            sendResponse(401, 'Tidak diizinkan. Silakan login.');
        }

        $id_influencer = $_POST['id_influencer'] ?? 0;
        $id_bisnis = $_POST['id_bisnis'] ?? 0;
        $detail_kolaborasi = htmlspecialchars($_POST['detail_kolaborasi'] ?? '');

        if ($id_influencer <= 0 || $id_bisnis <= 0) {
            sendResponse(400, 'ID influencer dan bisnis wajib diisi.');
        }

        $query = $conn->prepare("INSERT INTO kolaborasi (id_influencer, id_bisnis, detail_kolaborasi)) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_influencer, $id_bisnis, $detail_kolaborasi);

        if ($stmt->execute()) {
            sendResponse(201, 'Kolaborasi berhasil dibuat.');
        } else {
            sendResponse(500, 'Gagal membuat kolaborasi: ' . $stmt->error);
        }

        $stmt->close();
        break;

    case 'get_collaborations':
        if ($request_method !== 'GET') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan GET.');
        }

        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['business', 'influencer', 'admin'])) {
            sendResponse(401, 'Tidak diizinkan. Silakan login.');
        }

        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'];

        if ($user_type === 'admin') {
            $query = "SELECT k.*, ui.name AS influencer_name, ub.nama_bisnis AS business_name 
                      FROM kolaborasi k 
                      JOIN user_influencer ui ON k.id_influencer = ui.id 
                      JOIN user_bisnis ub ON k.id_bisnis = ub.id";
        } else {
            $column = $user_type === 'business' ? 'id_bisnis' : 'id_influencer';
            $query = "SELECT k.*, ui.name AS influencer_name, ub.nama_bisnis AS business_name 
                      FROM kolaborasi k 
                      JOIN user_influencer ui ON k.id_influencer = ui.id 
                      JOIN user_bisnis ub ON k.id_bisnis = ub.id 
                      WHERE k.$column = ?";
        }

        $stmt = $conn->prepare($query);
        if ($user_type !== 'admin') {
            $stmt->bind_param("i", $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $collaborations = $result->fetch_all(MYSQLI_ASSOC);

        sendResponse(200, 'Data kolaborasi berhasil diambil.', $collaborations);
        $stmt->close();
        break;
        x
    case 'get_profile':
        if ($request_method !== 'GET') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan GET.');
        }

        if (!isset($_SESSION['username'])) {
            sendResponse(401, 'Tidak diizinkan. Silakan login.');
        }

        $username = $_SESSION['username'];
        $user_type = $_SESSION['user_type'];
        $table = $user_type === 'admin' ? 'user_admin' : ($user_type === 'business' ? 'user_bisnis' : 'user_influ');

        $query = "SELECT * FROM $table WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            sendResponse(404, 'User not found.');
        }

        $profile = $result->fetch_assoc();
        unset($profile['password']);
        sendResponse(200, 'Profil berhasil diambil.', $profile);

        $stmt->close();
        break;

    case 'logout':
        if ($request_method !== 'GET') {
            sendResponse(405, 'Metode tidak diizinkan. Gunakan GET.');
        }

        session_unset();
        session_destroy();
        header("Location: borsmenlanding.php");
        exit();
        break;

    default:
        sendResponse(400, 'Endpoint tidak ditemukan.');
}

$conn->close();
?>
```