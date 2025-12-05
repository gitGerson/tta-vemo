# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 starter kit for Tongtji inhouse applications using the Sneat admin template with LDAP authentication integration and comprehensive Role-Based Access Control (RBAC). The project combines Laravel's robust backend framework with a modern Bootstrap-based admin interface, OpenLDAP user authentication, comprehensive audit trail system, user activity logging, and a fully-featured RBAC management system with web-based UI.

## Development Commands

### Initial Setup
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies  
npm install

# Copy and configure environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder
```

### Development Server
```bash
# Start all development services (Laravel server, queue worker, and Vite)
composer run dev

# Or run components separately:
php artisan serve              # Laravel development server
php artisan queue:listen --tries=1  # Queue worker
npm run dev                    # Vite development server
```

### Build and Test
```bash
# Build assets for production
npm run build

# Run tests
composer run test

# Code formatting
php artisan pint

# Clear caches during development
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

## Architecture

### LDAP Authentication System
- **Provider**: Uses `LdapRecord\Models\OpenLDAP\User` as LDAP provider in `config/auth.php:74-93`
- **User Model**: `App\Models\User` implements `LdapAuthenticatable` with traits `AuthenticatesWithLdap` and `HasLdapUser`
- **Attribute Mapping**: LDAP attributes are synced to database columns:
  - `cn` → `name`
  - `uid` → `username` 
  - `mail` → `email`
  - `guid` → `guid`
  - `domain` → `domain`
- **Migration**: LDAP-specific columns added via `2025_01_01_000000_add_ldap_columns_to_users_table.php`

### Frontend Integration
- **Template**: Sneat Bootstrap admin template integrated in `public/sneat/`
- **Build System**: Vite with Laravel plugin, builds from `resources/sass/app.scss` and `resources/js/app.js`
- **Layouts**: Multiple Blade layouts in `resources/views/layouts/` including `sneat.blade.php` for admin interface

### User Management System
- **Controller**: `UserController` provides complete CRUD operations with audit trail and logging
- **Routes**: User management routes protected by `auth` and permission middleware in `routes/web.php`
- **DataTables**: Server-side processing via `yajra/laravel-datatables-oracle` package
- **Soft Deletes**: Users are soft deleted with restore functionality
- **Audit Trail**: Comprehensive tracking of created_by, updated_by, deleted_by with timestamps
- **Activity Logging**: Complete user action logging in `user_logs` table with IP tracking
- **Role Management**: Web-based interface for assigning/removing user roles with AJAX support

### Audit Trail Architecture
- **User Model**: Enhanced with SoftDeletes trait and audit relationships (creator, updater, deleter)
- **Boot Events**: Automatic tracking of user actions in model boot method
- **Foreign Keys**: Self-referencing relationships for audit trail fields
- **UserLog Model**: Dedicated logging system for all user activities with JSON change tracking

### User Activity Logging
- **Actions Tracked**: CREATE_USER, UPDATE_USER, DELETE_USER, RESTORE_USER, FORCE_DELETE_USER, VIEW_USER
- **Data Captured**: User ID, target user, action type, description, old/new values, IP address, user agent
- **Log Views**: Global activity logs and user-specific activity history
- **Export Support**: CSV, Excel, PDF export capabilities for audit reports

### Role-Based Access Control (RBAC) System
- **Custom Implementation**: Built from scratch with Laravel relationships
- **Models**: `Role`, `Permission`, many-to-many relationships with Users
- **Middleware**: `CheckPermission` middleware for route protection (registered as 'permission' alias in `bootstrap/app.php:15`)
- **5 Default Roles**: super_admin, admin, user_manager, viewer, user
- **19 Default Permissions**: Grouped by modules (users, roles, permissions, dashboard, reports, settings)
- **Permission Checking**: `hasRole()` and `hasPermission()` methods on User model
- **Web UI**: Complete role management interface with CRUD operations
- **User Role Assignment**: Web-based interface for managing user roles with AJAX
- **Activity Logging**: All role/permission changes are logged in UserLog

