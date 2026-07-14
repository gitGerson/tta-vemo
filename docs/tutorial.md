# VEMO - Panduan Fitur

## Gambaran Singkat Aplikasi

VEMO adalah aplikasi internal Tongtji untuk memonitor kendaraan operasional, mulai dari data armada, pencatatan harian, sampai laporan rekap dan analisa. Fitur yang tersedia saat ini:

- login memakai akun korporat, lalu masuk ke dashboard
- dashboard operasional berisi ringkasan, pengingat, dan ringkasan biaya
- data induk: supir, perusahaan, kendaraan, tipe kendaraan, merek kendaraan, tujuan, tipe pemeliharaan, dan jenis BBM
- operasional: pelacakan BBM, pemeliharaan dengan alur persetujuan, asuransi kendaraan, foto kendaraan bulanan, dan scan QR
- laporan harian, bulanan, analisa, dan tahunan untuk BBM maupun pemeliharaan
- pemisahan data antar perusahaan
- pengelolaan pengguna, peran, dan hak akses
- notifikasi di dalam aplikasi, integrasi Telegram, dan pencarian cepat

[PLACEHOLDER GAMBAR - Gambaran Umum Aplikasi]

## Peran dan Hak Akses

Setiap pengguna diberi hak akses sesuai kebutuhannya. Hak akses menentukan menu yang tampil, tombol yang bisa dipakai, dan data yang bisa dilihat.

Dua hal penting soal akses:

- **Data dibatasi per perusahaan.** Secara umum, pengguna hanya melihat data milik perusahaan tempat ia ditugaskan. Contohnya, pengguna dari cabang A tidak melihat kendaraan cabang B.
- **Akses lintas perusahaan.** Sebagian pengguna (misalnya tim pusat) dapat diberi izin khusus untuk melihat data seluruh perusahaan tanpa batas.

Untuk mempercepat pengaturan, tersedia beberapa preset peran:

- **Super Admin** — akses penuh ke semua fitur
- **Admin** — mengelola pengguna dan hampir semua modul operasional
- **Manajer Pengguna** — fokus mengelola pengguna
- **Viewer** — hanya bisa melihat data (baca saja)

Secara praktis:

- Admin sistem mengelola pengguna, peran, dan hak akses.
- Admin operasional mengelola data induk dan transaksi harian.
- Pengguna lapangan biasanya hanya perlu scan QR, mencatat BBM, mengunggah foto, atau input pemeliharaan.

[PLACEHOLDER GAMBAR - Menu Samping (Sidebar)]

## Alur Login

### 1. Halaman Login

Buka aplikasi, maka halaman login akan muncul. Login memakai:

- **username** akun korporat
- **password** akun korporat

Aplikasi ini tidak memakai kode OTP (verifikasi dua langkah) dan tidak memakai alamat login khusus yang berubah-ubah.

[PLACEHOLDER GAMBAR - Halaman Login]

### 2. Proses Masuk

Agar bisa login, akun pengguna harus sudah terdaftar di aplikasi terlebih dahulu. Jika username belum terdaftar, login ditolak walaupun akun korporatnya benar. Pendaftaran akun ke aplikasi dilakukan lewat modul Pengguna (lihat bagian Manajemen).

Untuk keperluan pengujian tanpa akun korporat, tersedia satu akun demo:

- **username** `stagingpurpose`
- **password** `P@ssw0rd1938`

Akun demo ini memiliki akses penuh.

### 3. Setelah Login

Setelah berhasil masuk, pengguna diarahkan ke dashboard. Menu yang tampil menyesuaikan hak akses, dan data yang muncul menyesuaikan perusahaan pengguna.

## Dashboard

Dashboard adalah pusat monitoring harian. Isinya menyesuaikan hak akses dan perusahaan pengguna.

Pengaturan tampilan di bagian atas:

- **Pilih perusahaan** — jika pengguna hanya memiliki satu perusahaan, otomatis terpilih; jika lebih dari satu, tersedia pilihan satu perusahaan tertentu atau semua perusahaan.
- **Pilih bulan** — menentukan periode data ringkasan.
- Saat filter diubah, data langsung diperbarui tanpa memuat ulang halaman.

Ringkasan yang ditampilkan:

