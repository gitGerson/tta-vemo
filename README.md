# VEMO

VEMO adalah aplikasi monitoring operasional kendaraan berbasis Laravel 12 untuk kebutuhan internal Tong Tji. Aplikasi ini mencakup autentikasi LDAP, pengelolaan master data armada, pencatatan pemakaian BBM, workflow maintenance, dokumentasi foto kendaraan, asuransi kendaraan, scan QR, notifikasi (in-app & Telegram), laporan operasional, upload gambar, multi-tenant scoping, dan manajemen akses berbasis role-permission.

## Ringkasan Fitur

- Login menggunakan akun LDAP.
- Dashboard operasional dengan KPI cards, quick actions, grafik kondisi kendaraan, widget finansial/utilisasi/komposisi armada, serta pengingat service dan maintenance draft.
- Master data untuk company, driver, vehicle, destination, type maintenance, fuel type, vehicle type, dan vehicle brand.
- Modul `records` (Pelacakan BBM) untuk pencatatan pemakaian dan snapshot data kendaraan.
- Modul `maintenances` untuk pengajuan, approval, konfirmasi, detail item, catatan, dan tracking total biaya.
- Modul `vehicle-photos` untuk dokumentasi foto bulanan kendaraan dengan pelacakan kelengkapan dan komentar.
- Modul `vehicle-insurances` dengan pengingat masa berlaku polis dan notifikasi.
- Scan QR pada label kendaraan untuk aksi cepat dan lookup data unit.
- Multi-tenant scoping berbasis company dengan permission bypass.
- Sistem notifikasi in-app (dropdown + read receipts) dan integrasi Telegram (account linking + reminder harian).
- Laporan BBM harian/bulanan dan maintenance harian/bulanan/tahunan dengan export PDF dan Excel.
- Upload dan manajemen gambar.
- RBAC untuk user, role template, permission assignment, dan activity log.
- Soft delete pada sebagian besar modul.

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Laravel UI + Bootstrap 5 (template admin Sneat)
- Tailwind CSS v4
- Vite
- LDAPRecord Laravel (autentikasi)
- Laravel Sanctum
- Yajra DataTables (server-side) + Tabulator.js (rendering tabel interaktif)
- Maatwebsite Excel & SheetJS (xlsx) untuk export Excel
- DomPDF & jsPDF untuk export PDF
- SimpleQRCode (f9webltd) untuk label & scan QR
- Laravel Notification Channels — Telegram
- Laravolt Avatar (avatar base64 SVG) & Laravolt Indonesia (data wilayah)
- Stevebauman Purify (sanitasi HTML komentar)
- Tiptap (rich text editor) & Select2 (multi-select)
- Predis / Redis
- AWS S3 compatible storage

## Changelog

### 2026-06

- **Master data armada** diperluas: modul Fuel Type, Vehicle Type, dan Vehicle Brand beserta seeder; vehicle kini mereferensikan fuel type, type, dan brand.
- **Modul Vehicle** dilengkapi CRUD penuh, redesign halaman detail, field finansial & dokumen, fungsi snapshot data, generate label kendaraan (QR), serta dropdown pengelolaan type/brand langsung dari index.
- **Vehicle Insurance**: modul manajemen polis asuransi dengan pengingat masa berlaku dan notifikasi.
- **Vehicle Photos**: dokumentasi foto bulanan kendaraan dengan pelacakan kelengkapan, halaman detail, dan komentar yang divalidasi serta disanitasi.
- **QR Scan**: scan label kendaraan untuk lookup unit dan aksi cepat.
- **Records (Pelacakan BBM)**: modul pencatatan dengan form modal, normalisasi tanggal, dan RecordSnapshot.
- **Maintenance**: detail item & catatan, integrasi Tabulator dengan editing lokal, kolom `code_mt`, badge status, serta `company_id` dan tenant scope.
- **Tenant scoping**: pembatasan data per company pada model terkait dengan permission bypass.
- **Notifikasi**: sistem notifikasi in-app (dropdown + read receipts) dan integrasi Telegram (account linking, reminder harian, polling update via long-polling).
- **Dashboard**: KPI cards, quick actions, grafik kondisi kendaraan, service reminders, serta widget finansial, utilisasi, dan komposisi armada.
- **Laporan**: penambahan laporan maintenance harian, bulanan, dan tahunan beserta export PDF/Excel.
- **UI tabel**: migrasi komponen tabel ke engine Tabulator dengan toggle visibilitas kolom (persisten), opsi export, dan layout `fitDataFill`.
- **Avatar** pengguna dirender sebagai base64 encoded SVG.

