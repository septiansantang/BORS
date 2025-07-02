<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'business') {
    header("Location: login.php");
    exit();
}

require '../php/koneksi.php';

$id_bisnis = $_SESSION['user_id'] ?? 0;
$jumlah_kampanye = 0;
$total_dana = 0;
$rata_rata = 0;
$labels = [];
$data_dana_influencer = [];
$campaigns = [];

if ($id_bisnis) {
    // Hitung jumlah kampanye dan total dana terkumpul
    $sql = "SELECT COUNT(*) as jumlah, SUM(dana_terkumpul) as total FROM campaign WHERE id_bisnis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_bisnis);
    $stmt->execute();
    $stmt->bind_result($jumlah_kampanye, $total_dana);
    $stmt->fetch();
    $stmt->close();

    if ($jumlah_kampanye > 0) {
        $rata_rata = $total_dana / $jumlah_kampanye;
    }

    // Ambil data kampanye dan total komisi yang dibayarkan ke influencer
    $sql2 = "SELECT c.judul, SUM(k.komisi) as total_komisi 
             FROM campaign c 
             LEFT JOIN kolaborasi k ON c.id = k.id_campaign 
             WHERE c.id_bisnis = ? 
             GROUP BY c.id 
             ORDER BY c.created_at DESC";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $id_bisnis);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $labels[] = $row['judul'];
        $data_dana_influencer[] = $row['total_komisi'] ?? 0;
        $campaigns[] = $row;
    }
    $stmt2->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik Kampanye - Borsmen</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/beranda.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: linear-gradient(120deg, #e3f0ff 0%, #f8fbff 100%); min-height:100vh;">
    <div class="content-box" id="content">
        <div class="row">
            <div class="col">
                <div class="card-analitik">
                    <div class="badge-count"><?php echo $jumlah_kampanye; ?></div>
                    <h6>Jumlah Kampanye</h6>
                    <p><i class="fa fa-bullhorn" style="color:#42A5F5"></i></p>
                </div>
            </div>
            <div class="col">
                <div class="card-analitik">
                    <h6>Total Dana Terkumpul</h6>
                    <p>Rp<?php echo number_format($total_dana, 0, ',', '.'); ?></p>
                </div>
            </div>
            <div class="col">
                <div class="card-analitik">
                    <h6>Rata-rata Dana per Kampanye</h6>
                    <p>Rp<?php echo number_format($rata_rata, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="grafik-box" style="margin-bottom:32px;">
            <h5>Grafik Dana yang Dibayarkan ke Influencer per Kampanye</h5>
            <canvas id="grafikDanaInfluencer" height="120"></canvas>
            <div id="grafik-empty" style="display:none; color:#888; text-align:center; margin-top:24px;">
                Tidak ada data kampanye untuk ditampilkan.
            </div>
        </div>
    </div>

    <script>
    // Ambil data dari PHP
    const labels = <?php echo json_encode($labels); ?>;
    const dataDanaInfluencer = <?php echo json_encode($data_dana_influencer); ?>;

    // Debug: tampilkan data di console
    console.log('labels:', labels);
    console.log('dataDanaInfluencer:', dataDanaInfluencer);

    if (!labels.length || !dataDanaInfluencer.length) {
        document.getElementById('grafikDanaInfluencer').style.display = 'none';
        document.getElementById('grafik-empty').style.display = 'block';
    } else {
        const ctx = document.getElementById('grafikDanaInfluencer').getContext('2d');
        new Chart(ctx, {
            type: 'line', // Menggunakan model grafik yang sama seperti di beranda_business.php
            data: {
                labels: labels,
                datasets: [{
                    label: 'Dana yang Dibayarkan ke Influencer (Rp)',
                    data: dataDanaInfluencer,
                    borderColor: '#42A5F5',
                    backgroundColor: 'rgba(66, 165, 245, 0.2)',
                    borderWidth: 2,
                    tension: 0.4, // Membuat garis lebih halus
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Dana yang Dibayarkan ke Influencer (Rp)',
                            color: '#1976D2',
                            font: {weight: 'bold'}
                        },
                        ticks: {
                            color: '#1976D2',
                            font: {size: 13}
                        },
                        grid: {
                            color: '#e3eafc'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Kampanye',
                            color: '#1976D2',
                            font: {weight: 'bold'}
                        },
                        ticks: {
                            color: '#1976D2',
                            font: {size: 13}
                        },
                        grid: {
                            color: '#f0f4fa'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#1976D2',
                            font: {weight: 'bold'}
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1976D2',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#42A5F5',
                        borderWidth: 1
                    }
                }
            }
        });
    }
    </script>
</body>
</html>