- total kendaraan dan jumlah kendaraan aktif
- kendaraan yang mendekati jadwal servis
- peringatan asuransi yang jatuh tempo atau hampir jatuh tempo
- foto bulanan yang belum lengkap
- kendaraan yang sedang dalam pemeliharaan dan yang menunggu konfirmasi

Daftar pengingat:

- **Pengingat servis** kendaraan
- **Pengingat foto bulanan** yang belum lengkap
- **Pengingat asuransi** menjelang jatuh tempo (H-30, H-14, H-7, dan H-3)
- **Pengingat dokumen kendaraan** (STNK menjelang H-30, KIR menjelang H-7)

Ringkasan biaya (untuk pengguna yang diberi hak akses):

- tren biaya 6 bulan (pemeliharaan dibanding BBM)
- kartu biaya pemeliharaan dan biaya BBM beserta perbandingan tahun sebelumnya

Selain itu tersedia grafik kondisi kendaraan, komposisi armada, volume pemeliharaan, tombol aksi cepat (catat servis, scan QR, tambah kendaraan, lihat semua kendaraan), dan daftar pemeliharaan terbaru pada periode terpilih.

[PLACEHOLDER GAMBAR - Dashboard Admin]

### Skenario Penggunaan

1. Buka dashboard setelah login.
2. Pilih perusahaan dan bulan yang ingin ditinjau.
3. Periksa kartu pengingat; jika ada kendaraan yang perlu servis atau asuransi hampir habis, klik untuk menindaklanjuti.
4. Gunakan tombol aksi cepat untuk langsung mencatat servis atau menambah kendaraan.

## Data Induk

### Supir

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, dan menghapus permanen data supir.

Fungsi modul:

- menyimpan data supir: nama, NIK, alamat, nomor identitas, tanggal lahir, nomor telepon, tanggal masuk/keluar, dan status (aktif/nonaktif)
- satu supir bisa ditugaskan ke beberapa perusahaan sekaligus

Cara pakai:

- tambah dan ubah data dilakukan lewat jendela pop-up di halaman daftar
- foto profil supir diunggah ke penyimpanan file
- saat NIK diisi, data karyawan dari sistem HR bisa muncul otomatis untuk mempercepat pengisian
- dua nomor telepon tidak boleh sama
- saat supir dihapus, statusnya menjadi nonaktif dulu; data terhapus bisa dipulihkan dari halaman sampah

[PLACEHOLDER GAMBAR - Daftar Supir]

### Skenario Penggunaan

1. Buka menu **Supir** lalu klik **Tambah**.
2. Isi NIK; data karyawan (nama, dsb.) muncul otomatis.
3. Lengkapi alamat, nomor telepon, dan tanggal masuk.
4. Pilih perusahaan tempat supir bertugas dan unggah foto.
5. Klik **Simpan**. Supir baru langsung muncul di daftar.

[PLACEHOLDER GAMBAR - Form Tambah Supir]

### Perusahaan

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, dan menghapus permanen data perusahaan.

Fungsi modul:

- menyimpan daftar perusahaan: kode (dibuat otomatis), nama, email, telepon, logo, dan catatan
- perusahaan menjadi dasar pemisahan data operasional antar cabang/entitas

Cara pakai:

- tambah dan ubah data lewat jendela pop-up; logo diunggah ke penyimpanan file
- tersedia halaman detail perusahaan dengan tab data terkait: **Supir**, **Kendaraan**, dan **Pengguna**
- soft delete, sampah, restore, dan hapus permanen tersedia

[PLACEHOLDER GAMBAR - Daftar Perusahaan]

### Skenario Penggunaan

1. Buka menu **Perusahaan** lalu klik **Tambah**.
2. Isi nama, email, telepon, dan unggah logo.
3. Klik **Simpan**.
4. Klik nama perusahaan untuk membuka detail dan meninjau supir, kendaraan, serta pengguna yang terkait.

[PLACEHOLDER GAMBAR - Detail Perusahaan dengan Tab Data Terkait]

### Kendaraan

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, dan menghapus permanen data kendaraan.

Fungsi modul:

- data armada: plat nomor (tidak boleh sama), perusahaan, merek, tipe kendaraan, jenis BBM, tahun masuk, kilometer, nomor mesin, nomor rangka, harga beli/jual, status, penanda kendaraan pribadi, dan penanda pemeliharaan
- dokumen BPKB, STNK, dan KIR bisa memuat beberapa gambar, masing-masing dengan tanggal jatuh tempo

Cara pakai:

- berbeda dari data induk lain, tambah dan ubah kendaraan memakai halaman formulir tersendiri karena isiannya banyak
- gambar dokumen dan tanggal jatuh tempo harus diisi berpasangan
- setiap perubahan disimpan sebagai riwayat kendaraan
- tersedia label QR yang bisa dicetak; label ini dipakai untuk scan cepat di lapangan
- halaman detail menampilkan riwayat, asuransi, dan foto kendaraan

Catatan penting:

- tanggal jatuh tempo STNK dan KIR dipakai untuk pengingat dokumen di dashboard

[PLACEHOLDER GAMBAR - Daftar Kendaraan]

### Skenario Penggunaan

1. Buka menu **Kendaraan** lalu klik **Tambah**.
2. Di halaman formulir, isi plat nomor, perusahaan, merek, tipe, dan jenis BBM.
3. Unggah gambar STNK/KIR dan isi tanggal jatuh temponya.
4. Klik **Simpan**.
5. Buka detail kendaraan, lalu cetak **label QR** untuk ditempel pada kendaraan.

[PLACEHOLDER GAMBAR - Form Kendaraan]
[PLACEHOLDER GAMBAR - Label QR Kendaraan]

### Tipe Kendaraan

Hak akses: melihat, menambah, mengubah, dan menghapus tipe kendaraan.

Fungsi modul:

- mengelompokkan kendaraan menurut tipe (kode dibuat otomatis, nama, dan deskripsi)

Cara pakai:

- tambah dan ubah lewat jendela pop-up di halaman daftar
- tersedia tampilan detail dan halaman sampah untuk pemulihan

[PLACEHOLDER GAMBAR - Modul Tipe Kendaraan]

### Skenario Penggunaan

1. Buka menu **Tipe Kendaraan** lalu klik **Tambah**.
2. Isi nama tipe (mis. Pickup, Truk) dan deskripsi singkat.
3. Klik **Simpan**.

### Merek Kendaraan

Hak akses: melihat, menambah, mengubah, dan menghapus merek kendaraan.

Fungsi modul:

- daftar merek kendaraan (kode dibuat otomatis, nama, logo, dan deskripsi)

Cara pakai:

- tambah dan ubah lewat jendela pop-up; logo merek diunggah ke penyimpanan file
- tersedia tampilan detail dan halaman sampah untuk pemulihan

[PLACEHOLDER GAMBAR - Modul Merek Kendaraan]

### Skenario Penggunaan

1. Buka menu **Merek Kendaraan** lalu klik **Tambah**.
2. Isi nama merek dan unggah logo.
3. Klik **Simpan**.

### Tujuan

Hak akses: melihat daftar tujuan.

Fungsi modul:

- daftar kota tujuan sebagai referensi lokasi pada catatan perjalanan

Catatan penting:

- modul ini hanya untuk melihat (tidak bisa diubah), karena datanya berasal dari daftar kota bawaan
- tampilan detail menampilkan peta lokasi kota terpilih

[PLACEHOLDER GAMBAR - Modul Tujuan]

### Skenario Penggunaan

1. Buka menu **Tujuan**.
2. Cari nama kota pada kotak pencarian tabel.
3. Klik detail untuk melihat lokasi kota di peta.

### Tipe Pemeliharaan

Hak akses: melihat, menambah, mengubah, dan menghapus tipe pemeliharaan.

Fungsi modul:

- daftar jenis pekerjaan atau komponen pemeliharaan: nama dan jenis (jasa atau barang)
- dipakai sebagai pilihan pada item pemeliharaan

Cara pakai:

- tambah dan ubah lewat jendela pop-up
- tersedia fitur **impor dari file Excel** untuk menambah banyak data sekaligus
- tersedia tampilan detail dan halaman sampah untuk pemulihan

[PLACEHOLDER GAMBAR - Modul Tipe Pemeliharaan]

### Skenario Penggunaan

