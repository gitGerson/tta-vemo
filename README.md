# VEMO

VEMO adalah aplikasi monitoring operasional kendaraan berbasis Laravel 12 untuk kebutuhan internal Tong Tji. Aplikasi ini mencakup autentikasi LDAP, pengelolaan master data armada, pencatatan perjalanan kendaraan, workflow maintenance, laporan operasional, upload gambar, dan manajemen akses berbasis role-permission.

## Ringkasan Fitur

- Login menggunakan akun LDAP.
- Dashboard operasional dengan notifikasi service dan maintenance draft.
- Master data untuk company, driver, vehicle, destination, category, product, dan type maintenance.
- Modul `record` untuk pencatatan aktivitas/perjalanan kendaraan.
- Modul `maintenances` untuk pengajuan, approval, konfirmasi, dan detail biaya maintenance.
- Laporan harian, bulanan, maintenance harian, dan maintenance tahunan.
- Export laporan ke PDF dan Excel.
- Upload dan manajemen gambar.
- RBAC untuk user, role template, permission assignment, dan activity log.
- Soft delete pada beberapa modul penting.

## Tech Stack

- PHP 8.4
- Laravel 12
- MySQL
- Laravel UI
- Bootstrap 5
- Tailwind CSS v4
- Vite
- LDAPRecord Laravel
- Yajra DataTables
- Maatwebsite Excel
- DomPDF
- AWS S3 compatible storage

## Modul Utama

### Authentication

- Login dari route `/login`.
- Kredensial menggunakan `username` dan `password` LDAP.
- Root URL `/` akan diarahkan ke halaman login.

### Dashboard

- Route utama setelah login: `/home`.
- Menampilkan ringkasan driver, vehicle aktif, notifikasi service, dan maintenance berstatus `DRAFT`.

### Management

- User management.
- Role template management.
- Assignment role dan permission ke user.
- User activity logs.

### Master Data

- Categories
- Products
- Companies
- Drivers
- Vehicles
- Destinations
- Type Maintenances

### Operational

- `record`: pencatatan operasional kendaraan dan export Excel.
- `maintenances`: request, detail item maintenance, approval, confirmation, dan tracking total biaya.
- `note`: catatan tambahan.
- `search`: pencarian data global.
- `mobile`: endpoint tampilan mobile.

### Reports

- Daily report
- Monthly report
- Daily maintenance report
- Yearly maintenance report
- Export PDF untuk seluruh report
- Export Excel untuk yearly maintenance report

### Media

- Upload file menggunakan FilePond.
- Image library dengan restore dan force delete.

## Struktur Penting

```text
app/
  Console/Commands/           # Artisan command aplikasi
  Exports/                    # Export Excel
  Http/Controllers/           # Controller modul aplikasi
  Http/Middleware/            # Middleware permission
  Imports/                    # Import Excel
  Models/                     # Model Eloquent
  Observers/                  # Audit / CRUD observer
database/
  seeders/                    # Seeder roles, permissions, dan master data
resources/views/              # Blade views
routes/web.php                # Web routes utama
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
- queue connection bila berbeda dari default

### 3. Siapkan database

Buat database, lalu jalankan:

```bash
php artisan migrate
php artisan db:seed
```

Seeder utama akan menyiapkan:

- role dan permission
- company
- driver
- vehicle
- destination
- type maintenance
- user awal `stagingpurpose` jika belum ada

### 4. Jalankan aplikasi

Mode development yang direkomendasikan:

```bash
composer run dev
```

Command ini menjalankan:

- Laravel development server
- queue listener
- Vite dev server

Alternatif manual:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## Storage dan Upload

Project ini menggunakan `FILESYSTEM_DISK=s3` pada konfigurasi contoh. Pastikan endpoint, bucket, key, dan secret sudah sesuai dengan environment yang dipakai.

Route upload yang tersedia:

- `POST /uploads/process`
- `DELETE /uploads/revert`
- `GET /uploads/load`

## Permission Model

Permission dibagi per area:

- `management.*`
- `images.*`
- `master.*`
- `record.*`
- `maintenances.*`
- `reports.*`

Seeder `RolePermissionSeeder` juga menyiapkan template role untuk kebutuhan assignment user.

## Reporting dan Export

Laporan yang tersedia:

- `/report/daily`
- `/report/monthly`
- `/report/maintenances/daily`
- `/report/maintenances/year`

Export:

- PDF untuk report harian, bulanan, dan maintenance
- Excel untuk maintenance tahunan
- Excel untuk modul record

## Command Aplikasi

Tersedia command internal:

```bash
php artisan recalculate:maintenances
```

Gunakan untuk proses perhitungan ulang maintenance bila diperlukan sesuai implementasi command aplikasi.

## Testing dan Quality

Menjalankan test:

```bash
composer run test
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
- Queue listener perlu aktif agar proses berbasis queue berjalan normal.
- Kredensial sensitif pada `.env` harus disesuaikan per environment dan tidak dipakai sebagai referensi dokumentasi publik.
