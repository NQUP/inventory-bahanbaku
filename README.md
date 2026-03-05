# Sistem Inventory Bahan Baku

Sistem Inventory Bahan Baku merupakan aplikasi berbasis web yang dikembangkan untuk membantu proses pengelolaan persediaan bahan baku secara terkomputerisasi. Sistem ini memungkinkan pengguna untuk mencatat barang masuk, barang keluar, melakukan pemesanan bahan baku, serta memantau ketersediaan stok secara lebih efektif dan efisien.

Aplikasi ini dibuat sebagai bagian dari **tugas skripsi** dalam pengembangan sistem informasi berbasis web.

---

## Fitur Sistem

* Manajemen data bahan baku
* Pencatatan barang masuk
* Pencatatan barang keluar
* Monitoring stok bahan baku
* Sistem pemesanan bahan baku
* Laporan inventory
* Manajemen pengguna

---

## Role Pengguna

Sistem ini memiliki **5 role pengguna**, yaitu:

### Admin

* Mengelola data user
* Mengelola data bahan baku
* Mengelola seluruh aktivitas sistem

### Manager

* Melihat laporan inventory
* Monitoring stok bahan baku

### Gudang

* Mengelola stok bahan baku
* Mencatat barang masuk
* Mencatat barang keluar

### Pemesan

* Melakukan pemesanan bahan baku
* Melihat status pemesanan

### Supplier

* Menerima permintaan pemesanan
* Mengelola pengiriman bahan baku

---

## Demo Login

Berikut akun yang dapat digunakan untuk mencoba sistem:

| Role     | Email                                           | Password |
| -------- | ----------------------------------------------- | -------- |
| Admin    | [admin@gmail.com](mailto:admin@gmail.com)       | 12345    |
| Manager  | [manager@gmail.com](mailto:manager@gmail.com)   | 12345    |
| Gudang   | [gudang@gmail.com](mailto:gudang@gmail.com)     | 12345    |
| Pemesan  | [pemesan@gmail.com](mailto:pemesan@gmail.com)   | 12345    |
| Supplier | [supplier@gmail.com](mailto:supplier@gmail.com) | 12345    |

Semua akun menggunakan password yang sama untuk keperluan demonstrasi sistem.

---

## Teknologi yang Digunakan

* PHP
* Laravel Framework
* MySQL
* Bootstrap
* JavaScript

---

## Instalasi Project

Clone repository

git clone https://github.com/NQUP/inventory-bahanbaku.git

Masuk ke folder project

cd inventory-bahanbaku

Install dependency

composer install

Copy file environment

cp .env.example .env

Generate application key

php artisan key:generate

Konfigurasi database pada file `.env`

Jalankan migration

php artisan migrate

Jalankan aplikasi

php artisan serve

Akses aplikasi melalui browser:

http://127.0.0.1:8000

---

## Author

Rizqi Alfa Reza
Email: rizqialfareza07@gmail.com

---

## License

Project ini menggunakan **MIT License**.
