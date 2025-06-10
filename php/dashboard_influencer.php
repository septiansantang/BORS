<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'influencer') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Ambil data profil influencer
$sql = "SELECT * FROM user_influencer WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$profil = $result->fetch_assoc();
$stmt->close();

// Dummy data kampanye
$my_campaigns = [
    [
        'title' => 'Kampanye A',
        'status' => 'Menunggu Verifikasi',
        'views' => '10.2K',
        'target_views' => '15K',
        'progress' => 68
    ],
    [
        'title' => 'Kampanye B',
        'status' => 'Terverifikasi',
        'views' => '25.1K',
        'target_views' => '20K',
        'progress' => 100
    ]
];

$earnings = [
    'this_month' => 2500000,
    'total' => 7800000
];

$recommended_campaigns = [
    [ 'title' => 'Produk Baru X', 'target_views' => '30K', 'reward' => 'Rp5.000.000/1 Juta views' ],
    [ 'title' => 'Event Liburan Y', 'target_views' => '20K', 'reward' => 'Rp3.500.000/100 ribu views' ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borsmen - Dashboard Influencer</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/beranda.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="header">
                <div class="list-item">
                    <a href="#">
                        <i class='bx bxs-star'></i>
                        <span class="description-header">Borsmen</span>
                    </a>
                </div>
                <div class="illustration">
                    <img src="../assets/Desain tanpa judul.png" alt="Logo">
                </div>
            </div>
            <div class="tab">
                <div class="list-item"><a href="#"><i class='bx bxs-dashboard'></i><span class="description-header">Dashboard</span></a></div>
                <div class="list-item"><a href="#"><i class='bx bxs-video'></i><span class="description-header">Kampanye Saya</span></a></div>
                <div class="list-item"><a href="#"><i class='bx bxs-upload'></i><span class="description-header">Unggah Konten</span></a></div>
                <div class="list-item"><a href="#"><i class='bx bxs-wallet'></i><span class="description-header">Penghasilan</span></a></div>
                <div class="list-item"><a href="#"><i class='bx bxs-cog'></i><span class="description-header">Pengaturan</span></a></div>
                <div class="list-item"><a href="api.php?action=logout"><i class='bx bxs-log-out'></i><span class="description-header">Logout</span></a></div>
            </div>
            <div class="profile">
                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="ms-2">
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Influencer</div>
                </div>
            </div>
        </div>
        <div class="main">
            <div id="content" class="content-box">
                <h4>Dashboard</h4>

                <h6>Profil Saya</h6>
                <div class="card-trend mb-3">
                    <img src="../uploads/<?php echo htmlspecialchars($profil['foto_profile']); ?>" class="profile-img" alt="Foto Profil">
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($profil['name']); ?></p>
                    <p><strong>Kota:</strong> <?php echo htmlspecialchars($profil['kota']); ?></p>
                    <p><strong>Konten:</strong> <?php echo htmlspecialchars($profil['konten']); ?></p>
                    <p><strong>Pengenalan:</strong> <?php echo htmlspecialchars($profil['pengenalan']); ?></p>
                </div>

                <h6>Kampanye yang Saya Ikuti</h6>
                <div class="grid-container">
                    <?php foreach ($my_campaigns as $c): ?>
                        <div class="card-trend">
                            <span class="badge bg-info"><?php echo $c['status']; ?></span>
                            <h6><?php echo $c['title']; ?></h6>
                            <small><?php echo $c['views']; ?> views • Target <?php echo $c['target_views']; ?></small>
                            <div class="mt-2">
                                <div class="progress-label">Progress Views</div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $c['progress']; ?>%"></div>
                                </div>
                                <small><?php echo $c['progress']; ?>%</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h6>Ringkasan Penghasilan</h6>
                <div class="grid-container">
                    <div class="card-trend"><h6>Bulan Ini</h6><strong>Rp<?php echo number_format($earnings['this_month'], 0, ',', '.'); ?></strong></div>
                    <div class="card-trend"><h6>Total</h6><strong>Rp<?php echo number_format($earnings['total'], 0, ',', '.'); ?></strong></div>
                </div>

                <h6>Kampanye Rekomendasi</h6>
                <div class="grid-container">
                    <?php foreach ($recommended_campaigns as $rec): ?>
                        <div class="card-trend">
                            <h6><?php echo $rec['title']; ?></h6>
                            <small>Target Views: <?php echo $rec['target_views']; ?></small><br>
                            <small>Reward: <?php echo $rec['reward']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
