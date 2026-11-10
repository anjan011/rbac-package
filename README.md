# Roles and Permissions for Laravel

A flexible and highly configurable RBAC (Role-Based Access Control) system for Laravel applications. This package allows for full customization of table and column names to fit into any database schema.

## Features

- **Granular Control**: Manage roles and permissions with ease.
- **Full Customization**: Configure table names and primary key column names.
- **Super Admin Support**: Built-in support for a Super Admin role with automatic assignment.
- **Rich API**: Extensive set of methods for checking and managing roles/permissions.
- **Artisan Integration**: Commands for setup, import, export, and testing.
- **Protection**: `System` type roles and permissions are protected from deletion or modification.
- **Validity Checks**: Automatic verification that roles and permissions are valid and enabled before any operation.

---

## Installation and Setup Guide

### Prerequisites

- PHP 8.5 or higher
- Laravel 11.x, 12.x, or 13.x

### Step 1: Package Installation

Install the package using Composer:

```bash
composer require anjan011/roles-and-permissions
```

After installation, you can see the next setup steps by running:

```bash
php artisan anjan011-roles-permissions:setup-instructions
```

#### Configuration Publication

After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="Anjan011\RolesAndPermissions\RolesAndPermissionsServiceProvider" --tag="config"
```

The configuration file will be placed at: `config/anjan001/roles-and-permissions.php`

### Step 2: Application Integration

#### 1. Configure Settings

Open `config/anjan001/roles-and-permissions.php` and review the settings. You can customize table names and column names to match your existing database structure.

**Key Settings:**
- `user_id_column`: The primary key of your users table (default: `id`).
- `super_admin_user_ids`: An array of user IDs that should be granted the `Super Admin` role during setup.

#### 2. Update User Model

Add the `UseRolesAndPermissions` trait to your `User` model (typically `app/Models/User.php`).

```php
namespace App\Models;

use Anjan011\RolesAndPermissions\Traits\UseRolesAndPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use UseRolesAndPermissions;

    // ...
}
```

### Step 3: Database Setup

Run the dedicated setup command to handle database migrations and initial seeding:

```bash
php artisan anjan011-roles-permissions:setup
```

**What this command does:**
1. Runs migrations to create roles, permissions, and cross-reference tables.
2. Seeds initial roles:
   - `Super Admin` (System, ID: 1): Has full access to everything.
   - `Admin` (Custom): Predefined role with essential management permissions.
3. Seeds initial `System` type permissions for User and Custom Role management.
4. Automatically assigns the `Super Admin` role to any user IDs specified in the `super_admin_user_ids` config array.
5. Assigns essential permissions to the `Admin` role.

---

## Initial Roles and Permissions

During the setup process, the following roles and permissions are automatically created.

### Initial Roles

| Role | Type | ID | Description |
|------|------|----|-------------|
| `Super Admin` | System | 1 | Full access to all system features. |
| `Admin` | Custom | Auto | Predefined role for general administration. |

### Initial Permissions

The system creates the following `System` type permissions:

#### User Management
- `user.view`: View user list and details.
- `user.create`: Create new users.
- `user.update`: Update existing users.
- `user.delete`: Delete users.
- `user.import`: Import users via CSV.
- `user.export`: Export users to CSV.

#### Custom Role Management
- `custom-role.view`, `custom-role.create`, `custom-role.update`, `custom-role.delete`, `custom-role.import`, `custom-role.export`.

### Role-Permission Assignments

- **Super Admin**: Automatically bypasses all permission checks (has all permissions).
- **Admin**: Assigned the following permissions:
  - `custom-role.view`, `custom-role.create`, `custom-role.update`
  - `user.view`, `user.create`, `user.update`, `user.delete`, `user.import`, `user.export`

---

## Available Methods

Methods are available via the `UseRolesAndPermissions` trait on your `User` model and as static methods on the `RolesAndPermissionsManager` class.

### Role Methods

| Trait Method | Description |
|--------------|-------------|
| `hasRoleByName(string $name)` | Check if user has role by name. |
| `hasRoleById(int $id)` | Check if user has role by ID. |
| `hasAnyRoleByName(array $names)` | Check if user has any of the roles by name. |
| `hasAnyRoleById(array $ids)` | Check if user has any of the roles by ID. |
| `hasAllRolesByName(array $names)` | Check if user has all of the roles by name. |
| `hasAllRolesById(array $ids)` | Check if user has all of the roles by ID. |
| `addRoleById(int $id)` | Assign a role by ID to the user. |
| `addRoleByName(string $name)` | Assign a role by name to the user. |
| `removeRoleById(int $id)` | Remove a role by ID from the user. |
| `removeRoleByName(string $name)` | Remove a role by name from the user. |
| `addRolesByIdList(array $ids)` | Assign multiple roles by ID to the user. |
| `addRolesByNameList(array $names)` | Assign multiple roles by name to the user. |
| `removeRolesByIdList(array $ids)` | Remove multiple roles by ID from the user. |
| `removeRolesByNameList(array $names)` | Remove multiple roles by name from the user. |

### Permission Methods

| Trait Method | Description |
|--------------|-------------|
| `hasPermissionByName(string $name)` | Check if user has permission by name. |
| `hasPermissionById(int $id)` | Check if user has permission by ID. |
| `hasAnyPermissionByName(array $names)` | Check if user has any of the permissions by name. |
| `hasAnyPermissionById(array $ids)` | Check if user has any of the permissions by ID. |
| `hasAllPermissionsByName(array $names)` | Check if user has all of the permissions by name. |
| `hasAllPermissionsById(array $ids)` | Check if user has all of the permissions by ID. |

### Static Manager Methods

The `RolesAndPermissionsManager` class provides static versions of all methods. The first parameter is always the `int $userId`.

```php
use Anjan011\RolesAndPermissions\RolesAndPermissionsManager as RBAC;

