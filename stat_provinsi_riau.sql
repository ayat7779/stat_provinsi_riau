# Host: localhost  (Version 5.5.5-10.4.14-MariaDB)
# Date: 2025-06-16 19:35:02
# Generator: MySQL-Front 6.1  (Build 1.26)


#
# Structure for table "apbd_lampiran_1"
#

DROP TABLE IF EXISTS `apbd_lampiran_1`;
CREATE TABLE `apbd_lampiran_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` int(5) DEFAULT NULL,
  `id_kode_urut` int(5) DEFAULT NULL,
  `jumlah` float DEFAULT NULL,
  `id_jenis_apbd` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

#
# Data for table "apbd_lampiran_1"
#


#
# Structure for table "jenis_apbd"
#

DROP TABLE IF EXISTS `jenis_apbd`;
CREATE TABLE `jenis_apbd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uraian` varchar(255) DEFAULT NULL,
  `akronim` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

#
# Data for table "jenis_apbd"
#

INSERT INTO `jenis_apbd` VALUES (1,'APBD INDUK','Induk'),(2,'APBD Perubahan','APBDP'),(3,'APBD Pergeseran Pertama','APBDP01'),(4,'APBD Pergeseran Kedua','APBDP02'),(5,'APBD Pergeseran Ketiga','APBDP03');

#
# Structure for table "kode_catatan"
#

DROP TABLE IF EXISTS `kode_catatan`;
CREATE TABLE `kode_catatan` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `kode_catatan` varchar(255) DEFAULT NULL,
  `uraian` varchar(1024) DEFAULT NULL,
  `id_kode_level` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

#
# Data for table "kode_catatan"
#

INSERT INTO `kode_catatan` VALUES (1,'1.','PENDAPATAN',1),(2,'1.1.','PENDAPATAN ASLI DAERAH',2),(3,'1.1.1.','Pendapatan Pajak Daerah',3),(4,'1.1.2.','Pendapatan Retribusi Daerah',3),(5,'1.1.3.','Pendapatan Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',3),(6,'1.1.4.','Pendapatan Lain-lain PAD Yang Sah',3),(11,'1.2.','PENDAPATAN TRANSFER PEMERINTAH PUSAT - DANA PERIMBANGAN',2),(12,'1.2.1.','Dana Bagi Hasil',3),(13,'1.2.2.','Dana Alokasi Umum',3),(14,'1.2.3.','Dana Alokasi Khusus - Fisik',3),(15,'1.2.4.','Dana Alokasi Khusus - Non Fisik',3),(16,'1.3.','PENDAPATAN TRANSFER PEMERINTAH PUSAT - LAINNYA',2),(17,'1.3.1.','Dana Insentif Daerah (DID)',3),(18,'1.4.','LAIN-LAIN PENDAPATAN DAERAH YANG SAH',2),(19,'1.4.1.','Pendapatan Hibah',3),(20,'1.4.2.','Pendapatan Dana Darurat',3),(21,'1.4.3.','Pendapatan Lainnya',3),(22,'2.','BELANJA',1),(23,'2.1.','BELANJA OPERASI',2),(24,'2.1.1.','Belanja Pegawai',3),(25,'2.1.2.','Belanja Barang dan Jasa',3),(26,'2.1.3.','Belanja Bunga',3),(27,'2.1.4.','Belanja Subsidi',3),(28,'2.1.5.','Belanja Hibah',3),(29,'2.1.6.','Belanja Bantuan Sosial',3),(30,'2.2.','BELANJA MODAL',2),(31,'2.2.1.','Belanja Modal Tanah',3),(32,'2.2.2.','Belanja Modal Peralatan dan Mesin',3),(33,'2.2.3.','Belanja Modal Gedung dan Bangunan',3),(34,'2.2.4.','Belanja Modal Jalan, Irigasi dan Jaringan',3),(35,'2.2.5.','Belanja Modal Aset Tetap Lainnya',3),(36,'2.2.6.','Belanja Modal Aset Lainnya',3),(37,'2.3.','BELANJA TAK TERDUGA',2),(38,'2.3.1.','Belanja Tak Terduga',3),(39,'3.','TRANSFER',1),(40,'3.1.','TRANSFER - BAGI HASIL PENDAPATAN KE KAB/KOTA',2),(41,'3.1.1.','Bagi Hasil Pajak ke Kabupaten/Kota',3),(42,'3.1.2.','Bagi Hasil Retribusi ke Kabupaten/Kota',3),(43,'3.1.3.','Bagi Hasil Pendapatan Lainnya ke Kabupaten/Kota',3),(44,'3.2.','TRANSFER - BANTUAN KEUANGAN',2),(45,'3.2.1.','Bantuan Keuangan ke Pemerintah Daerah Lainnya',3),(46,'3.2.2.','Bantuan Keuangan ke Desa',3),(47,'3.2.3.','Bantuan Keuangan Lainnya',3),(48,'4.','PEMBIAYAAN',1),(49,'4.1.','PENERIMAAN PEMBIAYAAN',2),(50,'4.1.1.','Penggunaan SILPA',3),(51,'4.1.2.','Pencairan Dana Cadangan',3),(52,'4.1.3.','Penerimaan Pinjaman Daerah dan Obligasi Daerah',3);

