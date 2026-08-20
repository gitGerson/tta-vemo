# Changelog

Dokumen ini mencatat perubahan penting pada **Vemo** (Vehicle Monitoring) dalam format changelog yang lebih formal dan detail. Isi changelog disusun dari git tag rilis dan seluruh commit yang sudah masuk ke repository.

Penomoran versi mengikuti **git tag asli** pada remote `origin`: `v2.0.0`, `v2.0.1`, `v2.0.2`, dan `v2.0.3`. Setiap tag final memiliki pasangan tag staging `-rc.1` dengan konten identik (promosi `staging` → `main`). Seluruh commit yang berada **setelah `v2.0.3`** (build-out Vemo v3, Mei–Juli 2026) dikumpulkan sebagai rilis **`3.0.0`**. Setiap entri menyertakan hash commit sebagai referensi; commit merge antar-branch tidak dirinci.

## [3.0.0] - 2026-07-29

Rilis mayor **Vemo v3**: build-out modul master data, operasional, dashboard, notifikasi (email dan Telegram), foto kendaraan, QR scan, dan laporan analisis. Bagian akhir section ini memuat fondasi starter kit Sneat 2025 yang berasal dari git root terpisah dan tidak pernah masuk ke lineage tag `v2.0.x`.

Range: setelah `v2.0.3` (`c890f97..origin/staging`, 174 commit non-merge), ditambah root legacy Sneat.

### Ringkasan Perubahan Utama

Vemo v3 mengubah aplikasi dari modul maintenance dan laporan BBM menjadi sistem monitoring kendaraan yang lengkap. Sepuluh perubahan besar:

1. **Modul master data baru** - Company, Vehicle, Vehicle Brand, Vehicle Type, Fuel Type, Driver, Destination, dan Type Maintenance, masing-masing dengan CRUD, validasi, permission, seeder, dan fungsi trash (restore dan force delete).
2. **Modul operasional Record** - Record dan Record Snapshot dengan CRUD berbasis modal, normalisasi tanggal, harga satuan bahan bakar, filter default awal tahun, dan shared edit modal antara view index dan show.
3. **Vehicle Insurance** - Modul asuransi kendaraan lengkap: konstanta tipe asuransi, attachment, permission per-field, serta reminder dan notifikasi masa berlaku.
4. **Dokumen kendaraan (STNK dan KIR)** - Field gambar dan due date, reminder due-date beserta permission-nya, tabel masa berlaku pada dashboard, status overdue pada notifikasi, dan validasi gambar/due date yang independen.
5. **Sistem notifikasi** - Notifikasi in-app dengan dropdown dan read receipt, notifikasi ter-grup dengan UI offcanvas beserta pagination dan filter, reminder via email dengan logging, serta integrasi Telegram (account linking, long-polling).
6. **Dashboard baru** - KPI card, quick action, chart kondisi kendaraan, service reminder, widget financial/utilization/composition, filter company dan bulan, serta pemuatan data via AJAX.
7. **Foto kendaraan dan QR scan** - Manajemen foto kendaraan dengan tracking kelengkapan, komentar dengan validasi dan sanitasi, penyimpanan S3 lewat signed URL sementara, label kendaraan, dan QR scan untuk lookup kendaraan beserta quick action.
8. **Laporan analisis** - Laporan maintenance harian, bulanan, dan tahunan, serta laporan analisis bahan bakar dan maintenance bulanan dengan export PDF dan Excel, dilengkapi pemilihan company dan validasi cetak.
9. **Fondasi UI dan tabel** - Migrasi DataTables ke engine Tabulator dengan komponen Blade reusable, toggle visibilitas kolom yang persisten, export, komponen form generik (FilePond, Select2, daterangepicker, rich editor, money/rupiah input), breadcrumbs, sidebar dan navbar search dari config menu, serta terjemahan UI ke bahasa Indonesia.
10. **Fondasi arsitektur dan akses** - Tenant scoping pada model beserta permission bypass, katalog permission berbasis config dengan command `permission:update` yang sync/prune aman, hashid route binding, auto-generation kode unik, audit trail generik pada level model, global record search dengan keyboard shortcut, middleware `PreventSearchIndexing`, dan import SQL dump VEMO legacy.