1. Buka menu **Tipe Pemeliharaan**.
2. Untuk sedikit data, klik **Tambah**, isi nama dan jenis, lalu **Simpan**.
3. Untuk banyak data, klik **Impor**, pilih file Excel, lalu unggah.

### Jenis BBM

Hak akses: melihat, menambah, mengubah, dan menghapus jenis BBM.

Fungsi modul:

- daftar bahan bakar: nama, tipe (bensin atau solar), harga, dan merek (Pertamina, Vivo, BP, atau Shell)

Cara pakai:

- tambah dan ubah lewat jendela pop-up; harga diisi dalam format Rupiah
- nama harus unik untuk tiap merek
- tersedia tampilan detail dan halaman sampah untuk pemulihan

[PLACEHOLDER GAMBAR - Modul Jenis BBM]

### Skenario Penggunaan

1. Buka menu **Jenis BBM** lalu klik **Tambah**.
2. Pilih tipe (bensin/solar) dan merek, lalu isi harga.
3. Klik **Simpan**.

## Operasional

### Pelacakan BBM

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, dan menghapus permanen catatan perjalanan.

Fungsi modul:

- mencatat perjalanan dan pemakaian BBM: kendaraan/plat, supir 1 dan supir 2, tujuan, perusahaan, jenis BBM, KM awal, KM akhir, liter BBM, harga, dan tanggal

Cara pakai:

- tambah dan ubah lewat jendela pop-up di halaman daftar
- KM awal otomatis mengambil KM akhir dari catatan sebelumnya jika dikosongkan
- sistem menghitung total jarak tempuh dan efisiensi (km per liter) secara otomatis
- kilometer kendaraan ikut diperbarui setelah disimpan
- tersedia ekspor Excel dengan filter rentang tanggal dan perusahaan
- setiap perubahan disimpan sebagai riwayat; sampah dan pemulihan tersedia

[PLACEHOLDER GAMBAR - Modul Pelacakan BBM]

### Skenario Penggunaan

1. Buka menu **Pelacakan BBM** lalu klik **Tambah**.
2. Pilih kendaraan; KM awal terisi otomatis dari perjalanan sebelumnya.
3. Isi KM akhir, jumlah liter BBM, supir, dan tujuan.
4. Klik **Simpan**; efisiensi dihitung otomatis.
5. Untuk laporan, klik **Ekspor** untuk mengunduh data dalam bentuk Excel.

### Pemeliharaan

Hak akses: melihat, menambah, mengubah, menyetujui/konfirmasi, menghapus, memulihkan, dan menghapus permanen data pemeliharaan.

Fungsi modul:

- mencatat pemeliharaan kendaraan: kode dibuat otomatis, plat kendaraan, tanggal masuk/keluar, kilometer, deskripsi, penanda servis rutin, dan status
- **item pekerjaan**: nama pekerjaan (dari daftar tipe pemeliharaan), harga, jumlah, subtotal otomatis, dan catatan material
- **lampiran** berupa gambar yang diunggah ke penyimpanan file

Alur persetujuan:

- status mengikuti tahapan **Draf → Selesai → Terkonfirmasi** (bisa juga Dibatalkan)
- menyimpan sebagai draf menyimpan data awal tanpa harus lengkap
- menyimpan sebagai selesai memerlukan minimal satu item pekerjaan
- konfirmasi hanya bisa dilakukan saat status sudah Selesai, dan mencatat siapa serta kapan mengonfirmasi
- konfirmasi hanya bisa dilakukan oleh pengguna yang diberi hak menyetujui

Cara pakai:

- tambah, ubah, dan lihat memakai halaman tersendiri
- item pekerjaan dan lampiran dikelola langsung di halaman formulir

Catatan penting:

- hanya pemeliharaan berstatus Selesai dan sudah dikonfirmasi yang dihitung pada laporan pemeliharaan

[PLACEHOLDER GAMBAR - Daftar Pemeliharaan]

### Skenario Penggunaan

1. Buka menu **Pemeliharaan** lalu klik **Tambah**.
2. Pilih kendaraan dan isi tanggal serta deskripsi.
3. Tambahkan item pekerjaan (mis. ganti oli) beserta harga dan jumlah; total dihitung otomatis.
4. Unggah lampiran/nota bila ada, lalu **Simpan sebagai Selesai**.
5. Atasan yang berwenang membuka data tersebut lalu menekan **Konfirmasi** agar masuk ke laporan.

