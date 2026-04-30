# Panduan Implementasi `DiffrntHashId` pada Laravel

## 1. Tujuan

`DiffrntHashId` digunakan untuk menyamarkan ID asli dari database agar tidak langsung terlihat pada URL, API response, atau parameter frontend.

Contoh tanpa hashid:

```txt
/users/15
/assets/120
```

Contoh dengan hashid:

```txt
/users/k9Pz2
/assets/mB8xQ
```

Konsep ini bukan enkripsi penuh, tetapi **ID obfuscation**. Tujuannya adalah membuat ID tidak mudah ditebak, bukan menggantikan authorization, policy, atau validasi akses.

---

## 2. File yang Terlibat

Berdasarkan struktur implementasi saat ini, ada tiga file utama:

| File | Fungsi |
|---|---|
| `app/Services/DiffrntHashId.php` | Service utama untuk encode dan decode ID |
| `app/Providers/AppServiceProvider.php` | Mendaftarkan `DiffrntHashId` sebagai singleton service |
| `app/Helpers/CommonHelper.php` | Menyediakan helper global `diffrntEncode()` dan `diffrntDecode()` |

---

## 3. Alur Kerja

```txt
ID asli dari database
        ↓
diffrntEncode($id)
        ↓
Hashid tampil di URL/API/frontend
        ↓
Request masuk membawa hashid
        ↓
diffrntDecode($hashid)
        ↓
ID asli digunakan untuk query database
```

Contoh:

```php
$hashid = diffrntEncode(15); // contoh hasil: "k9Pz2"
$id = diffrntDecode($hashid); // hasil: 15
```

---

## 4. Implementasi Service `DiffrntHashId`

File:

```txt
app/Services/DiffrntHashId.php
```

Contoh implementasi yang direkomendasikan:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Vinkla\Hashids\Facades\Hashids;

class DiffrntHashId
{
    /**
     * Cache sementara selama request berjalan.
     */
    protected array $memoryCache = [];

    /**
     * Durasi cache dalam hari.
     */
    protected int $cacheExpiry = 7;

    /**
     * Encode ID menjadi hashid.
     */
    public function encode(int|string $id, ?string $namespace = null): string
    {
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            return '';
        }

        $key = $this->makeKey($id, $namespace);

        if (isset($this->memoryCache[$key])) {
            return $this->memoryCache[$key];
        }

        $hash = Cache::remember(
            $key,
            now()->addDays($this->cacheExpiry),
            fn () => Hashids::encode((int) $id)
        );

        return $this->memoryCache[$key] = $hash;
    }

    /**
     * Decode hashid menjadi ID asli.
     */
    public function decode(?string $hashid): ?int
    {
        if (!is_string($hashid) || trim($hashid) === '') {
            return null;
        }

        $decoded = Hashids::decode($hashid);

        return $decoded[0] ?? null;
    }

    /**
     * Membuat cache key.
     */
    protected function makeKey(int|string $id, ?string $namespace = null): string
    {
        return $namespace
            ? "hashid:{$namespace}:{$id}"
            : "hashid:{$id}";
    }
}
```

### Catatan Penting

Pada kode awal, validasi berikut kurang aman:

```php
if (!is_int($id) && !is_string($id) && !is_numeric($id)) {
    return '';
}
```

Karena string non-numeric seperti `"abc"` tetap bisa lolos akibat `is_string($id)` bernilai `true`.

Gunakan validasi yang lebih ketat:

```php
if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
    return '';
}
```

---

## 5. Register Service di `AppServiceProvider`

File:

```txt
app/Providers/AppServiceProvider.php
```

Tambahkan import:

```php
use App\Services\DiffrntHashId;
```

Lalu daftarkan pada method `register()`:

```php
public function register(): void
{
    $this->app->singleton(DiffrntHashId::class);
}
```

Dengan `singleton`, Laravel hanya membuat satu instance `DiffrntHashId` selama lifecycle request. Ini berguna karena service memiliki `memoryCache`.

---

## 6. Buat Helper Global

File:

```txt
app/Helpers/CommonHelper.php
```

Tambahkan import:

```php
use App\Services\DiffrntHashId;
```

Tambahkan helper:

```php
if (!function_exists('diffrntEncode')) {
    function diffrntEncode(int|string $id, ?string $namespace = null): string
    {
        return app(DiffrntHashId::class)->encode($id, $namespace);
    }
}