**Breaking change:** field `plat_old` dan atribut `isprivate` pada vehicle dihapus, label maintenance `IN_PROGRESS` menjadi `DRAFT`, input kode manual dihapus (kode dibuat otomatis), permission dipindah ke config sehingga perlu di-sync ulang, dan modul starter kit catalog, mobile, dan employee dihapus.

### Added

- `77f512f` - Menambahkan status overdue pada document reminder STNK dan KIR beserta penyesuaian UI dan logikanya.
- `c2c71bd` - Menambahkan notifikasi ter-grup dengan UI offcanvas, pagination, dan filter per grup.
- `7971d40` - Menambahkan logika badge status maintenance untuk label rutin dan non-rutin beserta test-nya.
- `24a730e` - Menambahkan field due date STNK dan KIR pada data QR scan kendaraan beserta test terkait.
- `aeb8176` - Mengganti reminder foto kendaraan pada dashboard dengan tabel masa berlaku STNK dan KIR.
- `ead7512` - Menambahkan handling harga satuan bahan bakar pada record.
- `0c0db43` - Menambahkan kolom tipe kendaraan pada `RecordDataTable` beserta test label-nya.
- `2bb7674` - Menambahkan hidden input untuk sinkronisasi raw value pada input rupiah.
- `e5ea01c` - Mengganti input number dengan komponen money pada coverage value dan premi asuransi.
- `93805ed` - Menambahkan formatting input rupiah dan komponen money pada form kendaraan.
- `bf4ee3e` - Menambahkan badge terkomputasi pada kolom type dan merk fuel type.
- `9a729f8` - Menambahkan dukungan AJAX dan komponen UI baru pada dashboard.
- `80464e1` - Menambahkan import SQL dump VEMO legacy dengan preservasi primary key dan seeding data.
- `d6420f1` - Menambahkan laporan analisis maintenance bulanan dengan export PDF dan Excel.
- `5b53e88` - Menambahkan laporan analisis bahan bakar bulanan dengan export PDF dan Excel.
- `89697c6` - Menambahkan `VehicleSeeder`, `MaintenanceSeeder`, dan `RecordSeeder`.
- `173cc07` - Memindahkan pembuatan user dari `DatabaseSeeder` dan menambahkan test `DriverSeeder`.
- `14ad9ac` - Menambahkan permission per-field pada form vehicle insurance beserta aturan validasinya.
- `cda5b3e` - Menambahkan filter company dan bulan pada dashboard beserta tampilan metrik yang diperbarui.
- `37c2f3c` - Menambahkan handling attachment pada vehicle insurance dengan validasi dan tampilan.
- `d6fbd93` - Menambahkan document due-date reminder STNK dan KIR beserta permission dan notifikasinya.
- `00fd269` - Menambahkan field gambar dan due date KIR pada model vehicle dan snapshot.
- `b3c0ae1` - Menambahkan konstanta tipe asuransi dan memperbarui aturan validasi.
- `99c2217` - Menambahkan auto-generation kode unik pada model dan menghapus input kode manual dari form.
- `5082bfa` - Menambahkan notifikasi reminder via email beserta logging-nya.
- `3a7f6cf` - Menambahkan concern `HasDiffrntHashId` pada beberapa model.
- `836fa04` - Menambahkan utility `S3Url` untuk signed URL sementara dan pencocokan reference.
- `61d9881` - Menambahkan fungsi trash (restore dan force delete) untuk company, fuel type, vehicle brand, dan vehicle type.
- `ac7d7e8` - Menambahkan integrasi FilePond pada handling gambar company beserta aturan validasinya.
- `dff87a9` - Mengganti pemilihan company pada edit user dengan komponen select kustom dan memperbaiki error handling.
- `0b81d34` - Menambahkan global record search dengan keyboard shortcut dan tampilan hasil pencarian lintas record.
- `0b2cc58` - Menambahkan widget dashboard untuk financial, utilization, dan composition.
- `cbaf4e2` - Menambahkan KPI cards dan quick actions, serta merapikan layout home.
- `5c59a30` - Menambahkan rendering avatar user berbasis SVG yang di-encode base64.
- `e6e4e58` - Menambahkan seeder untuk companies, fuel types, maintenance types, vehicle brands, dan vehicle types.
- `245bb55` - Menambahkan fitur QR scan untuk lookup kendaraan beserta quick actions.
- `0b606a6` - Menambahkan generation dan tampilan label kendaraan.
- `6bc0abc` - Menambahkan integrasi Telegram untuk notifikasi reminder user dan account linking.
- `9f999da` - Menambahkan reminder dan notifikasi masa berlaku asuransi kendaraan.
- `946fcff` - Menambahkan fitur komentar pada foto kendaraan dengan validasi dan sanitasi.
- `39d25cb` - Menambahkan detail view foto kendaraan dan menyempurnakan fitur pengelolaannya.
- `e9e20f7` - Menyempurnakan manajemen foto kendaraan dengan tracking kelengkapan dan validasi.
- `4bf3ed8` - Menambahkan fungsi manajemen foto kendaraan.
- `7f22d09` - Menambahkan fitur dan view monthly maintenance report.
- `77aee0d` - Menambahkan view dan PDF generation untuk daily dan yearly maintenance report.
- `0a47678` - Menambahkan handling `company_id` dan tenant scope pada record maintenance.
- `1b04f54` - Menambahkan permission service reminders dan mengatur visibilitas pada home view.
- `c289a14` - Menambahkan `UserTrashDataTable` untuk manajemen trash user.
- `d1cb42f` - Menambahkan `RoleDataTable` dan `UserDataTable` untuk modul roles dan users.
- `1d1696a` - Menambahkan sistem notifikasi dengan dropdown dan read receipts.
- `bae53fb` - Menambahkan chart kondisi kendaraan dan service reminders pada home view.
- `e566289` - Menambahkan pengelolaan detail dan notes maintenance.
- `232ab30` - Menyempurnakan manajemen record dengan modal form dan normalisasi tanggal.
- `0feb393` - Menambahkan model `RecordSnapshot`, data table, dan CRUD-nya.
- `1c44c3b` - Menambahkan modul manajemen Record dengan operasi CRUD.
- `a8e6264` - Menambahkan tenant scoping untuk model beserta permission bypass.
- `8ed3344` - Menambahkan modul manajemen vehicle insurance.
- `6fcb147` - Menambahkan dropdown pengelolaan vehicle type dan brand pada index kendaraan.
- `35ee0eb` - Menambahkan fungsi manajemen vehicle brand.
- `db107b0` - Menambahkan fungsi manajemen vehicle type.
- `b6d194e` - Menambahkan pengelolaan fuel type pada model, form, dan data table kendaraan.
- `3b0a018` - Menambahkan modul fuel type dengan CRUD, route, permission, dan komponen UI.
- `117b0e1` - Menambahkan data table terkait (driver, vehicle, user) pada detail company.
- `9848acb` - Menambahkan fungsi create dan edit kendaraan beserta form dan view-nya.
- `a064c49` - Menambahkan field financial dan document pada model vehicle dan snapshot.
- `1e4581f` - Menambahkan fungsi vehicle snapshot dan menghapus field `plat_old` yang usang.
- `b56bcb8` - Menambahkan opsi export dan perbaikan dropdown pada tabel.
- `5aabaa6` - Menambahkan toggle visibilitas kolom dan persistensinya pada tabel Tabulator.
- `c6bbfcb` - Menambahkan modul manajemen company dengan CRUD, validasi, dan permission.
- `fef716d` - Menambahkan modul master data vehicle.
- `a214317` - Menambahkan modul master data destination.
- `74b965b` - Menambahkan modul master data type maintenance.
- `584f98c` - Menambahkan detail modal record driver, menggantikan halaman show standalone dengan partial via AJAX.
- `9012757` - Menambahkan struktur DataTable config-driven untuk driver, menggantikan komponen legacy.
- `a9029ba` - Mengintegrasikan Select2 untuk multi-select pada form.
- `7177105` - Menambahkan asosiasi company untuk driver dan vehicle.
- `8fb46f2` - Menambahkan NIK employee lookup dengan prefill form dan prop `imagePreviewHeight` FilePond.
- `999fcc6` - Memulihkan modul master data yang sudah ada.
- `da3f1a4` - Menambahkan breadcrumbs generik pada layout.
- `0545f45` - Menambahkan middleware `PreventSearchIndexing`, memperbarui `robots.txt`, beserta test-nya.
- `3066cf3` - Menambahkan `fruitcake/laravel-debugbar` untuk kebutuhan debugging.
- `31c1693` - Menambahkan hashid route binding untuk modul web inti.
- `08b718a` - Merefactor DataTables menjadi komponen Blade reusable.
- `858ed36` - Menambahkan FilePond uploads generik dengan dukungan preload file existing.
- `227d897` - Menambahkan modul form-demo dan meng-upgrade komponen rich editor/date.
- `f4e69df` - Menambahkan mode komponen date dengan modul daterangepicker berbasis Vite.
- `568e4f7` - Menambahkan komponen Blade form reusable.
- `3bf7431` - Menambahkan dokumentasi komprehensif untuk komponen Blade form reusable.
- `a03e312` - Menambahkan command `permission:update` dengan refresh config dan sync yang aman.
- `e116c37` - Menambahkan manajemen employee dengan sinkronisasi dari JPayroll.

