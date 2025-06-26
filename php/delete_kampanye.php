<?php
session_start();
require_once '../database/koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 401, 'message' => 'Anda harus login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_campaign = intval($_POST['id_campaign'] ?? 0);
    $id_bisnis = $_SESSION['user_id'];

    // Pastikan kampanye milik user yang login
    $cek = $conn->prepare("SELECT id FROM campaign WHERE id = ? AND id_bisnis = ?");
    $cek->bind_param("ii", $id_campaign, $id_bisnis);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Hapus data escrow terkait campaign
        $del_escrow = $conn->prepare("DELETE FROM escrow WHERE id_campaign = ?");
        $del_escrow->bind_param("i", $id_campaign);
        $del_escrow->execute();
        $del_escrow->close();

        // Hapus data kolaborasi terkait campaign
        $del_kolaborasi = $conn->prepare("DELETE FROM kolaborasi WHERE id_campaign = ?");
        $del_kolaborasi->bind_param("i", $id_campaign);
        $del_kolaborasi->execute();
        $del_kolaborasi->close();

        // Hapus campaign
        $del = $conn->prepare("DELETE FROM campaign WHERE id = ?");
        $del->bind_param("i", $id_campaign);
        if ($del->execute()) {
            echo json_encode(['status' => 200, 'message' => 'Kampanye berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 500, 'message' => 'Gagal menghapus kampanye.']);
        }
        $del->close();
    } else {
        echo json_encode(['status' => 403, 'message' => 'Akses ditolak.']);
    }
    $cek->close();
    exit;
}

// Jika bukan POST
echo json_encode(['status' => 404, 'message' => 'Endpoint tidak ditemukan.']);
exit;