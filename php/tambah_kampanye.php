<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../php/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';
$judul = '';
$deskripsi = '';
$tanggal_mulai = '';
$tanggal_selesai = '';
$dana_kampanye = '';
$jumlah_view_unit = '';
$nominal_per_view = '';
$errors = [
    'judul' => '',
    'deskripsi' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'dana_escrow' => '',
    'jumlah_view_unit' => '',
    'nominal_per_view' => ''
];

// Cek saldo escrow available
$sql = "SELECT SUM(jumlah) as total FROM escrow WHERE id_bisnis = ? AND status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_bisnis);
$stmt->execute();
$stmt->bind_result($saldo_escrow);
$stmt->fetch();
$stmt->close();
$saldo_escrow = $saldo_escrow ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $dana_kampanye = floatval($_POST['dana_kampanye'] ?? 0);
    $jumlah_view_unit = intval($_POST['jumlah_view_unit'] ?? 0);
    $nominal_per_view = floatval($_POST['nominal_per_view'] ?? 0);

    // Validasi sederhana
    if (!$judul) $errors['judul'] = 'Judul wajib diisi';
    if (!$deskripsi) $errors['deskripsi'] = 'Deskripsi wajib diisi';
    if (!$tanggal_mulai) $errors['tanggal_mulai'] = 'Tanggal mulai wajib diisi';
    if (!$tanggal_selesai) $errors['tanggal_selesai'] = 'Tanggal selesai wajib diisi';
    if ($dana_kampanye <= 0) $errors['dana_escrow'] = 'Dana kampanye harus lebih dari 0';
    if ($jumlah_view_unit <= 0) $errors['jumlah_view_unit'] = 'Jumlah view unit harus lebih dari 0';
    if ($nominal_per_view <= 0) $errors['nominal_per_view'] = 'Nominal per view harus lebih dari 0';

    if ($dana_kampanye > $saldo_escrow) {
        $errors['dana_escrow'] = "Saldo escrow tidak mencukupi. Silakan setor dana ke escrow terlebih dahulu.";
    }

    // Jika tidak ada error, proses simpan kampanye
    if (!array_filter($errors)) {
        $stmt = $conn->prepare("INSERT INTO campaign (id_bisnis, judul, deskripsi, tanggal_mulai, tanggal_selesai, target_dana, dana_terkumpul, jumlah_view_unit, dana_per_view, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif', NOW())");
        $stmt->bind_param("issssddii", $id_bisnis, $judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, $dana_kampanye, $dana_kampanye, $jumlah_view_unit, $nominal_per_view);
        if ($stmt->execute()) {
            $id_campaign = $stmt->insert_id;
            $stmt->close();

            // Potong saldo escrow (dari baris available paling lama)
            $sisa = $dana_kampanye;
            $sql = "SELECT id, jumlah FROM escrow WHERE id_bisnis = ? AND status = 'available' ORDER BY id ASC";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("i", $id_bisnis);
            $stmt2->execute();
            $result = $stmt2->get_result();
            if ($result) {
                while (($row = $result->fetch_assoc()) && $sisa > 0) {
                    $eid = $row['id'];
                    $ejml = $row['jumlah'];
                    if ($ejml > $sisa) {
                        $conn->query("UPDATE escrow SET jumlah = jumlah - $sisa WHERE id = $eid");
                        $sisa = 0;
                    } else {
                        $conn->query("UPDATE escrow SET jumlah = 0, status = 'used' WHERE id = $eid");
                        $sisa -= $ejml;
                    }
                }
            }
            $stmt2->close();

            // Catat pemotongan escrow untuk kampanye ini (history, opsional)
            $stmt2 = $conn->prepare("INSERT INTO escrow (id_campaign, id_bisnis, jumlah, status, created_at) VALUES (?, ?, ?, 'used', NOW())");
            if ($stmt2) {
                $stmt2->bind_param("iid", $id_campaign, $id_bisnis, $dana_kampanye);
                $stmt2->execute();
                $stmt2->close();
            }

            $success = "Kampanye berhasil dibuat dan dana escrow telah dipotong.";
            // Reset form
            $judul = $deskripsi = $tanggal_mulai = $tanggal_selesai = $dana_kampanye = $jumlah_view_unit = $nominal_per_view = '';
        } else {
            $error = "Gagal membuat kampanye.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Kampanye</title>
  <link rel="stylesheet" href="../CSS/tambah_kampanye.css">
</head>
<body>
  <div class="form-container">
    <form method="POST" enctype="multipart/form-data">
      <h2>Tambah Kampanye</h2>
      <?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
      <?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>

      <label>Judul Kampanye:</label>
      <input type="text" name="judul" value="<?= htmlspecialchars($judul) ?>" required>
      <div class="field-error"><?= $errors['judul'] ?></div>

      <label>Deskripsi:</label>
      <textarea name="deskripsi" required><?= htmlspecialchars($deskripsi) ?></textarea>
      <div class="field-error"><?= $errors['deskripsi'] ?></div>

      <label>Tanggal Mulai:</label>
      <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" required>
      <div class="field-error"><?= $errors['tanggal_mulai'] ?></div>

      <label>Tanggal Selesai:</label>
      <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai) ?>" required>
      <div class="field-error"><?= $errors['tanggal_selesai'] ?></div>

      <label>Dana Kampanye (Rp):</label>
      <input type="number" name="dana_kampanye" value="<?= htmlspecialchars($dana_kampanye) ?>" required>
      <div class="field-error"><?= $errors['dana_escrow'] ?></div>

      <label>Pembayaran per (jumlah view):</label>
      <input type="number" name="jumlah_view_unit" value="<?= htmlspecialchars($jumlah_view_unit) ?>" placeholder="Contoh: 1000000" required>
      <div class="field-error"><?= $errors['jumlah_view_unit'] ?></div>

      <label>Jumlah Dana untuk unit tersebut (Rp):</label>
      <input type="number" name="nominal_per_view" value="<?= htmlspecialchars($nominal_per_view) ?>" placeholder="Contoh: 500000" required>
      <div class="field-error"><?= $errors['nominal_per_view'] ?></div>

      <br><input type="submit" value="Simpan Kampanye">
      <a href="beranda_business.php" class="btn-cancel">Batal dan Kembali</a>
    </form>
    <?php if ($saldo_escrow <= 0): ?>
      <div class="alert alert-danger" style="margin-top:18px;">
        Anda belum memiliki saldo escrow. <a href="beranda_business.php">Setor dana ke escrow</a> terlebih dahulu untuk membuat kampanye.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

