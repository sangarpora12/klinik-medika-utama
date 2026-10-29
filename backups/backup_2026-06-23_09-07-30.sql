-- Backup Klinik Medika Utama
-- Generated: 2026-06-23 09:07:30

-- Table: antrian
DROP TABLE IF EXISTS `antrian`;
CREATE TABLE `antrian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pasien_id` int NOT NULL,
  `dokter_id` int NOT NULL,
  `poli_id` int NOT NULL,
  `operator_id` int DEFAULT NULL COMMENT 'Operator yang mendaftarkan',
  `tanggal` date NOT NULL,
  `no_antrian` smallint unsigned NOT NULL,
  `keluhan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('menunggu','dipanggil','selesai','batal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `jam_daftar` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `antrian` (`id`,`pasien_id`,`dokter_id`,`poli_id`,`operator_id`,`tanggal`,`no_antrian`,`keluhan`,`status`,`jam_daftar`,`jam_panggil`,`jam_selesai`) VALUES ('1','3','1','1',NULL,'2026-06-24','1','diabetes','menunggu','2026-06-23 01:55:17',NULL,NULL);
INSERT INTO `antrian` (`id`,`pasien_id`,`dokter_id`,`poli_id`,`operator_id`,`tanggal`,`no_antrian`,`keluhan`,`status`,`jam_daftar`,`jam_panggil`,`jam_selesai`) VALUES ('2','3','1','3',NULL,'2026-06-22','1','','selesai','2026-06-23 01:56:24','2026-06-23 02:30:46','2026-06-23 02:30:58');
INSERT INTO `antrian` (`id`,`pasien_id`,`dokter_id`,`poli_id`,`operator_id`,`tanggal`,`no_antrian`,`keluhan`,`status`,`jam_daftar`,`jam_panggil`,`jam_selesai`) VALUES ('3','3','2','3',NULL,'2026-06-22','1','','selesai','2026-06-23 04:05:32','2026-06-23 04:05:58','2026-06-23 04:06:35');
INSERT INTO `antrian` (`id`,`pasien_id`,`dokter_id`,`poli_id`,`operator_id`,`tanggal`,`no_antrian`,`keluhan`,`status`,`jam_daftar`,`jam_panggil`,`jam_selesai`) VALUES ('4','4','1','1',NULL,'2026-06-24','2','sakit kepala','dipanggil','2026-06-23 15:55:49','2026-06-23 15:57:46',NULL);
INSERT INTO `antrian` (`id`,`pasien_id`,`dokter_id`,`poli_id`,`operator_id`,`tanggal`,`no_antrian`,`keluhan`,`status`,`jam_daftar`,`jam_panggil`,`jam_selesai`) VALUES ('5','4','1','1',NULL,'2026-06-24','3','pusing','selesai','2026-06-23 16:04:31','2026-06-23 16:04:50','2026-06-23 16:05:00');

-- Table: audit_log
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `aksi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_aksi` (`aksi`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('1','1','login_failed','Gagal login: admin (ke-0)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:18:49');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('2','1','login_failed','Gagal login: admin (ke-1)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:19:44');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('3','1','login_failed','Gagal login: admin (ke-2)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:22:41');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('4','1','login_success','Login berhasil: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:25:53');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('5','1','cetak_laporan','Laporan Data Pasien (2026-06-01 s/d 2026-06-22), 0 baris','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:35:53');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('6','1','cetak_laporan','Laporan Data Pasien (2026-06-01 s/d 2026-06-22), 0 baris','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:39:17');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('7','1','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:39:39');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('8','1','login_success','Login successful: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:41:02');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('9','1','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:45:06');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('10','1','login_success','Login successful: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 21:52:53');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('11',NULL,'register','User baru: zul_1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:02:11');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('12','2','verify_email','Verifikasi email berhasil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:02:22');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('13','2','login_success','Login successful: zul_1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:03:04');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('14','1','admin_tambah_pasien','Tambah pasien: Budii (RM-2026-0001)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:05:47');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('15','1','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:15:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('16','3','login_failed','Failed login: operator (attempt 0)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:16:12');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('17','3','login_failed','Failed login: operator (attempt 1)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:16:56');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('18','3','login_failed','Failed login: operator (attempt 2)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:20:31');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('19',NULL,'register','User baru: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:24:08');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('20','5','verify_email','Verifikasi email berhasil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:24:11');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('21','5','login_success','Login successful: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:24:25');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('22','5','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:25:03');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('23','3','login_failed','Failed login: operator (attempt 3)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:25:15');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('24','1','login_success','Login successful: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:27:11');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('25','2','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:27:34');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('26',NULL,'login_failed','Username not found: operator klinik','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:27:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('27','1','admin_ubah_role','User #5 role → 2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-22 22:28:12');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('28',NULL,'login_failed','Username not found: oprator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:28:44');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('29','5','login_success','Login successful: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-22 22:29:04');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('30','1','login_sukses','Login berhasil: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:01:42');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('31','1','admin_ubah_dokter','Update dokter #1: dr. Anisa Rahmawati','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:20:38');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('32','1','admin_ubah_dokter','Update dokter #1: dr. Anisa Rahmawat','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:20:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('33','5','login_sukses','Login berhasil: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 01:22:13');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('34','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:31:24');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('35','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:31:35');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('36','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:32:26');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('37','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:35:07');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('38','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:35:14');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('39','1','ubah_role','Ubah role user #5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:42:44');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('40','5','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 01:50:30');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('41','5','login_sukses','Login berhasil: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 01:50:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('42','1','hapus_akun','Hapus permanen user #4','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:52:26');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('43','1','ubah_status_antrian','Antrian #2 → menunggu','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:56:37');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('44','1','ubah_status_antrian','Antrian #2 → dipanggil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:57:00');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('45','1','ubah_status_antrian','Antrian #2 → selesai','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 01:57:59');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('46','5','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 02:00:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('47','1','ubah_role','Ubah role user #3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:01:24');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('48','1','reset_password','Reset password user #3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:01:43');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('49','1','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:01:48');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('50','3','login_sukses','Login berhasil: operator','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:02:16');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('51','3','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:02:33');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('52','1','login_sukses','Login berhasil: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:02:55');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('53','1','ubah_role','Ubah role user #3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:03:08');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('54','3','login_sukses','Login berhasil: operator','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 02:03:41');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('55','1','ubah_status_antrian','Antrian #2 → menunggu','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:24:08');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('56','1','ubah_status_antrian','Antrian #2 → dipanggil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:30:47');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('57','1','ubah_status_antrian','Antrian #2 → selesai','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 02:30:58');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('58','1','backup_database','Backup created: backup_2026-06-22_20-39-08.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 03:39:08');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('59','1','restore_database','Restore from backup_2026-06-22_20-39-20.sql','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 03:40:19');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('60','3','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 03:41:39');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('61','5','login_gagal','Gagal login: operator1 (ke-0)','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 03:42:02');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('62','5','login_sukses','Login berhasil: operator1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 03:43:16');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('63','1','ubah_status_antrian','Antrian #3 → dipanggil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 04:05:58');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('64','1','ubah_status_antrian','Antrian #3 → selesai','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 04:06:35');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('65','1','login_sukses','Login berhasil: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 13:38:49');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('66','1','hapus_akun','Hapus permanen user #2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 13:39:09');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('67','1','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 13:39:35');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('68','1','login_sukses','Login berhasil: admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 15:48:31');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('69','5','logout','User logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 15:51:06');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('70',NULL,'register','User baru: pasien','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 15:53:27');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('71','6','verify_email','Verifikasi email berhasil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 15:53:32');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('72',NULL,'login_gagal','Username tidak ditemukan: pasin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 15:53:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('73','6','login_sukses','Login berhasil: pasien','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 15:54:11');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('74','1','ubah_status_antrian','Antrian #4 → dipanggil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 15:57:46');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('75','1','ubah_role','Ubah role user #6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:03:03');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('76','1','ubah_role','Ubah role user #6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:03:07');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('77','1','ubah_role','Ubah role user #6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:03:32');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('78','1','ubah_role','Ubah role user #6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:03:37');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('79','1','ubah_status_antrian','Antrian #5 → dipanggil','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:04:50');
INSERT INTO `audit_log` (`id`,`user_id`,`aksi`,`detail`,`ip_address`,`user_agent`,`created_at`) VALUES ('80','1','ubah_status_antrian','Antrian #5 → selesai','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-23 16:05:00');

-- Table: dokter
DROP TABLE IF EXISTS `dokter`;
CREATE TABLE `dokter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `spesialisasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_sip` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jadwal` json DEFAULT NULL COMMENT 'Contoh: {"senin":"08:00-12:00","rabu":"13:00-17:00"}',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_sip` (`no_sip`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dokter` (`id`,`user_id`,`nama`,`spesialisasi`,`no_sip`,`telepon`,`email`,`foto`,`jadwal`,`aktif`,`created_at`) VALUES ('1',NULL,'dr. Anisa Rahmawat','Umum','SIP.001/2024','0812000000033',NULL,NULL,'{}','1','2026-06-22 21:16:06');
INSERT INTO `dokter` (`id`,`user_id`,`nama`,`spesialisasi`,`no_sip`,`telepon`,`email`,`foto`,`jadwal`,`aktif`,`created_at`) VALUES ('2',NULL,'dr. Budi Santoso','Gigi','SIP.002/2024','081200000002',NULL,NULL,NULL,'1','2026-06-22 21:16:06');
INSERT INTO `dokter` (`id`,`user_id`,`nama`,`spesialisasi`,`no_sip`,`telepon`,`email`,`foto`,`jadwal`,`aktif`,`created_at`) VALUES ('3',NULL,'dr. Citra Dewi','Anak','SIP.003/2024','081200000003',NULL,NULL,NULL,'1','2026-06-22 21:16:06');

-- Table: obat
DROP TABLE IF EXISTS `obat`;
CREATE TABLE `obat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_obat` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satuan` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tablet',
  `stok` int NOT NULL DEFAULT '0',
  `harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('1','Paracetamol 500mg','Analgesik','tablet','200','5000.00','Obat pereda demam dan nyeri ringan','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('2','Ibuprofen 400mg','Antiinflamasi','tablet','150','8000.00','Antiinflamasi non-steroid','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('3','Amoxicillin 500mg','Antibiotik','kapsul','100','12000.00','Antibiotik spektrum luas','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('4','Cefixime 200mg','Antibiotik','kapsul','80','25000.00','Antibiotik sefalosporin','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('5','Azithromycin 500mg','Antibiotik','tablet','90','35000.00','Antibiotik makrolida','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('6','Metformin 500mg','Antidiabetes','tablet','180','7000.00','Obat diabetes tipe 2','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('7','Glibenclamide 5mg','Antidiabetes','tablet','160','4000.00','Obat diabetes golongan sulfonilurea','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('8','Omeprazole 20mg','Antasida','kapsul','220','10000.00','Penghambat pompa proton untuk maag','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('9','Ranitidine 150mg','Antasida','tablet','200','6000.00','Antagonis H2 untuk tukak lambung','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('10','Amlodipine 10mg','Antihipertensi','tablet','140','9000.00','Penghambat saluran kalsium','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('11','Captopril 25mg','Antihipertensi','tablet','130','7000.00','ACE inhibitor untuk hipertensi','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('12','Simvastatin 20mg','Antikolesterol','tablet','120','11000.00','Statin untuk menurunkan kolesterol','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('13','Atorvastatin 20mg','Antikolesterol','tablet','110','15000.00','Statin generasi terbaru','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('14','Salbutamol Inhaler','Pernapasan','botol','50','45000.00','Bronkodilator untuk asma','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('15','Dexamethasone 0.5mg','Kortikosteroid','tablet','170','3000.00','Antiinflamasi kortikosteroid','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('16','Prednisone 5mg','Kortikosteroid','tablet','140','3500.00','Kortikosteroid sistemik','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('17','Diazepam 5mg','Sedatif','tablet','80','8000.00','Obat penenang dan antikejang','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('18','Furosemide 40mg','Diuretik','tablet','100','5000.00','Diuretik untuk edema dan hipertensi','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('19','Spironolactone 25mg','Diuretik','tablet','90','7500.00','Diuretik hemat kalium','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('20','Bisoprolol 5mg','Antihipertensi','tablet','95','8500.00','Beta blocker untuk hipertensi','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('21','Warfarin 5mg','Antikoagulan','tablet','60','6000.00','Pengencer darah','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('22','Aspirin 80mg','Antiplatelet','tablet','250','2000.00','Pengencer darah dosis rendah','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('23','Diphenhydramine 25mg','Antihistamin','kapsul','130','4000.00','Obat alergi generasi pertama','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('24','Cetirizine 10mg','Antihistamin','tablet','200','5000.00','Obat alergi generasi kedua','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('25','Loratadine 10mg','Antihistamin','tablet','180','6000.00','Obat alergi non-mengantuk','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('26','Mefenamic Acid 500mg','Analgesik','kapsul','160','7000.00','Obat nyeri haid dan sakit gigi','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('27','Domperidone 10mg','Antiemetik','tablet','120','6000.00','Obat mual dan muntah','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('28','Ondansetron 4mg','Antiemetik','tablet','75','12000.00','Anti mual kuat','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('29','Bromhexine 8mg','Mukolitik','tablet','140','4000.00','Pengencer dahak','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('30','CTM 4mg','Antihistamin','tablet','190','1500.00','Obat alergi dan pilek','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('31','Dextromethorphan 15mg','Antitusif','tablet','110','5500.00','Obat batuk kering','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('32','Multivitamin','Suplemen','tablet','300','10000.00','Suplemen multivitamin harian','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('33','Vitamin C 500mg','Suplemen','tablet','280','8000.00','Vitamin C imunitas','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('34','Vitamin B Kompleks','Suplemen','tablet','250','9500.00','Vitamin B lengkap','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('35','Zinc 20mg','Suplemen','tablet','220','7000.00','Suplemen seng untuk imunitas','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('36','Betadine 30ml','Antiseptik','botol','80','18000.00','Larutan antiseptik luka','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('37','Gentamicin Salep','Antibiotik','tube','65','15000.00','Salep antibiotik kulit','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('38','Miconazole Krim','Antijamur','tube','55','12000.00','Krim antijamur kulit','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('39','Albendazole 400mg','Antelmintik','tablet','70','8000.00','Obat cacingan','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('40','Hydrocortisone Krim','Kortikosteroid','tube','45','20000.00','Krim anti radang kulit','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('41','Chloramphenicol Tetes','Antibiotik','botol','40','15000.00','Tetes mata antibiotik','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('42','Allopurinol 100mg','Antigout','tablet','85','6000.00','Penurun asam urat','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('43','Aciclovir 400mg','Antivirus','tablet','60','20000.00','Obat herpes dan cacar','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('44','Kombinasi Obat Batuk','Batuk','botol','70','25000.00','Obat batuk kombinasi sirup','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('45','Antasida Sirup','Antasida','botol','90','15000.00','Obat maag cair','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('46','Gerbinafin Krim','Antijamur','tube','50','18000.00','Krim jamur kadas kurap','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('47','Ketoconazole 200mg','Antijamur','tablet','60','15000.00','Obat jamur sistemik','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('48','Paracetamol Sirup','Analgesik','botol','85','20000.00','Obat demam anak sirup','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('49','Attapulgite 600mg','Antidiare','tablet','100','5000.00','Obat diare','2026-06-23 02:10:29','2026-06-23 02:10:29');
INSERT INTO `obat` (`id`,`nama_obat`,`kategori`,`satuan`,`stok`,`harga`,`keterangan`,`created_at`,`updated_at`) VALUES ('50','Loperamide 2mg','Antidiare','kapsul','90','7000.00','Obat diare akut','2026-06-23 02:10:29','2026-06-23 02:10:29');

-- Table: pasien
DROP TABLE IF EXISTS `pasien`;
CREATE TABLE `pasien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `no_rm` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor Rekam Medis',
  `nama_lengkap` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gol_darah` enum('A','B','AB','O','tidak_diketahui') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'tidak_diketahui',
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alergi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_rm` (`no_rm`),
  UNIQUE KEY `nik` (`nik`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pasien` (`id`,`user_id`,`no_rm`,`nama_lengkap`,`nik`,`tanggal_lahir`,`jenis_kelamin`,`gol_darah`,`alamat`,`telepon`,`email`,`alergi`,`created_at`,`updated_at`) VALUES ('1',NULL,'RM-2026-0001','Budii','1234567890123456','1999-02-22','L','tidak_diketahui','bantul','08227766544',NULL,NULL,'2026-06-22 22:05:47','2026-06-22 22:05:47');
INSERT INTO `pasien` (`id`,`user_id`,`no_rm`,`nama_lengkap`,`nik`,`tanggal_lahir`,`jenis_kelamin`,`gol_darah`,`alamat`,`telepon`,`email`,`alergi`,`created_at`,`updated_at`) VALUES ('3','5','RM-2026-0002','andi','201100000000000','2000-01-23','L','A','bantul','085455665544','andi@gmail.com',NULL,'2026-06-23 01:53:05','2026-06-23 01:54:08');
INSERT INTO `pasien` (`id`,`user_id`,`no_rm`,`nama_lengkap`,`nik`,`tanggal_lahir`,`jenis_kelamin`,`gol_darah`,`alamat`,`telepon`,`email`,`alergi`,`created_at`,`updated_at`) VALUES ('4','6','RM-2026-0003','samsudin','10102039485','2004-02-23','L','A','jakarta','0865564535','samsudin@gmail.com','udang','2026-06-23 15:55:22','2026-06-23 15:55:22');

-- Table: poli
DROP TABLE IF EXISTS `poli`;
CREATE TABLE `poli` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_poli` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `poli` (`id`,`nama_poli`,`deskripsi`,`aktif`) VALUES ('1','Poli Umum','Pelayanan kesehatan umum','1');
INSERT INTO `poli` (`id`,`nama_poli`,`deskripsi`,`aktif`) VALUES ('2','Poli Gigi','Pelayanan kesehatan gigi dan mulut','1');
INSERT INTO `poli` (`id`,`nama_poli`,`deskripsi`,`aktif`) VALUES ('3','Poli Anak','Pelayanan kesehatan anak','1');

-- Table: rekam_medis
DROP TABLE IF EXISTS `rekam_medis`;
CREATE TABLE `rekam_medis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `antrian_id` int NOT NULL,
  `pasien_id` int NOT NULL,
  `dokter_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `anamnesis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Keluhan & riwayat penyakit',
  `diagnosa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kode_icd` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kode ICD-10',
  `tindakan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `resep` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Resep dalam format teks/JSON',
  `tekanan_darah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `catatan_dokter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `antrian_id` (`antrian_id`),
  KEY `pasien_id` (`pasien_id`),
  KEY `dokter_id` (`dokter_id`),
  CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`antrian_id`) REFERENCES `antrian` (`id`),
  CONSTRAINT `rekam_medis_ibfk_2` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`),
  CONSTRAINT `rekam_medis_ibfk_3` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rekam_medis` (`id`,`antrian_id`,`pasien_id`,`dokter_id`,`tanggal`,`anamnesis`,`diagnosa`,`kode_icd`,`tindakan`,`resep`,`tekanan_darah`,`suhu`,`berat_badan`,`tinggi_badan`,`catatan_dokter`,`created_at`) VALUES ('1','1','3','1','2026-06-22','','','','','Dexamethasone 0.5mg 1x1 (1)','','36.5',NULL,NULL,NULL,'2026-06-23 04:19:07');
INSERT INTO `rekam_medis` (`id`,`antrian_id`,`pasien_id`,`dokter_id`,`tanggal`,`anamnesis`,`diagnosa`,`kode_icd`,`tindakan`,`resep`,`tekanan_darah`,`suhu`,`berat_badan`,`tinggi_badan`,`catatan_dokter`,`created_at`) VALUES ('2','4','4','1','2026-06-24','','','','','Aciclovir 400mg 3x1 (10)','','36.5',NULL,NULL,NULL,'2026-06-23 16:05:53');

-- Table: roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`,`nama_role`,`deskripsi`,`created_at`) VALUES ('1','admin','Administrator penuh sistem','2026-06-22 21:16:06');
INSERT INTO `roles` (`id`,`nama_role`,`deskripsi`,`created_at`) VALUES ('2','operator','Petugas front office klinik','2026-06-22 21:16:06');
INSERT INTO `roles` (`id`,`nama_role`,`deskripsi`,`created_at`) VALUES ('3','user','Pasien / pengguna umum','2026-06-22 21:16:06');

-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL DEFAULT '3',
  `nama_lengkap` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('belum_verifikasi','aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_verifikasi',
  `token_verifikasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_profil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gagal_login` tinyint unsigned NOT NULL DEFAULT '0',
  `blokir_hingga` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`,`role_id`,`nama_lengkap`,`username`,`email`,`password_hash`,`status`,`token_verifikasi`,`foto_profil`,`gagal_login`,`blokir_hingga`,`last_login`,`created_at`,`updated_at`) VALUES ('1','1','Super Admin','admin','admin@klinik.local','$2y$12$PFG5m0/LzEetiZQf0N3OVuJ8c5Tsw8zWz..7G5/pyDkp6RcQ5q7uG','aktif',NULL,NULL,'0',NULL,'2026-06-23 15:48:28','2026-06-22 21:16:06','2026-06-23 15:48:28');