if (RBAC::hasPermissionByName($user->id, 'edit-posts')) {
    // ...
}

RBAC::addRoleByName($user->id, 'Editor');
```

---

## Middleware Support

The package provides middleware to protect your routes (including API routes) based on roles and permissions. These are automatically registered and available for use.

### 1. Permission Middleware
Use the `anjan011-rbac-permission` middleware to restrict access to users with specific permissions. Multiple permissions can be provided as a comma-separated list, and the user must have **all** of them.

- **Validation**: If any of the provided permissions do not exist or are disabled, it returns a `403 Forbidden` response with the message `Invalid permission`.
- **Super Admin Bypass**: Users with the `Super Admin` role automatically bypass this check.

```php
// In routes/api.php or routes/web.php
Route::get('/post/create', function () {
    // ...
})->middleware('anjan011-rbac-permission:create-post,edit-post');
```

### 2. Role Middleware
Use the `anjan011-rbac-role` middleware to restrict access to users with specific roles. Multiple roles can be provided as a comma-separated list, and the user must have **at least one** of them.

- **Validation**: If any of the provided roles do not exist or are disabled, it returns a `403 Forbidden` response with the message `Invalid role`.
- **Super Admin Bypass**: Users with the `Super Admin` role automatically bypass this check.

```php
// In routes/api.php or routes/web.php
Route::get('/admin', function () {
    // ...
})->middleware('anjan011-rbac-role:Editor,Admin');
```

### 3. Middleware Customization
You can change the names of the registered middleware in the configuration file to avoid conflicts or match your naming conventions.

In `config/anjan001/roles-and-permissions.php`:

```php
'middleware_name' => [
    'permission' => 'your-custom-permission-name',
    'role' => 'your-custom-role-name',
],
```

---

## Artisan Commands

The package provides several Artisan commands to streamline management and testing.

### 1. Setup
`php artisan anjan011-roles-permissions:setup`
Performs database migrations and seeds the initial `Super Admin` system role. It also assigns the role to any users specified in the `super_admin_user_ids` config.

### 2. Assign Role
`php artisan anjan011-roles-permissions:assign-role [options]`
Assigns a role to a specific user.

**Options:**
- `--role-id`: (integer) The ID of the role to assign.
- `--user-id`: (integer) The ID of the user.
- `--role-name`: (string) The name of the role (required if `role-id` is omitted).
- `--user-email`: (string) The email of the user (required if `user-id` is omitted).
- `--force`: (boolean) Force assign the role even if the user already has it.

**Examples:**
```shell
# Using IDs
php artisan anjan011-roles-permissions:assign-role --role-id=1 --user-id=1234 --force

# Using Name and Email
php artisan anjan011-roles-permissions:assign-role --role-name="Editor" --user-email="admin@example.com"
```

### 3. Data Import/Export
- `php artisan anjan011-roles-permissions:import-roles --file=path/to/file.csv`
- `php artisan anjan011-roles-permissions:export-roles --file=path/to/file.csv`
- `php artisan anjan011-roles-permissions:import-permissions --file=path/to/file.csv`
- `php artisan anjan011-roles-permissions:export-permissions --file=path/to/file.csv`

### 4. Testing
`php artisan anjan011-roles-permissions:test`
Runs the comprehensive unit test suite within a database transaction, ensuring no persistent changes are made to your database.

### 5. Dump Permissions as Constants
`php artisan anjan011-roles-permissions:dump-permissions [options]`
Reads all permissions from the database and dumps them as PHP constants in a file.

**Options:**
- `--path`: The path where the constants file will be generated (default: `app/Constants/permissions.php`).
- `--prefix`: The prefix for the constant names (default: `PERM`).

**Example Output:**
```php
const PERM_USER_CREATE = 'user.create';
const PERM_USER_UPDATE = 'user.update';
```

**Global Availability:**
To make these constants available globally, add the generated file to the `autoload.files` section in your `composer.json`:

```json
"autoload": {
    "files": [
        "app/Constants/permissions.php"
    ]
}
```
Then run `composer dump-autoload`.

### 6. Setup Instructions
`php artisan anjan011-roles-permissions:setup-instructions`
Lists the necessary steps to complete the setup of the package after installation.

---

## API Endpoints

The package provides optional API endpoints for managing roles and permissions. These can be enabled and configured via the configuration file.

### API Configuration

In `config/anjan001/roles-and-permissions.php`, you can find the `routes` section:

```php
'routes' => [
    'enabled'    => true, // Enable or disable all package API routes
    'prefix'     => 'api/anjan011/roles-and-permissions', // Route prefix
    'middleware' => ['api', 'auth:sanctum'], // Middleware to apply
],
```

### Endpoint Details

All endpoints use the configured prefix (default: `api/anjan011/roles-and-permissions`).

#### System Roles
*Requires `Super Admin` role access.*

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/system-roles` | List all system roles. |
| POST | `/system-roles` | Create a new system role. |
| GET | `/system-roles/{id}` | View details of a system role. |
| PUT/PATCH | `/system-roles/{id}` | Update a system role. |
| DELETE | `/system-roles/{id}` | Delete a system role. |