### Changed

- `d5ea4ab` - Mengaktifkan shared edit modal untuk manajemen record pada view index dan show.
- `bfd99c3` - Menghapus atribut `isprivate` dari model vehicle dan model terkait.
- `0d3eb46` - Membuat gambar dokumen dan due date menjadi optional secara independen.
- `c03ccf3` - Membatalkan (revert) penonaktifan dark mode beserta test-nya.
- `e36a7a7` - Merapikan route vehicle photo dan test-nya dengan penggunaan hashid.
- `29c4747` - Menggunakan atribut model secara langsung untuk handling title dan description pada search.
- `b61af9e` - Menerjemahkan berbagai elemen UI dan label dari bahasa Inggris ke bahasa Indonesia.
- `ab0ce11` - Mengubah label VIN menjadi 'Nomor Rangka'.
- `0140220` - Menambahkan catatan `next_update` pada dokumentasi.
- `49c2ed5` - Menyederhanakan konfigurasi deployment CI dan memperbarui path script deploy.
- `7907ae9` - Memperbarui aset branding Vemo.
- `6f8d8c0` - Mengubah penerimaan update bot Telegram dari webhook menjadi long-polling.
- `ea55b74` - Menambahkan section vehicle management pada menu dan memperbarui item terkait kendaraan.
- `662ef7b` - Memindahkan section vehicle insurances ke operational dan mengatur urutannya.
- `32a7d7f` - Mengubah layout tabel dari `fitDataStretch` ke `fitDataFill`.
- `137d49d` - Menambahkan section operational dan memperbarui urutan item pada konfigurasi menu.
- `af093fa` - Menambahkan kolom `code_mt` dan menyempurnakan tampilan badge status pada view maintenance.
- `bc8cb44` - Menyempurnakan integrasi Tabulator dengan kemampuan local editing dan elemen UI lebih baik.
- `90ad7be` - Mendesain ulang detail view kendaraan dengan layout dan informasi tambahan.
- `9e65460` - Memperbarui detail company dengan breadcrumb dan informasi brand.
- `e572d8a` - Menambahkan master CSV type maintenance dengan jenis repair dan service yang lengkap.
- `c4cafcf` - Merefactor komponen tabel agar memakai engine Tabulator.
- `e0bd1eb` - Menyempurnakan manajemen driver dengan integrasi source company dan nomor telepon nullable.
- `1aa2ecd` - Menyempurnakan detail destination dengan nama provinsi dan integrasi peta.
- `8a1f1f8` - Merombak skema master data dan source destination.
- `77a1f33` - Mendaftarkan modul vehicle destination dan type maintenance.
- `0d70861` - Menghapus `BreadcrumbService` dan komponen terkait, memperbarui struktur DataTable dan layout.
- `c52930e` - Menyelaraskan modul driver dengan guide dan menambahkan prop sizing FilePond.
- `023a6cc` - Memindahkan create/edit driver ke shared modal dan memperbaiki FilePond CORS untuk gambar eksternal.
- `81a50b5` - Memindahkan storage gambar driver ke S3, mengekstrak validasi FilePond, dan menambahkan NIK service.
- `941d45c` - Mengekstrak JPayroll service dan menambahkan form request serta view driver.
- `bd44a2c` - Memperbarui route redirect setelah update user dan menambahkan alert pesan sukses/gagal.
- `4a4a055` - Menghapus link role management dan memperbarui permission pada view user.
- `3a1d95f` - Menyempurnakan layout edit role dan tampilan permission.
- `2e22877` - Menyempurnakan layout dan interaktivitas detail role dan permission.
- `1063308` - Memperbaiki konfigurasi CI/CD.
- `83ae9b9` - Memindahkan aset SCSS layout Sneat ke modul Vite.
- `8f001db` - Memindahkan script layout Sneat ke modul Vite.
- `f0375be` - Menyempurnakan tombol action pada data table untuk UX yang lebih baik.
- `dcae6e2` - Menyempurnakan flow pembuatan user LDAP.
- `a3f322d` - Menambahkan auditable trait generik untuk logging aktivitas pada model.
- `0e49a91` - Mengekstrak validasi reusable ke model dan dedicated form request.
- `f078eff` - Merender sidebar dari config menu dan mengekspos entry form demo.
- `f9fdc18` - Menggerakkan navbar search dari config menu dengan permission gating.
- `dc56376` - Memindahkan katalog permission ke config dan membuat seeder melakukan sync/prune secara aman.
- `83503e0` - Mengubah timezone, menambahkan role template, navbar autofocus, dan modal create user.

