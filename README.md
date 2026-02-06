# API Manajemen Cuti Karyawan 🚀

RESTful API ini dirancang untuk menangani proses pengajuan cuti karyawan, lengkap dengan fitur autentikasi yang aman, manajemen hak akses (role), dan logika perhitungan kuota cuti otomatis. Proyek ini dibangun menggunakan **Laravel 11** dan menerapkan prinsip **Clean Architecture**.

---

## 🛠 Teknologi yang Digunakan

- **Framework:** Laravel 11
- **Database:** PostgreSQL
- **Authentication:** Laravel Sanctum (Token-based)
- **Architecture:** MVC + Service Repository Pattern (Clean Architecture)

---

## ✨ Fitur Utama

1.  **Autentikasi Aman:** Sistem login berbasis token menggunakan Laravel Sanctum.
2.  **Manajemen Role (RBAC):**
    - **Employee:** Dapat mengajukan cuti dan memantau status pengajuannya sendiri.
    - **Admin:** Memiliki akses penuh untuk melihat semua data dan melakukan persetujuan (Approve/Reject).
3.  **Logika Bisnis Lanjutan:**
    - **Manajemen Kuota:** Kuota dipotong otomatis saat pengajuan dibuat.
    - **Auto-Refund:** Jika pengajuan ditolak (Reject) oleh Admin, kuota karyawan akan otomatis dikembalikan (refund).
    - **Atomic Transactions:** Menggunakan Database Transactions untuk menjamin integritas data saat update kuota.
4.  **Upload File:** Validasi dan penyimpanan file lampiran (attachment) bukti cuti.

---

## 🏗 Penjelasan Arsitektur

Proyek ini menghindari praktik "Fat Controllers" dengan memisahkan logika ke dalam **Service Layer**:

- **Controllers (`LeaveController`)**: Hanya bertugas menerima request HTTP, validasi input, dan mengembalikan format respons JSON.
- **Services (`LeaveService`)**: Menangani logika bisnis yang kompleks (perhitungan tanggal, upload file, pengurangan kuota, dan transaksi database).
- **Middleware (`IsAdmin`)**: Menjaga keamanan endpoint sensitif agar hanya bisa diakses oleh Admin.

---

## ⚙️ Instalasi & Pengaturan

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal Anda.

### 1. Prasyarat
Pastikan software berikut sudah terinstall:
- PHP >= 8.2
- Composer
- PostgreSQL

### 2. Langkah Instalasi

```bash
# Clone repository
git clone <url-repository-anda>
cd backend-cuti

# Install dependencies PHP
composer install

# Buat file Environment
cp .env.example .env

# Generate Application Key
php artisan key:generate
