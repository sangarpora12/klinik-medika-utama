-- ============================================================
-- SISTEM INFORMASI KLINIK MEDIKA UTAMA - DATABASE SCHEMA
-- Versi: 3.0 | Engine: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS kmu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kmu_db;

-- ============================================================
-- TABEL 1: hak_akses (sebelumnya: roles)
-- ============================================================
CREATE TABLE hak_akses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label_akses VARCHAR(50) NOT NULL UNIQUE,
    keterangan TEXT,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO hak_akses (label_akses, keterangan) VALUES
('admin',    'Administrator penuh sistem'),
('operator', 'Petugas front office klinik'),
('user',     'Pasien / pengguna umum');

-- ============================================================
-- TABEL 2: pengguna_sistem (sebelumnya: users)
-- ============================================================
CREATE TABLE pengguna_sistem (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_akses INT NOT NULL DEFAULT 3,
    nama_lengkap VARCHAR(150) NOT NULL,
    nama_akses VARCHAR(60) NOT NULL UNIQUE COMMENT 'sebelumnya: username',
    alamat_email VARCHAR(150) NOT NULL UNIQUE COMMENT 'sebelumnya: email',
    kata_sandi VARCHAR(255) NOT NULL COMMENT 'sebelumnya: password_hash',
    status_akun ENUM('belum_verifikasi','aktif','nonaktif') NOT NULL DEFAULT 'belum_verifikasi',
    token_verif VARCHAR(100) DEFAULT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    gagal_masuk TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'sebelumnya: gagal_login',
    blokir_sampai DATETIME DEFAULT NULL COMMENT 'sebelumnya: blokir_hingga',
    terakhir_masuk DATETIME DEFAULT NULL COMMENT 'sebelumnya: last_login',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_akses) REFERENCES hak_akses(id)
) ENGINE=InnoDB;

-- Seed: Admin awal (password: Admin@1234)
INSERT INTO pengguna_sistem (id_akses, nama_lengkap, nama_akses, alamat_email, kata_sandi, status_akun) VALUES
(1, 'Super Admin', 'admin', 'admin@klinik.local',
 '$2y$12$PFG5m0/LzEetiZQf0N3OVuJ8c5Tsw8zWz..7G5/pyDkp6RcQ5q7uG', 'aktif'),
(2, 'Operator Klinik', 'operator', 'operator@klinik.local',
 '$2y$12$PFG5m0/LzEetiZQf0N3OVuJ8c5Tsw8zWz..7G5/pyDkp6RcQ5q7uG', 'aktif'),
(3, 'Pasien Demo', 'pasien', 'pasien@klinik.local',
 '$2y$12$PFG5m0/LzEetiZQf0N3OVuJ8c5Tsw8zWz..7G5/pyDkp6RcQ5q7uG', 'aktif');

