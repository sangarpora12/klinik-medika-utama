-- Backup Database klinik_db
-- Dibuat: 2026-06-21 18:37:18
-- Oleh sistem: SIK – Sistem Informasi Klinik

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Struktur tabel `antrian`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `antrian`;
CREATE TABLE `antrian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pasien_id` int(11) NOT NULL,
  `dokter_id` int(11) NOT NULL,
  `poli_id` int(11) NOT NULL,
  `operator_id` int(11) DEFAULT NULL COMMENT 'Operator yang mendaftarkan',
  `tanggal` date NOT NULL,
  `no_antrian` smallint(5) unsigned NOT NULL,
  `keluhan` text DEFAULT NULL,
  `status` enum('menunggu','dipanggil','selesai','batal') NOT NULL DEFAULT 'menunggu',
  `jam_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `jam_panggil` datetime DEFAULT NULL,
  `jam_selesai` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_antrian` (`dokter_id`,`tanggal`,`no_antrian`),
  KEY `pasien_id` (`pasien_id`),
  KEY `poli_id` (`poli_id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`),
  CONSTRAINT `antrian_ibfk_2` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`),
  CONSTRAINT `antrian_ibfk_3` FOREIGN KEY (`poli_id`) REFERENCES `poli` (`id`),
  CONSTRAINT `antrian_ibfk_4` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur tabel `audit_log`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_aksi` (`aksi`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `audit_log`
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', '1', 'login_failed', 'Gagal login: admin (ke-0)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 18:50:22');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', '1', 'login_failed', 'Gagal login: admin (ke-1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 18:51:01');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', '1', 'login_failed', 'Gagal login: admin (ke-2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 18:53:04');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', '1', 'login_failed', 'Gagal login: admin (ke-3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 19:03:52');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('5', '1', 'login_failed', 'Gagal login: admin (ke-0)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 19:08:50');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('6', '1', 'login_failed', 'Gagal login: admin (ke-1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 19:10:01');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('7', '1', 'login_failed', 'Gagal login: admin (ke-2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 19:41:18');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('8', '1', 'login_failed', 'Gagal login: admin (ke-3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 19:58:21');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('9', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 20:11:29');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('10', '1', 'logout', 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 20:14:52');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('11', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 20:15:02');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('12', '1', 'logout', 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 20:21:38');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('13', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:40:12');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('14', '1', 'admin_ubah_dokter', 'Update dokter #1: dr. Anisa Rahmawati', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:48:14');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('15', '1', 'admin_hapus_dokter', 'Hapus dokter #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:48:19');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('16', '1', 'admin_ubah_dokter', 'Update dokter #2: dr. erna', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:48:46');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('17', '1', 'admin_ubah_dokter', 'Update dokter #2: dr. erna', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:48:51');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('18', '1', 'admin_tambah_pasien', 'Tambah pasien: fransiana Berna Bei (RM-2026-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 20:50:25');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('19', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 21:11:17');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('20', '1', 'logout', 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 21:24:14');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('21', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 21:25:31');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('22', '1', 'logout', 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 21:25:43');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('23', '1', 'login_success', 'Login berhasil: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:15:06');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('24', '1', 'admin_ubah_pasien', 'Update pasien #1: fransiana Berna Bei', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:30:59');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('25', '1', 'admin_ubah_pasien', 'Update pasien #1: fransiana Berna Bei', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:31:06');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('26', '1', 'admin_tambah_pasien', 'Tambah pasien: selvina (RM-2026-0002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:32:27');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('27', '1', 'admin_ubah_status', 'User #1 → belum_verifikasi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:33:05');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('28', '1', 'admin_ubah_status', 'User #1 → nonaktif', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:33:09');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('29', '1', 'admin_ubah_status', 'User #1 → aktif', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:33:11');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('30', '1', 'admin_ubah_dokter', 'Update dokter #3: Citra Dewi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:33:30');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('31', '1', 'cetak_laporan', 'Laporan Data Pasien (2026-06-01 s/d 2026-06-21), 2 baris', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:34:34');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('32', '1', 'cetak_laporan', 'Laporan Data Pasien (2026-06-01 s/d 2026-06-21), 2 baris', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:34:44');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('33', '1', 'cetak_laporan', 'Laporan Rekap Antrian (2026-06-01 s/d 2026-06-21), 0 baris', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:35:48');
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES ('34', '1', 'cetak_laporan', 'Laporan Rekap Antrian (2026-06-01 s/d 2026-06-21), 0 baris', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-21 23:36:59');

-- --------------------------------------------------------
-- Struktur tabel `dokter`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `dokter`;
CREATE TABLE `dokter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `spesialisasi` varchar(100) NOT NULL,
  `no_sip` varchar(60) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `jadwal` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Contoh: {"senin":"08:00-12:00","rabu":"13:00-17:00"}' CHECK (json_valid(`jadwal`)),
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_sip` (`no_sip`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `dokter`
INSERT INTO `dokter` (`id`, `user_id`, `nama`, `spesialisasi`, `no_sip`, `telepon`, `email`, `foto`, `jadwal`, `aktif`, `created_at`) VALUES ('2', NULL, 'dr. erna', 'Gigi', 'SIP.002/2024', '081200000002', NULL, NULL, NULL, '1', '2026-06-20 18:38:40');
INSERT INTO `dokter` (`id`, `user_id`, `nama`, `spesialisasi`, `no_sip`, `telepon`, `email`, `foto`, `jadwal`, `aktif`, `created_at`) VALUES ('3', NULL, 'Citra Dewi', 'Anak', 'SIP.003/2024', '081200000003', NULL, NULL, NULL, '1', '2026-06-20 18:38:40');

-- --------------------------------------------------------
-- Struktur tabel `obat`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `obat`;
CREATE TABLE `obat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_obat` varchar(150) NOT NULL,
  `kategori` varchar(80) DEFAULT NULL,
  `satuan` varchar(30) NOT NULL DEFAULT 'tablet',
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur tabel `pasien`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pasien`;
CREATE TABLE `pasien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `no_rm` varchar(20) NOT NULL COMMENT 'Nomor Rekam Medis',
  `nama_lengkap` varchar(150) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `gol_darah` enum('A','B','AB','O','tidak_diketahui') DEFAULT 'tidak_diketahui',
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `alergi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_rm` (`no_rm`),
  UNIQUE KEY `nik` (`nik`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `pasien`
INSERT INTO `pasien` (`id`, `user_id`, `no_rm`, `nama_lengkap`, `nik`, `tanggal_lahir`, `jenis_kelamin`, `gol_darah`, `alamat`, `telepon`, `email`, `alergi`, `created_at`, `updated_at`) VALUES ('1', NULL, 'RM-2026-0001', 'fransiana Berna Bei', '1231213132331121', '2006-02-15', 'L', 'AB', 'Sonopakis lor Rt 02', '0998763665', NULL, 'udang', '2026-06-20 20:50:25', '2026-06-21 23:30:59');
INSERT INTO `pasien` (`id`, `user_id`, `no_rm`, `nama_lengkap`, `nik`, `tanggal_lahir`, `jenis_kelamin`, `gol_darah`, `alamat`, `telepon`, `email`, `alergi`, `created_at`, `updated_at`) VALUES ('2', NULL, 'RM-2026-0002', 'selvina', '1234556524252612', '2026-06-21', 'P', 'O', 'Sonopakis lor Rt 02', '099854543', 'datyngaru047@gmail.com', 'udang', '2026-06-21 23:32:27', '2026-06-21 23:32:27');

-- --------------------------------------------------------
-- Struktur tabel `poli`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `poli`;
CREATE TABLE `poli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_poli` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `poli`
INSERT INTO `poli` (`id`, `nama_poli`, `deskripsi`, `aktif`) VALUES ('1', 'Poli Umum', 'Pelayanan kesehatan umum', '1');
INSERT INTO `poli` (`id`, `nama_poli`, `deskripsi`, `aktif`) VALUES ('2', 'Poli Gigi', 'Pelayanan kesehatan gigi dan mulut', '1');
INSERT INTO `poli` (`id`, `nama_poli`, `deskripsi`, `aktif`) VALUES ('3', 'Poli Anak', 'Pelayanan kesehatan anak', '1');

-- --------------------------------------------------------
-- Struktur tabel `rekam_medis`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rekam_medis`;
CREATE TABLE `rekam_medis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `antrian_id` int(11) NOT NULL,
  `pasien_id` int(11) NOT NULL,
  `dokter_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `anamnesis` text DEFAULT NULL COMMENT 'Keluhan & riwayat penyakit',
  `diagnosa` text DEFAULT NULL,
  `kode_icd` varchar(20) DEFAULT NULL COMMENT 'Kode ICD-10',
  `tindakan` text DEFAULT NULL,
  `resep` text DEFAULT NULL COMMENT 'Resep dalam format teks/JSON',
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `catatan_dokter` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `antrian_id` (`antrian_id`),
  KEY `pasien_id` (`pasien_id`),
  KEY `dokter_id` (`dokter_id`),
  CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`antrian_id`) REFERENCES `antrian` (`id`),
  CONSTRAINT `rekam_medis_ibfk_2` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`),
  CONSTRAINT `rekam_medis_ibfk_3` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur tabel `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `roles`
INSERT INTO `roles` (`id`, `nama_role`, `deskripsi`, `created_at`) VALUES ('1', 'admin', 'Administrator penuh sistem', '2026-06-20 18:38:40');
INSERT INTO `roles` (`id`, `nama_role`, `deskripsi`, `created_at`) VALUES ('2', 'operator', 'Petugas front office klinik', '2026-06-20 18:38:40');
INSERT INTO `roles` (`id`, `nama_role`, `deskripsi`, `created_at`) VALUES ('3', 'user', 'Pasien / pengguna umum', '2026-06-20 18:38:40');

-- --------------------------------------------------------
-- Struktur tabel `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `nama_lengkap` varchar(150) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('belum_verifikasi','aktif','nonaktif') NOT NULL DEFAULT 'belum_verifikasi',
  `token_verifikasi` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `gagal_login` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `blokir_hingga` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data untuk tabel `users`
INSERT INTO `users` (`id`, `role_id`, `nama_lengkap`, `username`, `email`, `password_hash`, `status`, `token_verifikasi`, `foto_profil`, `gagal_login`, `blokir_hingga`, `last_login`, `created_at`, `updated_at`) VALUES ('1', '1', 'Super Admin', 'admin', 'admin@klinik.local', '$2y$12$WUBvE.v5atVbX8S4x6NyXep6SxcXyevRaDzr0HJuwm1ZZLSr65mhe', 'aktif', NULL, NULL, '0', NULL, '2026-06-21 23:15:06', '2026-06-20 18:38:40', '2026-06-21 23:33:11');

SET FOREIGN_KEY_CHECKS=1;
