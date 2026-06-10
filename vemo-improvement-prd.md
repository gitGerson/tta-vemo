# PRD (Product Requirements Document)

## VEMO — Penyempurnaan & Peningkatan Fungsionalitas  v2.1.0

## 1. Pendahuluan / Ringkasan Produk

### 1.1 Latar Belakang

VEMO telah memiliki dasar pengelolaan kendaraan, perusahaan, BBM/perjalanan, pemeliharaan, detail item perbaikan, serta hak akses pengguna. Namun, evaluasi terhadap penggunaan saat ini menunjukkan masih ada kebutuhan operasional yang belum terpenuhi secara menyeluruh.

Masalah utama saat ini:

- Total laporan pemeliharaan berisiko belum mengikuti subtotal detail item (`harga satuan x jumlah`).
- Format laporan pemeliharaan belum setara dengan laporan BBM dan belum menyediakan laporan bulanan.
- Kendaraan belum terhubung langsung ke perusahaan/cabang pemilik, sehingga pemisahan dan penyaringan data belum kuat.
- Hak akses pengguna sudah tersedia, tetapi pembatasan data per perusahaan/cabang belum diterapkan.
- Data induk kendaraan, foto bulanan, pengingat asuransi, ringkasan dashboard, dan koneksi sistem lanjutan masih perlu dilengkapi.

Penyempurnaan diarahkan secara bertahap: memperbaiki akurasi dan konsistensi laporan, memperjelas kepemilikan kendaraan dan data BBM, lalu melanjutkan pada pembatasan data, alur operasional, pemantauan kendaraan, dan koneksi ke sistem lain.

### 1.2 Tujuan Bisnis

Penyempurnaan sistem bertujuan untuk:

- Menyediakan laporan pemeliharaan yang akurat, konsisten, dan dapat digunakan untuk keuangan serta pemeriksaan.
- Memberikan kepemilikan kendaraan dan visibilitas data yang jelas berdasarkan perusahaan/cabang.
- Mengendalikan transaksi pemeliharaan melalui riwayat perubahan dan data lama yang tetap akurat.
- Meningkatkan pemantauan kondisi kendaraan melalui dashboard, foto berkala, servis, dan asuransi.
- Menyiapkan VEMO untuk penggunaan lapangan melalui telepon genggam serta hubungan dengan sistem lain.

### 1.3 Target Pengguna

- **Admin operasional** - mengelola kendaraan, data induk, BBM, pemeliharaan, foto, dan laporan.
- **Pengguna cabang/perusahaan** - memasukkan transaksi dan melihat data sesuai lingkup organisasi yang diizinkan.
- **Supervisor/pemberi persetujuan** - meninjau dan menyetujui transaksi pemeliharaan.
- **Bagian keuangan dan pemeriksa** - membaca laporan akhir dan riwayat transaksi.
- **Manajemen** - melihat ringkasan kondisi kendaraan dan perhatian operasional melalui dashboard.

## 2. Ruang Lingkup Produk

### 2.1 Termasuk dalam Must Have (Fitur Utama)

Must Have berfokus pada akurasi laporan dan data dasar kendaraan/BBM yang diperlukan agar pencatatan serta laporan dapat dipercaya:

- Perbaikan total laporan pemeliharaan berdasarkan subtotal detail item.
- Penambahan tahun masuk kendaraan pada laporan pemeliharaan.
- Standarisasi format laporan pemeliharaan mengikuti kelengkapan laporan BBM.
- Penambahan laporan pemeliharaan bulanan.
- Perapihan nama menu laporan agar konteks BBM dan pemeliharaan jelas.
- Hubungan kendaraan ke perusahaan/cabang pemilik.
- Jenis BBM sebagai pilihan data yang konsisten dalam pencatatan BBM.
- Riwayat perubahan plat nomor kendaraan.

### 2.2 Termasuk dalam Should Have dan Could Have

- Hubungan pengguna ke perusahaan/cabang beserta pembatasan akses data.
- Penyelarasan alur pemeliharaan.
- Ringkasan dashboard, dokumentasi foto bulanan, asuransi, serta pengingat.
- Perapihan data induk kendaraan dan penyimpanan data historis transaksi.
- QR code/aksi cepat melalui telepon genggam, BAST digital, dan koneksi Orlansoft.

