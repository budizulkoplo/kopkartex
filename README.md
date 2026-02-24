https://kopkartex.com/logo.png
# Kopkartex

Aplikasi sistem manajemen koperasi berbasis web untuk membantu pengelolaan transaksi, anggota, dan laporan keuangan secara terstruktur dan efisien.

---

## 🚀 Fitur Utama

- ✅ Manajemen Anggota
- ✅ Transaksi Simpanan & Pinjaman
- ✅ Manajemen Kas & Rekening
- ✅ Laporan Keuangan
- ✅ Multi User & Role Management
- ✅ Upload Bukti Transaksi
- ✅ Dashboard Monitoring

---

## 🛠️ Tech Stack

- **Backend** : Laravel
- **Frontend** : Blade / Bootstrap / jQuery
- **Database** : MySQL
- **Server** : Nginx / Apache
- **Version Control** : Git & GitHub

---

## ⚙️ Instalasi

1. Clone repository

```bash
git clone https://github.com/budizulkoplo/kopkartex.git
cd kopkartex


Install dependency

composer install


Copy file environment

cp .env.example .env


Generate key

php artisan key:generate


Setting database di file .env

DB_DATABASE=kopkartex
DB_USERNAME=root
DB_PASSWORD=


Migrasi database

php artisan migrate


Jalankan server

php artisan serve

📂 Struktur Folder Penting
app/            Logic aplikasi
database/       Migration & Seeder
resources/      View & Assets
routes/         Routing aplikasi
public/         Public assets

🔐 Default Login (Jika Ada Seeder)
Email: admin@kopkartex.com
Password: password


(Ubah sesuai kebutuhan)

📊 Roadmap

 Export laporan ke Excel

 Notifikasi jatuh tempo

 Integrasi WhatsApp

 Multi Cabang
