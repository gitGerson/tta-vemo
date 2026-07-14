# TTA Website - Feature Tutorial

## Gambaran Singkat Aplikasi

TTA Website adalah CMS internal Tongtji untuk mengelola konten website publik dan akses admin. Fitur yang aktif saat ini mencakup:

- landing page publik dan halaman publik pendukung
- banner utama, kategori, produk, kuliner, berita, sertifikat, dan kolaborasi
- kanal digital untuk e-commerce, social media, dan buku menu PDF
- master data cabang, store, dan social content
- manajemen user, role, permission, dan template akses
- login LDAP, admin URL bersuffix dinamis, dan two-factor authentication
- visitor analytics di dashboard admin

[PLACEHOLDER GAMBAR - Gambaran Umum Aplikasi]

## Peran dan Hak Akses

Hak akses ditentukan oleh permission. Dua hal penting:

- User harus punya permission `system.admin_access` untuk bisa masuk ke area admin.
- Setelah itu, menu dan tombol aksi tetap dibatasi lagi oleh permission per modul seperti `master.news.view`, `management.roles.edit`, dan seterusnya.

Secara praktis:

- Admin konten mengelola modul website dan kanal digital.
- Admin sistem mengelola user, role, permission, dan keamanan akses admin.

[PLACEHOLDER GAMBAR - Perbedaan Menu Berdasarkan Role]

## Alur Akses Admin

### 1. Login Admin Menggunakan URL Dinamis

Area admin tidak lagi memakai URL tetap seperti `/login`. Login admin sekarang memakai URL yang memiliki suffix dinamis, misalnya pola:

- `/login/{adminSuffix}`

Suffix ini dipakai untuk melindungi area admin dan dapat dirotasi. Link login aktif bisa didapat dari:

- email notifikasi admin access link
- dashboard admin pada user yang sudah berhasil masuk

Jika user tidak memiliki permission `system.admin_access`, login berhasil pun tetap tidak akan diberi akses ke area admin.

[PLACEHOLDER GAMBAR - Login Dengan Suffix Admin]

### 2. Autentikasi LDAP

Login menggunakan akun LDAP korporat dengan field:

- username LDAP
- password LDAP

Sistem juga memeriksa dulu apakah username tersebut sudah tersimpan di tabel `users`. Jadi akun yang ada di LDAP tetapi belum di-import ke aplikasi belum bisa masuk.

### 3. Two-Factor Authentication

Setelah login LDAP berhasil:

- jika user belum pernah setup 2FA, user diarahkan ke halaman setup QR code authenticator
- jika user sudah aktif 2FA, user diarahkan ke halaman challenge untuk memasukkan kode OTP

2FA juga bisa dilihat kembali dari menu keamanan akun admin.

[PLACEHOLDER GAMBAR - Setup 2FA]
[PLACEHOLDER GAMBAR - Challenge 2FA]

### 4. Dashboard

Dashboard memakai layout 60/40 dan saat ini menampilkan:

- greeting sesuai waktu Asia/Jakarta
- waktu real-time
- link login admin aktif beserta tombol copy
- visitor chart per bulan dengan year selector
- KPI visitor bulan ini dan tahun ini
- radial chart growth bulanan
- breakdown visitor mobile vs desktop
- kartu statistik modul yang bisa diklik bila user punya permission

Kartu bawah merangkum:

- Banner Utama
- Cabang
- Kolaborasi
- Sertifikat
- Blog / Berita
- Online Store
- Social Media
- ringkasan Produk, Kategori, Kuliner
- ringkasan Role dan User

[PLACEHOLDER GAMBAR - Dashboard Admin]

## Visitor Analytics

Kunjungan ke landing page publik `/` dicatat otomatis oleh middleware `track.visitor`.

Perilaku utamanya:

- tracking dilakukan saat halaman root publik dibuka
- deduplikasi visitor menggunakan cache per IP selama 24 jam
- data dasar yang disimpan meliputi IP, user agent, dan waktu kunjungan
- dashboard menghitung total bulanan, tahunan, growth, serta klasifikasi mobile dan desktop

Year selector di dashboard memanggil endpoint admin visitor stats secara asynchronous, jadi chart diperbarui tanpa reload penuh.

[PLACEHOLDER GAMBAR - Visitor Analytics]

## Modul Halaman Utama

### Banner Utama

Permission utama:

- `master.sliders.view`
- `master.sliders.create`
- `master.sliders.edit`
- `master.sliders.delete`

Fungsi modul:

- mengelola banner hero landing page
- membedakan banner `Primary` dan `Secondary`
- hanya satu banner yang boleh menjadi primary dalam satu waktu
- urutan banner secondary bisa diubah dengan drag and drop

Operasional:

- tambah dan edit dilakukan dari halaman indeks melalui modal
- gambar diunggah ke storage S3 melalui FilePond
- saat banner ditandai sebagai primary, banner primary lama otomatis dinonaktifkan

[PLACEHOLDER GAMBAR - Modul Banner Utama]

### Cabang

Permission utama:

- `master.branches.*`

Fungsi modul:

- menyimpan daftar cabang/outlet
- setiap cabang terhubung ke master kota
- koordinat longitude dan latitude dipakai untuk data peta

Catatan penting:

- koordinat bersifat opsional, tetapi hanya cabang yang punya koordinat yang akan berguna untuk map
- endpoint publik `/get-cabang-data` mengembalikan JSON lokasi cabang
- dari kartu cabang admin tersedia link cepat ke Google Maps bila koordinat tersedia

[PLACEHOLDER GAMBAR - Modul Cabang]

## Modul Produk dan Menu

### Kategori dan Produk Beranda

Permission utama:

- `master.categories.*`

Fitur:

- nama kategori, slug, nama Inggris, dan deskripsi
- gambar utama dan gambar hover
- status `Primary` dan `Aktif`
- drag and drop untuk reorder
- statistik jumlah kategori yang punya produk

Catatan:

- hanya satu kategori boleh menjadi primary pada saat yang sama
- kategori juga dipakai sebagai relasi untuk master produk

[PLACEHOLDER GAMBAR - Modul Kategori]

### Produk

Permission utama:

- `master.products.*`

Fitur:

- produk terhubung ke kategori aktif
- mendukung nama dan deskripsi bilingual
- mendukung multi image
- SKU otomatis digenerate dengan format `PRD-XXXXXXXX`
- slug unik akan dibuat bila kolom slug tersedia

Perilaku edit:

- jika admin upload batch gambar baru, gambar lama akan diganti
- jika tidak upload ulang, gambar lama tetap dipakai

Di halaman publik `/product-tong-tji`, produk dan kuliner ditampilkan bersama. Halaman ini juga mendukung query `?id=` untuk highlight item tertentu.

[PLACEHOLDER GAMBAR - Modul Produk]

### Kuliner

Permission utama:

- `master.culinaries.*`

Fitur:

- nama dan deskripsi bilingual
- multi image
- reorder daftar kuliner dari halaman indeks
- reorder urutan gambar yang sudah ada pada halaman edit
- halaman detail internal untuk preview data kuliner

Perilaku edit:

- upload batch gambar baru akan mengganti batch sebelumnya
- jika tidak ada upload baru, admin masih bisa mengubah urutan gambar lama

[PLACEHOLDER GAMBAR - Modul Kuliner]

### Buku Menu

Permission utama:

- `master.menu_books.*`

Fitur terbaru yang aktif:

- create dan edit memakai modal pada halaman indeks
- file harus PDF
- ukuran file maksimal 50 MB
- menu book publik hanya bisa dibuka jika `is_active = true` dan `upload_status = done`
- tabel admin menampilkan link publik, tombol copy, preview PDF, ukuran file, status aktif
- tiap item punya tombol QR code untuk generate dan download PNG

URL publik:

- `/buku-menu/{slug}`

Catatan:

- slug dibentuk dari nama atau input slug
- file disimpan di S3
- saat item dihapus, file PDF di storage ikut dihapus

[PLACEHOLDER GAMBAR - Modul Buku Menu]
[PLACEHOLDER GAMBAR - QR Code Buku Menu]

## Modul Konten Brand

### Kolaborasi

Permission utama:

- `master.collaborations.*`

Fitur:

- judul, tahun, deskripsi, versi Inggris
- multi image
- reorder daftar kolaborasi
- cover image diambil dari relasi gambar

Catatan:

- jika admin upload batch gambar baru saat edit, batch lama akan diganti

[PLACEHOLDER GAMBAR - Modul Kolaborasi]

### Sertifikat

Permission utama:

- `master.certificates.*`

Fitur:

- nama sertifikat bersifat opsional
- gambar sertifikat
- penanda primary
- reorder daftar sertifikat

Catatan:

- hanya satu sertifikat yang boleh primary pada satu waktu
- create dan edit dikelola dari halaman indeks

[PLACEHOLDER GAMBAR - Modul Sertifikat]

### Berita

Permission utama:

- `master.news.*`

Fitur:

- judul, slug, tag, deskripsi, judul Inggris, deskripsi Inggris
- multi image
- upload gambar editor untuk konten rich text
- halaman detail admin untuk preview artikel
- list admin memakai kartu dengan batch thumbnail gambar

URL publik:

- `/berita-tong-tji`
- `/berita/{slug}`
- `/berita/tag/{tag}`

Perilaku publik:

- daftar berita publik dipaginasi 9 item per halaman
- filter tag dipaginasi 6 item per halaman
- detail berita menampilkan 3 artikel acak lain sebagai rekomendasi

Catatan:

- upload gambar editor menghasilkan URL S3 yang langsung bisa disisipkan ke konten
- filter tag memakai pencarian `LIKE`, jadi konsistensi penulisan tag tetap penting

[PLACEHOLDER GAMBAR - Modul Berita]

## Modul Kanal Digital

### E-Commerce Group

Permission utama:

- `master.ecommerce_groups.*`

Fungsi:

- menjadi grup/platform untuk store
- contoh: Shopee, Tokopedia, TikTok Shop

Fitur:

- nama, slug, image, status aktif
- halaman indeks menampilkan daftar grup dan jumlah store
- setiap grup memuat store yang diurutkan berdasarkan field `sort`

[PLACEHOLDER GAMBAR - Modul E-Commerce Group]

### Store

Permission utama:

- `master.stores.*`

Fitur:

- relasi ke e-commerce group
- nama, slug, link eksternal, gambar, status aktif
- `site_flag` dengan nilai:
- `main_web`
- `link_official_web`
- reorder store berdasarkan urutan `sort`
- preview tombol buka link eksternal

Catatan:

- landing page publik mengambil store dengan `site_flag = main_web`
- hanya e-commerce group aktif yang ditawarkan pada form create/edit

[PLACEHOLDER GAMBAR - Modul Store]

### Social Media Group

Permission utama:

- `master.social_media_groups.*`

Fungsi:

- menjadi grup/platform untuk social content
- contoh: Instagram, Facebook, TikTok

Fitur:

- nama, slug, image, status aktif
- halaman indeks menampilkan jumlah item social content per group

[PLACEHOLDER GAMBAR - Modul Social Media Group]

### Social Content

Permission utama:

- `master.social_contents.*`

Fitur:

- relasi ke social media group
- nama akun/konten, link, image, status aktif
- `site_flag` dengan nilai:
- `main_web`
- `link_official_web`
- preview link eksternal

Catatan:

- hanya social media group aktif yang tersedia di form create/edit
- data ini menjadi master konten sosial lintas grup

[PLACEHOLDER GAMBAR - Modul Social Content]

## Management

### Users

Permission utama:

- `management.users.view`
- `management.users.create`
- `management.users.edit`
- `management.users.delete`
- `management.users.restore`
- `management.users.force_delete`
- `management.users.logs`
- `management.users.permissions`

Fitur:

- daftar user dengan DataTables
- create user dilakukan dengan sinkronisasi dari LDAP berdasarkan username
- soft delete, trash, restore, dan force delete
- halaman detail user
- halaman log seluruh user dan log per user
- assignment role
- direct permission management lewat dynamic role
- apply role template dari role yang bernama `_template`

Catatan penting:

- create user tidak membuat akun manual lokal biasa, tetapi mencari user di LDAP lalu menyinkronkan data ke database aplikasi
- login hanya bisa dilakukan jika user sudah ada di database

[PLACEHOLDER GAMBAR - Modul Users]
[PLACEHOLDER GAMBAR - User Trash]
[PLACEHOLDER GAMBAR - User Logs]

### Roles

Permission utama:

- `management.roles.*`

Fitur:

- create, edit, lihat, dan hapus role
- permission dikelompokkan sesuai section menu
- jumlah user dan jumlah permission per role ditampilkan di list
- role tidak bisa dihapus jika masih dipakai user

Catatan:

- seeder aplikasi juga mengenal role template seperti `super_admin_template`, `admin_template`, `user_manager_template`, dan `viewer_template`
- `system.admin_access` adalah permission kunci agar role tersebut bisa masuk ke admin route flow

[PLACEHOLDER GAMBAR - Modul Roles]

## Halaman Publik

### Landing Page Root

Route:

- `/`

Sumber data utama landing page saat ini:

- slider
- kategori
- sertifikat
- berita yang punya primary image
- kolaborasi
- store dengan `site_flag = main_web`

Jika user sudah login, akses ke halaman publik utama akan diarahkan ke dashboard admin.

[PLACEHOLDER GAMBAR - Landing Page Publik]

### Halaman Publik Lain

Route publik yang aktif:

| URL | Fungsi |
| --- | --- |
| `/company-overview` | Halaman company overview |
| `/product-tong-tji` | Halaman produk dan kuliner |
| `/berita-tong-tji` | Daftar berita |
| `/berita/{slug}` | Detail berita |
| `/berita/tag/{tag}` | Filter berita berdasarkan tag |
| `/buku-menu/{slug}` | Viewer buku menu publik |
| `/search?q=` | Pencarian publik |
| `/get-cabang-data` | JSON lokasi cabang |

### Pencarian Publik