### Key Dependencies
- **LDAP**: `directorytree/ldaprecord-laravel` for LDAP integration
- **UI**: `laravel/ui` for authentication scaffolding
- **DataTables**: `yajra/laravel-datatables-oracle` for data grid functionality
- **Avatar**: `laravolt/avatar` for user avatar generation

## LDAP Operations

### Import Users from LDAP
```bash
# Import specific user by uid
php artisan ldap:import users --filter="(uid=jptest)"

# Import all users (use with caution)
php artisan ldap:import users
```

### LDAP Configuration
LDAP settings are configured via environment variables in `.env`:
- `LDAP_HOST`: LDAP server hostname
- `LDAP_USERNAME`: Bind DN for LDAP connection
- `LDAP_PASSWORD`: Bind password
- `LDAP_BASE_DN`: Base distinguished name for searches
- `LDAP_LOGGING`: Enable/disable LDAP operation logging

## File Structure Notes

### Controllers
- `HomeController`: Dashboard after authentication
- `UserController`: Complete user management with CRUD, audit trail, and logging
- `RoleController`: Role management CRUD with permission assignment
- `UserRoleController`: User role assignment and management interface
- `MobileController`: Mobile-specific functionality
- `Auth/`: Laravel UI authentication controllers

### Models
- `User`: Extended with LDAP authentication, SoftDeletes, audit trail relationships, and RBAC methods
- `UserLog`: Dedicated model for activity logging with JSON change tracking
- `Role`: RBAC role model with permission relationships and assignment methods
- `Permission`: RBAC permission model with role relationships and module grouping

### Views Structure
- `layouts/sneat.blade.php`: Main admin template layout
- `layouts/auth.blade.php`: Authentication layout with collapsible version sections
- `users/index.blade.php`: User listing with audit trail columns and activity log access
- `users/create.blade.php`: User creation form
- `users/edit.blade.php`: User editing form
- `users/show.blade.php`: User details with complete audit information
- `users/trash.blade.php`: Soft deleted users with restore functionality
- `users/logs.blade.php`: Global activity logs with filtering
- `users/user-logs.blade.php`: Individual user activity history
- `users/roles.blade.php`: User role assignment interface with AJAX operations
- `roles/index.blade.php`: Role listing with DataTables
- `roles/create.blade.php`: Role creation form with permission selection
- `roles/edit.blade.php`: Role editing form with permission management
- `roles/show.blade.php`: Role details with permission listing
- `auth/login.blade.php`: Enhanced login with collapsible version notes (responsive)

### Key Migrations
- `create_users_table.php`: Standard Laravel user table
- `add_ldap_columns_to_users_table.php`: LDAP integration columns (guid, username, domain)
- `add_audit_trail_to_users_table.php`: Audit trail columns (created_by, updated_by, deleted_by, deleted_at)
- `create_user_logs_table.php`: Activity logging table with JSON fields and indexing
- `create_roles_table.php`: RBAC roles table with audit trail
- `create_permissions_table.php`: RBAC permissions table with module grouping
- `create_role_permission_table.php`: Many-to-many pivot table for role permissions
- `create_user_role_table.php`: Many-to-many pivot table for user roles with assignment tracking

## Implementation Patterns

### DataTables Integration
- Server-side processing with `yajra/laravel-datatables-oracle`
- Export buttons (CSV, Excel, PDF, Print) included by default
- Action dropdowns with view, edit, delete, and activity log options
- Responsive design with mobile-friendly controls

### Audit Trail Pattern
- Model boot events automatically populate audit fields during CRUD operations
- Relationships defined for creator, updater, and deleter users
- Soft delete functionality preserves data integrity
- Foreign key constraints with `SET NULL` on user deletion

### Activity Logging Implementation
- Static `UserLog::log()` method for consistent logging across controllers
- Automatic IP address and user agent capture
- JSON storage for old/new values with change detection
- Color-coded action badges in UI (success, warning, danger, info, dark, secondary)

