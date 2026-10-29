# Laporan Proyek: Sistem Informasi Manajemen Klinik Medika Utama

## 1. Pendahuluan

Laporan ini membahas implementasi Sistem Informasi Manajemen Klinik Medika Utama, sebuah aplikasi web berbasis PHP dan MySQL yang dirancang untuk mengelola operasional klinik secara digital. Sistem ini mengintegrasikan manajemen pasien, dokter, antrian, rekam medis, dan inventori obat dalam satu platform terpusat.

## 2. Tujuan Proyek

- Menyediakan sistem informasi klinik yang terintegrasi dan mudah digunakan
- Meningkatkan efisiensi pelayanan kesehatan melalui digitasi proses
- Menjamin keamanan dan privasi data medis pasien
- Mendukung tiga peran pengguna: Admin, Operator, dan Pasien

## 3. Teknologi yang Digunakan

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| **Bahasa Pemrograman** | PHP | 8.2+ |
| **Database** | MySQL | 8.0+ |
| **Frontend** | HTML5, CSS3, JavaScript | - |
| **Server Web** | Apache (Laragon) / PHP Built-in Server | - |
| **Session Management** | PHP Native Session dengan keamanan enhanced | - |

## 4. Arsitektur Sistem

### 4.1 Struktur Direktori

```
klinik/
├── admin/              # Modul administrator
│   ├── antrian.php
│   ├── audit.php
│   ├── backup.php
│   ├── cetak_laporan.php
│   ├── dokter.php
│   ├── index.php
│   ├── obat.php
│   ├── pasien.php
│   ├── rekam_medis.php
│   └── users.php
├── operator/           # Modul petugas front office
│   ├── antrian.php
│   ├── dokter.php
│   ├── index.php
│   └── pasien.php
├── user/               # Modul pasien
│   ├── antrian.php
│   ├── index.php
│   ├── profil.php
│   └── rekam_medis.php
├── auth/               # Autentikasi
│   ├── captcha.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── verify.php
├── includes/           # File konfigurasi dan layout
│   ├── config.php
│   ├── footer.php
│   └── header.php
├── assets/
│   └── css/
│       └── main.css
├── backups/            # Folder backup database
├── database.sql        # Skema database
├── database_new.sql
├── index.php           # Entry point
└── seeder_obat.php     # Seed data obat
```

### 4.2 Alur Aplikasi

```
User mengakses aplikasi
          ↓
    [Belum Login?]
       ↙        ↘
    Ya           Tidak
    ↓              ↓
Login Page    Dashboard (by Role)
    ↓
[Verifikasi Kredensial]
    ↓
[Redirect sesuai Role]
    ↓
  ┌───────┬───────────┬────────┐
  │ Admin │ Operator  │  User  │
  └───────┴───────────┴────────┘
```

## 5. Fitur-Fitur Utama

### 5.1 Modul Admin (Administrator)

1. **Dashboard** - Ringkasan data klinik
2. **Manajemen User** - CRUD akun pengguna dengan role-based access
3. **Data Dokter** - Manajemen profil dokter dan jadwal
4. **Data Pasien** - Registrasi dan manajemen data pasien
5. **Antrian** - Monitoring dan pengelolaan antrian pasien
6. **Rekam Medis** - Input dan pembuatan rekam medis elektronik
7. **Manajemen Obat** - Inventori dan stok obat
8. **Audit Log** - Pencatatan aktivitas sistem untuk keamanan
9. **Cetak Laporan** - Generate laporan dalam format cetak
10. **Backup Database** - Backup dan restore database

### 5.2 Modul Operator (Front Office)

1. **Dashboard** - Overview operasional
2. **Data Pasien** - Registrasi pasien baru
3. **Kelola Antrian** - Pendaftaran dan pengelolaan antrian
4. **Jadwal Dokter** - Informasi jadwal praktik dokter

### 5.3 Modul User (Pasien)

1. **Beranda** - Dashboard pribadi pasien
2. **Daftar Antrian** - Pendaftaran antrian online
3. **Rekam Medis** - Riwayat kunjungan dan diagnosis
4. **Profil Saya** - Edit data pribadi

