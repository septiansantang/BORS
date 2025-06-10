<?php
session_start();

// Cek apakah pengguna sudah login dan adalah bisnis
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

// Data kampanye statis (bisa diganti dengan API call ke api.php?action=get_collaborations)
$trending_campaigns = [
    [
        'rank' => 1,
        'title' => 'Peluncuran Koleksi Musim Panas',
        'views' => '24.5K',
        'days_ago' => '3 hari yang lalu',
        'progress' => 85,
        'current_amount' => 12750000,
        'target_amount' => 15000000,
        'days_left' => 15
    ],
    [
        'rank' => 2,
        'title' => 'Showcase Produk Teknologi',
        'views' => '18.2K',
        'days_ago' => '5 hari yang lalu',
        'progress' => 73,
        'current_amount' => 18250000,
        'target_amount' => 25000000,
        'days_left' => 8
    ],
    [
        'rank' => 3,
        'title' => 'Promosi Spesial Liburan',
        'views' => '15.7K',
        'days_ago' => '2 hari yang lalu',
        'progress' => 92,
        'current_amount' => 9200000,
        'target_amount' => 10000000,
        'days_left' => 3
    ]
];

$active_campaigns = [
    [
        'title' => 'Peluncuran Koleksi Musim Panas',
        'days_left' => 15,
        'progress' => 85,
        'current_amount' => 12750000,
        'target_amount' => 15000000
    ],
    [
        'title' => 'Showcase Produk Teknologi',
        'days_left' => 8,
        'progress' => 73,
        'current_amount' => 18250000,
        'target_amount' => 25000000
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borsmen - Dashboard Bisnis</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/beranda.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="header">
                <div class="list-item">
                    <a href="#">
                        <i class='bx bxs-coffee-bean'></i>
                        <span class="description-header">Borsmen</span>
                    </a>
                </div>
                <div class="illustration">
                    <video class="animation-video" playsinline muted loop autoplay poster="assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story.jpg" style="width: 50%; height: 50%; object-fit: cover;">
                   <source type="video/mp4" src="../assets/Black Pink Animated Modern Beauty Fashion Influencer Blog Your Story (1).mp4">
                   Your browser does not support the video tag.
               </video>
                </div>
            </div>
            <div class="tab">
                <div class="list-item" data-target="dashboard">
                    <a href="#">
                        <i class='bx bxs-dashboard'></i>
                        <span class="description-header">Dashboard</span>
                    </a>
                </div>
                <div class="list-item" data-target="kampanye">
                    <a href="#">
                        <i class='bx bxs-megaphone'></i>
                        <span class="description-header">Kampanye Saya</span>
                    </a>
                </div>
                <div class="list-item" data-target="analitik">
                    <a href="#">
                        <i class='bx bxs-bar-chart-alt-2'></i>
                        <span class="description-header">Analitik</span>
                    </a>
                </div>
                <div class="list-item" data-target="escrow">
                    <a href="#">
                        <i class='bx bxs-wallet'></i>
                        <span class="description-header">Escrow</span>
                    </a>
                </div>
                <div class="list-item" data-target="pengaturan">
                    <a href="#">
                        <i class='bx bxs-cog'></i>
                        <span class="description-header">Pengaturan</span>
                    </a>
                </div>
                <div class="list-item">
                    <a href="api.php?action=logout">
                        <i class='bx bxs-log-out'></i>
                        <span class="description-header">Logout</span>
                    </a>
                </div>
            </div>
            <div class="profile">
                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="ms-2">
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Bisnis</div>
                </div>
            </div>
        </div>
        <div class="main">
            <div id="content" class="content-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Dashboard</h4>
                    <div class="d-flex align-items-center gap-3">
                        <div class="notification">
                            <i class='bx bxs-bell'></i>
                            <span class="badge">3</span>
                        </div>
                        <button class="btn btn-primary">+ Kampanye Baru</button>
                    </div>
                </div>

                <h6>Video Trending</h6>
                <div class="grid-container">
                    <?php foreach ($trending_campaigns as $campaign): ?>
                        <div class="card-trend">
                            <span class="badge bg-info">Trending#<?php echo $campaign['rank']; ?></span>
                            <h6><?php echo htmlspecialchars($campaign['title']); ?></h6>
                            <small><?php echo $campaign['views']; ?> tayangan • <?php echo htmlspecialchars($campaign['days_ago']); ?></small>
                            <div class="mt-2">
                                <div class="progress-label">Progres Kampanye</div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $campaign['progress']; ?>%"></div>
                                </div>
                                <small>Rp<?php echo number_format($campaign['current_amount'], 0, ',', '.'); ?> / Rp<?php echo number_format($campaign['target_amount'], 0, ',', '.'); ?> - <?php echo $campaign['days_left']; ?> hari lagi</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h6>Kampanye Aktif</h6>
                <?php foreach ($active_campaigns as $campaign): ?>
                    <div class="card-trend mb-3">
                        <h6><?php echo htmlspecialchars($campaign['title']); ?></h6>
                        <small>Berakhir dalam <?php echo $campaign['days_left']; ?> hari</small>
                        <div class="mt-2">
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $campaign['progress']; ?>%"></div>
                            </div>
                            <small>Target: Rp<?php echo number_format($campaign['target_amount'], 0, ',', '.'); ?><br>Saat ini: <span class="text-success">Rp<?php echo number_format($campaign['current_amount'], 0, ',', '.'); ?> (<?php echo $campaign['progress']; ?>%)</span></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
</body>
</html>
