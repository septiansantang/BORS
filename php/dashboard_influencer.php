<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'influencer') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "borsmen";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Ambil data profil influencer
$sql = "SELECT * FROM user_influencer WHERE username = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Gagal mempersiapkan query profil: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$profil = $result->fetch_assoc();
if (!$profil) {
    die("Data influencer tidak ditemukan untuk username: " . htmlspecialchars($username));
}
$stmt->close();

// Ambil data kampanye aktif
$sql_campaigns = "SELECT c.id, c.judul, c.deskripsi, c.target_dana, c.dana_terkumpul, c.tanggal_selesai 
                 FROM campaign c 
                 WHERE c.status = 'aktif' 
                 AND c.id NOT IN (SELECT id_campaign FROM kolaborasi WHERE id_influencer = ?) 
                 LIMIT 3";
$stmt = $conn->prepare($sql_campaigns);
if ($stmt === false) {
    die("Gagal mempersiapkan query kampanye: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$active_campaigns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data kampanye aktif untuk tab Kampanye Aktif (semua)
$sql_all_campaigns = "SELECT c.id, c.judul, c.deskripsi, c.target_dana, c.dana_terkumpul, c.tanggal_selesai 
                     FROM campaign c 
                     WHERE c.status = 'aktif' 
                     AND c.id NOT IN (SELECT id_campaign FROM kolaborasi WHERE id_influencer = ?)";
$stmt = $conn->prepare($sql_all_campaigns);
if ($stmt === false) {
    die("Gagal mempersiapkan query kampanye aktif: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$all_campaigns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data kampanye yang diikuti (konten saya)
$sql_my_campaigns = "SELECT c.judul, c.deskripsi, k.id as collaboration_id, k.status, k.tanggal_pengajuan 
                    FROM kolaborasi k 
                    JOIN campaign c ON k.id_campaign = c.id 
                    WHERE k.id_influencer = ?";
$stmt = $conn->prepare($sql_my_campaigns);
if ($stmt === false) {
    die("Gagal mempersiapkan query kampanye saya: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$my_campaigns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data konten yang diunggah
$sql_contents = "SELECT c.judul, ct.file_path, ct.link_konten, ct.status, ct.tanggal_upload 
                 FROM konten ct 
                 JOIN kolaborasi k ON ct.id_kolaborasi = k.id 
                 JOIN campaign c ON k.id_campaign = c.id 
                 WHERE k.id_influencer = ?";
$stmt = $conn->prepare($sql_contents);
if ($stmt === false) {
    die("Gagal mempersiapkan query konten: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$contents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data kolaborasi yang diterima untuk form unggah konten
$sql_accepted_collabs = "SELECT k.id as collaboration_id, c.judul 
                        FROM kolaborasi k 
                        JOIN campaign c ON k.id_campaign = c.id 
                        WHERE k.id_influencer = ? AND k.status = 'diterima'";
$stmt = $conn->prepare($sql_accepted_collabs);
if ($stmt === false) {
    die("Gagal mempersiapkan query kolaborasi diterima: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$accepted_collabs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data penghasilan
$sql_earnings = "SELECT SUM(k.komisi) as total, 
                       SUM(CASE WHEN MONTH(k.tanggal_disetujui) = MONTH(CURRENT_DATE()) 
                                AND YEAR(k.tanggal_disetujui) = YEAR(CURRENT_DATE()) 
                                THEN k.komisi ELSE 0 END) as this_month 
                 FROM kolaborasi k 
                 WHERE k.id_influencer = ? AND k.status = 'selesai'";
$stmt = $conn->prepare($sql_earnings);
if ($stmt === false) {
    die("Gagal mempersiapkan query penghasilan: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$earnings = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil data riwayat transaksi
$sql_transactions = "SELECT c.judul, k.tanggal_disetujui, k.komisi 
                     FROM kolaborasi k 
                     JOIN campaign c ON k.id_campaign = c.id 
                     WHERE k.id_influencer = ? AND k.status = 'selesai' 
                     ORDER BY k.tanggal_disetujui DESC";
$stmt = $conn->prepare($sql_transactions);
if ($stmt === false) {
    die("Gagal mempersiapkan query riwayat transaksi: " . $conn->error);
}
$stmt->bind_param("i", $profil['id']);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

// Tangani pesan feedback
$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borsmen - Dashboard Influencer</title>
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
                <div class="list-item" data-target="kampanye"><a href="#"><i class='bx bxs-megaphone'></i><span class="description-header">Kampanye Aktif</span></a></div>
                <div class="list-item" data-target="konten"><a href="#"><i class='bx bxs-video'></i><span class="description-header">Konten Saya</span></a></div>
                <div class="list-item" data-target="penghasilan"><a href="#"><i class='bx bxs-wallet'></i><span class="description-header">Penghasilan</span></a></div>
                <div class="list-item" data-target="pengaturan"><a href="#"><i class='bx bxs-cog'></i><span class="description-header">Pengaturan</span></a></div>
                <div class="list-item"><a href="api.php?action=logout"><i class='bx bxs-log-out'></i><span class="description-header">Logout</span></a></div>
            </div>
            <div class="profile">
                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="ms-2">
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Influencer</div>
                </div>
            </div>
        </div>
        <div class="main">
            <!-- Tombol Mengambang -->
            <?php if (!empty($accepted_collabs)): ?>
                <div class="floating-btn" onclick="openModal()">
                    <i class='bx bxs-cloud-upload'></i>
                </div>
            <?php endif; ?>
            <!-- Modal untuk Unggah Konten -->
            <div id="uploadModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <h4>Unggah Konten</h4>
                    <form action="api.php?action=upload_content" method="POST" id="uploadContentForm">
                        <div class="form-group mb-3">
                            <label>Pilih Kampanye</label>
                            <select name="collaboration_id" required>
                                <option value="">Pilih kampanye</option>
                                <?php foreach ($accepted_collabs as $collab): ?>
                                    <option value="<?php echo htmlspecialchars($collab['collaboration_id']); ?>">
                                        <?php echo htmlspecialchars($collab['judul']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Link Konten</label>
                            <input type="url" name="content_link" placeholder="Masukkan link konten (contoh: https://instagram.com/..)" required>
                            <div class="error"></div>
                        </div>
                        <button type="submit" class="btn-primary">Unggah</button>
                    </form>
                </div>
            </div>
            <div id="content" class="content-box">
                <?php if ($message === 'success' && $action === 'join_campaign'): ?>
                    <div class="alert alert-success">Berhasil bergabung dengan kampanye!</div>
                <?php elseif ($message === 'success' && $action === 'upload_content'): ?>
                    <div class="alert alert-success">Link konten berhasil diunggah!</div>
                <?php endif; ?>
                <!-- Konten Dashboard -->
                <div id="dashboard-content">
                    <h4>Dashboard</h4>
                    <h6>Profil Saya</h6>
                    <div class="card-trend mb-3">
                        <img src="../Uploads/<?php echo htmlspecialchars($profil['foto_profile'] ?? 'default.jpg'); ?>" class="profile-img" alt="Foto Profil">
                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($profil['name']); ?></p>
                        <p><strong>Kota:</strong> <?php echo htmlspecialchars($profil['kota'] ?? '-'); ?></p>
                        <p><strong>Konten:</strong> <?php echo htmlspecialchars($profil['konten'] ?? '-'); ?></p>
                        <p><strong>Pengenalan:</strong> <?php echo htmlspecialchars($profil['pengenalan'] ?? '-'); ?></p>
                    </div>

                    <h6>Kampanye yang Saya Ikuti</h6>
                    <div class="grid-container">
                        <?php if (empty($my_campaigns)): ?>
                            <p>Belum ada kampanye yang diikuti.</p>
                        <?php else: ?>
                            <?php foreach ($my_campaigns as $c): ?>
                                <div class="card-trend">
                                    <span class="badge <?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                                    <h6><?php echo htmlspecialchars($c['judul']); ?></h6>
                                    <small>Tanggal Pengajuan: <?php echo date('d M Y', strtotime($c['tanggal_pengajuan'])); ?></small>
                                    <div class="mt-2">
                                        <div class="progress-label">Status</div>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo htmlspecialchars($c['status']); ?>" style="width: <?php echo $c['status'] == 'selesai' ? 100 : ($c['status'] == 'diterima' ? 50 : 25); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h6>Ringkasan Penghasilan</h6>
                    <div class="grid-container">
                        <div class="card-trend"><h6>Bulan Ini</h6><strong>Rp<?php echo number_format($earnings['this_month'] ?? 0, 0, ',', '.'); ?></strong></div>
                        <div class="card-trend"><h6>Total</h6><strong>Rp<?php echo number_format($earnings['total'] ?? 0, 0, ',', '.'); ?></strong></div>
                    </div>

                    <h6>Kampanye Rekomendasi</h6>
                    <div class="grid-container">
                        <?php if (empty($active_campaigns)): ?>
                            <p>Belum ada kampanye aktif yang tersedia.</p>
                        <?php else: ?>
                            <?php foreach ($active_campaigns as $rec): ?>
                                <div class="card-trend">
                                    <h6><?php echo htmlspecialchars($rec['judul']); ?></h6>
                                    <small>Target Dana: Rp<?php echo number_format($rec['target_dana'], 0, ',', '.'); ?></small><br>
                                    <small>Tenggat: <?php echo date('d M Y', strtotime($rec['tanggal_selesai'])); ?></small>
                                    <button class="btn-primary mt-2" onclick="joinCampaign(<?php echo $rec['id']; ?>)">Bergabung</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Konten Kampanye Aktif -->
                <div id="kampanye-content" style="display: none;">
                    <h4>Kampanye Aktif</h4>
                    <div class="grid-container">
                        <?php if (empty($all_campaigns)): ?>
                            <p>Belum ada kampanye aktif yang tersedia.</p>
                        <?php else: ?>
                            <?php foreach ($all_campaigns as $c): ?>
                                <div class="card-trend">
                                    <h6><?php echo htmlspecialchars($c['judul']); ?></h6>
                                    <p><?php echo htmlspecialchars($c['deskripsi'] ?? 'Tidak ada deskripsi.'); ?></p>
                                    <small>Target Dana: Rp<?php echo number_format($c['target_dana'], 0, ',', '.'); ?></small><br>
                                    <small>Tenggat: <?php echo date('d M Y', strtotime($c['tanggal_selesai'])); ?></small>
                                    <button class="btn-primary mt-2" onclick="joinCampaign(<?php echo $c['id']; ?>)">Bergabung</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Konten Saya -->
                <div id="konten-content" style="display: none;">
                    <h4>Konten Saya</h4>
                    <h6>Kampanye yang Diikuti</h6>
                    <div class="grid-container">
                        <?php if (empty($my_campaigns)): ?>
                            <p>Belum ada kampanye yang diikuti.</p>
                        <?php else: ?>
                            <?php foreach ($my_campaigns as $c): ?>
                                <div class="card-trend">
                                    <h6><?php echo htmlspecialchars($c['judul']); ?></h6>
                                    <small>Status: <?php echo htmlspecialchars($c['status']); ?></small><br>
                                    <small>Tanggal Pengajuan: <?php echo date('d M Y', strtotime($c['tanggal_pengajuan'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <h6>Konten yang Diunggah</h6>
                    <div class="grid-container">
                        <?php if (empty($contents)): ?>
                            <p>Belum ada konten yang diunggah.</p>
                        <?php else: ?>
                            <?php foreach ($contents as $ct): ?>
                                <div class="card-trend">
                                    <h6><?php echo htmlspecialchars($ct['judul']); ?></h6>
                                    <small>Status: <?php echo htmlspecialchars($ct['status']); ?></small><br>
                                    <small>Tanggal Upload: <?php echo date('d M Y', strtotime($ct['tanggal_upload'])); ?></small>
                                    <?php if (!empty($ct['link_konten'])): ?>
                                        <a href="<?php echo htmlspecialchars($ct['link_konten']); ?>" target="_blank" class="btn-primary mt-2">Lihat Konten</a>
                                    <?php elseif (!empty($ct['file_path'])): ?>
                                        <a href="../Uploads/content/<?php echo htmlspecialchars($ct['file_path']); ?>" target="_blank" class="btn-primary mt-2">Lihat Konten</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Penghasilan -->
                <div id="penghasilan-content" style="display: none;">
                    <h4>Penghasilan</h4>
                    <div class="grid-container">
                        <div class="card-trend">
                            <h6>Penghasilan Bulan Ini</h6>
                            <strong>Rp<?php echo number_format($earnings['this_month'] ?? 0, 0, ',', '.'); ?></strong>
                        </div>
                        <div class="card-trend">
                            <h6>Total Penghasilan</h6>
                            <strong>Rp<?php echo number_format($earnings['total'] ?? 0, 0, ',', '.'); ?></strong>
                        </div>
                    </div>
                    <h6>Riwayat Transaksi</h6>
                    <?php if (empty($transactions)): ?>
                        <p>Belum ada transaksi.</p>
                    <?php else: ?>
                        <table class="transaction-table">
                            <thead>
                                <tr>
                                    <th>Kampanye</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $tx): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tx['judul']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($tx['tanggal_disetujui'])); ?></td>
                                        <td>Rp<?php echo number_format($tx['komisi'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Pengaturan -->
                <div id="pengaturan-content" style="display: none;">
                    <h4>Pengaturan</h4>
                    <form action="api.php?action=update_influencer" method="POST" enctype="multipart/form-data" class="form-group" id="profileForm">
                        <div class="mb-3">
                            <label>Nama</label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($profil['name']); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Nomor HP</label>
                            <input type="text" name="nomor_hp" value="<?php echo htmlspecialchars($profil['nomor_hp'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($profil['tanggal_lahir'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Kota</label>
                            <input type="text" name="kota" value="<?php echo htmlspecialchars($profil['kota'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Pengenalan</label>
                            <textarea name="pengenalan" required><?php echo htmlspecialchars($profil['pengenalan'] ?? ''); ?></textarea>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Konten</label>
                            <input type="text" name="konten" value="<?php echo htmlspecialchars($profil['konten'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Instagram</label>
                            <input type="url" name="instagram" value="<?php echo htmlspecialchars($profil['link_ig'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>TikTok</label>
                            <input type="url" name="tiktok" value="<?php echo htmlspecialchars($profil['link_tiktok'] ?? ''); ?>" required>
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>YouTube</label>
                            <input type="url" name="youtube" value="<?php echo htmlspecialchars($profil['link_youtube'] ?? ''); ?>">
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Facebook</label>
                            <input type="url" name="facebook" value="<?php echo htmlspecialchars($profil['link_fb'] ?? ''); ?>">
                            <div class="error"></div>
                        </div>
                        <div class="mb-3">
                            <label>Foto Profil</label>
                            <input type="file" name="foto_profile" accept="image/*">
                            <div class="error"></div>
                        </div>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../JS/beranda.js"></script>
    <script>
        function joinCampaign(campaignId) {
            if (confirm("Apakah Anda ingin bergabung dengan kampanye ini?")) {
                window.location.href = `api.php?action=join_campaign&id=${campaignId}`;
            }
        }

        // Validasi form Pengaturan
        document.getElementById('profileForm')?.addEventListener('submit', function(e) {
            let valid = true;
            const fields = {
                nama: { regex: /^.{1,255}$/, message: 'Nama tidak valid.' },
                nomor_hp: { regex: /^[0-9]{10,15}$/, message: 'Nomor HP tidak valid.' },
                tanggal_lahir: { regex: /^\d{4}-\d{2}-\d{2}$/, message: 'Tanggal lahir tidak valid.' },
                kota: { regex: /^.{1,50}$/, message: 'Kota tidak valid.' },
                pengenalan: { regex: /^.{1,}$/, message: 'Pengenalan tidak valid.' },
                konten: { regex: /^.{1,255}$/, message: 'Konten tidak valid.' },
                instagram: { regex: /^(https?:\/\/)?([\w-]+\.)+[\w-]+(\/[\w-]*)*$/, message: 'URL Instagram tidak valid.' },
                tiktok: { regex: /^(https?:\/\/)?([\w-]+\.)+[\w-]+(\/[\w-]*)*$/, message: 'URL TikTok tidak valid.' },
                youtube: { regex: /^(https?:\/\/)?([\w-]+\.)+[\w-]+(\/[\w-]*)*$|^$/, message: 'URL YouTube tidak valid.' },
                facebook: { regex: /^(https?:\/\/)?([\w-]+\.)+[\w-]+(\/[\w-]*)*$|^$/, message: 'URL Facebook tidak valid.' }
            };

            for (const [name, { regex, message }] of Object.entries(fields)) {
                const input = this.querySelector(`[name="${name}"]`);
                const error = input.nextElementSibling;
                if (input && !regex.test(input.value)) {
                    error.textContent = message;
                    error.style.display = 'block';
                    valid = false;
                } else if (error) {
                    error.textContent = '';
                    error.style.display = 'none';
                }
            }

            if (!valid) {
                e.preventDefault();
                alert('Silakan perbaiki kesalahan pada formulir.');
            }
        });

        // Fungsi untuk mengelola modal
        function openModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('uploadModal').style.display = 'none';
            document.getElementById('uploadContentForm').reset();
        }

        // Validasi form unggah konten
        document.getElementById('uploadContentForm')?.addEventListener('submit', function(e) {
        const linkInput = this.querySelector('[name="content_link"]');
        const error = linkInput.nextElementSibling;
        // Regex yang mendukung URL TikTok dengan @ dan parameter query
        const urlRegex = /^(https?:\/\/)([\w-]+\.)+[\w-]+(\/[\w@.-]*)*(\?[\w=&-]*)?$/;
    
        console.log('Link yang diinput:', linkInput.value); // Debugging
        if (!urlRegex.test(linkInput.value)) {
            error.textContent = 'Link konten tidak valid. Pastikan menggunakan link TikTok yang benar.';
            error.style.display = 'block';
            console.log('Validasi gagal untuk:', linkInput.value); // Debugging
            e.preventDefault();
        } else {
            error.textContent = '';
            error.style.display = 'none';
            console.log('Validasi berhasil untuk:', linkInput.value); // Debugging
        }
    });
    </script>
</body>
</html>