### 2026-04-16

- Monthly BBM report was redesigned for both web and PDF views with a denser table layout, kilometer columns (`awal`, `akhir`, `total`), fuel and rupiah totals, combined driver display, scale column, and signature/footer blocks.
- Monthly PDF export now uses portrait orientation and includes vehicle metadata lookup so the printed report shows vehicle type and more structured report headers.
- Monthly report filter handling was tightened so the selected company is preserved consistently between the filter form and PDF export action.

### 2026-03-13

- Daily BBM report was redesigned for both web and PDF views with vehicle type, kilometer breakdown, liter and rupiah columns, totals row, and a cleaner export/search toolbar.
- Daily PDF export now uses portrait orientation and applies category filtering before rendering so printed totals follow the selected `KESELURUHAN`, `UMUM`, or `PRIBADI` filter.
- Daily report exports were aligned to visible columns for copy, CSV, Excel, PDF, and print output.

### 2026-03-12

- GitLab CI was updated with a dedicated `development` deployment job in addition to the existing `staging` and `main` pipelines.
- Project documentation was refreshed to reflect the current VEMO scope, setup flow, reporting modules, and deployment model.

## Modul Utama

### Authentication

- Login dari route `/login` menggunakan `username` dan `password` LDAP.
- Untuk pengembangan tanpa LDAP, tersedia superadmin lokal: username `stagingpurpose`, password `P@ssw0rd1938`.
- Root URL `/` diarahkan ke halaman login; setelah login diarahkan ke `/home`.

### Dashboard

- Route utama setelah login: `/home`.
- KPI cards, quick actions, grafik kondisi kendaraan, service reminders, serta widget finansial, utilisasi, dan komposisi armada.

### Management

- User management dan user trash (restore/force delete).
- Role template management.
- Assignment role dan permission ke user.
- User activity logs.

### Master Data

- Companies
- Drivers
- Vehicles (+ snapshot, label QR)
- Destinations
- Type Maintenances
- Fuel Types
- Vehicle Types
- Vehicle Brands

### Operational

- `records` (Pelacakan BBM): pencatatan pemakaian dan snapshot kendaraan.
- `maintenances`: request, detail item, catatan, approval, confirmation, dan tracking total biaya.
- `vehicle-photos`: dokumentasi foto bulanan dengan pelacakan kelengkapan dan komentar.
- `vehicle-insurances`: pengelolaan polis dengan pengingat masa berlaku.
- `qr-scan`: scan label kendaraan untuk aksi cepat.

### Reports

- Laporan BBM harian dan bulanan.
- Laporan maintenance harian, bulanan, dan tahunan.
- Export PDF untuk seluruh report.
- Export Excel untuk laporan maintenance tahunan dan modul record.

### Notifications

- Notifikasi in-app dengan dropdown dan read receipts.
- Integrasi Telegram: account linking, reminder harian, dan polling update via long-polling.

### Media

- Upload file menggunakan FilePond (chunked upload).
- Image library dengan restore dan force delete.

## Struktur Penting

```text
app/
  Console/Commands/           # Artisan command aplikasi (Telegram, dll)
  DataTables/                 # Definisi DataTable config-driven
  Exports/                    # Export Excel
  Http/Controllers/           # Controller modul aplikasi
  Http/Middleware/            # Middleware permission, tenant, anti-index
  Imports/                    # Import Excel
  Models/                     # Model Eloquent
  Observers/                  # Audit / CRUD observer
  Services/                   # PermissionSync, JPayroll, FilePond, dll
config/
  permission.php              # Sumber tunggal permission & role template
  menu.php                    # Navigasi sidebar & search palette
database/
  seeders/                    # Seeder roles, permissions, dan master data
  factories/                  # Factory model
resources/views/              # Blade views
routes/web.php                # Web routes utama
routes/console.php            # Scheduler & artisan closure command
```

