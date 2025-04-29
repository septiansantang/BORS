<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Influencer</title>
    <link rel="stylesheet" href="../CSS/form.css">
</head>
<body>
    <div class="form-container">
        <form action="save_influencer.php" method="POST" enctype="multipart/form-data">
            <div class="flex-container">
                <!-- Data Diri Section -->
                <div class="form-section">
                    <h2>Data Diri</h2>
                    <label for="foto">Foto Profile</label>
                    <input type="file" name="foto_profile" id="foto" accept="image/*" onchange="previewImage(event)" required>
                    <img id="preview" src="#" alt="Preview Foto Profile" class="preview-img"/>

                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" placeholder="Nama" required>

                    <label for="nomor">Phone Number</label>
                    <input type="tel" name="nomor_hp" id="nomor" placeholder="Phone number" required>

                    <label for="tanggal">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal" required>

                    <label for="kota">Kota</label>
                    <select name="kota" id="kota" required>
                        <option value="">Pilih Kota</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Bandung">Bandung</option>
                        <option value="Surabaya">Surabaya</option>
                    </select>
                </div>

                <!-- Konten Section -->
                <div class="form-section">
                    <h2>Konten</h2>

                    <label for="pengenalan">Pengenalan Diri</label>
                    <textarea name="pengenalan" id="pengenalan" placeholder="Ceritakan tentang diri Anda" rows="5" required></textarea>

                    <label for="konten">Jenis Konten</label>
                    <select name="konten" id="konten" required>
                        <option value="">Pilih Jenis Konten</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Teknologi">Teknologi</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Travel">Travel</option>
                        <option value="Kuliner">Kuliner</option>
                    </select>

                    <label for="instagram">Instagram</label>
                    <input type="url" name="instagram" id="instagram" placeholder="Link Instagram" required>

                    <label for="tiktok">Tiktok</label>
                    <input type="url" name="tiktok" id="tiktok" placeholder="Link Tiktok" required>

                    <label for="youtube">Youtube</label>
                    <input type="url" name="youtube" id="youtube" placeholder="Link Youtube">

                    <label for="facebook">Facebook</label>
                    <input type="url" name="facebook" id="facebook" placeholder="Link Facebook">
                </div>
            </div>

            <button type="submit" class="btn">Kirim</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
