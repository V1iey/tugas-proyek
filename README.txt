SIPN - Sistem Informasi Pengelolaan Nilai (PHP Native + MySQL)

CARA PASANG
1. Install XAMPP/Laragon (PHP 7.4+ / 8.x, MySQL).
2. Copy folder "sipn" ke htdocs (XAMPP) atau www (Laragon).
3. Buka phpMyAdmin -> Import file database.sql (otomatis membuat db_siacad_smk_2).
   Catatan: import ulang akan menghapus & membuat ulang semua tabel.
4. Cek config.php (BASE_URL harus sama dengan nama folder, user/pass database).
   DEBUG=true menampilkan error di layar; ubah ke false untuk produksi.
5. Buka http://localhost/sipn

AKUN CONTOH (password semua: password)
  admin / guru1 / siswa1 / siswa2

STRUKTUR
  sipn/        config, index, login, logout, register, profile, dashboard
  sipn/inc/    header.php, footer.php
  sipn/assets/ style.css
  sipn/admin/  users, siswa, guru, mapel, mengajar
  sipn/guru/   nilai.php
  sipn/siswa/  nilai.php