## 3. Fitur dan Fungsionalitas

### 3.1 Daftar Fitur Utama

| No | Fitur | Prioritas | Kebutuhan Utama |
| --- | --- | --- | --- |
| 1 | Akurasi total laporan pemeliharaan | Must Have | Total akhir wajib mengikuti subtotal setiap item dan sama pada tampilan, PDF, serta file unduhan. |
| 2 | Tahun masuk kendaraan pada laporan | Must Have | Tahun masuk ditampilkan bersama informasi kendaraan untuk analisis umur dan biaya. |
| 3 | Standarisasi laporan pemeliharaan | Must Have | Memiliki judul, perusahaan, periode, kendaraan, total, waktu/nama pencetak, dan area tanda tangan bila diperlukan. |
| 4 | Laporan pemeliharaan bulanan | Must Have | Penyaringan minimal berdasarkan bulan/tahun dan kendaraan; siap ditambah perusahaan/cabang. |
| 5 | Penamaan laporan yang jelas | Must Have | Menu membedakan laporan BBM dan pemeliharaan serta periode laporannya. |
| 6 | Hubungan kendaraan ke perusahaan/cabang | Must Have | Kendaraan memiliki perusahaan/cabang pemilik; perusahaan otomatis mengikuti kendaraan saat pencatatan transaksi. |
| 7 | Batas akses pengguna per perusahaan/cabang | Must Have | Pengguna hanya dapat melihat dan memproses data dalam lingkup organisasinya. |
| 8 | Jenis BBM | Must Have | Pilihan jenis BBM tersedia secara konsisten untuk mengurangi perbedaan penulisan pada transaksi dan laporan. |
| 9 | Riwayat perubahan plat nomor | Must Have | Menyimpan plat lama, plat baru, tanggal, pengguna pengubah, dan alasan. |
| 10 | Alur pemeliharaan | Should Have | Status mudah dipahami, pemeriksaan bukti, informasi servis rutin, dan peringatan jarak servis. |
| 11 | Ringkasan dashboard | Should Have | Status setelah pemeliharaan, kendaraan perlu servis, dan status foto tampil secara tepat. |
| 12 | Foto kendaraan bulanan | Must Have | Foto empat sisi, periode, plat nomor, tanggal, dan komentar/tinjauan. |
| 13 | Data induk kendaraan terstruktur | Must Have | Daftar jenis, merek, penyedia asuransi, data teknis, data pembelian/penjualan, dan penanda kendaraan dalam perbaikan. |
| 14 | Asuransi dan pengingat | Must Have | Riwayat polis, masa berlaku, premi, dan pengingat sebelum habis. |
| 15 | Data historis transaksi | Must Have | Informasi penting tetap sesuai kondisi saat transaksi dibuat. |
| 16 | QR code dan aksi cepat lapangan | Could Have | Pindai kendaraan untuk ringkasan, pencatatan pemeliharaan, dan unggah foto sesuai izin akses. |
| 17 | BAST digital | Could Have | Serah terima kendaraan dan tanda tangan digital yang dapat ditelusuri. |
| 18 | Koneksi Orlansoft | Could Have | Mengirim transaksi yang telah disetujui agar kasir tidak perlu memasukkan data ulang. |

### 3.2 Alur Penggunaan

#### Flow 1 - Mencetak Laporan Pemeliharaan Bulanan

1. Pengguna membuka menu **Laporan Bulanan Pemeliharaan**.
2. Pengguna memilih bulan, tahun, dan kendaraan.
3. Sistem mengambil transaksi pada periode terpilih dan menghitung total berdasarkan subtotal detail item.
4. Laporan menampilkan informasi perusahaan, periode, kendaraan, tahun masuk, tabel detail, total akhir, waktu cetak, dan nama pencetak.
5. Pengguna mencetak atau mengunduh laporan dengan nilai yang sama seperti tampilan sistem.

#### Flow 2 - Pencatatan dan Pelacakan Perubahan BBM

