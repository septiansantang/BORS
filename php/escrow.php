<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../database/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$escrows = [];
$total_escrow = 0;

// Ambil total saldo escrow yang available
if ($id_bisnis) {
    $sql = "SELECT SUM(jumlah) as total FROM escrow WHERE id_bisnis = ? AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($total_escrow);
    $stmt->fetch();
    $stmt->close();

    // Ambil riwayat escrow
    $sql = "SELECT * FROM escrow WHERE id_bisnis = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $escrows[] = $row;
    }
    $stmt->close();
}
?>
<div class="content-box" id="content">
    <div class="escrow-saldo-box">
        <div class="escrow-saldo-info">
            <div class="escrow-saldo-title">Saldo Escrow Bisnis</div>
            <div class="escrow-saldo-label">Saldo Escrow Tersedia:</div>
            <div class="escrow-saldo-value">Rp<?php echo number_format($total_escrow, 0, ',', '.'); ?></div>
        </div>
        <a href="escrow_setor.php" class="escrow-saldo-btn">Tambah Dana ke Escrow</a>
    </div>

    <h5>Riwayat Dana Escrow</h5>
    <?php if (empty($escrows)): ?>
        <p>Belum ada transaksi escrow.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fbff;">
                        <th style="padding:10px;">Tanggal</th>
                        <th style="padding:10px;">Jumlah</th>
                        <th style="padding:10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($escrows as $escrow): ?>
                    <tr>
                        <td style="padding:10px;"><?php echo htmlspecialchars($escrow['created_at']); ?></td>
                        <td style="padding:10px;">Rp<?php echo number_format($escrow['jumlah'], 0, ',', '.'); ?></td>
                        <td style="padding:10px;">
                            <span class="badge" style="background:#e3f0ff; color:#1976d2; border-radius:6px; padding:2px 10px;">
                                <?php echo htmlspecialchars($escrow['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>