if (!function_exists('diffrntDecode')) {
    function diffrntDecode(?string $hashid): ?int
    {
        return app(DiffrntHashId::class)->decode($hashid);
    }
}
```

---

## 7. Pastikan Helper Ter-load oleh Composer

Jika `CommonHelper.php` belum otomatis ter-load, tambahkan ke `composer.json`:

```json
{
    "autoload": {
        "files": [
            "app/Helpers/CommonHelper.php"
        ]
    }
}
```

Setelah itu jalankan:

```bash
composer dump-autoload
```

Jika file helper sudah pernah dipakai sebelumnya, kemungkinan langkah ini sudah dilakukan.

---

## 8. Konfigurasi Hashids

Pastikan package Hashids sudah tersedia di project dan konfigurasi salt sudah benar.

Contoh konfigurasi umum pada `.env`:

```env
HASHIDS_SALT=your-unique-secret-salt
HASHIDS_LENGTH=8
```

Gunakan nilai salt yang stabil. Jangan ganti salt sembarangan pada production karena hashid lama bisa tidak bisa di-decode lagi.

### Rekomendasi

- Gunakan salt berbeda antara development, staging, dan production.
- Jangan commit salt production ke repository.
- Simpan salt di `.env`.
- Jika hashid sudah dipakai di URL publik, perubahan salt akan berdampak pada backward compatibility.

---

## 9. Contoh Penggunaan di Blade

Misalnya ingin membuat link detail user.

Tanpa hashid:

```blade
<a href="{{ route('users.show', $user->id) }}">
    Detail
</a>
```

Dengan hashid:

```blade
<a href="{{ route('users.show', diffrntEncode($user->id, 'users')) }}">
    Detail
</a>
```

---

## 10. Contoh Penggunaan di Route

```php
use App\Http\Controllers\UserController;

Route::get('/users/{hashid}', [UserController::class, 'show'])
    ->name('users.show');
```

Gunakan nama parameter `hashid` agar jelas bahwa parameter tersebut bukan ID asli.

---

## 11. Contoh Penggunaan di Controller

```php
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $hashid)
    {
        $id = diffrntDecode($hashid);

        abort_if(!$id, 404);

        $user = User::findOrFail($id);

        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }
}
```

Poin penting:

1. Decode hashid terlebih dahulu.
2. Jika decode gagal, return `404`.
3. Query model berdasarkan ID asli.
4. Tetap jalankan policy/authorization.

---

## 12. Contoh Penggunaan di API Resource

Misalnya pada `UserResource`:

```php
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => diffrntEncode($this->id, 'users'),
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

Dengan begitu API tidak mengembalikan ID asli database.

---

## 13. Contoh Penggunaan Saat Menerima Request API

Misalnya frontend mengirim `user_id` dalam bentuk hashid:

```json
{
    "user_id": "k9Pz2"
}
```

Controller:

```php
public function update(Request $request)
{
    $userId = diffrntDecode($request->input('user_id'));

    abort_if(!$userId, 422, 'User ID tidak valid.');

    $user = User::findOrFail($userId);

    $this->authorize('update', $user);

    // lanjut proses update
}
```

---

## 14. Contoh Accessor pada Model

Agar tidak selalu menulis `diffrntEncode($model->id)`, bisa tambahkan accessor pada model.

Contoh pada `User` model:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    protected function hashid(): Attribute
    {
        return Attribute::make(
            get: fn () => diffrntEncode($this->id, 'users')
        );
    }
}
```

Penggunaan:

```blade
{{ $user->hashid }}
```

Atau pada resource:

```php
'id' => $this->hashid,
```

---

## 15. Contoh Implementasi pada Modul Asset

### Route

```php
Route::get('/assets/{hashid}', [AssetController::class, 'show'])
    ->name('assets.show');
```

### Blade

```blade
<a href="{{ route('assets.show', diffrntEncode($asset->id, 'assets')) }}">
    Detail Asset
</a>
```

### Controller

```php
use App\Models\Asset;

