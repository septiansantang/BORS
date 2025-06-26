<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../database/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$campaigns = [];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Cek saldo escrow available
$saldo_escrow = 0;
if ($id_bisnis) {
    $sql = "SELECT SUM(jumlah) as total FROM escrow WHERE id_bisnis = ? AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($saldo_escrow);
    $stmt->fetch();
    $stmt->close();
    $saldo_escrow = $saldo_escrow ?: 0;
}

if ($id_bisnis) {
    $sql = "SELECT * FROM campaign WHERE id_bisnis = ?";
    if ($status_filter) {
        $sql .= " AND status = ?";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($status_filter) {
        $stmt->bind_param("isii", $id_bisnis, $status_filter, $limit, $offset);
    } else {
        $stmt->bind_param("iii", $id_bisnis, $limit, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
    $stmt->close();

    // Hitung total kampanye untuk pagination
    $sql_count = "SELECT COUNT(*) as total FROM campaign WHERE id_bisnis = ?";
    if ($status_filter) {
        $sql_count .= " AND status = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("is", $id_bisnis, $status_filter);
    } else {
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $id_bisnis);
    }
    $stmt_count->execute();
    $stmt_count->bind_result($total_campaigns);
    $stmt_count->fetch();
    $stmt_count->close();
    $total_pages = ceil($total_campaigns / $limit);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kampanye Saya - Borsmen</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/beranda.css">
</head>
<body>
    <div class="content-box" id="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Kampanye Saya</h4>
            <?php if ($saldo_escrow > 0): ?>
                <a href="tambah_kampanye.php" class="btn btn-primary">+ Kampanye Baru</a>
            <?php else: ?>
                <a href="escrow.php" class="btn btn-primary" style="background:#ffc107; color:#222;">Isi Dana Escrow Dulu</a>
            <?php endif; ?>
        </div>
        <div class="filter-box">
            <form method="GET">
                <label for="status">Filter Status:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="aktif" <?php echo $status_filter === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="batal" <?php echo $status_filter === 'batal' ? 'selected' : ''; ?>>Batal</option>
                </select>
            </form>
        </div>
        <?php if (empty($campaigns)): ?>
            <p>Belum ada kampanye yang dibuat.</p>
        <?php else: ?>
            <?php foreach ($campaigns as $campaign): ?>
                <?php
                // Ambil info escrow (total dana yang sudah dipotong untuk kampanye ini)
                $escrow_used = $conn->query("SELECT SUM(jumlah) as total FROM escrow WHERE id_campaign=" . intval($campaign['id']) . " AND status='used'");
                $escrow_used_row = $escrow_used ? $escrow_used->fetch_assoc() : ['total' => 0];
                $dana_escrow_kampanye = $escrow_used_row['total'] ?? 0;

                // Sisa dana escrow untuk kampanye ini (jika ada refund/penyesuaian)
                $escrow_available = $conn->query("SELECT SUM(jumlah) as total FROM escrow WHERE id_campaign=" . intval($campaign['id']) . " AND status='available'");
                $escrow_available_row = $escrow_available ? $escrow_available->fetch_assoc() : ['total' => 0];
                $sisa_dana = $escrow_available_row['total'] ?? 0;

                // Hitung total dibayar ke influencer (asumsi: dana_escrow_kampanye - sisa_dana)
                $total_dibayar = $dana_escrow_kampanye - $sisa_dana;
                if ($total_dibayar < 0) $total_dibayar = 0;
                ?>
                <div class="card-trend mb-3" style="border:1px solid #e3eafc; border-radius:10px; padding:18px 20px; margin-bottom:18px; background:#f8fbff;">
                    <h6 style="font-size:1.2em; color:#1976d2;"><?php echo htmlspecialchars($campaign['judul']); ?></h6>
                    <div style="margin-bottom:8px;">
                        <span class="badge" style="background:#e3f0ff; color:#1976d2; border-radius:6px; padding:2px 10px; font-size:0.95em;">
                            Status: <?php echo htmlspecialchars($campaign['status']); ?>
                        </span>
                    </div>
                    <div style="font-size:0.98em; margin-bottom:6px;">
                        <b>Pembayaran per <?php echo number_format($campaign['jumlah_view_unit'] ?? 0, 0, ',', '.'); ?> views:</b>
                        Rp<?php echo number_format($campaign['dana_per_view'] ?? 0, 0, ',', '.'); ?>
                    </div>
                    <div style="font-size:0.98em; margin-bottom:6px;">
                        <b>Dana Escrow untuk Kampanye:</b>
                        <span style="color:#388e3c;">Rp<?php echo number_format($dana_escrow_kampanye, 0, ',', '.'); ?></span>
                    </div>
                    <div style="font-size:0.98em; margin-bottom:6px;">
                        <b>Sisa Dana Escrow:</b>
                        <span style="color:#388e3c;">Rp<?php echo number_format($sisa_dana, 0, ',', '.'); ?></span>
                    </div>
                    <div style="font-size:0.98em; margin-bottom:6px;">
                        <b>Total Dibayar ke Influencer:</b>
                        <span style="color:#c62828;">Rp<?php echo number_format($total_dibayar, 0, ',', '.'); ?></span>
                    </div>
                    <div style="font-size:0.98em; margin-bottom:6px;">
                        <b>Target Dana:</b>
                        Rp<?php echo number_format($campaign['target_dana'] ?? 0, 0, ',', '.'); ?>
                        <br>
                        <b>Dana Terkumpul:</b>
                        Rp<?php echo number_format($campaign['dana_terkumpul'] ?? 0, 0, ',', '.'); ?>
                    </div>
                    <div class="progress" style="height:12px; background:#e3eafc; border-radius:6px; margin-bottom:8px;">
                        <div class="progress-bar" style="width: <?php echo ($campaign['target_dana'] > 0 ? ($campaign['dana_terkumpul'] / $campaign['target_dana'] * 100) : 0); ?>%; background:#1976d2; border-radius:6px;"></div>
                    </div>
                    <div style="margin-top:16px;">
                        <a href="detail_kampanye.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary btn-sm btn-detail-kampanye" data-id="<?php echo $campaign['id']; ?>" style="margin-right:8px;">Detail</a>
                        <a href="#" class="btn btn-danger btn-sm delete-campaign" data-id="<?php echo $campaign['id']; ?>">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="../JS/beranda.js"></script>
    <script>
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
                                setupDeleteCampaign();
                                setupDetailKampanyeAjax();
                            }
                        })
                        .catch(err => console.error('Error fetching detail:', err));
                });
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            setupDeleteCampaign();
            setupDetailKampanyeAjax();
        });
    </script>
</body>
</html>