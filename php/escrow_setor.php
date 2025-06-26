<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../database/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jumlah'])) {
    $jumlah = floatval($_POST['jumlah']);
    if ($jumlah > 0) {
        if ($id_bisnis > 0) {
            $null = null;
            $stmt = $conn->prepare("INSERT INTO escrow (id_campaign, id_bisnis, jumlah, status, created_at) VALUES (?, ?, ?, 'available', NOW())");
            if (!$stmt) {
                $error = "Gagal menyiapkan query: " . $conn->error;
            } else {
                $stmt->bind_param("iid", $null, $id_bisnis, $jumlah);
                if ($stmt->execute()) {
                    $success = "Dana berhasil ditambahkan ke escrow.";
                } else {
                    $error = "Gagal menambah dana ke escrow: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $error = "ID bisnis tidak valid. Silakan login ulang.";
        }
    } else {
        $error = "Nominal dana harus lebih dari 0.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Dana ke Escrow</title>
    <link rel="stylesheet" href="../CSS/escrow_setor.css">
</head>
<body>
<div class="form-container">
    <h4>Tambah Dana ke Escrow</h4>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" class="mb-4" style="margin-bottom:24px;">
        <div class="form-group">
            <label for="jumlah">Nominal Dana</label>
            <input type="number" name="jumlah" id="jumlah" min="10000" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Setor ke Escrow</button>
        <a href="beranda_business.php" class="btn btn-secondary btn-sm" style="margin-left:8px;">Kembali ke Dashboard</a>
    </form>
</div>
</body>
</html>
