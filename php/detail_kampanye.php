<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../database/koneksi.php';

$id_campaign = intval($_GET['id'] ?? 0);
$id_bisnis = $_SESSION['user_id'] ?? 0;

// Handle approve/decline action
if (isset($_GET['action'], $_GET['kolab_id']) && in_array($_GET['action'], ['approve', 'decline'])) {
    $kolab_id = intval($_GET['kolab_id']);
    $action = $_GET['action'] === 'approve' ? 'diterima' : 'ditolak';
    $stmt = $conn->prepare("UPDATE kolaborasi SET status = ? WHERE id = ? AND id_campaign = ? LIMIT 1");
    $stmt->bind_param("sii", $action, $kolab_id, $id_campaign);
    $stmt->execute();
    $stmt->close();
    header("Location: detail_kampanye.php?id=" . $id_campaign);
    exit;
}

// Ambil data kampanye
$stmt = $conn->prepare("SELECT * FROM campaign WHERE id = ? AND id_bisnis = ?");
$stmt->bind_param("ii", $id_campaign, $id_bisnis);
$stmt->execute();
$result = $stmt->get_result();
$campaign = $result->fetch_assoc();
$stmt->close();

if (!$campaign) {
    echo '<div class="content-box" id="content"><p>Kampanye tidak ditemukan atau Anda tidak berhak mengaksesnya.</p></div>';
    exit;
}

// Ambil influencer yang ikut kampanye (kolaborasi diterima)
$influencers = [];
$stmt = $conn->prepare("SELECT k.*, u.name, u.username, u.email FROM kolaborasi k JOIN user_influencer u ON k.id_influencer = u.id WHERE k.id_campaign = ? AND k.status = 'diterima'");
$stmt->bind_param("i", $id_campaign);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $influencers[] = $row;
}
$stmt->close();

// Ambil influencer yang mendaftar (pending)
$pending_influencers = [];
$stmt = $conn->prepare("SELECT k.*, u.name, u.username, u.email FROM kolaborasi k JOIN user_influencer u ON k.id_influencer = u.id WHERE k.id_campaign = ? AND k.status = 'pending'");
$stmt->bind_param("i", $id_campaign);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_influencers[] = $row;
}
$stmt->close();

// Ambil total dana escrow untuk kampanye ini
$stmt = $conn->prepare("SELECT SUM(jumlah) as total_escrow FROM escrow WHERE id_campaign = ? AND status = 'available'");
$stmt->bind_param("i", $id_campaign);
$stmt->execute();
$stmt->bind_result($total_escrow);
$stmt->fetch();
$stmt->close();
$total_escrow = $total_escrow ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kampanye | BORS</title>
    <link rel="stylesheet" type="text/css" href="../CSS/detail_kampanye.css">
</head>
<body>
    <div class="content-box" id="content">
        <h4>Detail Kampanye: <?php echo htmlspecialchars($campaign['judul']); ?></h4>
        <div class="campaign-info">
            <b>Status</b><span><?php echo htmlspecialchars($campaign['status']); ?></span>
            <b>Target Dana</b><span>Rp<?php echo number_format($campaign['target_dana'], 0, ',', '.'); ?></span>
            <b>Dana Terkumpul</b><span>Rp<?php echo number_format($campaign['dana_terkumpul'], 0, ',', '.'); ?></span>
            <b>Sisa Dana Escrow</b><span class="escrow">Rp<?php echo number_format($total_escrow, 0, ',', '.'); ?></span>
        </div>
        <h5>Influencer yang Sudah Diterima</h5>
        <?php if (empty($influencers)): ?>
            <p>Belum ada influencer yang diterima.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Tanggal Gabung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($influencers as $inf): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inf['name']); ?></td>
                        <td><?php echo htmlspecialchars($inf['username']); ?></td>
                        <td><?php echo htmlspecialchars($inf['email']); ?></td>
                        <td><?php echo htmlspecialchars($inf['tanggal_pengajuan']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h5>Influencer yang Mendaftar</h5>
        <?php if (empty($pending_influencers)): ?>
            <p>Tidak ada influencer yang mendaftar.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_influencers as $inf): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inf['name']); ?></td>
                        <td><?php echo htmlspecialchars($inf['username']); ?></td>
                        <td><?php echo htmlspecialchars($inf['email']); ?></td>
                        <td><?php echo htmlspecialchars($inf['tanggal_pengajuan']); ?></td>
                        <td>
                            <a href="?id=<?php echo $id_campaign; ?>&action=approve&kolab_id=<?php echo $inf['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui influencer ini?')">Setujui</a>
                            <a href="?id=<?php echo $id_campaign; ?>&action=decline&kolab_id=<?php echo $inf['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak influencer ini?')">Tolak</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="beranda_business.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</body>
</html>