class AssetController extends Controller
{
    public function show(string $hashid)
    {
        $assetId = diffrntDecode($hashid);

        abort_if(!$assetId, 404);

        $asset = Asset::findOrFail($assetId);

        $this->authorize('view', $asset);

        return view('assets.show', compact('asset'));
    }
}
```

---

## 16. Peran `namespace`

Contoh:

```php
diffrntEncode(15, 'users');
diffrntEncode(15, 'assets');
```

Pada implementasi saat ini, `namespace` hanya digunakan untuk membedakan cache key:

```txt
hashid:users:15
hashid:assets:15
```

Namun hasil hashid-nya tetap bisa sama karena proses encode tetap memakai ID yang sama:

```php
Hashids::encode((int) $id)
```

Artinya, `namespace` saat ini berguna untuk cache separation, bukan untuk menghasilkan hash berbeda per modul.

Jika ingin hash berbeda untuk setiap modul, perlu pendekatan tambahan, misalnya:

1. menggunakan koneksi Hashids berbeda per modul,
2. menggunakan salt berbeda per modul,
3. atau menambahkan mekanisme prefix yang terkontrol.

Untuk implementasi awal, cukup gunakan namespace sebagai pembeda cache dan konteks pemakaian.

---

## 17. Hal yang Tidak Boleh Dilakukan

Jangan menganggap hashid sebagai pengganti authorization.

Contoh yang salah:

```php
public function show(string $hashid)
{
    $id = diffrntDecode($hashid);

    return User::findOrFail($id);
}
```

Masalahnya: jika user berhasil mendapatkan hashid milik user lain, data tetap bisa terbuka.

Contoh yang benar:

```php
public function show(string $hashid)
{
    $id = diffrntDecode($hashid);

    abort_if(!$id, 404);

    $user = User::findOrFail($id);

    $this->authorize('view', $user);

    return $user;
}
```

---

## 18. Checklist Implementasi

| No | Checklist | Status |
|---|---|---|
| 1 | Package Hashids tersedia di project | ☐ |
| 2 | `DiffrntHashId.php` dibuat di `app/Services` | ☐ |
| 3 | `DiffrntHashId` diregister sebagai singleton di `AppServiceProvider` | ☐ |
| 4 | Helper `diffrntEncode()` dibuat | ☐ |
| 5 | Helper `diffrntDecode()` dibuat | ☐ |
| 6 | `CommonHelper.php` terdaftar di autoload Composer | ☐ |
| 7 | `.env` memiliki salt Hashids | ☐ |
| 8 | Route memakai `{hashid}`, bukan `{id}` | ☐ |
| 9 | Controller melakukan decode sebelum query | ☐ |
| 10 | Decode gagal menghasilkan `404` atau `422` | ☐ |
| 11 | Policy/authorization tetap dijalankan | ☐ |
| 12 | API response tidak menampilkan ID asli jika memang perlu disembunyikan | ☐ |
| 13 | Testing encode/decode sudah dibuat | ☐ |

---

## 19. Contoh Unit Test

```php
use App\Services\DiffrntHashId;

it('can encode and decode id', function () {
    $service = app(DiffrntHashId::class);

    $hashid = $service->encode(15, 'users');

    expect($hashid)->toBeString();
    expect($hashid)->not->toBe('15');
    expect($service->decode($hashid))->toBe(15);
});
```

Contoh test input invalid:

```php
it('returns empty string for invalid encode input', function () {
    $service = app(DiffrntHashId::class);

    expect($service->encode('abc'))->toBe('');
});
```

---

## 20. Best Practice

1. Gunakan hashid untuk data yang tampil ke user melalui URL atau API.
2. Tetap gunakan ID asli di internal database dan relasi Eloquent.
3. Decode hashid hanya di boundary aplikasi, seperti controller, request handler, atau service input.
4. Jangan menyimpan hashid sebagai primary key di database.
5. Jangan mengganti salt production tanpa rencana migrasi/backward compatibility.
6. Tetap gunakan policy, gate, atau permission check.
7. Jangan expose ID asli jika standar project sudah memakai hashid.
8. Gunakan nama parameter `{hashid}` agar tidak membingungkan developer lain.

---

## 21. Rekomendasi Pola Implementasi Final

Pola yang paling aman dan rapi:

```txt
Blade / Frontend / API Resource
        ↓
diffrntEncode($model->id, 'module_name')
        ↓
URL atau payload berisi hashid
        ↓
Controller menerima {hashid}
        ↓
diffrntDecode($hashid)
        ↓
Model::findOrFail($id)
        ↓
Policy / authorization check
        ↓
Return response
```

Contoh final:

```php
public function show(string $hashid)
{
    $id = diffrntDecode($hashid);

    abort_if(!$id, 404);

    $data = Model::findOrFail($id);

    $this->authorize('view', $data);

    return view('module.show', compact('data'));
}
```

---

## 22. Kesimpulan

`DiffrntHashId` adalah service pembungkus Hashids yang bertugas mengubah ID asli menjadi hashid dan mengembalikan hashid menjadi ID asli saat diperlukan.

Implementasi ini cocok digunakan untuk:

- menyamarkan ID di URL,
- menyamarkan ID pada API response,
- membuat parameter frontend lebih aman dari tebakan sederhana,
- menjaga konsistensi penggunaan hashid di seluruh project.

Namun, hashid tetap harus diperlakukan sebagai lapisan presentasi, bukan lapisan keamanan utama. Keamanan akses tetap harus mengandalkan policy, gate, permission, dan validasi ownership data.
