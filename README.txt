PANDUAN MENJALANKAN WEBSITE PERHITUNGAN INVESTASI PROYEK SUMUR MIGAS

Stack:
- HTML + Tailwind CSS CDN
- PHP
- MySQL
- Chart.js CDN

Cara menjalankan di XAMPP:
1. Copy folder "migas-website" ke folder htdocs.
   Contoh: C:\xampp\htdocs\migas-website
2. Buka XAMPP, jalankan Apache dan MySQL.
3. Buka phpMyAdmin: http://localhost/phpmyadmin
4. Import file database/migas.sql.
5. Buka website: http://localhost/migas-website

Catatan:
- Konfigurasi database ada di config/database.php.
- Dashboard hanya memiliki 2 menu sidebar: Dashboard dan Proyek Sumur.
- Halaman tambah, edit, dan detail proyek dibuka melalui tombol dari halaman Proyek Sumur.
- Kurs USD-IDR mencoba mengambil data dari API publik. Jika API gagal, sistem memakai fallback Rp15.700.
- Rumus NCF mengikuti contoh spreadsheet:
  Income = Produksi x Harga Minyak
  Taxable Income = Income - OPEX - Depresiasi
  Tax = Taxable Income x Tarif Pajak
  NCF = Taxable Income - Tax
  Total NCF Setelah Investasi = Total NCF Operasi - (Capital + Non-Capital)