INSERT INTO `users` (`id`,`role_id`,`nama_lengkap`,`username`,`email`,`password_hash`,`status`,`token_verifikasi`,`foto_profil`,`gagal_login`,`blokir_hingga`,`last_login`,`created_at`,`updated_at`) VALUES ('3','2','Operator Klinik','operator','operator@klinik.local','$2y$12$/8vSJV.TBnUolzXBv8ZHwe0o/JXVBBv8BOKidi/.4IVAxtSwOCXS.','aktif',NULL,NULL,'0',NULL,'2026-06-23 02:03:41','2026-06-22 22:15:08','2026-06-23 02:03:41');
INSERT INTO `users` (`id`,`role_id`,`nama_lengkap`,`username`,`email`,`password_hash`,`status`,`token_verifikasi`,`foto_profil`,`gagal_login`,`blokir_hingga`,`last_login`,`created_at`,`updated_at`) VALUES ('5','3','operator1','operator1','operator1@berliano.com','$2y$12$p8y88NKHcGuSJ.KouImQF.E1wKCJrj6MISNVwNj8UZZ.kW8mkxz8m','aktif',NULL,NULL,'0',NULL,'2026-06-23 03:43:16','2026-06-22 22:24:08','2026-06-23 03:43:16');
INSERT INTO `users` (`id`,`role_id`,`nama_lengkap`,`username`,`email`,`password_hash`,`status`,`token_verifikasi`,`foto_profil`,`gagal_login`,`blokir_hingga`,`last_login`,`created_at`,`updated_at`) VALUES ('6','3','pasien','pasien','pasien@gmail.com','$2y$12$7vuM326XlNKXhW9/1sbSguvGvmJpmIeXbTGlysBJCP.c3aDAX81NW','aktif',NULL,NULL,'0',NULL,'2026-06-23 15:54:11','2026-06-23 15:53:27','2026-06-23 16:03:37');