Route:

- `/search`

Fitur:

- mencari produk berdasarkan nama dan deskripsi
- mencari kuliner berdasarkan nama dan deskripsi
- mencari berita berdasarkan judul dan deskripsi
- hasil produk dan kuliner digabung dalam satu list
- default menampilkan maksimal 4 hasil gabungan
- parameter `lihat_semua=1` menampilkan semua hasil gabungan

[PLACEHOLDER GAMBAR - Pencarian Publik]

## Fitur Keamanan Admin yang Perlu Dipahami

Fitur keamanan terbaru yang perlu dicatat dalam SOP:

- area admin memakai suffix dinamis, bukan URL tetap
- suffix dapat dirotasi dan bisa dikirim lewat email ke user yang eligible
- hanya user dengan `system.admin_access` yang bisa melewati admin access check
- login admin selalu melewati flow pending login dan 2FA
- link login admin aktif bisa dicopy dari dashboard

Implikasi operasional:

- jangan menyebarkan URL admin lama ke luar tim yang berwenang
- pastikan admin baru tidak hanya dibuat di LDAP, tetapi juga di-import ke aplikasi dan diberi role yang memuat `system.admin_access`
- jika user gagal masuk padahal akun LDAP valid, cek role/permission dan suffix login yang dipakai

[PLACEHOLDER GAMBAR - Alur Keamanan Admin]

## Modul Tersedia Tetapi Tidak Aktif di Menu

Di codebase ada modul tambahan berikut, tetapi item menu-nya saat ini `enabled = false`:

- Sync Product ESB
- Employee / sync JPayroll
- demo form components

Route dan controller masih ada, tetapi secara operasional modul ini belum menjadi fokus tutorial pengguna umum sampai menu-nya diaktifkan kembali.

## Tips Operasional

1. Gunakan menu sidebar, bukan mengandalkan hafalan URL admin, karena route admin sekarang bersuffix dinamis.
2. Pastikan user admin punya permission `system.admin_access` selain permission modul.
3. Untuk modul dengan batch image seperti produk, kuliner, berita, dan kolaborasi, upload batch baru biasanya menggantikan batch lama.
4. Untuk Banner Utama, Kategori, dan Sertifikat, penanda primary bersifat tunggal.
5. Untuk Buku Menu, verifikasi status aktif, link publik, QR code, dan preview PDF setelah upload.
6. Isi slug dengan format yang rapi karena slug dipakai pada URL publik.
7. Pastikan cabang yang ingin ditampilkan di map memiliki longitude dan latitude.
8. Pantau dashboard visitor secara berkala dan gunakan year selector untuk membandingkan tren tahunan.

## Troubleshooting Singkat

### Tidak Bisa Masuk ke Admin

Periksa:

- user sudah ada di tabel `users`
- user punya permission `system.admin_access`
- login dilakukan memakai URL admin yang suffix-nya masih valid
- setup/challenge 2FA sudah diselesaikan
- koneksi LDAP tersedia

### Konten Tidak Muncul di Halaman Publik

Periksa:

- status aktif pada modul terkait
- data relasi yang dibutuhkan memang tersedia
- untuk menu book, `upload_status` harus `done`
- untuk berita di landing page, artikel harus punya primary image
- untuk official store di landing page, `site_flag` harus `main_web`

### QR atau Link Buku Menu Bermasalah

Periksa:

- slug unik dan benar
- file PDF berhasil terunggah
- item menu book aktif
- URL aplikasi `APP_URL` benar karena link publik dibentuk dari konfigurasi aplikasi

### Dashboard Visitor Selalu Nol

Periksa:

- tabel `visitors` sudah ada
- middleware tracking pada route `/` aktif
- landing page publik benar-benar menerima traffic
- cache berjalan normal untuk deduplikasi visitor

### Asset Frontend Tidak Berubah

Periksa:

- jalankan `npm run dev`
- atau jalankan `npm run build`

## Ringkasan

Kondisi fitur saat ini dapat dirangkum sebagai berikut:

- akses admin sudah diproteksi oleh suffix dinamis, permission admin access, LDAP, dan 2FA
- dashboard sudah menjadi pusat akses operasional sekaligus sumber link login admin aktif
- modul konten utama website publik seluruhnya sudah terhubung ke admin
- menu book sekarang punya alur yang lebih lengkap: validasi ukuran PDF, preview, copy URL, QR code, dan gating status
- management user dan role sudah mendukung LDAP import, audit log, role template, dan permission management

Dokumen ini sebaiknya diperbarui lagi setiap kali ada perubahan pada:

- alur akses admin
- struktur permission
- sumber data landing page
- perilaku upload atau publikasi pada tiap modul

[PLACEHOLDER GAMBAR - Ringkasan Fitur]
