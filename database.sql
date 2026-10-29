-- ============================================================
-- SISTEM INFORMASI KLINIK - DATABASE SCHEMA
-- Versi: 1.0 | Engine: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS klinik_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE klinik_db;

-- ============================================================
-- TABEL 1: roles
-- ============================================================
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (nama_role, deskripsi) VALUES
('admin',    'Administrator penuh sistem'),
('operator', 'Petugas front office klinik'),
('user',     'Pasien / pengguna umum');

-- ============================================================
-- TABEL 2: users
-- ============================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL DEFAULT 3,
    nama_lengkap VARCHAR(150) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('belum_verifikasi','aktif','nonaktif') NOT NULL DEFAULT 'belum_verifikasi',
    token_verifikasi VARCHAR(100) DEFAULT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    gagal_login TINYINT UNSIGNED NOT NULL DEFAULT 0,
    blokir_hingga DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Seed: admin awal  (password: Admin@1234)
INSERT INTO users (role_id, nama_lengkap, username, email, password_hash, status) VALUES
(1, 'Super Admin', 'admin', 'admin@klinik.local',
 '$2y$12$kWFdMlSXMq3FqtS5Xvs.IuKvMHAnWjIHXlqzF3FiJk7vIbH3J2aEO', 'aktif');

-- ============================================================
-- TABEL 3: dokter
-- ============================================================
CREATE TABLE dokter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,  -- bisa terhubung ke akun, bisa tidak
    nama VARCHAR(150) NOT NULL,
    spesialisasi VARCHAR(100) NOT NULL,
    no_sip VARCHAR(60) UNIQUE,
    telepon VARCHAR(20),
    email VARCHAR(150),
    foto VARCHAR(255),
    jadwal JSON COMMENT 'Contoh: {"senin":"08:00-12:00","rabu":"13:00-17:00"}',
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO dokter (nama, spesialisasi, no_sip, telepon, aktif) VALUES
('dr. Anisa Rahmawati', 'Umum',         'SIP.001/2024', '081200000001', TRUE),
('dr. Budi Santoso',   'Gigi',          'SIP.002/2024', '081200000002', TRUE),
('dr. Citra Dewi',     'Anak',          'SIP.003/2024', '081200000003', TRUE);

-- ============================================================
-- TABEL 4: pasien
-- ============================================================
CREATE TABLE pasien (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    no_rm VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor Rekam Medis',
    nama_lengkap VARCHAR(150) NOT NULL,
    nik VARCHAR(20) UNIQUE,
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L','P') NOT NULL,
    gol_darah ENUM('A','B','AB','O','tidak_diketahui') DEFAULT 'tidak_diketahui',
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(150),
    alergi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 5: poli (poliklinik / layanan)
-- ============================================================
CREATE TABLE poli (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_poli VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    aktif BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

INSERT INTO poli (nama_poli, deskripsi) VALUES
('Poli Umum',      'Pelayanan kesehatan umum'),
('Poli Gigi',      'Pelayanan kesehatan gigi dan mulut'),
('Poli Anak',      'Pelayanan kesehatan anak');

-- ============================================================
-- TABEL 6: antrian
-- ============================================================
CREATE TABLE antrian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    poli_id INT NOT NULL,
    operator_id INT DEFAULT NULL COMMENT 'Operator yang mendaftarkan',
    tanggal DATE NOT NULL,
    no_antrian SMALLINT UNSIGNED NOT NULL,
    keluhan TEXT,
    status ENUM('menunggu','dipanggil','selesai','batal') NOT NULL DEFAULT 'menunggu',
    jam_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    jam_panggil DATETIME DEFAULT NULL,
    jam_selesai DATETIME DEFAULT NULL,
    UNIQUE KEY uniq_antrian (dokter_id, tanggal, no_antrian),
    FOREIGN KEY (pasien_id)  REFERENCES pasien(id),
    FOREIGN KEY (dokter_id)  REFERENCES dokter(id),
    FOREIGN KEY (poli_id)    REFERENCES poli(id),
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 7: rekam_medis
-- ============================================================
CREATE TABLE rekam_medis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    antrian_id INT NOT NULL UNIQUE,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    tanggal DATE NOT NULL,
    anamnesis TEXT COMMENT 'Keluhan & riwayat penyakit',
    diagnosa TEXT,
    kode_icd VARCHAR(20) COMMENT 'Kode ICD-10',
    tindakan TEXT,
    resep TEXT COMMENT 'Resep dalam format teks/JSON',
    tekanan_darah VARCHAR(20),
    suhu DECIMAL(4,1),
    berat_badan DECIMAL(5,2),
    tinggi_badan DECIMAL(5,2),
    catatan_dokter TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (antrian_id) REFERENCES antrian(id),
    FOREIGN KEY (pasien_id)  REFERENCES pasien(id),
    FOREIGN KEY (dokter_id)  REFERENCES dokter(id)
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 8: obat
-- ============================================================
CREATE TABLE obat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_obat VARCHAR(150) NOT NULL,
    kategori VARCHAR(80),
    satuan VARCHAR(30) NOT NULL DEFAULT 'tablet',
    stok INT NOT NULL DEFAULT 0,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 9: audit_log (keamanan)
-- ============================================================
CREATE TABLE audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    aksi VARCHAR(100) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_aksi (aksi),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;
