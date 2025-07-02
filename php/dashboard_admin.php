<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: borsmenlanding.php");
    exit();
}

require_once 'koneksi.php';

$username = $_SESSION['username'];

$sql = "SELECT * FROM user_admin WHERE username = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Gagal mempersiapkan query profil: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$profil = $result->fetch_assoc();
if (!$profil) {
    die("Data admin tidak ditemukan untuk username: " . htmlspecialchars($username));
}
$stmt->close();

$sql_campaigns = "SELECT c.id, c.judul, c.deskripsi, c.target_dana, c.dana_terkumpul, c.tanggal_selesai, c.status, ub.nama_bisnis 
                 FROM campaign c 
                 JOIN user_bisnis ub ON c.id_bisnis = ub.id 
                 WHERE c.status = 'aktif'";
$stmt = $conn->prepare($sql_campaigns);
if ($stmt === false) {
    die("Gagal mempersiapkan query kampanye: " . $conn->error);
}
$stmt->execute();
$active_campaigns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql_contents = "SELECT ct.id, ct.link_konten, ct.status, ct.tanggal_upload, c.judul, ui.name as influencer_name, ub.nama_bisnis 
                 FROM konten ct 
                 JOIN kolaborasi k ON ct.id_kolaborasi = k.id 
                 JOIN campaign c ON k.id_campaign = c.id 
                 JOIN user_influencer ui ON k.id_influencer = ui.id 
                 JOIN user_bisnis ub ON k.id_bisnis = ub.id 
                 WHERE ct.status = 'pending'";
$stmt = $conn->prepare($sql_contents);
if ($stmt === false) {
    die("Gagal mempersiapkan query konten: " . $conn->error);
}
$stmt->execute();
$pending_contents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borsmen - Dashboard Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/beranda.css">
</head>
<body>
    <div class="container">
        <i class='bx bx-menu hamburger' style='display: none;'></i>
        <div class="sidebar">
            <div class="header">
                <div class="list-item">
                    <a href="#">
                        <i class='bx bxs-star'></i>
                        <span class="description-header">Borsmen</span>
                    </a>
                </div>
                <div class="illustration">
                    <video class="animation-video" playsinline muted loop autoplay poster="../assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story.jpg" style="width: 50%; height: 50%; object-fit: cover;">
                        <source type="video/mp4" src="../assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story (1).mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <div class="tab">
                <div class="list-item active" data-target="dashboard"><a href="#"><i class='bx bxs-dashboard'></i><span class="description-header">Dashboard</span></a></div>
                <div class="list-item"><a href="api.php?action=logout"><i class='bx bxs-log-out'></i><span class="description-header">Logout</span></a></div>
            </div>
            <div class="profile">
                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="ms-2">
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Admin</div>
                </div>
            </div>
        </div>
        <div class="main">
            <div id="content" class="content-box">
                <?php if ($message === 'success' && $action === 'approve_content'): ?>
                    <div class="alert alert-success">Konten berhasil disetujui!</div>
                <?php elseif ($message === 'success' && $action === 'reject_content'): ?>
                    <div class="alert alert-success">Konten berhasil ditolak!</div>
                <?php endif; ?>
                <div id="dashboard-content">
                    <h4>Dashboard Admin</h4>
                    <h6>Kampanye Aktif</h6>
                    <div class="grid-container">
                        <?php if (empty($active_campaigns)): ?>
                            <p>Belum ada kampanye aktif.</p>
                        <?php else: ?>
                            <?php foreach ($active_campaigns as $c): ?>
                                <div class="card-trend">
                                    <span class="badge <?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                                    <h6><?php echo htmlspecialchars($c['judul']); ?></h6>
                                    <small>Bisnis: <?php echo htmlspecialchars($c['nama_bisnis']); ?></small><br>
                                    <small>Target Dana: Rp<?php echo number_format($c['target_dana'], 0, ',', '.'); ?></small><br>
                                    <small>Tenggat: <?php echo date('d M Y', strtotime($c['tanggal_selesai'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <h6>Konten Pending</h6>
                    <div class="grid-container">
                        <?php if (empty($pending_contents)): ?>
                            <p>Belum ada konten pending.</p>
                        <?php else: ?>
                            <?php foreach ($pending_contents as $ct): ?>
                                <div class="card-trend">
                                    <h6><?php echo htmlspecialchars($ct['judul']); ?></h6>
                                    <small>Influencer: <?php echo htmlspecialchars($ct['influencer_name']); ?></small><br>
                                    <small>Bisnis: <?php echo htmlspecialchars($ct['nama_bisnis']); ?></small><br>
                                    <small>Tanggal Upload: <?php echo date('d M Y', strtotime($ct['tanggal_upload'])); ?></small>
                                    <a href="<?php echo htmlspecialchars($ct['link_konten']); ?>" target="_blank" class="btn-primary mt-2">Lihat Konten</a>
                                    <form action="api.php?action=approve_content" method="POST" style="display: inline;">
                                        <input type="hidden" name="content_id" value="<?php echo $ct['id']; ?>">
                                        <input type="number" name="komisi" placeholder="Masukkan komisi" required min="0">
                                        <button type="submit" class="btn-primary mt-2">Setujui</button>
                                    </form>
                                    <form action="api.php?action=reject_content" method="POST" style="display: inline;">
                                        <input type="hidden" name="content_id" value="<?php echo $ct['id']; ?>">
                                        <input type="text" name="alasan_penolakan" placeholder="Alasan penolakan" required>
                                        <button type="submit" class="btn-primary mt-2">Tolak</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
</body>
</html>