1. Pengguna memilih kendaraan dan mencatat BBM dengan memilih jenis BBM yang telah tersedia.
2. Sistem menentukan perusahaan/cabang kendaraan secara otomatis setelah hubungan kendaraan tersedia.
3. Setiap pembuatan dan perubahan pada catatan BBM disimpan sebagai snapshot pada pelacakan BBM, mencakup nilai sebelum dan sesudah, pengguna pengubah, dan waktu perubahan.
4. Pengguna dapat meninjau riwayat perubahan setiap catatan BBM untuk keperluan pemeriksaan dan audit.

#### Flow 3 - Dokumentasi Foto Kendaraan Bulanan

1. Pengguna memilih kendaraan dan periode bulan berjalan.
2. Pengguna mengunggah foto tampak depan, belakang, samping kanan, dan samping kiri.
3. Sistem menyimpan tanggal foto, plat nomor, dan komentar/tinjauan.
4. Dashboard menampilkan kelengkapan foto dan kendaraan yang membutuhkan tindak lanjut.

#### Flow 4 - QR Code / Aksi Cepat Lapangan

1. Pengguna masuk ke sistem dan memindai QR code kendaraan.
2. Sistem memeriksa hak akses dan perusahaan/cabang pengguna.
3. Pengguna melihat ringkasan kendaraan.
4. Pengguna dapat membuat pemeliharaan cepat atau memperbarui foto kendaraan sesuai hak akses.

### 3.3 Prioritas Fitur

**Must Have:**

1. Perbaikan total laporan pemeliharaan berdasarkan subtotal.
2. Penambahan tahun masuk kendaraan pada laporan.
3. Standarisasi format laporan pemeliharaan.
4. Laporan pemeliharaan bulanan.
5. Penamaan laporan yang jelas.
6. Hubungan kendaraan ke perusahaan/cabang.
7. Jenis BBM yang terstandar.
8. Riwayat perubahan plat nomor.

**Should Have:**

1. Hubungan pengguna ke perusahaan/cabang beserta pembatasan datanya.
2. Data historis transaksi dan perapihan data induk kendaraan.
3. Alur pemeliharaan yang jelas.
4. Foto kendaraan bulanan, ringkasan dashboard, asuransi, dan pengingat.

**Could Have:**

1. QR code dan aksi cepat lapangan.
2. BAST digital.
3. Koneksi Orlansoft.

## 4. Kebutuhan Non-Fungsional

### 4.1 Akurasi dan Konsistensi Data

1. Total laporan pemeliharaan harus berasal dari subtotal detail item dan sama pada tampilan sistem, PDF, serta file unduhan.
2. Perubahan data induk kendaraan, perusahaan, plat, atau harga tidak boleh mengubah arti transaksi lama.
3. Sistem menyimpan informasi penting sesuai kondisi saat transaksi terjadi, minimal kendaraan/plat, perusahaan, dan pihak terkait.
4. Perubahan status, plat nomor, serta pembatalan harus memiliki riwayat yang dapat diperiksa.

### 4.2 Keamanan

1. Akses menu dan data mengikuti hak pengguna serta perusahaan/cabang yang menjadi tanggung jawabnya.
2. Penggunaan QR code dan aksi cepat lapangan tetap wajib mengikuti proses masuk sistem dan hak akses pengguna.
3. File bukti dan foto kendaraan harus divalidasi berdasarkan tipe, ukuran, dan otorisasi akses.

### 4.3 Performa dan Keandalan

1. Penyaringan laporan berdasarkan periode, kendaraan, perusahaan/cabang, dan status harus tetap cepat digunakan.
2. Laporan dan dashboard harus dapat ditampilkan dengan waktu tunggu yang wajar meskipun data bertambah.
3. Tombol simpan transaksi yang membutuhkan unggahan bukti hanya aktif setelah unggahan berhasil.
4. Pengiriman data ke sistem lain harus menampilkan status berhasil/gagal, dapat dicoba kembali, dan mencegah data terkirim ganda.

### 4.4 Kemudahan Pengembangan dan Perawatan Sistem

