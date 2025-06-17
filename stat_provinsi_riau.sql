-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2025 at 07:25 AM
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
-- Database: `stat_provinsi_riau`
--

-- --------------------------------------------------------

--
-- Table structure for table `apbd_lampiran_1`
--

CREATE TABLE `apbd_lampiran_1` (
  `id` int(11) NOT NULL,
  `tahun` int(5) DEFAULT NULL,
  `id_kode_urut` int(5) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `id_jenis_apbd` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_apbd`
--

CREATE TABLE `jenis_apbd` (
  `id` int(11) NOT NULL,
  `uraian` varchar(255) DEFAULT NULL,
  `akronim` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_apbd`
--

INSERT INTO `jenis_apbd` (`id`, `uraian`, `akronim`) VALUES
(1, 'APBD INDUK', 'Induk'),
(2, 'APBD Perubahan', 'APBDP'),
(3, 'APBD Pergeseran Pertama', 'APBDP01'),
(4, 'APBD Pergeseran Kedua', 'APBDP02'),
(5, 'APBD Pergeseran Ketiga', 'APBDP03');

-- --------------------------------------------------------

--
-- Table structure for table `kode_catatan`
--

CREATE TABLE `kode_catatan` (
  `id` int(5) NOT NULL,
  `kode_catatan` varchar(255) DEFAULT NULL,
  `uraian` varchar(1024) DEFAULT NULL,
  `id_kode_level` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kode_catatan`
--

INSERT INTO `kode_catatan` (`id`, `kode_catatan`, `uraian`, `id_kode_level`) VALUES
(1, '1.', 'PENDAPATAN', 1),
(2, '1.1.', 'PENDAPATAN ASLI DAERAH', 2),
(3, '1.1.1.', 'Pendapatan Pajak Daerah', 3),
(4, '1.1.2.', 'Pendapatan Retribusi Daerah', 3),
(5, '1.1.3.', 'Pendapatan Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan', 3),
(6, '1.1.4.', 'Lain-lain PAD Yang Sah', 3),
(11, '1.2.', 'PENDAPATAN TRANSFER PEMERINTAH PUSAT - DANA PERIMBANGAN', 2),
(12, '1.2.1.', 'Dana Bagi Hasil', 3),
(13, '1.2.2.', 'Dana Alokasi Umum', 3),
(14, '1.2.3.', 'Dana Alokasi Khusus - Fisik', 3),
(15, '1.2.4.', 'Dana Alokasi Khusus - Non Fisik', 3),
(16, '1.3.', 'PENDAPATAN TRANSFER PEMERINTAH PUSAT - LAINNYA', 2),
(17, '1.3.1.', 'Dana Insentif Daerah (DID)', 3),
(18, '1.4.', 'LAIN-LAIN PENDAPATAN DAERAH YANG SAH', 2),
(19, '1.4.1.', 'Pendapatan Hibah', 3),
(20, '1.4.2.', 'Pendapatan Dana Darurat', 3),
(21, '1.4.3.', 'Pendapatan Lainnya', 3),
(22, '2.', 'BELANJA', 1),
(23, '2.1.', 'BELANJA OPERASI', 2),
(24, '2.1.1.', 'Belanja Pegawai', 3),
(25, '2.1.2.', 'Belanja Barang dan Jasa', 3),
(26, '2.1.3.', 'Belanja Bunga', 3),
(27, '2.1.4.', 'Belanja Subsidi', 3),
(28, '2.1.5.', 'Belanja Hibah', 3),
(29, '2.1.6.', 'Belanja Bantuan Sosial', 3),
(30, '2.2.', 'BELANJA MODAL', 2),
(31, '2.2.1.', 'Belanja Modal Tanah', 3),
(32, '2.2.2.', 'Belanja Modal Peralatan dan Mesin', 3),
(33, '2.2.3.', 'Belanja Modal Gedung dan Bangunan', 3),
(34, '2.2.4.', 'Belanja Modal Jalan, Irigasi dan Jaringan', 3),
(35, '2.2.5.', 'Belanja Modal Aset Tetap Lainnya', 3),
(36, '2.2.6.', 'Belanja Modal Aset Lainnya', 3),
(37, '2.3.', 'BELANJA TAK TERDUGA', 2),
(38, '2.3.1.', 'Belanja Tak Terduga', 3),
(39, '3.', 'TRANSFER', 1),
(40, '3.1.', 'TRANSFER - BAGI HASIL PENDAPATAN KE KAB/KOTA', 2),
(41, '3.1.1.', 'Bagi Hasil Pajak ke Kabupaten/Kota', 3),
(42, '3.1.2.', 'Bagi Hasil Retribusi ke Kabupaten/Kota', 3),
(43, '3.1.3.', 'Bagi Hasil Pendapatan Lainnya ke Kabupaten/Kota', 3),
(44, '3.2.', 'TRANSFER - BANTUAN KEUANGAN', 2),
(45, '3.2.1.', 'Bantuan Keuangan ke Pemerintah Daerah Lainnya', 3),
(46, '3.2.2.', 'Bantuan Keuangan ke Desa', 3),
(47, '3.2.3.', 'Bantuan Keuangan Lainnya', 3),
(48, '4.', 'PEMBIAYAAN', 1),
(49, '4.1.', 'PENERIMAAN PEMBIAYAAN', 2),
(50, '4.1.1.', 'Penggunaan SILPA', 3),
(51, '4.1.2.', 'Pencairan Dana Cadangan', 3),
(52, '4.1.3.', 'Penerimaan Pinjaman Daerah dan Obligasi Daerah', 3);

-- --------------------------------------------------------

--
-- Table structure for table `kode_level`
--

CREATE TABLE `kode_level` (
  `id` int(11) NOT NULL,
  `nama_level` varchar(255) DEFAULT NULL,
  `akronim` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kode_level`
--

INSERT INTO `kode_level` (`id`, `nama_level`, `akronim`) VALUES
(1, 'Akun', 'Akn'),
(2, 'Kelompok', 'Klp'),
(3, 'Jenis', 'Jns');

-- --------------------------------------------------------

--
-- Table structure for table `kode_urut`
--

CREATE TABLE `kode_urut` (
  `id` int(11) NOT NULL,
  `no_urut` varchar(6) DEFAULT NULL,
  `uraian` varchar(255) DEFAULT NULL,
  `id_kode_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kode_urut`
--

INSERT INTO `kode_urut` (`id`, `no_urut`, `uraian`, `id_kode_level`) VALUES
(1, '4.', 'PENDAPATAN DAERAH', 1),
(2, '4.1.', 'PENDAPATAN ASLI DAERAH', 2),
(3, '4.1.1.', 'Pajak Daerah', 3),
(4, '4.1.2.', 'Retribusi Daerah', 3),
(5, '4.1.3.', 'Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan', 3),
(6, '4.1.4.', 'Lain-lain Pendapatan Asli Daerah Yang Sah', 3),
(7, '4.2.', 'DANA PERIMBANGAN', 2),
(8, '4.2.1.', 'Bagi Hasil Pajak/Bagi Hasil Bukan Pajak', 3),
(9, '4.2.2.', 'Dana Alokasi Umum', 3),
(10, '4.2.3.', 'Dana Alokasi Khusus', 3),
(11, '4.3.', 'LAIN-LAIN PENDAPATAN DAERAH YANG SAH', 2),
(12, '4.3.1.', 'Pendapatan Hibah', 3),
(15, '4.3.4.', 'Dana Penyesuaian dan Otonomi Khusus', 3),
(16, '5.', 'BELANJA DAERAH', 1),
(17, '5.1.', 'BELANJA TIDAK LANGSUNG', 2),
(18, '5.1.1.', 'Belanja Pegawai', 3),
(21, '5.1.4.', 'Belanja Hibah', 3),
(22, '5.1.6.', 'Belanja Bagi Hasil Kepada Provinsi/Kabupaten/Kota dan Pemerintahan Desa', 3),
(23, '5.1.7.', 'Belanja Bantuan Keuangan Kepada Provinsi/Kabupaten/Kota dan Pemerintahan Desa', 3),
(24, '5.1.8.', 'Belanja Tidak Terduga', 3),
(25, '5.2.', 'BELANJA LANGSUNG', 2),
(26, '5.2.1.', 'Belanja Pegawai', 3),
(27, '5.2.2.', 'Belanja Barang dan Jasa', 3),
(28, '5.2.3.', 'Belanja Modal', 3),
(30, '6.', 'PEMBIAYAAN DAERAH', 1),
(31, '6.1.', 'Penerimaan Pembiayaan Daerah', 2),
(32, '6.1.1.', 'Sisa Lebih Perhitungan Anggaran Daerah Tahun Sebelumnya', 3),
(33, '6.1.5.', 'Penerimaan Kembali Pemberian Pinjaman', 3);

-- --------------------------------------------------------

--
-- Table structure for table `lkpd_apbd_lampiran_1`
--

CREATE TABLE `lkpd_apbd_lampiran_1` (
  `id` int(11) NOT NULL,
  `tahun_lkpd` int(5) DEFAULT NULL,
  `id_kode_catatan` int(5) DEFAULT NULL,
  `jumlah_anggaran` decimal(18,2) DEFAULT NULL,
  `jumlah_realisasi` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lkpd_apbd_lampiran_1`
--

INSERT INTO `lkpd_apbd_lampiran_1` (`id`, `tahun_lkpd`, `id_kode_catatan`, `jumlah_anggaran`, `jumlah_realisasi`) VALUES
(13, 2023, 3, 4365532859053.00, 4412361453766.84),
(14, 2023, 4, 18586888850.00, 17145487338.50),
(15, 2023, 5, 954288533724.00, 948660382439.65),
(16, 2023, 6, 462676053980.00, 513438419227.22),
(17, 2023, 12, 1832034058592.00, 1536866672146.00),
(18, 2023, 13, 1506322398000.00, 1506286541167.00),
(19, 2023, 14, 303091172000.00, 295138574452.00),
(20, 2023, 15, 761964490000.00, 749456374741.00),
(21, 2023, 17, 21547646000.00, 21547646000.00),
(22, 2023, 19, 8992680000.00, 9078040000.00),
(23, 2023, 24, 2609355838396.00, 2549829683419.00),
(24, 2023, 25, 2930933682961.00, 2811156492698.00),
(25, 2023, 28, 396745845167.00, 390363362858.00),
(26, 2023, 29, 35816280000.00, 35430670000.00),
(27, 2023, 31, 17738404000.00, 16531477000.00),
(28, 2023, 33, 407378302771.00, 391132240152.00),
(29, 2023, 34, 1240049557979.00, 1195568098007.00),
(30, 2023, 35, 57849163180.00, 57537651640.00),
(31, 2023, 36, 4521283605.00, 1075029880.00),
(32, 2023, 38, 18064238270.00, 249778000.00),
(33, 2023, 41, 2083597551764.00, 2083597551764.00),
(34, 2023, 45, 290103855241.00, 288344360586.00),
(35, 2023, 46, 278425000000.00, 273669947239.00),
(36, 2022, 3, 3784151194784.00, 4054918904730.36),
(37, 2022, 4, 15643150000.00, 16684961995.00),
(38, 2022, 5, 519395517928.00, 101607146246.00),
(39, 2022, 6, 535313362878.00, 523558066938.81);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apbd_lampiran_1`
--
ALTER TABLE `apbd_lampiran_1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jenis_apbd`
--
ALTER TABLE `jenis_apbd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kode_catatan`
--
ALTER TABLE `kode_catatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kode_level`
--
ALTER TABLE `kode_level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kode_urut`
--
ALTER TABLE `kode_urut`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lkpd_apbd_lampiran_1`
--
ALTER TABLE `lkpd_apbd_lampiran_1`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apbd_lampiran_1`
--
ALTER TABLE `apbd_lampiran_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_apbd`
--
ALTER TABLE `jenis_apbd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kode_catatan`
--
ALTER TABLE `kode_catatan`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `kode_level`
--
ALTER TABLE `kode_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kode_urut`
--
ALTER TABLE `kode_urut`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `lkpd_apbd_lampiran_1`
--
ALTER TABLE `lkpd_apbd_lampiran_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