### Authentication Flow
- LDAP authentication with automatic user creation/sync
- Attribute mapping from LDAP to database fields
- Login screen with responsive collapsible version notes
- Post-authentication redirect to `/home` dashboard

### Frontend Architecture
- Sneat Bootstrap template with custom Tea-themed login
- Blade components for consistent UI elements
- DataTables with Bootstrap styling
- Responsive design with mobile-first approach
- Success/error flash message handling

### RBAC Implementation Patterns
- **Permission Middleware**: Routes protected with `permission:permission.name` middleware
- **View Permission Checks**: Use `@if(auth()->user()->hasPermission('permission.name'))` in Blade templates
- **Role-based UI**: Action buttons and menu items conditionally displayed based on permissions
- **Permission Grouping**: Permissions organized by modules for easier management
- **Hierarchical Roles**: Role hierarchy from super_admin (all permissions) to user (minimal permissions)
- **Many-to-many Relationships**: Users can have multiple roles, roles can have multiple permissions
- **Audit Trail Integration**: Role and permission assignments logged in UserLog system

## User Management Features

### Complete CRUD Operations
- Create: Form validation with password confirmation
- Read: Detailed view with audit trail information
- Update: Optional password updates with validation
- Delete: Soft delete with restore functionality
- Force Delete: Permanent removal from trash

### Advanced Features
- **Trash Management**: View and restore soft deleted users
- **Activity Logs**: Global and per-user activity tracking
- **Export Capabilities**: Data export in multiple formats
- **Search & Filter**: DataTables with full-text search
- **Bulk Operations**: Export selected records

### Security Features
- CSRF protection on all forms
- Input validation and sanitization
- SQL injection prevention via Eloquent ORM
- IP address logging for security audits
- User agent tracking for device identification

## RBAC Management Features

### Role Management Interface
- **Role CRUD**: Complete create, read, update, delete operations for roles
- **Permission Assignment**: Web-based interface for assigning permissions to roles
- **Module-based Permissions**: Permissions grouped by modules (users, roles, permissions, dashboard, reports, settings)
- **Role Usage Tracking**: Cannot delete roles that are assigned to users
- **Activity Logging**: All role changes logged with old/new values

### User Role Assignment
- **Web Interface**: `/users/{user}/roles` provides comprehensive role management
- **Multiple Assignment Methods**: Bulk checkbox assignment or individual quick-add/remove
- **AJAX Operations**: Real-time role assignment/removal without page refresh
- **Visual Feedback**: Role badges, confirmation dialogs, success/error messages
- **Permission Preview**: Shows total permissions for each role

### Default Test User
- **jptest User**: Pre-configured super administrator for testing
- **Full Access**: Has all permissions for testing role management features
- **Login**: Use LDAP credentials or username 'jptest' for development

### Permission Structure
```
users.*     - User management (view, create, edit, delete, restore, force_delete, logs)
roles.*     - Role management (view, create, edit, delete)
permissions.* - Permission management (view, create, edit, delete)
dashboard.view - Dashboard access
reports.view - Reports access
settings.*  - Settings management (view, edit)
```

### Adding New Permissions
1. Add to `RolePermissionSeeder.php` permissions array
2. Protect routes with `permission:new.permission` middleware
3. Add permission checks to views with `hasPermission('new.permission')`
4. Update controller DataTables action columns for permission-based buttons
5. Run `php artisan db:seed --class=RolePermissionSeeder` to apply changes

### Route Organization Pattern
Routes in `routes/web.php` are organized by permission groups:
```php
// Example pattern for new features
Route::middleware(['permission:feature.view'])->group(function () {
    Route::get('/feature', [FeatureController::class, 'index']);
    Route::get('/feature/data', [FeatureController::class, 'getData']);
});

Route::middleware(['permission:feature.create'])->group(function () {
    Route::get('/feature/create', [FeatureController::class, 'create']);
    Route::post('/feature', [FeatureController::class, 'store']);
});
```
This pattern ensures consistent permission checking and makes it easy to see which routes require which permissions.