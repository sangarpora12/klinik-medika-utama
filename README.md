# Klinik Medika Utama - Sistem Informasi Manajemen (SIM) Klinik

Sistem Informasi Manajemen Klinik berbasis web yang dibangun menggunakan **PHP Native** dan **MySQL**. Aplikasi ini dirancang untuk mengelola operasional layanan kesehatan secara terintegrasi dengan sistem *multi-role*, manajemen rekam medis, antrean pasien, serta sistem keamanan yang andal.

---

## 🚀 Fitur Utama

### 1. Multi-Role Access Control
* **Admin:** Mengelola data pengguna (Admin, Operator, Pasien), memantau aktivitas sistem (*Audit Log*), dan mencetak laporan operasional.
* **Operator:** Menangani operasional harian seperti pengelolaan data dokter, pasien, dan antrean layanan.
* **Pasien (User):** Mendaftar antrean secara *online*, melihat status antrean hari ini, serta mengakses riwayat rekam medis pribadi.

### 2. Modul Operasional & Medis
* **Manajemen Antrean:** Sistem pendaftaran dan pemantauan nomor antrean harian secara *real-time*.
* **Rekam Medis:** Pencatatan riwayat berobat, keluhan, diagnosa, dan tindakan medis pasien.
* **Manajemen Obat:** Pengelolaan inventaris data obat klinik.
* **Cetak Laporan:** Fitur rekapitulasi data untuk kebutuhan administratif.

### 3. Keamanan & Audit Sistem
* **Proteksi Keamanan:** Dilengkapi pencegahan *Cross-Site Scripting* (`htmlspecialchars`), proteksi *Cross-Site Request Forgery* (CSRF), serta verifikasi keamanan (*captcha* matematika) pada halaman *login*.
* **Audit Log:** Pencatatan otomatis setiap aktivitas penting pengguna (*login* sukses, *login* gagal, perubahan data) lengkap dengan alamat IP dan *timestamp* untuk transparansi sistem.

---

## 💻 Teknologi yang Digunakan
* **Backend:** PHP Native (Structured / OOP)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (Modern Responsive Dashboard UI)
* **Server Lokal:** XAMPP / Laragon

---

## ⚙️ Cara Instalasi & Menjalankan (Local Development)

1. *Clone repository* ini atau *download* sebagai ZIP.
2. Pindahkan folder proyek ke direktori server lokal (misalnya `htdocs` di XAMPP atau `www` di Laragon).
3. Buat database baru di MySQL dengan nama `klinik_db`.
4. *Import* file database (`.sql`) yang disertakan ke dalam database `klinik_db`.
5. Sesuaikan konfigurasi koneksi database pada file `includes/config.php` jika diperlukan (default: `localhost`, username: `root`, password: kosong).
6. Jalankan server lokal dan akses melalui browser:
   ```text
   http://localhost:8000/auth/login.php
   ```