# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 starter kit for Tongtji inhouse applications. Uses the Sneat Bootstrap admin template, OpenLDAP authentication, and a custom RBAC system. The project name is **vemo** (vehicle monitoring).

## Development Commands

```bash
# Start all services (Laravel, queue worker, Vite)
composer run dev

# Run tests
php artisan test --compact
php artisan test --compact --filter=testName

# Code formatting (run after editing PHP files)
vendor/bin/pint --dirty --format agent

# Sync permissions, role templates, and staging superadmin from config
php artisan db:seed --class=PermissionSeeder

# Clear caches
php artisan route:clear && php artisan config:clear && php artisan view:clear
```

## Architecture

### Authentication

LDAP auth via `directorytree/ldaprecord-laravel`. The `User` model implements `LdapAuthenticatable` with `AuthenticatesWithLdap` and `HasLdapUser` traits. LDAP attributes sync to DB: `cn→name`, `uid→username`, `mail→email`. Post-login redirects to `/home`.

For development without LDAP, a local superadmin is seeded: **username** `stagingpurpose`, **password** `P@ssw0rd1938`.

### RBAC

Custom implementation — no Spatie package.

- **`config/permission.php`** is the single source of truth for all permissions and role templates.
- **`PermissionSyncService`** reads this config and upserts `Permission` and `Role` records. It also prunes permissions removed from config. Run via `db:seed --class=PermissionSeeder`.
- **`CheckPermission` middleware** is registered as `permission` alias in `bootstrap/app.php`.
- Routes are protected with `permission:module.resource.action` syntax (e.g. `permission:master.drivers.view`).
- Users have direct permissions (not just roles). The `users.edit` page has a permission editor using role templates as presets.

**Current permission groups:**
- `management.*` — users, roles management
- `master.*` — master data (drivers, vehicles, companies, destinations, type-maintenances)

**Role templates** (defined in `config/permission.php` under `role_templates`): `super_admin_template`, `admin_template`, `user_manager_template`, `viewer_template`.

**Adding permissions for a new module:**
1. Add entries to `config/permission.php` under `permissions` and optionally `role_templates`
2. Run `php artisan db:seed --class=PermissionSeeder`
3. Protect routes with `permission:module.feature.action`
4. Add menu entry to `config/menu.php`

### Audit Trail

`app/Models/Concerns/Auditable` — a trait that auto-populates `created_by`, `updated_by`, `deleted_by` on models, and writes `UserLog` entries on every create/update/delete/restore via Eloquent model events. Add `use Auditable` to any model whose table has those columns.

### Obfuscated Route IDs

`app/Models/Concerns/HasDiffrntHashId` — add to models to expose encoded IDs in URLs instead of raw integers. The `DiffrntHashId` service handles encode/decode. The `User` model uses this trait; route model binding resolves the hash automatically.

### Menu System

`config/menu.php` defines all sidebar and search-palette entries. `MenuService` filters by user permission and context (`desktop` / `search`) and resolves route URLs at runtime. Add a new menu item by appending to `config/menu.items`.

### DataTables

Server-side via `yajra/laravel-datatables-oracle`. Three reusable Blade components wrap the boilerplate:

- `<x-datatable.card title="...">` — card wrapper with export buttons
- `<x-datatable.table>` — the `<table>` element
- `<x-datatable.scripts>` — JS init block

Action column views live in `resources/views/datatable/actions/`. There are `drivers.blade.php` and `drivers-trash.blade.php` as reference examples.

### File Uploads (FilePond)

`FileUploadController` + `FilePondUploadService` handle generic chunked uploads. Routes: `POST /uploads/process`, `DELETE /uploads/revert`, `GET /uploads/load`. Driver images are stored in `public/profile_images/`.

### Form Blade Components

Reusable form field components in `resources/views/components/form/`: `input`, `select`, `date`, `textarea`, `checkbox`, `switch`, `number`, `file`, `rich-editor`, `group`, `error`, `text`. See `resources/views/form-demo/index.blade.php` for usage examples.

### External HR API

`DriverController` validates NIK against an internal HR payroll API (`http://19.38.40.20/jpayroll/...`). `getEmpInfo` is the lookup endpoint; `validateNikWithApi` is the internal guard used on store/update. This API is only reachable on the internal network.

## Module Pattern

The Driver module is the canonical example of a full module:

| Layer | Location |
|---|---|
| Model | `app/Models/Driver.php` |
| Controller | `app/Http/Controllers/DriverController.php` |
| Form Requests | `app/Http/Requests/DriverStoreRequest.php`, `DriverUpdateRequest.php` |
| Factory | `database/factories/DriverFactory.php` |
| Seeder | `database/seeders/DriverSeeder.php` |
| Views | `resources/views/drivers/` (index, create, edit, show, trash, partials/form) |
| Datatable actions | `resources/views/datatable/actions/drivers.blade.php`, `drivers-trash.blade.php` |
| Tests | `tests/Feature/DriverModuleTest.php` |

Key driver model patterns to follow:
- Validation rules defined as static methods on the model (`validationRules`, `validationMessages`)
- `casts()` method rather than `$casts` property
- `imageUrl()` helper method for asset URLs
- SoftDeletes: `destroy` sets status to `PASSIVE` before deleting; `restore` reverts

## Route Organization

All authenticated routes are wrapped in `Route::middleware(['auth'])`. Inside, permission groups use separate `Route::middleware(['permission:...'])` blocks per action (view, create, edit, delete, restore). See `routes/web.php` for the established pattern.

## Key Config Files

- `config/permission.php` — all permission definitions, role templates, staging superadmin credentials
- `config/menu.php` — sidebar navigation and search palette entries