#### Custom Roles
*Access is controlled via specific permissions.*

| Method | Endpoint | Description | Required Permission |
|--------|----------|-------------|---------------------|
| GET | `/custom-roles` | List all custom roles. | `custom-role.view` |
| POST | `/custom-roles` | Create a new custom role. | `custom-role.create` |
| GET | `/custom-roles/{id}` | View details of a custom role. | `custom-role.view` |
| PUT/PATCH | `/custom-roles/{id}` | Update a custom role. | `custom-role.update` |
| DELETE | `/custom-roles/{id}` | Delete a custom role. | `custom-role.delete` |

#### Permissions
*Requires `Super Admin` role access.*

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/permissions` | List all permissions. |
| POST | `/permissions` | Create a new permission. |
| GET | `/permissions/{id}` | View details of a permission. |
| PUT/PATCH | `/permissions/{id}` | Update a permission. |
| DELETE | `/permissions/{id}` | Delete a permission. |

### Role-Permission Assignment via API

When creating or updating roles (both system and custom), you can assign permissions using the following parameters:

- `permission_ids`: (array of integers) A list of permission IDs to assign to the role.
- `permission_names`: (array of strings) A list of permission names to assign to the role.

**Rules:**
1. Both parameters are optional.
2. If `permission_ids` is provided and is a non-empty array, `permission_names` is ignored.
3. **Validation**:
   - If any element in `permission_ids` is not an integer, the API returns a `400 Bad Request`.
   - If any element in `permission_names` is not a string, the API returns a `400 Bad Request`.
   - If any specified permission (by ID or Name) does not exist or is disabled, the API throws an `Invalid permission` exception (returns `422 Unprocessable Entity`).
4. **Relationship**: If a role-permission relationship already exists, it is silently ignored and the process continues to the next permission (implemented via `sync`).

### Postman Collection

A Postman collection is provided to help you test the API endpoints.

**Path:** `packages/anjan011/roles-and-permissions/postman/collection.json`

**Variables:**
- `RBAC_API_ROOT`: The full URL to the API root (e.g., `http://localhost/api/anjan011/roles-and-permissions`).
- `RBAC_API_TOKEN`: A valid Sanctum bearer token for an authenticated user.

---

## Validity and Enablement Checks

The system automatically verifies if a role or permission is valid (exists in the database) and enabled (`is_enabled` column set to `1`) before performing any check or management operation.

- If a role is invalid or disabled, an `\Exception('Invalid role')` is thrown.
- If a permission is invalid or disabled, an `\Exception('Invalid permission')` is thrown.

This ensures that checks against non-existent or deactivated roles/permissions are caught immediately during development or runtime.

---

## System Entry Protection
 
 To prevent accidental breakage of the RBAC system, entries marked as `type = 'System'` have restricted modification and deletion:
 
 - **Modification/Deletion**: Blocked by default, throwing an `\Exception`.
 - **Super Admin Override**: Users assigned the `Super Admin` role (default ID: 1) are permitted to modify or delete system roles (except the Super Admin role itself). This allows for necessary system adjustments while maintaining a high level of protection for regular administrators.
 - **Absolute Protection**: The `Super Admin` role (default ID: 1) **cannot be deleted via Laravel under any circumstances**, even by a Super Admin user. It can only be removed directly from the database if absolutely necessary.

---

### Sample CSV Files

Sample files are included in the `samples` directory of the package (typically `vendor/anjan011/roles-and-permissions/samples/` or `packages/anjan011/roles-and-permissions/samples/`):

- **Roles**: `roles.csv`
- **Permissions**: `permissions.csv`

---

## Unit Testing

Trigger the full test suite (using TestDox for readable output) using:

```bash
php artisan anjan011-roles-permissions:test
```

### Test Coverage

The suite covers:
1. Role & Permission CRUD (System/Custom types).
2. Role-Permission mapping.
3. User-Role mapping.
4. Protection of `System` type entries (with Super Admin override for Roles).

### Database Safety

All tests are performed within a **database transaction** and automatically rolled back, ensuring your database remains clean.

---

## Technical Implementation

The core logic resides in:
- `src/Models/`: Role and Permission Eloquent models.
- `src/Traits/UseRolesAndPermissions.php`: Main trait for the User model.
- `src/RolesAndPermissionsManager.php`: Static access manager.
- `src/Console/`: Artisan command implementations.