## 6. Desain Database

### 6.1 Tabel-Tabel Utama

| Tabel | Deskripsi |
|-------|------------|
| `roles` | Menyimpan peran pengguna (admin, operator, user) |
| `users` | Data akun pengguna sistem |
| `dokter` | Profil dan jadwal dokter |
| `pasien` | Data medis dan identitas pasien |
| `poli` | Jenis layanan poliklinik |
| `antrian` | Data antrian pasien per dokter |
| `rekam_medis` | Rekam medis pasien (anamnesis, diagnosa, resep) |
| `obat` | Inventori obat klinik |
| `audit_log` | Log keamanan dan aktivitas pengguna |

### 6.2 Relasi Database

```
roles 1───∞ users
users 1───∞ dokter
users 1───∞ pasien
dokter 1───∞ antrian
pasien 1───∞ antrian
poli 1───∞ antrian
antrian 1───1 rekam_medis
users 1───∞ audit_log
```

## 7. Keamanan Sistem

### 7.1 Fitur Keamanan yang Diterapkan

1. **Prepared Statements** - Mencegah SQL Injection
2. **CSRF Token** - Proteksi terhadap Cross-Site Request Forgery
3. **Password Hashing** - Menggunakan bcrypt dengan cost factor 12
4. **Session Security** - Session regeneration dan secure session handling
5. **Login Attempt Limiting** - Blokir akun setelah 5 kali percobaan gagal
6. **CAPTCHA** - Verifikasi keamanan pada form login
7. **Input Sanitization** - Filter dan sanitasi semua input user
8. **Audit Logging** - Pencatatan semua aktivitas penting
9. **Role-Based Access Control (RBAC)** - Hak akses berdasarkan role

### 7.2 Konfigurasi Keamanan

```php
define('MAX_LOGIN_ATTEMPTS', 5);        // Maksimal percobaan login
define('LOCKOUT_MINUTES', 5);           // Durasi blokir akun (menit)
define('CAPTCHA_SESSION_KEY', 'captcha_answer');
```

## 8. Kredensial Akun Demo

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `Admin@1234` |
| Operator | `operator` | `Admin@1234` |
| Pasien | `pasien` | `Admin@1234` |

## 9. Panduan Instalasi dan Menjalankan

### 9.1 Persyaratan Sistem

- PHP 8.2 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Web Server (Apache/Nginx) atau PHP Built-in Server
- Minimal 256MB RAM
- Extension PHP: PDO, PDO_MySQL, openssl, session

### 9.2 Langkah Instalasi

1. **Clone/Extract Project**
   ```bash
   # Letakkan project di folder web server
   # Contoh untuk Laragon: C:\laragon\www\klinik
   ```

2. **Konfigurasi Database**
   ```bash
   # Import database
   mysql -u root -p < database.sql
   ```

3. **Konfigurasi Koneksi Database**
   
   Edit file `includes/config.php` sesuai environment:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'klinik_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('BASE_URL', 'http://localhost:8000');
   ```

4. **Jalankan Server**
   
   Opsi A - Menggunakan Laragon:
   ```bash
   # Pastikan Laragon sudah running
   # Akses via browser atau start Apache & MySQL di Laragon
   ```
   
   Opsi B - PHP Built-in Server:
   ```bash
   cd C:\laragon\www\klinik
   php -S localhost:8000
   ```

5. **Akses Aplikasi**
   ```
   http://localhost:8000/auth/login.php
   ```

## 10. Cara Penggunaan

### 10.1 Login Pertama Kali

1. Buka browser dan akses `http://localhost:8000/auth/login.php`
2. Masukkan kredensial demo:
   - Username: `admin`
   - Password: `Admin@1234`
3. Sistem akan redirect ke Dashboard Admin

### 10.2 Alur Kerja Admin

