# TTA Starter Kit - Aplikasi Internal Tongtji

Starter kit untuk aplikasi internal tongtji menggunakan Laravel dengan template admin Sneat, integrasi LDAP, dan sistem Role-Based Access Control (RBAC) lengkap dengan antarmuka manajemen web.

## 🚀 Cara Install

### 1. Persiapan Awal
```bash
# goto project ini
cd tta-starter-sneat

# Install dependencies PHP
composer install

# Install dependencies JavaScript
npm install
```

### 2. Konfigurasi Environment
```bash
# Copy file .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Setup Database
Pastikan database MySQL sudah dibuat dengan nama `tta_starter_sneat`, lalu:
```bash
# Jalankan migrasi database
php artisan migrate

# Seed roles dan permissions default
php artisan db:seed --class=RolePermissionSeeder
```

## 🏃‍♂️ Cara Menjalankan

### Development Mode (Recommended)
```bash
# Jalankan semua service sekaligus (server, queue, vite)
composer run dev
```

### Manual (Jika ingin jalankan terpisah)
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Queue worker
php artisan queue:listen --tries=1

# Terminal 3 - Vite dev server
npm run dev
```

Buka browser ke: `http://localhost:8000`

## 👥 LDAP Integration

### Import User dari LDAP
```bash
# Import user tertentu
php artisan ldap:import users --filter="(uid=stagingpupose)"

# Import semua user (hati-hati!)
php artisan ldap:import users
```

### Login
- Username: gunakan UID dari LDAP
- Password: password LDAP Anda

## 🧪 Testing

```bash
# Jalankan test
composer run test

# Format code (jika ada)
php artisan pint
```

## ✨ Fitur Utama

### 👥 User Management
- **CRUD Lengkap**: Create, Read, Update, Delete user dengan validasi
- **Soft Delete**: User dihapus secara soft dengan kemampuan restore
- **Audit Trail**: Tracking lengkap created_by, updated_by, deleted_by
- **Activity Logging**: Log semua aktivitas user dengan IP tracking
- **DataTables**: Tampilan data dengan export, search, dan pagination

### 🔐 RBAC System
- **5 Role Default**: Super Admin, Admin, User Manager, Viewer, User
- **19 Permission**: Dikelompokkan berdasarkan modul (users, roles, permissions, etc)
- **Web Interface**: UI lengkap untuk manajemen role dan assignment user
- **Permission Middleware**: Route protection otomatis
- **Real-time Assignment**: AJAX operations untuk role management

### 📊 Audit & Logging
- **User Activity Logs**: Semua aktivitas user tercatat dengan detail
- **Change Tracking**: Old/new values disimpan dalam JSON
- **IP & User Agent**: Tracking lokasi dan device access
- **Export Reports**: Export audit logs ke CSV, Excel, PDF

### 🎨 UI/UX Features
- **Responsive Design**: Bootstrap-based dengan mobile support
- **DataTables**: Advanced table dengan export dan filter
- **AJAX Operations**: Update data tanpa refresh halaman
- **Toast Notifications**: Feedback visual untuk user actions
- **Permission-based UI**: Menu dan tombol muncul sesuai permission

## 📁 Struktur Project

### Controllers Utama
- `UserController` - User management dengan audit trail
- `RoleController` - Role management dengan permission assignment
- `UserRoleController` - User role assignment interface
- `HomeController` - Dashboard utama

### Models Utama
- `User` - Model user dengan LDAP dan RBAC integration
- `Role` - Model role dengan permission relationships
- `Permission` - Model permission dengan module grouping
- `UserLog` - Model untuk activity logging

### Views Structure
- `resources/views/users/` - User management views
- `resources/views/roles/` - Role management views
- `resources/views/layouts/` - Layout templates
- `public/sneat/` - Asset template Sneat

## ⚙️ Konfigurasi LDAP

Ubah setting di file `.env`:
```env
LDAP_HOST=19.38.40.5
LDAP_USERNAME="cn=admin,dc=tongtji,dc=com"
LDAP_PASSWORD="your-password"
LDAP_BASE_DN="dc=tongtji,dc=com"
```

## 🎨 Template

Menggunakan **Sneat Bootstrap Admin Template** untuk tampilan yang modern dan responsive.

## 🔐 Role-Based Access Control (RBAC)

Aplikasi ini menggunakan sistem RBAC lengkap dengan antarmuka manajemen web untuk mengatur akses pengguna.

### 🎛️ Fitur RBAC Management UI
- **Manajemen Role**: `/roles` - CRUD role dengan assignment permission
- **Assignment User Role**: `/users/{user}/roles` - Interface lengkap untuk mengelola role user
- **Permission Grouping**: Permission dikelompokkan berdasarkan modul
- **Real-time Operations**: AJAX untuk add/remove role tanpa refresh halaman
- **Activity Logging**: Semua perubahan role/permission tercatat di audit log

### 👤 User Testing
- **Username**: `stagingpupose` (Super Administrator)
- **Akses**: Semua permission untuk testing fitur RBAC
- **Login**: Gunakan kredensial LDAP atau username 'stagingpupose'

### Role Default
- **Super Administrator**: Akses penuh ke seluruh sistem
- **Administrator**: Akses ke manajemen user dan sebagian besar fitur
- **User Manager**: Dapat mengelola user dengan akses sistem terbatas
- **Viewer**: Akses read-only ke sebagian besar fitur
- **User**: User biasa dengan akses terbatas