### Fixed

- `6a361e2` - Memperbaiki aturan validasi record agar mendukung destination legacy dan nilai tersimpan tetap terpilih pada modal edit.
- `e8fd97d` - Memperbaiki `applyEdit` agar total harga dihitung ulang saat edit dan input event harga satuan ter-trigger.
- `bf8a3b3` - Merapikan styling dan fungsionalitas view QR scan dan report.
- `8724806` - Memperbaiki path script deployment staging.
- `5bdf6d6` - Mengubah layout tabel foto kendaraan dan history menjadi `fitData`.
- `4cc1ef3` - Memastikan kendaraan tersedia sebelum redirect pada vehicle photo.
- `04358f4` - Menggunakan route key pada `data-id` tombol pembatalan maintenance.
- `d0fcd7f` - Menghapus atribut `mode` yang tidak diperlukan pada select tipe asuransi.
- `e05c01f` - Mengubah default start date filter record menjadi awal tahun.
- `9cb340e` - Memfilter maintenance completed dan confirmed pada query laporan.
- `d60930e` - Memperbaiki pemilihan company dan month picker pada dashboard.
- `bb06a0e` - Memperbaiki specificity input pencarian tabel agar layout tidak rusak.
- `2f412c9` - Menambahkan styling global grid Tabulator dengan dukungan dark mode.
- `7ad3abd` - Menonaktifkan dark mode dan menambahkan test bahwa navbar tidak menampilkan toggle dark mode.
- `9220306` - Memperbaiki permission akses section Dashboard beserta permission terkait.
- `388af78` - Memperbaiki generation laporan dengan pemilihan company dan validasi cetak.
- `563535e` - Memperbaiki teks assertion pada test index type maintenance.
- `c947f83` - Menukar urutan tampilan kode dan nama company pada dropdown select.
- `af51f56` - Memperbaiki atribut data agar tidak bermasalah dengan hyphen pada binding JS.
- `acb42d9` - Menyederhanakan logic redirect setelah simpan foto kendaraan, serta memperbarui assertion test modul destination.
- `7640615` - Memperbaiki dropdown menu user pada navbar.
- `dc04026` - Menambahkan field `guard_name` pada tabel roles dan memperbarui atribut fillable model.
- `e723652` - Memulihkan kolom role count dan menyelaraskan permission index role.
- `66a18a1` - Memperbaiki error custom page dan contoh ESB sync.