1. Aturan perhitungan biaya harus digunakan secara konsisten pada layar, laporan cetak, dan file unduhan.
2. Pembatasan akses perusahaan/cabang harus berlaku konsisten pada kendaraan, BBM, pemeliharaan, laporan, dan dashboard.
3. Data yang perlu seragam, seperti jenis kendaraan, merek, penyedia asuransi, jenis pemeliharaan, dan jenis BBM, dikelola melalui daftar pilihan resmi, bukan isian bebas.
4. Setiap kelompok fitur utama perlu diuji sebelum dirilis agar perhitungan, akses data, dan riwayat perubahan tetap benar.
5. Pengembangan fitur telepon genggam dalam kelompok Could Have harus menggunakan aturan akses dan data kendaraan yang sama dengan sistem utama.

## 5. Desain Antarmuka

### 5.1 Gaya Visual / UI-UX Guideline

1. Gunakan desain dan format form dari **Starter Kit** yang sudah tersedia sebagai dasar tampilan, tanpa perubahan besar pada UI/UX.
2. Penyesuaian tampilan hanya dilakukan untuk kebutuhan fitur baru dan penyempurnaan fitur yang sudah ada.
3. Gunakan nama menu yang spesifik:
   - Laporan Harian BBM
   - Laporan Bulanan BBM
   - Laporan Harian Pemeliharaan
   - Laporan Bulanan Pemeliharaan
   - Laporan Tahunan Pemeliharaan
4. Format laporan pemeliharaan mengikuti pola laporan BBM: judul, perusahaan, periode, kendaraan, detail transaksi, total akhir, waktu cetak, nama pencetak, dan area tanda tangan bila dibutuhkan.
5. Tahun masuk kendaraan ditampilkan berdekatan dengan plat nomor dan informasi kendaraan.
6. Nilai rupiah dan total akhir harus mudah dipindai serta konsisten formatnya.
7. Dashboard menekankan kendaraan yang butuh servis, foto belum lengkap, dan asuransi mendekati berakhir.
8. Halaman QR/telepon genggam menampilkan aksi cepat dan ringkasan inti, bukan formulir administrasi yang panjang.

## 6. Keterkaitan Sistem dan Prasyarat

### 6.1 Keterkaitan Sistem

- **Data induk kendaraan** - identitas kendaraan, tahun masuk, perusahaan/cabang, plat, status, foto, dan asuransi.
- **Transaksi BBM/perjalanan** - jenis BBM, laporan, dan penyimpanan informasi historis.
- **Pemeliharaan** - detail item, biaya, bukti unggahan, status, servis rutin, dan laporan.
- **Hak akses dan perusahaan/cabang** - pembatasan fitur dan data berdasarkan pengguna.
- **Dashboard** - ringkasan status kendaraan, foto, servis, serta pengingat asuransi.
- **Orlansoft (Could Have)** - menerima transaksi BBM dan pemeliharaan yang telah disetujui.
- **Data karyawan/JPayroll (Could Have, bila diperlukan)** - mendukung data penerima pada BAST digital.
- **Notifikasi Telegram (opsional)** - pengingat asuransi atau perhatian operasional.

### 6.2 Prasyarat Pengembangan

- Hubungan perusahaan/cabang untuk kendaraan dan pengguna harus ditetapkan sebelum pembatasan laporan dan aksi QR diterapkan.
- Alur persetujuan dan definisi transaksi yang siap dikirim harus disepakati sebelum koneksi Orlansoft dilakukan.
- Struktur data induk kendaraan dan informasi historis yang disimpan harus ditetapkan sebelum BAST digital dan laporan pemeriksaan lanjutan.
- Perubahan data, aturan akses, dan hasil perhitungan perlu diuji sebelum setiap kelompok fitur dirilis.

## 7. Jadwal dan Tahapan (Perkiraan)

### 7.1 Jadwal Pengembangan (Estimasi)

