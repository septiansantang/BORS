<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$campaigns = [];
$notifications = [];
$foto_profile = '';
$total_campaigns = 0;
$total_funds = 0;
$pending_collab = 0;

if ($id_bisnis) {
    // Ambil foto_profile
    $sql_foto = "SELECT foto_profile FROM user_bisnis WHERE id = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    if ($stmt_foto === false) {
        die("Gagal mempersiapkan query foto: " . $conn->error);
    }
    $stmt_foto->bind_param("i", $id_bisnis);
    $stmt_foto->execute();
    $stmt_foto->bind_result($foto_profile);
    $stmt_foto->fetch();
    $stmt_foto->close();

    // Ambil kampanye
    $sql = "SELECT * FROM campaign WHERE id_bisnis = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Gagal mempersiapkan query kampanye: " . $conn->error);
    }
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
    $stmt->close();

    // Ambil notifikasi dengan judul kampanye
    $sql = "SELECT k.*, c.judul FROM kolaborasi k JOIN campaign c ON k.id_campaign = c.id WHERE k.id_bisnis = ? AND k.status = 'pending' ORDER BY k.tanggal_pengajuan DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Gagal mempersiapkan query notifikasi: " . $conn->error);
    }
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    // Statistik
    $sql = "SELECT COUNT(*) as total, SUM(dana_terkumpul) as total_funds FROM campaign WHERE id_bisnis = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Gagal mempersiapkan query statistik: " . $conn->error);
    }
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($total_campaigns, $total_funds);
    $stmt->fetch();
    $stmt->close();

    $sql = "SELECT COUNT(*) FROM kolaborasi WHERE id_bisnis = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Gagal mempersiapkan query kolaborasi: " . $conn->error);
    }
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($pending_collab);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borsmen - Dashboard Bisnis</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/beranda.css?t=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <i class="bx bx-menu hamburger" style="display: none;"></i>
        <div class="sidebar">
            <div class="header">
                <div class="list-item">
                    <a href="#">
                        <i class="bx bxs-coffee-bean"></i>
                        <span class="description-header">Borsmen</span>
                    </a>
                </div>
                <div class="illustration">
                    <video class="animation-video" playsinline muted loop autoplay style="width: 100%; height: 50%; object-fit: cover;">
                        <source type="video/mp4" src="../assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story (1).mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <div class="tab">
                <div class="list-item active" data-target="dashboard">
                    <a href="beranda_business.php">
                        <i class="bx bxs-dashboard"></i>
                        <span class="description-header">Dashboard</span>
                    </a>
                </div>
                <div class="list-item" data-target="kampanye">
                    <a href="kampanye_saya.php">
                        <i class="bx bxs-megaphone"></i>
                        <span class="description-header">Kampanye Saya</span>
                    </a>
                </div>
                <div class="list-item" data-target="analitik">
                    <a href="analitik.php">
                        <i class="bx bxs-bar-chart-alt-2"></i>
                        <span class="description-header">Analitik</span>
                    </a>
                </div>
                <div class="list-item" data-target="escrow">
                    <a href="escrow.php">
                        <i class="bx bxs-wallet"></i>
                        <span class="description-header">Escrow</span>
                    </a>
                </div>
                <div class="list-item" data-target="pengaturan">
                    <a href="pengaturan.php">
                        <i class="bx bxs-cog"></i>
                        <span class="description-header">Pengaturan</span>
                    </a>
                </div>
                <div class="list-item">
                    <a href="api.php?action=logout">
                        <i class="bx bxs-log-out"></i>
                        <span class="description-header">Logout</span>
                    </a>
                </div>
            </div>
            <div class="profile">
                <?php
                $foto_profile_clean = $foto_profile ? basename($foto_profile) : '';
                $foto_path_fs = $foto_profile_clean ? __DIR__ . '/../Uploads/' . $foto_profile_clean : '';
                $foto_path_url = $foto_profile_clean ? '../Uploads/' . rawurlencode($foto_profile_clean) : '';
                ?>
                <?php if ($foto_profile_clean && file_exists($foto_path_fs)): ?>
                    <img src="<?php echo $foto_path_url; ?>" alt="Foto Profil" class="profile-img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
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
            <div id="content" class="content-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($foto_profile_clean && file_exists($foto_path_fs)): ?>
                            <img src="<?php echo $foto_path_url; ?>" alt="Foto Profil" class="profile-img" style="width: 48px; height: 48px; object-fit: cover; border-radius: 50%; margin-right:12px;">
                        <?php else: ?>
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; margin-right:12px;">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h4 style="margin-bottom:0;"><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                            <div style="font-size: 0.9rem; color: var(--text-light);">Bisnis</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="notification position-relative">
                            <i class="bx bxs-bell"></i>
                            <span class="badge"><?php echo count($notifications); ?></span>
                            <div class="notification-dropdown">
                                <?php if (empty($notifications)): ?>
                                    <div class="notification-item">Tidak ada notifikasi.</div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="notification-item">
                                            Kolaborasi baru pada <?php echo htmlspecialchars($notif['judul']); ?> (Status: <?php echo htmlspecialchars($notif['status']); ?>)
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="tambah_kampanye.php" class="btn btn-primary">+ Kampanye Baru</a>
                    </div>
                </div>

                <div class="mini-stats">
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-bar-chart-alt-2"></i></div>
                        <div class="mini-value"><?php echo $total_campaigns; ?></div>
                        <h5>Kampanye</h5>
                    </div>
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-wallet"></i></div>
                        <div class="mini-value">Rp<?php echo number_format($total_funds, 0, ',', '.'); ?></div>
                        <h5>Total Dana Kampanye</h5>
                    </div>
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-bell"></i></div>
                        <div class="mini-value"><?php echo $pending_collab; ?></div>
                        <h5>Kolaborasi Pending</h5>
                    </div>
                </div>

                <div class="grafik-box" style="margin-bottom:32px;">
                    <h5>Grafik Dana Terkumpul per Kampanye</h5>
                    <canvas id="grafikDana" height="120"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('grafikDana').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_column($campaigns, 'judul')); ?>,
                                datasets: [{
                                    label: 'Dana Terkumpul',
                                    data: <?php echo json_encode(array_map('floatval', array_column($campaigns, 'dana_terkumpul'))); ?>,
                                    backgroundColor: 'rgba(66, 165, 245, 0.7)'
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true } }
                            }
                        });
                    });
                </script>

                <h6>Kampanye Saya</h6>
                <?php if (empty($campaigns)): ?>
                    <p>Belum ada kampanye yang dibuat.</p>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="card-trend mb-3">
                            <h6><?php echo htmlspecialchars($campaign['judul']); ?></h6>
                            <small>Status: <?php echo htmlspecialchars($campaign['status']); ?> | Target: Rp<?php echo number_format($campaign['target_dana'], 0, ',', '.'); ?></small>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo ($campaign['target_dana'] > 0 ? ($campaign['dana_terkumpul'] / $campaign['target_dana'] * 100) : 0); ?>%"></div>
                                </div>
                                <small>Saat ini: <span class="text-success">Rp<?php echo number_format($campaign['dana_terkumpul'], 0, ',', '.'); ?></span></small>
                            </div>
                            <div style="margin-top:20px; margin-bottom:20px;">
                                <a href="detail_kampanye.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                                <a href="#" class="btn btn-danger btn-sm delete-campaign" data-id="<?php echo $campaign['id']; ?>">Hapus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
    <script>
        function setupSidebarAjax() {
            const menuLinks = document.querySelectorAll('.tab .list-item a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');
                    console.log('Mengklik link:', href);
                    if (href.endsWith('.php') && href !== 'api.php?action=logout') {
                        e.preventDefault();
                        fetch(href, { method: 'GET' })
                            .then(res => {
                                console.log('Status respons:', res.status, res.statusText);
                                if (!res.ok) {
                                    throw new Error(`Gagal memuat ${href}: ${res.statusText}`);
                                }
                                return res.text();
                            })
                            .then(html => {
                                console.log('Konten diterima:', html.substring(0, 100));
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newContent = doc.getElementById('content');
                                if (newContent) {
                                    document.getElementById('content').innerHTML = newContent.innerHTML;
                                    menuLinks.forEach(l => l.parentElement.classList.remove('active'));
                                    link.parentElement.classList.add('active');
                                    setupSidebarAjax();
                                    setupDeleteCampaign();
                                    setupDetailKampanyeAjax();
                                } else {
                                    console.error('Elemen #content tidak ditemukan di:', href);
                                }
                            })
                            .catch(err => console.error('Error fetching content:', err));
                    }
                });
            });
        }

        function setupDetailKampanyeAjax() {
            document.querySelectorAll('.btn-detail-kampanye').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    console.log('Mengklik detail kampanye:', url);
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => {
                            console.log('Status respons detail:', res.status, res.statusText);
                            if (!res.ok) {
                                throw new Error(`Gagal memuat ${url}: ${res.statusText}`);
                            }
                            return res.text();
                        })
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('content');
                            if (newContent) {
                                document.getElementById('content').innerHTML = newContent.innerHTML;
                                setupSidebarAjax();
                                setupDeleteCampaign();
                                setupDetailKampanyeAjax();
                            } else {
                                console.error('Elemen #content tidak ditemukan di:', url);
                            }
                        })
                        .catch(err => console.error('Error fetching detail:', err));
                });
            });
        }

        function setupDeleteCampaign() {
            document.querySelectorAll('.delete-campaign').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Apakah Anda yakin ingin menghapus kampanye ini?')) {
                        const campaignId = this.getAttribute('data-id');
                        console.log('Menghapus kampanye ID:', campaignId);
                        fetch('delete_kampanye.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id_campaign=${campaignId}`
                        })
                        .then(res => {
                            console.log('Status respons hapus:', res.status, res.statusText);
                            return res.json();
                        })
                        .then(data => {
                            alert(data.message);
                            if (data.status === 200) {
                                window.location.reload();
                            }
                        })
                        .catch(err => console.error('Error deleting campaign:', err));
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupSidebarAjax();
            setupDeleteCampaign();
            setupDetailKampanyeAjax();
        });
    </script>
</body>
</html>