#
# Structure for table "kode_level"
#

DROP TABLE IF EXISTS `kode_level`;
CREATE TABLE `kode_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_level` varchar(255) DEFAULT NULL,
  `akronim` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

#
# Data for table "kode_level"
#

INSERT INTO `kode_level` VALUES (1,'Akun','Akn'),(2,'Kelompok','Klp'),(3,'Jenis','Jns');

#
# Structure for table "kode_urut"
#

DROP TABLE IF EXISTS `kode_urut`;
CREATE TABLE `kode_urut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_urut` varchar(6) DEFAULT NULL,
  `uraian` varchar(255) DEFAULT NULL,
  `id_kode_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4;

#
# Data for table "kode_urut"
#

INSERT INTO `kode_urut` VALUES (1,'4.','PENDAPATAN DAERAH',1),(2,'4.1.','PENDAPATAN ASLI DAERAH',2),(3,'4.1.1.','Pajak Daerah',3),(4,'4.1.2.','Retribusi Daerah',3),(5,'4.1.3.','Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',3),(6,'4.1.4.','Lain-lain Pendapatan Asli Daerah Yang Sah',3),(7,'4.2.','DANA PERIMBANGAN',2),(8,'4.2.1.','Bagi Hasil Pajak/Bagi Hasil Bukan Pajak',3),(9,'4.2.2.','Dana Alokasi Umum',3),(10,'4.2.3.','Dana Alokasi Khusus',3),(11,'4.3.','LAIN-LAIN PENDAPATAN DAERAH YANG SAH',2),(12,'4.3.1.','Pendapatan Hibah',3),(15,'4.3.4.','Dana Penyesuaian dan Otonomi Khusus',3),(16,'5.','BELANJA DAERAH',1),(17,'5.1.','BELANJA TIDAK LANGSUNG',2),(18,'5.1.1.','Belanja Pegawai',3),(21,'5.1.4.','Belanja Hibah',3),(22,'5.1.6.','Belanja Bagi Hasil Kepada Provinsi/Kabupaten/Kota dan Pemerintahan Desa',3),(23,'5.1.7.','Belanja Bantuan Keuangan Kepada Provinsi/Kabupaten/Kota dan Pemerintahan Desa',3),(24,'5.1.8.','Belanja Tidak Terduga',3),(25,'5.2.','BELANJA LANGSUNG',2),(26,'5.2.1.','Belanja Pegawai',3),(27,'5.2.2.','Belanja Barang dan Jasa',3),(28,'5.2.3.','Belanja Modal',3),(30,'6.','PEMBIAYAAN DAERAH',1),(31,'6.1.','Penerimaan Pembiayaan Daerah',2),(32,'6.1.1.','Sisa Lebih Perhitungan Anggaran Daerah Tahun Sebelumnya',3),(33,'6.1.5.','Penerimaan Kembali Pemberian Pinjaman',3);

#
# Structure for table "lkpd_apbd_lampiran_1"
#

DROP TABLE IF EXISTS `lkpd_apbd_lampiran_1`;
CREATE TABLE `lkpd_apbd_lampiran_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_lkpd` int(5) DEFAULT NULL,
  `id_kode_catatan` int(5) DEFAULT NULL,
  `jumlah_anggaran` float DEFAULT NULL,
  `jumlah_realisasi` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

#
# Data for table "lkpd_apbd_lampiran_1"
#

