-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 01:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `borsmen`
--

-- --------------------------------------------------------

--
-- Table structure for table `campaign`
--

CREATE TABLE `campaign` (
  `id` int(11) NOT NULL,
  `id_bisnis` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `target_dana` decimal(15,2) NOT NULL,
  `dana_terkumpul` decimal(15,2) DEFAULT 0.00,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('aktif','selesai','batal') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jumlah_view_unit` int(11) NOT NULL DEFAULT 0,
  `dana_per_view` decimal(15,2) NOT NULL DEFAULT 0.00,
  `foto_kampanye` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaign`
--

INSERT INTO `campaign` (`id`, `id_bisnis`, `judul`, `deskripsi`, `target_dana`, `dana_terkumpul`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`, `jumlah_view_unit`, `dana_per_view`, `foto_kampanye`) VALUES
(3, 18, 'Kampanye Test', 'Deskripsi test', 1000000.00, 0.00, '2025-06-01', '2025-12-31', 'aktif', '2025-06-21 08:46:38', '2025-06-21 08:46:38', 0, 0.00, NULL),
(4, 18, 'Kampanye Test', 'Deskripsi test', 1000000.00, 0.00, '2025-06-01', '2025-12-31', 'aktif', '2025-06-21 08:49:32', '2025-06-21 08:49:32', 0, 0.00, NULL),
(5, 18, 'Kampanye Promosi Produk', 'Promosi produk makanan sehat', 2000000.00, 0.00, '2025-07-01', '2025-12-01', 'aktif', '2025-06-21 08:57:55', '2025-06-21 08:57:55', 0, 0.00, NULL),
(6, 17, 'Kampanye Sosial Media', 'Kampanye untuk meningkatkan brand awareness', 1500000.00, 0.00, '2025-06-15', '2025-11-30', 'aktif', '2025-06-21 08:57:55', '2025-06-23 02:06:32', 0, 0.00, NULL),
(7, 18, 'Kampanye Event Lokal', 'Event komunitas lokal untuk bisnis', 3000000.00, 0.00, '2025-08-01', '2025-12-15', 'selesai', '2025-06-21 08:57:55', '2025-06-24 07:25:01', 0, 0.00, NULL),
(28, 17, 'Kampanye Fashion Musim Panas', 'Promosikan koleksi pakaian musim panas terbaru kami di media sosial.', 50000000.00, 0.00, '0000-00-00', '2025-07-01', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(29, 17, 'Luncurkan Produk Skincare Baru', 'Buat konten untuk memperkenalkan produk skincare ramah lingkungan.', 30000000.00, 0.00, '0000-00-00', '2025-07-05', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(30, 17, 'Kampanye Minuman Sehat', 'Promosikan minuman sehat dengan bahan alami di Instagram dan TikTok.', 25000000.00, 0.00, '0000-00-00', '2025-07-03', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(31, 17, 'Event Kuliner Lokal', 'Undang audiens untuk event kuliner lokal dengan konten video.', 40000000.00, 0.00, '0000-00-00', '2025-06-30', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(32, 17, 'Peluncuran Aplikasi Mobile', 'Buat konten tentang aplikasi mobile baru untuk produktivitas.', 60000000.00, 0.00, '0000-00-00', '2025-07-10', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(33, 17, 'Kampanye Perhiasan Eksklusif', 'Promosikan koleksi perhiasan limited edition.', 35000000.00, 0.00, '0000-00-00', '2025-07-02', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(34, 17, 'Wisata Alam Bersama', 'Buat konten untuk mempromosikan paket wisata alam.', 45000000.00, 0.00, '0000-00-00', '2025-07-07', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(35, 17, 'Kampanye Kebugaran', 'Promosikan alat fitness baru melalui video latihan.', 20000000.00, 0.00, '0000-00-00', '2025-06-29', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(36, 17, 'Produk Elektronik Inovatif', 'Tampilkan fitur produk elektronik terbaru di media sosial.', 70000000.00, 0.00, '0000-00-00', '2025-07-15', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(37, 17, 'Kampanye Amal Pendidikan', 'Dukung pendidikan anak melalui konten inspiratif.', 55000000.00, 0.00, '0000-00-00', '2025-07-08', 'aktif', '2025-06-23 08:00:49', '2025-06-23 08:00:49', 0, 0.00, NULL),
(40, 17, 'Kampanye Tas', 'qdaksjdsajdhsakhjd', 5000000.00, 5000000.00, '2025-05-05', '2026-07-01', 'aktif', '2025-07-01 10:54:28', '2025-07-01 10:55:48', 100000, 50000.00, NULL),
(41, 17, 'Kampanye sepatu', 'ajsdnkasjdkjan', 15000000.00, 15000000.00, '2025-06-01', '2025-08-01', 'aktif', '2025-07-01 11:00:47', '2025-07-01 11:00:47', 100000, 1000000.00, NULL),
(42, 17, 'Kampanye Laptop Asus', 'laptop', 12500000.00, 12500000.00, '2025-06-03', '2025-08-01', 'aktif', '2025-07-02 03:52:26', '2025-07-02 03:52:26', 100000, 1000000.00, NULL),
(43, 17, 'Kampanye Nasi puyung', 'nasi aslkdnalsknd', 10000000.00, 10000000.00, '2025-06-03', '2025-08-01', 'aktif', '2025-07-02 07:34:53', '2025-07-02 07:34:53', 50000, 100000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `escrow`
--

CREATE TABLE `escrow` (
  `id` int(11) NOT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `id_bisnis` int(11) NOT NULL,
  `jumlah` double NOT NULL,
  `status` varchar(32) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `escrow`
--

INSERT INTO `escrow` (`id`, `id_campaign`, `id_bisnis`, `jumlah`, `status`, `created_at`) VALUES
(1, NULL, 17, 47500000, 'available', '2025-07-01 16:15:11'),
(3, 40, 17, 5000000, 'used', '2025-07-01 17:54:28'),
(4, 41, 17, 15000000, 'used', '2025-07-01 18:00:47'),
(5, 42, 17, 12500000, 'used', '2025-07-02 10:52:26'),
(6, 43, 17, 10000000, 'used', '2025-07-02 14:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `kolaborasi`
--

CREATE TABLE `kolaborasi` (
  `id` int(11) NOT NULL,
  `id_influencer` int(11) NOT NULL,
  `id_bisnis` int(11) NOT NULL,
  `detail_kolaborasi` text DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `komisi` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','diterima','ditolak','selesai') DEFAULT 'pending',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_disetujui` timestamp NULL DEFAULT NULL,
  `alasan_penolakan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kolaborasi`
--

INSERT INTO `kolaborasi` (`id`, `id_influencer`, `id_bisnis`, `detail_kolaborasi`, `id_campaign`, `komisi`, `status`, `tanggal_pengajuan`, `tanggal_disetujui`, `alasan_penolakan`) VALUES
(7, 5, 18, NULL, 6, 0.00, 'pending', '2025-06-21 08:58:55', NULL, NULL),
(8, 5, 18, NULL, 4, 1000000.00, 'selesai', '2025-06-21 10:01:04', '2025-06-23 07:32:52', NULL),
(9, 5, 18, NULL, 7, 1500000.00, 'selesai', '2025-06-21 10:02:00', '2025-06-24 07:26:08', NULL),
(10, 5, 18, NULL, 3, 0.00, 'diterima', '2025-06-23 02:28:37', NULL, NULL),
(11, 5, 17, NULL, 31, 1000000.00, 'selesai', '2025-06-24 07:19:36', '2025-06-30 14:07:36', NULL),
(12, 5, 17, NULL, 29, 100000.00, 'selesai', '2025-06-24 07:19:49', '2025-07-01 15:39:53', NULL),
(13, 5, 18, NULL, 5, 0.00, 'pending', '2025-06-30 13:03:57', NULL, NULL),
(14, 5, 17, NULL, 30, 1500000.00, 'selesai', '2025-06-30 14:32:15', '2025-07-02 08:13:44', NULL),
(15, 5, 17, NULL, 41, 1500000.00, 'selesai', '2025-07-01 11:01:18', '2025-07-01 11:37:12', NULL),
(16, 5, 17, NULL, 42, 1500000.00, 'selesai', '2025-07-02 03:52:49', '2025-07-02 04:36:10', NULL),
(17, 5, 17, NULL, 43, 500000.00, 'selesai', '2025-07-02 07:35:17', '2025-07-02 07:38:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `konten`
--

CREATE TABLE `konten` (
  `id` int(11) NOT NULL,
  `id_kolaborasi` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `link_konten` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konten`
--

INSERT INTO `konten` (`id`, `id_kolaborasi`, `file_path`, `status`, `tanggal_upload`, `link_konten`) VALUES
(2, 8, NULL, 'disetujui', '2025-06-23 02:35:45', 'https://www.tiktok.com/@makanmoloe_/video/7383295327933107461?is_from_webapp=1&sender_device=pc&web_id=7522328277206402568'),
(3, 9, NULL, 'disetujui', '2025-06-24 07:22:23', 'https://www.instagram.com/'),
(4, 11, NULL, 'disetujui', '2025-06-30 13:06:04', 'https://www.instagram.com/'),
(5, 15, NULL, 'disetujui', '2025-07-01 11:30:37', 'https://www.instagram.com/'),
(6, 12, NULL, 'disetujui', '2025-07-01 15:39:23', 'https://www.instagram.com/'),
(7, 16, NULL, 'disetujui', '2025-07-02 04:34:20', 'https://www.tiktok.com/@makanmoloe_/video/7383295327933107461?is_from_webapp=1&amp;web_id=7522328277206402568'),
(8, 17, NULL, 'disetujui', '2025-07-02 07:37:45', 'https://www.tiktok.com/@kulineranyuk_2/video/7447555252913491218?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7522328277206402568'),
(9, 14, NULL, 'disetujui', '2025-07-02 08:13:07', 'https://www.tiktok.com/@machelwie_/video/7500924288707398943?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7522328277206402568');

-- --------------------------------------------------------

--
-- Table structure for table `user_admin`
--

CREATE TABLE `user_admin` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_admin`
--

INSERT INTO `user_admin` (`id`, `name`, `username`, `password`, `email`) VALUES
(1, 'admin', 'admin1', '$2y$10$FVwM0e.n9gXklsZPoAllu.Lsgl.HvBrjaluDxM3P/fpo4bZc41xM6', 'admin1@borsmen.com');

-- --------------------------------------------------------

--
-- Table structure for table `user_bisnis`
--

CREATE TABLE `user_bisnis` (
  `id` int(11) NOT NULL,
  `nama_bisnis` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_bisnis`
--

INSERT INTO `user_bisnis` (`id`, `nama_bisnis`, `username`, `password`, `email`, `foto_profile`, `website`, `nomor_telepon`, `deskripsi`) VALUES
(15, 'nasi puyung drice', 'qweqweqwe', '$2y$10$p99rXNH510EmMxX25GVVK.Op3wmR.z650Tuo6/EyBN8Ump4i48mUO', 'qweqwe@gmail.com', '68476f98c7d09_2021-12-21 (1).png', 'https://www.instagram.com/', '08123712312', 'nasi puyasdunasjn'),
(16, 'aldi burger', 'aldiklisman', '$2y$10$0ShJfgNLLN8A9krKMKR6duR1FCJHzS0kylIYD1uigEBDFeRCF295W', 'aldi@gmail.com', '684770796c997_2021-12-21 (1).png', 'https://www.instagram.com/', '08123213', 'kedai burger'),
(17, 'kekalik fc', 'septdwi', '$2y$10$oDlu6JKVT2iIaer7dShhfeb5bRJ4UR1sSBn2oUf3dGYpwFjDy44.K', 'septiansantang@gmail.com', '6847f9e1c2ec3_2021-12-21 (6).png', 'https://www.instagram.com/', '08234234', 'nasi puyung mataram'),
(18, 'Test Bisnis', 'testbiz', 'hashedpassword', 'biz@example.com', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_influencer`
--

CREATE TABLE `user_influencer` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `link_ig` varchar(255) DEFAULT NULL,
  `link_fb` varchar(255) DEFAULT NULL,
  `link_tiktok` varchar(255) DEFAULT NULL,
  `link_youtube` varchar(255) DEFAULT NULL,
  `konten` varchar(255) DEFAULT NULL,
  `pengenalan` text DEFAULT NULL,
  `nomor_hp` varchar(15) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_influencer`
--

INSERT INTO `user_influencer` (`id`, `name`, `username`, `password`, `email`, `foto_profile`, `link_ig`, `link_fb`, `link_tiktok`, `link_youtube`, `konten`, `pengenalan`, `nomor_hp`, `tanggal_lahir`, `kota`) VALUES
(4, 'septian dwi saputra', 'septiandwi', '$2y$10$9gYgjN3TiEhMesiSAlwA0uS7sj8wbICVTCjqDnoXYvISRHOzeIF1W', 'septian@gmail.com', '68477442ec31f.png', 'https://www.instagram.com/', 'https://www.instagram.com/', 'https://www.instagram.com/', 'https://www.instagram.com/', 'Kesehatan', 'konter kreator newbie', '12312312', '2004-02-03', 'Surabaya'),
(5, 'Mohammad Klisman ', 'aldi', '$2y$10$pLNPzpf.RYBIFwu9oC7fOOhL4iY/z1Qv2fb6wK5kMt8g31aoGDPhG', 'aldiklisman4@gmail.com', '685651956fd7b.png', 'https://www.instagram.com/', 'https://www.instagram.com/', 'https://www.instagram.com/', 'https://www.instagram.com/', 'Kuliner', 'konten kreator newbie dengan konten yang berfokus pada makanan', '08123123123', '2005-03-21', 'Mataram'),
(6, 'Test Influencer', 'testuser', 'hashedpassword', 'test@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaign`
--
ALTER TABLE `campaign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bisnis` (`id_bisnis`);

--
-- Indexes for table `escrow`
--
ALTER TABLE `escrow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_campaign` (`id_campaign`),
  ADD KEY `id_bisnis` (`id_bisnis`);

--
-- Indexes for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_influencer` (`id_influencer`),
  ADD KEY `id_bisnis` (`id_bisnis`),
  ADD KEY `id_campaign` (`id_campaign`);

--
-- Indexes for table `konten`
--
ALTER TABLE `konten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kolaborasi` (`id_kolaborasi`);

--
-- Indexes for table `user_admin`
--
ALTER TABLE `user_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_bisnis`
--
ALTER TABLE `user_bisnis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_influencer`
--
ALTER TABLE `user_influencer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaign`
--
ALTER TABLE `campaign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `escrow`
--
ALTER TABLE `escrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `konten`
--
ALTER TABLE `konten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_admin`
--
ALTER TABLE `user_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_bisnis`
--
ALTER TABLE `user_bisnis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_influencer`
--
ALTER TABLE `user_influencer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaign`
--
ALTER TABLE `campaign`
  ADD CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`id_bisnis`) REFERENCES `user_bisnis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `escrow`
--
ALTER TABLE `escrow`
  ADD CONSTRAINT `escrow_ibfk_1` FOREIGN KEY (`id_campaign`) REFERENCES `campaign` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `escrow_ibfk_2` FOREIGN KEY (`id_bisnis`) REFERENCES `user_bisnis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  ADD CONSTRAINT `kolaborasi_ibfk_1` FOREIGN KEY (`id_influencer`) REFERENCES `user_influencer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kolaborasi_ibfk_2` FOREIGN KEY (`id_bisnis`) REFERENCES `user_bisnis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kolaborasi_ibfk_3` FOREIGN KEY (`id_campaign`) REFERENCES `campaign` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `konten`
--
ALTER TABLE `konten`
  ADD CONSTRAINT `konten_ibfk_1` FOREIGN KEY (`id_kolaborasi`) REFERENCES `kolaborasi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