[PLACEHOLDER GAMBAR - Form Pemeliharaan dengan Item Pekerjaan]
[PLACEHOLDER GAMBAR - Konfirmasi Pemeliharaan]

### Asuransi Kendaraan

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, dan menghapus permanen data asuransi. Tampilan tiap kolom formulir juga bisa dibatasi per pengguna.

Fungsi modul:

- mencatat polis asuransi kendaraan: kode dibuat otomatis, kendaraan, nama penanggung, nomor polis, tipe (TLO atau All Risk), tanggal mulai/berakhir, nilai pertanggungan, premi, catatan, dan lampiran dokumen

Cara pakai:

- tambah dan ubah memakai halaman formulir tersendiri
- lampiran dokumen diunggah ke penyimpanan file
- tanggal berakhir tidak boleh lebih awal dari tanggal mulai
- setiap perubahan disimpan sebagai riwayat

Catatan penting:

- **tampilan kolom pada formulir menyesuaikan hak akses pengguna**; pengguna hanya melihat dan mengisi kolom yang diizinkan untuknya
- tanggal berakhir dipakai untuk pengingat asuransi di dashboard

[PLACEHOLDER GAMBAR - Daftar Asuransi Kendaraan]

### Skenario Penggunaan

1. Buka menu **Asuransi** lalu klik **Tambah**.
2. Pilih kendaraan, isi nomor polis, tipe, serta tanggal mulai dan berakhir.
3. Unggah dokumen polis pada bagian lampiran.
4. Klik **Simpan**; polis akan diawasi untuk pengingat jatuh tempo.

[PLACEHOLDER GAMBAR - Form Asuransi Kendaraan]

### Foto Kendaraan

Hak akses: melihat, mengunggah, mengubah, dan menghapus dokumentasi foto kendaraan.

Fungsi modul:

- dokumentasi kondisi kendaraan **setiap bulan**: satu catatan per kendaraan per bulan
- empat sisi foto: depan, belakang, samping kiri, dan samping kanan, ditambah catatan kondisi

Cara pakai:

- tambah dan ubah memakai halaman tersendiri dengan pilihan kendaraan dan bulan
- setiap sisi foto diunggah ke penyimpanan file
- halaman detail menampilkan foto beserta **komentar**; komentar hanya bisa dihapus oleh penulisnya
- bulan yang sudah didokumentasikan ditandai agar mudah melihat yang belum lengkap

Catatan penting:

- foto bulanan yang belum lengkap muncul sebagai pengingat di dashboard

[PLACEHOLDER GAMBAR - Modul Foto Kendaraan]

### Skenario Penggunaan

1. Buka menu **Foto Kendaraan** lalu klik **Tambah**.
2. Pilih kendaraan dan bulan dokumentasi.
3. Unggah foto keempat sisi kendaraan dan tulis catatan kondisi.
4. Klik **Simpan**; bulan tersebut tidak lagi muncul sebagai pengingat.

### Scan QR Kendaraan

Hak akses: memakai halaman scan QR.

Fungsi modul:

- mencari kendaraan dengan cepat lewat scan label QR atau ketik plat manual
- hasil scan menampilkan data kendaraan beserta aksi cepat

Cara pakai:

- halaman scan menerima hasil scan label atau input plat
- pencarian kendaraan tetap mengikuti pembatasan data per perusahaan
- aksi cepat yang tersedia (sesuai hak akses): lihat kendaraan, cetak label, buat pemeliharaan, dan unggah foto

[PLACEHOLDER GAMBAR - Modul Scan QR]

### Skenario Penggunaan

1. Di lapangan, buka menu **Scan QR** dan arahkan kamera ke label kendaraan (atau ketik platnya).
2. Data kendaraan muncul di layar.
3. Pilih aksi cepat, misalnya **Buat Pemeliharaan** atau **Unggah Foto**, untuk langsung menindaklanjuti.

## Laporan

Setiap laporan bisa dilihat maupun diekspor sesuai hak akses. Ekspor (PDF/Excel/CSV) umumnya mensyaratkan pemilihan perusahaan lebih dulu.