### Removed

- `e249455` - Menghapus modul catalog, mobile, dan employee yang tidak terpakai dari starter kit.

### Legacy Foundation (root Sneat, di luar lineage tag)

Commit fondasi starter kit Sneat dari git root terpisah (Agustus–November 2025). Tidak pernah masuk ke tag `v2.0.x` dan tergabung ke HEAD melalui merging branch belakangan.

- `2718532` - Mengimplementasikan struktur layout baru dengan template Sneat.
- `e78529d` - Mengimplementasikan audit trail.
- `4973262` - Mengimplementasikan Role-Based Access Control (RBAC).
- `dc816ea` - Menyempurnakan manajemen user dan role dengan routing berbasis permission.
- `22cb7e9` - Menambahkan aset dan UI mobile.
- `e807215` - Menambahkan view CRUD untuk categories dan products.
- `bd8d33c` - Memperbarui style dark theme untuk state menu aktif.
- `ff200b5` / `9f818d3` / `5035e0b` / `93ec7ed` - Penyesuaian kebutuhan starter (thumbnail, CDN, dashboard role).
- `22a831f` - Initial Commit (root starter kit).

## [2.0.4] - 2026-07-01 (branch `hotfix/v2.0.3`, belum di-tag)

Hotfix yang dikerjakan langsung di atas `v2.0.3` dan belum di-merge ke `staging`/`development`.