-- ============================================================
-- TABEL 3: tenaga_medis (sebelumnya: dokter)
-- ============================================================
CREATE TABLE tenaga_medis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pengguna INT DEFAULT NULL COMMENT 'sebelumnya: user_id',
    nama_dokter VARCHAR(150) NOT NULL COMMENT 'sebelumnya: nama',
    bidang_keahlian VARCHAR(100) NOT NULL COMMENT 'sebelumnya: spesialisasi',
    nomor_izin VARCHAR(60) UNIQUE COMMENT 'sebelumnya: no_sip',
    kontak VARCHAR(20) COMMENT 'sebelumnya: telepon',
    surel VARCHAR(150) COMMENT 'sebelumnya: email',
    foto_dokter VARCHAR(255) COMMENT 'sebelumnya: foto',
    jadwal_praktek JSON COMMENT 'sebelumnya: jadwal',
    status_aktif BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'sebelumnya: aktif',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna_sistem(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO tenaga_medis (nama_dokter, bidang_keahlian, nomor_izin, kontak, status_aktif) VALUES
('dr. Anisa Rahmawati, Sp.PD',  'Penyakit Dalam',  'SIP.101/2025', '081300000001', TRUE),
('dr. Budi Santoso, Sp.BM',     'Bedah Mulut',     'SIP.102/2025', '081300000002', TRUE),
('dr. Citra Dewi, Sp.A',        'Kesehatan Anak',  'SIP.103/2025', '081300000003', TRUE);

-- ============================================================
-- TABEL 4: data_pengunjung (sebelumnya: pasien)
-- ============================================================
CREATE TABLE data_pengunjung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pengguna INT DEFAULT NULL COMMENT 'sebelumnya: user_id',
    nomor_rekam VARCHAR(20) NOT NULL UNIQUE COMMENT 'sebelumnya: no_rm',
    nama_pasien VARCHAR(150) NOT NULL COMMENT 'sebelumnya: nama_lengkap',
    nik_ktp VARCHAR(20) UNIQUE COMMENT 'sebelumnya: nik',
    tgl_lahir DATE COMMENT 'sebelumnya: tanggal_lahir',
    gender ENUM('L','P') NOT NULL COMMENT 'sebelumnya: jenis_kelamin',
    golongan_darah ENUM('A','B','AB','O','tidak_diketahui') DEFAULT 'tidak_diketahui' COMMENT 'sebelumnya: gol_darah',
    alamat_rumah TEXT COMMENT 'sebelumnya: alamat',
    no_hp VARCHAR(20) COMMENT 'sebelumnya: telepon',
    email_pasien VARCHAR(150) COMMENT 'sebelumnya: email',
    riwayat_alergi TEXT COMMENT 'sebelumnya: alergi',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna_sistem(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 5: layanan_klinik (sebelumnya: poli)
-- ============================================================
CREATE TABLE layanan_klinik (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_layanan VARCHAR(100) NOT NULL COMMENT 'sebelumnya: nama_poli',
    deskripsi_layanan TEXT COMMENT 'sebelumnya: deskripsi',
    aktif BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

INSERT INTO layanan_klinik (nama_layanan, deskripsi_layanan) VALUES
('Klinik Umum',     'Pelayanan kesehatan umum dan konsultasi medis dasar'),
('Klinik Gigi',     'Perawatan kesehatan gigi dan mulut menyeluruh'),
('Klinik Anak',     'Pelayanan kesehatan khusus anak dan tumbuh kembang');

-- ============================================================
-- TABEL 6: jadwal_kunjungan (sebelumnya: antrian)
-- ============================================================
CREATE TABLE jadwal_kunjungan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pengunjung INT NOT NULL COMMENT 'sebelumnya: pasien_id',
    id_medis INT NOT NULL COMMENT 'sebelumnya: dokter_id',
    id_layanan INT NOT NULL COMMENT 'sebelumnya: poli_id',
    id_petugas INT DEFAULT NULL COMMENT 'sebelumnya: operator_id',
    tgl_kunjungan DATE NOT NULL COMMENT 'sebelumnya: tanggal',
    nomor_antrian SMALLINT UNSIGNED NOT NULL COMMENT 'sebelumnya: no_antrian',
    keluhan_utama TEXT COMMENT 'sebelumnya: keluhan',
    status_kunjungan ENUM('menunggu','dipanggil','selesai','batal') NOT NULL DEFAULT 'menunggu' COMMENT 'sebelumnya: status',
    waktu_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'sebelumnya: jam_daftar',
    waktu_panggil DATETIME DEFAULT NULL COMMENT 'sebelumnya: jam_panggil',
    waktu_selesai DATETIME DEFAULT NULL COMMENT 'sebelumnya: jam_selesai',
    UNIQUE KEY kunci_antrian (id_medis, tgl_kunjungan, nomor_antrian),
    FOREIGN KEY (id_pengunjung) REFERENCES data_pengunjung(id),
    FOREIGN KEY (id_medis)      REFERENCES tenaga_medis(id),
    FOREIGN KEY (id_layanan)    REFERENCES layanan_klinik(id),
    FOREIGN KEY (id_petugas)    REFERENCES pengguna_sistem(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 7: riwayat_pemeriksaan (sebelumnya: rekam_medis)
-- ============================================================
CREATE TABLE riwayat_pemeriksaan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_kunjungan INT NOT NULL UNIQUE COMMENT 'sebelumnya: antrian_id',
    id_pengunjung INT NOT NULL COMMENT 'sebelumnya: pasien_id',
    id_medis INT NOT NULL COMMENT 'sebelumnya: dokter_id',
    tgl_periksa DATE NOT NULL COMMENT 'sebelumnya: tanggal',
    anamnesa TEXT COMMENT 'sebelumnya: anamnesis',
    diagnosa_akhir TEXT COMMENT 'sebelumnya: diagnosa',
    kode_icd_diagnosa VARCHAR(20) COMMENT 'sebelumnya: kode_icd',
    tindakan_medis TEXT COMMENT 'sebelumnya: tindakan',
    resep_obat TEXT COMMENT 'sebelumnya: resep',
    tensi_darah VARCHAR(20) COMMENT 'sebelumnya: tekanan_darah',
    suhu_tubuh DECIMAL(4,1) COMMENT 'sebelumnya: suhu',
    berat_badan DECIMAL(5,2),
    tinggi_badan DECIMAL(5,2),
    catatan_dokter TEXT,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kunjungan)   REFERENCES jadwal_kunjungan(id),
    FOREIGN KEY (id_pengunjung)  REFERENCES data_pengunjung(id),
    FOREIGN KEY (id_medis)       REFERENCES tenaga_medis(id)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 8: stok_farmasi (sebelumnya: obat)
-- ============================================================
CREATE TABLE stok_farmasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_obat VARCHAR(150) NOT NULL,
    kelompok_obat VARCHAR(80) COMMENT 'sebelumnya: kategori',
    satuan_obat VARCHAR(30) NOT NULL DEFAULT 'tablet' COMMENT 'sebelumnya: satuan',
    jumlah_stok INT NOT NULL DEFAULT 0 COMMENT 'sebelumnya: stok',
    harga_satuan DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'sebelumnya: harga',
    catatan TEXT COMMENT 'sebelumnya: keterangan',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO stok_farmasi (nama_obat, kelompok_obat, satuan_obat, jumlah_stok, harga_satuan) VALUES
('Paracetamol 500mg', 'Analgesik', 'tablet', 500, 5000.00),
('Amoxicillin 500mg', 'Antibiotik', 'kapsul', 300, 12000.00),
('Vitamin C 100mg',   'Vitamin',   'tablet', 1000, 3500.00);

-- ============================================================
-- TABEL 9: catatan_aktivitas (sebelumnya: audit_log)
-- ============================================================
CREATE TABLE catatan_aktivitas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_pengguna INT DEFAULT NULL COMMENT 'sebelumnya: user_id',
    aksi VARCHAR(100) NOT NULL,
    detail_aksi TEXT COMMENT 'sebelumnya: detail',
    alamat_ip VARCHAR(45) COMMENT 'sebelumnya: ip_address',
    peramban TEXT COMMENT 'sebelumnya: user_agent',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pengguna (id_pengguna),
    INDEX idx_aksi (aksi),
    INDEX idx_waktu (dibuat_pada)
) ENGINE=InnoDB;