| Laporan | Filter | Format Ekspor |
| --- | --- | --- |
| Laporan Harian BBM | tanggal, kategori, perusahaan | PDF |
| Laporan Bulanan BBM | bulan, perusahaan, unit (plat) | PDF |
| Laporan Analisa Bulanan BBM | bulan, perusahaan, unit | PDF + Excel |
| Laporan Pemeliharaan (Harian/Unit) | rentang tanggal, unit, perusahaan | PDF |
| Laporan Pemeliharaan Bulanan | bulan, unit, perusahaan | PDF |
| Laporan Analisa Bulanan Pemeliharaan | bulan, perusahaan, unit | PDF + Excel |
| Laporan Pemeliharaan Tahunan | tahun, perusahaan | PDF + CSV |

Catatan:

- laporan **analisa** merangkum per kendaraan (jumlah, total jarak/liter/biaya, dan efisiensi) untuk periode terpilih
- laporan **pemeliharaan tahunan** menampilkan biaya per kendaraan per bulan (Januari–Desember) beserta total
- seluruh laporan pemeliharaan hanya menghitung data yang sudah berstatus Selesai dan sudah dikonfirmasi

[PLACEHOLDER GAMBAR - Halaman Laporan]

### Skenario Penggunaan

1. Buka menu **Laporan** dan pilih jenis laporan yang diinginkan.
2. Atur filter periode (tanggal/bulan/tahun) dan pilih perusahaan.
3. Tinjau hasilnya di layar.
4. Klik **Ekspor** untuk mengunduh dalam bentuk PDF atau Excel.

## Umum

### Notifikasi

- daftar notifikasi bisa dibuka dari ikon lonceng dan dimuat bertahap
- notifikasi dapat ditandai sebagai sudah dibaca

**Skenario:** klik ikon lonceng, baca notifikasi terbaru, lalu tandai sudah dibaca.

[PLACEHOLDER GAMBAR - Notifikasi]

### Hubungkan Telegram

- pengguna dapat menautkan akun Telegram pribadinya untuk menerima notifikasi
- aplikasi menyiapkan tautan ke bot Telegram; penautan bisa dilepas kapan saja

**Skenario:** buka menu **Hubungkan Telegram**, klik tautan yang tersedia untuk membuka bot, lalu ikuti perintah untuk menautkan.

[PLACEHOLDER GAMBAR - Hubungkan Telegram]

### Pencarian Cepat

- kotak pencarian mencari lintas modul sekaligus
- cakupan: supir, kendaraan, perusahaan, tujuan, tipe pemeliharaan, tipe kendaraan, merek kendaraan, jenis BBM, catatan perjalanan, dan pemeliharaan
- hasil menyesuaikan hak akses dan perusahaan pengguna

**Skenario:** ketik kata kunci (mis. plat kendaraan) di kotak pencarian, lalu klik hasil untuk membuka halamannya.

[PLACEHOLDER GAMBAR - Pencarian Cepat]

## Manajemen

### Pengguna

Hak akses: melihat, menambah, mengubah, menghapus, memulihkan, menghapus permanen, melihat log aktivitas, dan mengatur hak akses pengguna.

Fungsi modul:

- daftar pengguna, halaman detail, sampah, pemulihan, dan hapus permanen
- **pendaftaran pengguna berbasis akun korporat**: pengguna dicari di sistem korporat berdasarkan username, lalu ditarik ke aplikasi (bukan membuat akun manual)
- log aktivitas seluruh pengguna maupun per pengguna
- penetapan peran dan pengaturan hak akses, termasuk memakai preset peran

Catatan penting:

- pengguna baru bisa login hanya setelah ditarik ke aplikasi
- penugasan perusahaan pada pengguna menentukan data yang bisa ia lihat

[PLACEHOLDER GAMBAR - Daftar Pengguna]

### Skenario Penggunaan

1. Buka menu **Pengguna** lalu klik **Tambah**.
2. Masukkan username; sistem mencari akun di sistem korporat.
3. Setelah ditemukan, tarik ke aplikasi.
4. Tetapkan peran/hak akses dan perusahaan pengguna, lalu **Simpan**.

[PLACEHOLDER GAMBAR - Log Aktivitas Pengguna]

