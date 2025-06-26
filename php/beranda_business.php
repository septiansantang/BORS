<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../database/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$campaigns = [];
$notifications = [];
$foto_profile = '';
$total_campaigns = 0;
$total_funds = 0;
$pending_collab = 0;

// Ambil data bisnis (foto_profile) dan notifikasi serta kampanye hanya sekali
if ($id_bisnis) {
    // Ambil foto_profile
    $sql_foto = "SELECT foto_profile FROM user_bisnis WHERE id = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    $stmt_foto->bind_param("i", $id_bisnis);
    $stmt_foto->execute();
    $stmt_foto->bind_result($foto_profile);
    $stmt_foto->fetch();
    $stmt_foto->close();

    // Ambil kampanye
    $sql = "SELECT * FROM campaign WHERE id_bisnis = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
    $stmt->close();

    // Ambil notifikasi
    $sql = "SELECT * FROM kolaborasi WHERE id_bisnis = ? AND status = 'pending' ORDER BY tanggal_pengajuan DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    // Statistik kecil
    $sql = "SELECT COUNT(*) as total, SUM(dana_terkumpul) as total_funds FROM campaign WHERE id_bisnis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($total_campaigns, $total_funds);
    $stmt->fetch();
    $stmt->close();

    $sql = "SELECT COUNT(*) FROM kolaborasi WHERE id_bisnis = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($pending_collab);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Borsmen - Dashboard Bisnis</title>
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../CSS/beranda.css">
        <!-- Tambahkan ini untuk Font Awesome jika ingin icon fa-bullhorn dll -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <style>
        .mini-stats { display: flex; gap: 18px; margin-bottom: 24px; }
        .mini-card {
            background: #f8fbff;
            border: 1px solid #e3eafc;
            border-radius: 10px;
            padding: 18px 18px;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(53,114,239,0.06);
        }
        .mini-card h5 { margin: 0 0 6px 0; font-size: 1.08em; color: #1976d2; }
        .mini-card .mini-value { font-size: 1.5em; font-weight: bold; color: #1565c0; }
        .mini-card .mini-icon { font-size: 1.5em; margin-bottom: 6px; color: #42A5F5; }
        @media (max-width: 700px) {
            .mini-stats { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
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
                <div class="list-item" data-target="dashboard">
                    <a href="beranda_business.php" id="menu-dashboard">
                        <i class="bx bxs-dashboard"></i>
                        <span class="description-header">Dashboard</span>
                    </a>
                </div>
                <div class="list-item" data-target="kampanye">
                    <a href="kampanye_saya.php" id="menu-kampanye">
                        <i class="bx bxs-megaphone"></i>
                        <span class="description-header">Kampanye Saya</span>
                    </a>
                </div>
                <div class="list-item" data-target="analitik">
                    <a href="analitik.php" id="menu-analitik">
                        <i class="bx bxs-bar-chart-alt-2"></i>
                        <span class="description-header">Analitik</span>
                    </a>
                </div>
                <div class="list-item" data-target="escrow">
                    <a href="escrow.php" id="menu-escrow">
                        <i class="bx bxs-wallet"></i>
                        <span class="description-header">Escrow</span>
                    </a>
                </div>
                <div class="list-item" data-target="pengaturan">
                    <a href="pengaturan.php" id="menu-pengaturan">
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
                // Perbaiki path dan pengecekan file foto profile
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
                        <?php
                        // Gunakan path yang sama untuk dashboard header
                        ?>
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
                                            Kolaborasi baru dari influencer ID <?php echo htmlspecialchars($notif['id_influencer']); ?> (Status: <?php echo htmlspecialchars($notif['status']); ?>)
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="tambah_kampanye.php" class="btn btn-primary">+ Kampanye Baru</a>
                    </div>
                </div>

                <!-- Mini statistik dashboard -->
                <div class="mini-stats">
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-bar-chart-alt-2"></i></div>
                        <div class="mini-value"><?php echo $total_campaigns; ?></div>
                        <h5>Kampanye</h5>
                    </div>
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-wallet"></i></div>
                        <div class="mini-value">Rp<?php echo number_format($total_funds, 0, ',', '.'); ?></div>
                        <h5>Total Dana</h5>
                    </div>
                    <div class="mini-card">
                        <div class="mini-icon"><i class="bx bxs-bell"></i></div>
                        <div class="mini-value"><?php echo $pending_collab; ?></div>
                        <h5>Kolaborasi Pending</h5>
                    </div>
                </div>

                <!-- Grafik Dana Terkumpul per Kampanye -->
                <div class="grafik-box" style="margin-bottom:32px;">
                    <h5>Grafik Dana Terkumpul per Kampanye</h5>
                    <canvas id="grafikDana" height="120"></canvas>
                </div>
                <script>
                    // Data grafik dari PHP
                    const labels = <?php echo json_encode(array_column($campaigns, 'judul')); ?>;
                    const dataDana = <?php echo json_encode(array_map('floatval', array_column($campaigns, 'dana_terkumpul'))); ?>;
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof Chart !== "undefined" && document.getElementById('grafikDana')) {
                            const ctx = document.getElementById('grafikDana').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Dana Terkumpul',
                                        data: dataDana,
                                        backgroundColor: 'rgba(66, 165, 245, 0.7)'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { display: false }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                    });
                </script>

                <h6>Kampanye Saya</h6>
                <?php if (empty($campaigns)): ?>
                    <p>Belum ada kampanye yang dibuat.</p>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="card-trend mb-3">
                            <?php
                            // Path foto kampanye
                            $foto_kampanye = isset($campaign['foto_kampanye']) ? $campaign['foto_kampanye'] : '';
                            // Perbaiki path: gunakan __DIR__ untuk path absolut, dan basename untuk URL
                            $foto_kampanye_fs = $foto_kampanye ? __DIR__ . '/../Uploads/' . $foto_kampanye : '';
                            $foto_kampanye_url = $foto_kampanye ? '../Uploads/' . rawurlencode(basename($foto_kampanye)) : '';
                            ?>
                            <?php if ($foto_kampanye && file_exists($foto_kampanye_fs)): ?>
                                <img src="<?php echo $foto_kampanye_url; ?>" alt="Foto Kampanye" style="width:100%;max-width:320px;height:140px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                            <?php else: ?>
                                <img src="../assets/default-campaign.jpg" alt="Default Kampanye" style="width:100%;max-width:320px;height:140px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                            <?php endif; ?>
                            <h6><?php echo htmlspecialchars($campaign['judul']); ?></h6>
                            <small>Status: <?php echo htmlspecialchars($campaign['status']); ?> | Target: Rp<?php echo number_format($campaign['target_dana'], 0, ',', '.'); ?></small>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo ($campaign['target_dana'] > 0 ? ($campaign['dana_terkumpul'] / $campaign['target_dana'] * 100) : 0); ?>%"></div>
                                </div>
                                <small>Saat ini: <span class="text-success">Rp<?php echo number_format($campaign['dana_terkumpul'], 0, ',', '.'); ?></span></small>
                            </div>
                            <div style="margin-top:20px; margin-bottom:20px;">
                                <a href="detail_kampanye.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary btn-sm btn-detail-kampanye" data-id="<?php echo $campaign['id']; ?>">Detail</a>
                                <a href="#" class="btn btn-danger btn-sm delete-campaign" data-id="<?php echo $campaign['id']; ?>">Hapus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pastikan script Chart.js pada analitik.php dijalankan ulang setelah AJAX load
        function runChartScriptIfAnalitikLoaded(html) {
            // Cari script Chart.js di hasil fetch analitik.php
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            // Ambil semua <script> yang ada di hasil AJAX
            doc.querySelectorAll('script').forEach(s => {
                // Jalankan hanya script yang mengandung 'Chart(' atau 'new Chart('
                if (s.textContent.includes('Chart(') || s.textContent.includes('new Chart(')) {
                    // Hapus canvas lama jika ada
                    const oldCanvas = document.getElementById('grafikDana');
                    if (oldCanvas) {
                        oldCanvas.remove();
                    }
                    // Sisipkan canvas baru dari hasil AJAX ke .grafik-box
                    const newContent = doc.getElementById('content');
                    if (newContent) {
                        const newCanvas = newContent.querySelector('#grafikDana');
                        const grafikBox = document.querySelector('.grafik-box');
                        if (newCanvas && grafikBox) {
                            grafikBox.appendChild(newCanvas.cloneNode(true));
                        }
                    }
                    // Jalankan ulang script Chart.js
                    setTimeout(() => { eval(s.textContent); }, 0);
                }
            });
        }

        function setupSidebarAjax() {
            const menuLinks = document.querySelectorAll('.tab .list-item a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');
                    if (href.endsWith('.php') && href !== 'api.php?action=logout') {
                        e.preventDefault();
                        fetch(href)
                            .then(res => res.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newContent = doc.getElementById('content');
                                if (newContent) {
                                    document.getElementById('content').innerHTML = newContent.innerHTML;
                                    setupSidebarAjax();
                                    setupDeleteCampaign && setupDeleteCampaign();
                                    setupDetailKampanyeAjax && setupDetailKampanyeAjax();
                                    // Jalankan ulang Chart.js jika analitik
                                    if (href.indexOf('analitik.php') !== -1) {
                                        runChartScriptIfAnalitikLoaded(html);
                                    }
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
                    fetch(url)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('content');
                            if (newContent) {
                                document.getElementById('content').innerHTML = newContent.innerHTML;
                                setupSidebarAjax && setupSidebarAjax();
                                setupDeleteCampaign && setupDeleteCampaign();
                                setupDetailKampanyeAjax();
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
                        fetch('delete_kampanye.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id_campaign=${campaignId}`
                        })
                        .then(res => res.json())
                        .then data => {
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