| Sprint | Durasi | Pekerjaan |
| --- | --- | --- |
| 1 | 1&nbsp;minggu | **Finalisasi FRD dan Ruang Lingkup** - finalisasi kebutuhan rinci, prioritas, aturan laporan, dan kebutuhan data utama berdasarkan PRD yang telah disusun. |
| 2 | 1&nbsp;minggu | **Akurasi, Standarisasi, dan Laporan Bulanan Pemeliharaan** - total pemeliharaan, tahun masuk kendaraan, penamaan laporan, format laporan, dan laporan pemeliharaan bulanan. |
| 3 | 2&nbsp;minggu | **Kepemilikan Kendaraan, Pemantauan dan Laporan Perusahaan** - hubungan kendaraan ke perusahaan/cabang dan laporan berdasarkan perusahaan kendaraan, foto bulanan, dashboard, asuransi/pengingat, data induk kendaraan. |
| 4 | 1&nbsp;minggu | **Jenis BBM dan Riwayat Plat Nomor** - jenis BBM dan riwayat perubahan plat nomor. |
| 5 | 1&nbsp;minggu | **Pembatasan Akses Perusahaan/Cabang** - hubungan pengguna ke perusahaan/cabang dan pembatasan data. |
| 6 | 1&nbsp;minggu | **Alur Pemeliharaan dan Pindai QR** - alur pemeliharaan, data historis, dan pindai QR/aksi cepat mobile. |
| 7 | 1&nbsp;minggu | **Integrasi Orlansoft** - pengiriman transaksi yang telah disetujui, status pengiriman, dan penanganan kegagalan pengiriman. |
| 8 | 1&nbsp;minggu | **Penyelesaian Pengembangan dan Stabilisasi** - penyempurnaan hasil pengembangan, pengecekan alur antar fitur, dan kesiapan masuk QA/testing. |
| 9 | 2&nbsp;minggu | **QA dan Testing** - pengujian seluruh fitur Must Have, Should Have, Could Have, serta integrasi. |
| 10 | 2&nbsp;minggu | **UAT, Perbaikan, dan Go Live** - validasi pengguna, penyelesaian temuan, pengujian ulang, dan persiapan rilis. |

### 7.2 Estimasi Rilis

PRD telah selesai disusun. Estimasi pekerjaan berikutnya adalah **13 minggu**: **9 minggu development** termasuk 1 minggu finalisasi FRD dan ruang lingkup, dilanjutkan **2 minggu QA dan testing** serta **2 minggu UAT, perbaikan, dan persiapan go live**. Target rilis ditetapkan setelah hasil UAT memenuhi kebutuhan dan temuan utama telah diselesaikan.

## 8. Kriteria Keberhasilan

- Total laporan pemeliharaan menghitung subtotal detail item dengan benar pada tampilan sistem, PDF, dan file unduhan.
- Laporan pemeliharaan menampilkan tahun masuk kendaraan, informasi perusahaan/periode/kendaraan, waktu cetak, nama pencetak, dan total akhir.
- Pengguna dapat membuat laporan pemeliharaan bulanan dengan penyaringan bulan/tahun dan kendaraan.
- Nama menu laporan jelas membedakan konteks BBM, pemeliharaan, dan periode.
- Setelah pembatasan perusahaan/cabang tersedia, pengguna tidak dapat melihat atau memproses data di luar lingkup yang diizinkan.
- Dashboard dapat menampilkan kendaraan yang perlu servis, foto belum lengkap, dan pengingat asuransi setelah fitur terkait dirilis.
- Laporan historis tetap bermakna meskipun data induk berubah.
- Koneksi ke sistem lain hanya mengirim transaksi yang sudah disetujui dengan status pengiriman yang dapat dilihat.

## 9. Tujuan Dokumen PRD

- Menyatukan kebutuhan operasional, keuangan, pemeriksaan, dan usulan penyempurnaan VEMO berdasarkan hasil evaluasi.
- Menjadi acuan utama penyusunan kebutuhan rinci, estimasi pekerjaan, pengembangan, uji penerimaan pengguna, dan rilis.
- Menetapkan prioritas pekerjaan dari perbaikan laporan hingga integrasi lanjutan.
- Mengurangi miskomunikasi mengenai ruang lingkup fitur, alur kerja, kepemilikan data, dan definisi keberhasilan.