### Peran

Hak akses: melihat, menambah, mengubah, dan menghapus peran.

Fungsi modul:

- membuat, mengubah, melihat, dan menghapus peran
- hak akses dikelompokkan per modul; jumlah pengguna dan hak akses per peran ditampilkan di daftar

Catatan:

- tersedia preset peran siap pakai seperti Super Admin, Admin, Manajer Pengguna, dan Viewer

[PLACEHOLDER GAMBAR - Daftar Peran]

### Skenario Penggunaan

1. Buka menu **Peran** lalu klik **Tambah**.
2. Beri nama peran (mis. Operator Cabang).
3. Centang hak akses yang dibutuhkan per modul.
4. Klik **Simpan**; peran ini bisa dipakai saat mengatur pengguna.

## Modul Belum Aktif di Menu

Ada modul tambahan di aplikasi yang menunya belum diaktifkan:

- Demo Form (contoh komponen formulir)

Modul ini belum menjadi fokus penggunaan umum sampai menunya diaktifkan kembali.

## Tips Operasional

1. Gunakan menu di samping dan pencarian cepat untuk berpindah antar modul.
2. Pastikan pengguna punya hak akses yang sesuai dan ditugaskan ke perusahaan yang benar agar datanya muncul.
3. Untuk kendaraan, isi tanggal jatuh tempo STNK dan KIR agar pengingat dokumen berjalan.
4. Untuk pemeliharaan, selesaikan alur Draf → Selesai → Konfirmasi agar datanya masuk laporan.
5. Untuk asuransi, tampilan kolom formulir menyesuaikan hak akses masing-masing pengguna.
6. Untuk foto kendaraan, lengkapi keempat sisi setiap bulan agar tidak muncul di pengingat.
7. Gunakan scan QR di lapangan untuk mempercepat input pemeliharaan dan foto.
8. Pantau dashboard secara berkala dan gunakan pilihan perusahaan/bulan untuk membandingkan kondisi.

## Panduan Mengatasi Masalah

### Tidak Bisa Login

Periksa:

- akun sudah ditarik ke aplikasi lewat modul Pengguna
- kombinasi username dan password akun korporat sudah benar
- koneksi ke sistem login korporat tersedia

### Data Tidak Muncul Padahal Sudah Ada

Periksa:

- pengguna sudah ditugaskan ke perusahaan pemilik data
- bila butuh akses lintas perusahaan, minta admin memberi izin akses seluruh perusahaan
- hak akses untuk melihat modul terkait sudah diberikan

### Laporan Pemeliharaan Kosong

Periksa:

- data pemeliharaan sudah berstatus Selesai
- data pemeliharaan sudah dikonfirmasi
- filter perusahaan/unit dan periode sudah sesuai

### Asuransi: Kolom Tidak Muncul di Formulir

Periksa:

- pengguna memiliki hak akses untuk kolom tersebut (minta admin menyesuaikan)

### Tampilan Aplikasi Tidak Berubah Setelah Update

Periksa:

- proses build tampilan sudah dijalankan oleh tim teknis

## Ringkasan

Kondisi fitur saat ini:

- akses dilindungi oleh login akun korporat, hak akses per pengguna, dan pemisahan data per perusahaan
- dashboard menjadi pusat monitoring: ringkasan armada, pengingat servis/asuransi/dokumen/foto, dan ringkasan biaya
- data induk armada lengkap: supir, perusahaan, kendaraan (dengan dokumen dan label QR), tipe/merek kendaraan, tujuan, tipe pemeliharaan, dan jenis BBM
- operasional mencakup pelacakan BBM, pemeliharaan dengan alur persetujuan, asuransi, foto kendaraan bulanan, dan scan QR
- laporan tersedia untuk BBM dan pemeliharaan dalam bentuk harian, bulanan, analisa, dan tahunan dengan ekspor PDF/Excel/CSV
- manajemen pengguna dan peran mendukung penarikan akun korporat, log aktivitas, preset peran, dan pengaturan hak akses

Perbarui dokumen ini setiap kali ada perubahan pada alur login, hak akses, aturan pemisahan data per perusahaan, atau perilaku tiap modul.

[PLACEHOLDER GAMBAR - Ringkasan Fitur]