### Fixed

- `f575c58` - Menampilkan tombol cancel untuk seluruh maintenance berstatus draft dan completed.
- `43535cb` - Menambahkan route update status maintenance yang belum terdaftar.
- `8874055` - Menetapkan status maintenance `DRAFT` atau `COMPLETED` sesuai opsi simpan yang dipilih.
- `cd75e7d` - Memfilter maintenance completed dan confirmed pada laporan maintenance tahunan.

## [v2.0.3] - 2026-04-20

Tag staging: `v2.0.3-rc.1` (`e70834a`, 2026-04-20) — konten identik, promosi `development` → `staging` → `main`. Range: `v2.0.2..v2.0.3`.

### Added

- `91f5fa4` - Memperbarui layout kolom PDF dan view monthly report.
- `3c25d9b` - Menambahkan kolom `TANGGAL` dan formatting tanggal pada tabel monthly report.

### Fixed

- `949fc66` - Menghapus margin berlebih pada div `table-responsive` report.

### Documentation

- `cf84a4a` - Memperbarui copy changelog login ke bahasa Indonesia dan menyelaraskan versi dengan git tag.

## [v2.0.2] - 2026-03-13

Tag staging: `v2.0.2-rc.1` (`1638400`, 2026-03-13) — konten identik. Range: `v2.0.1..v2.0.2`.

### Added

- `267906e` - Mendesain ulang tabel daily BBM report untuk PDF dan web view.
- `34ef3e8` - Memperbarui konfigurasi GitLab CI.

### Fixed

- `82c947f` - Menghapus watermark staging pada layout.
- `3bd5cd7` - Memperbaiki konfigurasi GitLab CI yml.

## [v2.0.1] - 2026-03-11

Tag staging: `v2.0.1-rc.1` (`8527f01`, 2026-03-11) — konten identik. Range: `v2.0.0..v2.0.1`.

### Fixed

- `0f9767d` - Memperbarui versi rilis yang ditampilkan pada login menjadi 2.0.1.
- `8c45914` - Mengganti respons firm delete record dan menyelaraskan layout tabel.

## [v2.0.0] - 2026-03-11

Rilis tag pertama. Lineage `v2.0.x` dimulai dari `Initial commit` (`4b3cd9e`, 2025-12-05) — root yang berbeda dari fondasi Sneat 2025. Range: awal lineage `..v2.0.0`.

### Added

- `9c7be41` - Menyelesaikan modul search dan menerapkan observer untuk log aktivitas user.
- `2082cc3` - Mengimplementasikan logic service notifications.
- `d313829` - Menyesuaikan guard submit form maintenance dan flow cancel.
- `7b73768` - Membuat modul error page dari starter kit.
- `68570d7` - Menambahkan modal create user dan validasinya.
- `058dcb4` - Menambahkan modal preview gambar pada maintenance.
- `d6a3f41` - Menambahkan CI/CD.
- `4b3cd9e` - Initial commit (root lineage `v2.0.x`).

### Changed

- `2e76334` - Merapikan UI, konfirmasi, dan notifikasi maintenance.
- `b516329` - Mengubah label `IN_PROGRESS` menjadi `DRAFT` pada maintenance.
- `20de4db` - Menyesuaikan wording file upload maintenance.

### Fixed

- `edb9988` - Memperbaiki beberapa issue pada maintenance dan menyesuaikan RBAC.
- `b7639ae` - Memperbaiki view index maintenance.
- `d882114` - Menambahkan default value 0 pada field KM kendaraan.

### Deployment Notes

- Jalankan `php artisan db:seed --class=RolePermissionSeeder` untuk sinkronisasi permission dan role template.
- Jalankan `npm run build` setelah perubahan aset frontend (modul Vite layout Sneat, komponen form, Tabulator).
- Jalankan migrasi database untuk seluruh modul master data dan operasional (company, vehicle, driver, destination, type-maintenance, record, snapshot, insurance, maintenance, notification).
- Pastikan koneksi S3 tersedia untuk penyimpanan gambar driver dan foto kendaraan.
- Pastikan bot Telegram aktif (long-polling) bila fitur notifikasi Telegram digunakan.