```
Dashboard
   ↓
├── Kelola User (tambah/edit/hapus akun)
├── Kelola Dokter (tambah/edit data dokter)
├── Kelola Pasien (registrasi pasien)
├── Kelola Antrian (monitor antrian)
├── Input Rekam Medis (diagnosa + resep)
├── Kelola Obat (stok dan harga)
├── Backup Database (export/import)
└── Audit Log (monitor aktivitas)
```

### 10.3 Alur Kerja Operator

```
Dashboard Operator
      ↓
├── Registrasi Pasien Baru
├── Buat Antrian (pilih dokter/poli)
├── Lihat Jadwal Dokter
└── Cetak Tiket Antrian
```

### 10.4 Alur Kerja Pasien

```
Dashboard Pasien
      ↓
├── Daftar Antrian Online
├── Lihat Riwayat Rekam Medis
└── Edit Profil Pribadi
```

## 11. Keunggulan Sistem

1. **Role-Based Access Control** - Setiap user melihat sesuai hak aksesnya
2. **Responsive Design** - Tampilan menyesuaikan perangkat mobile
3. **Keamanan Berlapis** - CSRF, SQL Injection protection, session security
4. **Audit Trail** - Semua aktivitas tercatat untuk compliance
5. **Backup & Restore** - Fitur cadangan database terintegrasi
6. **Cetak Laporan** - Output laporan dalam format printable
7. **CAPTCHA Protection** - Mencegah brute force attack
8. **Auto-logout Blocker** - Proteksi akun dari percobaan login berulang

## 12. Keterbatasan dan Pengembangan Masa Depan

### 12.1 Keterbatasan Saat Ini

- Belum ada notifikasi real-time untuk antrian
- Belum ada integrasi payment gateway
- Belum ada mobile app (hanya web)
- Laporan masih dalam format dasar

### 12.2 Saran Pengembangan

1. **Notifikasi Real-time** - Implementasi WebSocket untuk update antrian
2. **SMS/Email Notification** - Notifikasi jadwal dan reminder
3. **Mobile Application** - Develop Android/iOS app
4. **Integrasi BPJS** - Koneksi dengan sistem BPJS Kesehatan
5. **Laporan Advanced** - Grafik dan chart untuk statistik
6. **Multi-branch Support** - Dukungan multiple cabang klinik
7. **API REST** - Untuk integrasi dengan sistem lain

## 13. Kesimpulan

Sistem Informasi Manajemen Klinik Medika Utama merupakan solusi komprehensif untuk mengelola operasional klinik secara digital. Dengan memiliki tiga modul pengguna yang berbeda (Admin, Operator, dan Pasien), sistem ini mampu menjangkau seluruh kebutuhan stakeholders klinik. Fitur keamanan yang berlapas dan audit logging memastikan compliance terhadap regulasi kesehatan dan perlindungan data pasien. Sistem ini siap digunakan untuk mensuport operasional klinik di dunia nyata dengan beberapa tambahan pengembangan di masa depan.

---

## Lampiran

### A. Struktur Database Detail

```sql
-- Total 9 tabel utama:
-- 1. roles
-- 2. users  
-- 3. dokter
-- 4. pasien
-- 5. poli
-- 6. antrian
-- 7. rekam_medis
-- 8. obat
-- 9. audit_log
```

### B. Daftar File PHP

| File | Fungsi |
|------|--------|
| `index.php` | Entry point aplikasi |
| `auth/login.php` | Form login |
| `auth/logout.php` | Proses logout |
| `auth/register.php` | Registrasi akun |
| `auth/verify.php` | Verifikasi email |
| `auth/captcha.php` | Generator CAPTCHA |
| `admin/*.php` | Module admin |
| `operator/*.php` | Module operator |
| `user/*.php` | Module pasien |
| `includes/config.php` | Konfigurasi aplikasi |

### C. Environment & Deployment

- **Development**: PHP Built-in Server (port 8000)
- **Production**: Apache/Laragon (port 80)
- **Database**: MySQL 8.0.3 (via Laragon)
- **Browser Compatibility**: Chrome, Firefox, Edge, Safari (modern versions)


---

*Laporan dibuat pada: 23 Juni 2026*  
*Sistem: Klinik Medika Utama v1.0.0*