### Permission Default
**User Management:**
- `users.view` - Melihat daftar user
- `users.create` - Membuat user baru
- `users.edit` - Mengedit user
- `users.delete` - Menghapus user (soft delete)
- `users.restore` - Memulihkan user yang dihapus
- `users.force_delete` - Menghapus permanen
- `users.logs` - Melihat activity logs

**Role Management:**
- `roles.view` - Melihat daftar role
- `roles.create` - Membuat role baru
- `roles.edit` - Mengedit role
- `roles.delete` - Menghapus role

**Permission Management:**
- `permissions.view` - Melihat daftar permission
- `permissions.create` - Membuat permission baru
- `permissions.edit` - Mengedit permission
- `permissions.delete` - Menghapus permission

**System:**
- `dashboard.view` - Akses dashboard
- `reports.view` - Melihat reports
- `settings.view` - Melihat pengaturan
- `settings.edit` - Mengedit pengaturan

### Cara Menambah Menu Baru dengan RBAC

Ketika menambah menu/fitur baru, ikuti langkah ini:

#### 1. Buat Permission Baru
```bash
# Tambahkan di RolePermissionSeeder.php
['name' => 'orders.view', 'display_name' => 'View Orders', 'description' => 'Can view orders', 'module' => 'orders'],
['name' => 'orders.create', 'display_name' => 'Create Orders', 'description' => 'Can create orders', 'module' => 'orders'],
['name' => 'orders.edit', 'display_name' => 'Edit Orders', 'description' => 'Can edit orders', 'module' => 'orders'],
['name' => 'orders.delete', 'display_name' => 'Delete Orders', 'description' => 'Can delete orders', 'module' => 'orders'],
```

#### 2. Update Routes dengan Middleware
```php
// Di routes/web.php
Route::middleware(['permission:orders.view'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['permission:orders.create'])->group(function () {
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});
```

#### 3. Update Views dengan Permission Check
```blade
{{-- Di view --}}
@if(auth()->user()->hasPermission('orders.create'))
    <a href="{{ route('orders.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Tambah Order
    </a>
@endif
```

#### 4. Update Controller Actions
```php
// Di controller DataTables
->addColumn('action', function ($order) {
    $actions = '<div class="dropdown">...';
    
    if (auth()->user()->hasPermission('orders.edit')) {
        $actions .= '<a href="' . route('orders.edit', $order->id) . '">Edit</a>';
    }
    
    if (auth()->user()->hasPermission('orders.delete')) {
        $actions .= '<form>...Delete</form>';
    }
    
    return $actions;
})
```

#### 5. Assign Permission ke Role
```bash
# Jalankan seeder lagi atau manual assign
php artisan db:seed --class=RolePermissionSeeder

# Atau via code:
$role = Role::where('name', 'admin')->first();
$permission = Permission::where('name', 'orders.view')->first();
$role->assignPermission($permission);
```

### 🖥️ Antarmuka Web RBAC

#### Manajemen Role
1. **Akses**: `/roles` - Daftar semua role
2. **Tambah Role**: `/roles/create` - Form pembuatan role dengan selection permission
3. **Edit Role**: `/roles/{role}/edit` - Edit role dan assignment permission
4. **Detail Role**: `/roles/{role}` - Lihat detail role dan daftar permission

#### Manajemen User Role
1. **Akses**: `/users/{user}/roles` - Interface lengkap manajemen role user
2. **Multiple Methods**: 
   - Checkbox bulk assignment
   - Quick add dropdown
   - One-click remove dengan konfirmasi
3. **Real-time Updates**: AJAX operations tanpa refresh halaman
4. **Visual Feedback**: Role badges, notifikasi success/error

#### Programmatic Management
```bash
# Assign role ke user
$user = User::find(1);
$user->assignRole('admin');

# Check permission
if ($user->hasPermission('users.create')) {
    // User dapat membuat user baru
}

# Check role
if ($user->hasRole('admin')) {
    // User adalah admin
}
```

## 🚀 Quick Access URLs

Setelah aplikasi berjalan di `http://localhost:8000`:

### 🔐 Authentication
- **Login**: `/login` - Halaman login dengan LDAP
- **Dashboard**: `/home` - Dashboard utama setelah login

### 👥 User Management  
- **Daftar User**: `/users` - Kelola semua user
- **Tambah User**: `/users/create` - Buat user baru
- **User Logs**: `/users/logs` - Activity logs global
- **Trash**: `/users/trash` - User yang dihapus (restore)

### 🛡️ RBAC Management
- **Daftar Role**: `/roles` - Kelola role dan permission
- **Tambah Role**: `/roles/create` - Buat role baru
- **User Role**: `/users/{id}/roles` - Assign role ke user tertentu

### 📊 Features  
- **Export Data**: Tersedia di semua tabel (CSV, Excel, PDF)
- **Search & Filter**: Advanced filtering di semua DataTables
- **Responsive**: Akses optimal di desktop dan mobile

## 🧪 Testing RBAC

1. **Login** dengan user `stagingpupose` (Super Administrator)
2. **Akses** `/roles` untuk manajemen role
3. **Test** assignment role di `/users/{id}/roles`
4. **Verifikasi** permission dengan user role berbeda
5. **Cek** activity logs di `/users/logs`

## 📞 Support

Jika ada masalah, hubungi tim IT Tongtji.