## Setup Lokal

### 1. Install dependency

```bash
composer install
npm install
```

### 2. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi berikut di `.env`:

- `APP_NAME`, `APP_URL`
- koneksi database MySQL
- konfigurasi LDAP
- konfigurasi S3 / object storage
- konfigurasi Redis / queue connection
- `TELEGRAM_BOT_TOKEN` untuk integrasi notifikasi Telegram

### 3. Siapkan database

Buat database, lalu jalankan:

```bash
php artisan migrate
php artisan db:seed
```

Seeder utama akan menyiapkan:

- role, permission, dan role template
- company, driver, vehicle
- fuel type, vehicle type, vehicle brand
- destination, type maintenance
- user awal `stagingpurpose` jika belum ada

Untuk sinkronisasi ulang permission dan role template dari config:

```bash
php artisan db:seed --class=RolePermissionSeeder
# atau
php artisan permission:update
```

### 4. Jalankan aplikasi

Mode development yang direkomendasikan:

```bash
composer run dev
```

Command ini menjalankan Laravel development server, queue listener, dan Vite dev server.

Alternatif manual:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## Storage dan Upload

Project ini menggunakan `FILESYSTEM_DISK=s3` pada konfigurasi contoh. Pastikan endpoint, bucket, key, dan secret sudah sesuai dengan environment yang dipakai. Gambar driver dan foto kendaraan disimpan pada disk S3.

Route upload yang tersedia:

- `POST /uploads/process`
- `DELETE /uploads/revert`
- `GET /uploads/load`

## Permission Model

Permission dibagi per area:

- `management.*` — users, roles, tenant bypass
- `master.*` — companies, drivers, vehicles, destinations, type maintenances, fuel types, vehicle types, vehicle brands, vehicle insurances
- `operational.*` — records, maintenances, vehicle photos, qr scan
- `reports.*` — daily/monthly BBM, daily/monthly/yearly maintenance
- `images.*` — image library

Seeder `RolePermissionSeeder` (atau `php artisan permission:update`) menyiapkan permission dan template role (`super_admin_template`, `admin_template`, `user_manager_template`, `viewer_template`) untuk kebutuhan assignment user.

Multi-tenant scoping membatasi data per company; permission `management.tenant.bypass` memungkinkan akses lintas tenant.

## Reporting dan Export

Laporan yang tersedia:

- `/report/daily`
- `/report/monthly`
- `/report/maintenances/daily`
- `/report/maintenances/monthly`
- `/report/maintenances/year`

Export:

- PDF untuk seluruh laporan harian, bulanan, dan maintenance
- Excel untuk laporan maintenance tahunan dan modul record

## Command Aplikasi

Command internal yang tersedia:

```bash
# Sinkronisasi permission & role template dari config
php artisan permission:update

# Telegram
php artisan telegram:dispatch-reminders     # kirim reminder harian (dijadwalkan 08:00)
php artisan telegram:poll-updates            # terima update bot via long-polling
php artisan telegram:test-notification       # uji kirim notifikasi
```

Scheduler `telegram:dispatch-reminders` berjalan harian pukul 08:00. Pastikan `php artisan schedule:work` atau cron sistem aktif. Jalankan `telegram:poll-updates` sebagai proses long-running untuk menerima update bot.

## Testing dan Quality

Menjalankan test:

```bash
php artisan test --compact
php artisan test --compact --filter=NamaTest
```

Menjalankan formatter:

```bash
vendor/bin/pint --dirty
```

Build asset production:

```bash
npm run build
```

## Deployment

Pipeline GitLab menggunakan branch-based deployment:

- `development` -> server development
- `staging` -> server staging
- `main` -> server production

Deploy dijalankan melalui `.gitlab-ci.yml` dengan SSH dan remote script `deploy-tta-vemo-2-spm.sh`.

## Catatan

- Jika perubahan frontend tidak terlihat, jalankan `npm run dev` atau `npm run build`.
- Queue listener dan scheduler perlu aktif agar proses berbasis queue dan reminder berjalan normal.
- Kredensial sensitif pada `.env` harus disesuaikan per environment dan tidak dipakai sebagai referensi dokumentasi publik.
```
