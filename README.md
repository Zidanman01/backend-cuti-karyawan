# Dokumentasi Sistem Manajemen Cuti Karyawan
Dokumen ini berisi penjelasan mengenai arsitektur, logika bisnis, dan panduan penggunaan API untuk Sistem Manajemen Cuti Karyawan. Sistem ini dibangun menggunakan Laravel 11 dengan pendekatan Clean Architecture untuk memastikan kode yang terstruktur, aman, dan mudah dikembangkan.

## Arsitektur Sistem
Saya tidak menumpuk logika pemrograman di dalam Controller sistem ini. Saya memisahkan tanggung jawab kode menjadi 3 layer untuk menjaga kualitas dan keterbacaan:

1.  **Service Layer (`LeaveService`)**
    * Bertindak sebagai pusat logika bisnis.
    * Menangani kalkulasi durasi cuti, perhitungan sisa kuota, logika upload file, dan eksekusi transaksi database.
    * Menjamin integritas data: Jika terjadi kesalahan saat penyimpanan, semua perubahan data akan dibatalkan (rollback).

2.  **Controller Layer (`LeaveController`)**
    * Hanya bertugas menerima permintaan HTTP (Request).
    * Memvalidasi input dari pengguna.
    * Memanggil Service Layer untuk memproses data.
    * Mengembalikan respon dalam format JSON yang standar.

3.  **Middleware (`IsAdmin`)**
    * Bertugas sebagai penjaga keamanan untuk rute khusus.
    * Memastikan hanya pengguna dengan peran "Admin" yang dapat mengakses fitur persetujuan cuti.

## Logika Bisnis Utama
Saya menerapkan aturan yang ketat untuk menjaga akurasi data cuti. Saya menggunakan 3 metode untuk memastikan data cuti tetap akurat:

* **Pemotongan Kuota di Awal (Deduct First):**
    Saat karyawan mengajukan cuti, kuota mereka langsung dikurangi oleh sistem. Hal ini dilakukan untuk mencegah karyawan mengajukan cuti melebihi sisa kuota yang dimiliki dalam waktu yang bersamaan.

* **Pengembalian Kuota Otomatis (Auto-Refund):**
    Apabila pengajuan cuti ditolak (Rejected) oleh Admin, sistem secara otomatis akan mengembalikan kuota cuti ke saldo karyawan sesuai dengan jumlah hari yang diajukan.

* **Validasi Role:**
    * **Employee:** Hanya dapat mengajukan cuti dan melihat riwayat cuti miliknya sendiri.
    * **Admin:** Memiliki akses penuh untuk melihat seluruh data cuti karyawan dan melakukan aksi persetujuan.

## Panduan Penggunaan Sistem
Saya menggunakan Postman untuk API Testing, disarankan menggunakan Postman untuk hasil yang lebih akurat. 
Berikut adalah alur kerja dan daftar endpoint API yang tersedia dalam sistem ini.

### 1. Autentikasi Pengguna
Setiap permintaan ke API (kecuali Login dan Register) wajib menyertakan **Bearer Token** pada Header Authorization.

**A. Register User Baru**
Digunakan untuk mendaftarkan akun Admin atau Employee baru.

* **Endpoint:** `POST /api/auth/register`
* **Body (JSON):**
    ```json
    {
        "name": "Nama User",
        "email": "user@email.com",
        "password": "password123",
        "role": "admin"  // atau "employee"
    }
    ```

**B. Login**
Digunakan untuk mendapatkan Token Akses.

* **Endpoint:** `POST /api/auth/login`
* **Body (JSON):**
    ```json
    {
        "email": "user@email.com",
        "password": "password123"
    }
    ```

### 2. Manajemen Cuti (Employee)
Karyawan menggunakan endpoint ini untuk mengajukan permohonan cuti.

**Ajukan Cuti**
Wajib menggunakan tipe konten `multipart/form-data` karena menyertakan file lampiran.

* **Endpoint:** `POST /api/leaves`
* **Authorization:** `Auth Type: Bearer Token, Token: <token-employee>`
* **Body (Form-Data):**
    * `start_date`: 2026-03-01 (Format YYYY-MM-DD)
    * `end_date`: 2026-03-03
    * `reason`: Keperluan keluarga
    * `attachment`: [File Gambar/PDF]

### 3. Manajemen Persetujuan (Admin)
Admin menggunakan endpoint ini untuk menyetujui atau menolak permohonan yang masuk.

**A. Lihat Semua Cuti**
Admin akan melihat seluruh data cuti dari semua karyawan.

* **Endpoint:** `GET /api/leaves`
* **Authorization:** `Auth Type: Bearer Token, Token: <token-admin>`

**B. Proses Cuti (Approve/Reject)**
Mengubah status pengajuan cuti. Jika status diubah menjadi `rejected`, kuota karyawan akan dikembalikan.

* **Endpoint:** `PUT /api/leaves/{id_cuti}/approval`
* **Authorization:** `Auth Type: Bearer Token, Token: <token-admin>`
* **Body (JSON) - Untuk Menyetujui:**
    ```json
    {
        "status": "approved"
    }
    ```
* **Body (JSON) - Untuk Menolak:**
    ```json
    {
        "status": "rejected",
        "rejection_reason": "Jadwal terlalu padat"
    }
    ```

### 4. Contoh Skenario Pengujian
Untuk memverifikasi fungsionalitas sistem, Anda dapat menjalankan skenario berikut:

1.  **Register** dua akun: satu sebagai `admin`, satu sebagai `employee`.
2.  **Login** sebagai `employee` dan catat Token-nya.
3.  **Ajukan Cuti** menggunakan Token Employee. Pastikan melampirkan file bukti.
4.  Periksa database, pastikan kuota karyawan berkurang.
5.  **Login** sebagai `admin` dan catat Token-nya.
6.  **Tolak (Reject)** pengajuan cuti tersebut menggunakan Token Admin.
7.  Periksa kembali database atau endpoint user profil, pastikan kuota karyawan telah kembali seperti semula (Refund berhasil).

## API Documentation
Berikut adalah link untuk dokumentasi API Testing yang saya lakukan di Postman:
https://documenter.getpostman.com/view/51287657/2sBXc8p3bL
