# How to Create Apartment Admin Login

## Method 1: Using Super Admin Panel (Recommended)

1. **Login to Super Admin Panel**: `http://localhost/admin`
   - Use your super admin credentials (admin@aptkey.com / password)

2. **Create a Tenant (Apartment Complex)**:
   - Go to "Apartment Complexes" in the sidebar
   - Click "New Apartment Complex"
   - Fill in the details (name, address, etc.)
   - Save

3. **Create Apartment Manager User**:
   - Go to "Users" section (if available) OR use Method 2 below
   - Note: The UserResource in Admin panel is filtered to super_admin only
   - So use Method 2 (Tinker) or Method 3 (Seeder)

## Method 2: Using Tinker (Quick)

Run this command to create an apartment manager:

```bash
cd /home/srinu/Projects/AptKey
php artisan tinker
```

Then paste this code:

```php
// First, get or create a tenant
$tenant = \App\Models\Tenant::firstOrCreate(
    ['name' => 'Sample Apartment Complex'],
    [
        'address' => '123 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'zip_code' => '400001',
        'phone' => '+91-1234567890',
        'email' => 'info@sampleapartment.com',
        'is_active' => true,
        'onboarded_at' => now(),
    ]
);

// Create apartment manager user
$manager = \App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Apartment Manager',
    'email' => 'manager@sampleapartment.com',
    'password' => bcrypt('password'),
    'role' => 'apartment_manager',
    'phone' => '+91-9876543210',
]);

echo "✅ Tenant created: {$tenant->name} (ID: {$tenant->id})\n";
echo "✅ Manager created: {$manager->email} / password\n";
echo "\nLogin at: http://localhost/app\n";
```

## Method 3: Create a Seeder (Best for Production)

Create a seeder file:

```bash
php artisan make:seeder ApartmentManagerSeeder
```

Then edit `database/seeders/ApartmentManagerSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApartmentManagerSeeder extends Seeder
{
    public function run(): void
    {
        // Create tenant
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Sample Apartment Complex'],
            [
                'address' => '123 Main Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zip_code' => '400001',
                'phone' => '+91-1234567890',
                'email' => 'info@sampleapartment.com',
                'is_active' => true,
                'onboarded_at' => now(),
            ]
        );

        // Create apartment manager
        User::firstOrCreate(
            ['email' => 'manager@sampleapartment.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Apartment Manager',
                'password' => Hash::make('password'),
                'role' => 'apartment_manager',
                'phone' => '+91-9876543210',
            ]
        );

        $this->command->info('✅ Tenant and Apartment Manager created!');
        $this->command->info('Login: manager@sampleapartment.com / password');
    }
}
```

Run the seeder:
```bash
php artisan db:seed --class=ApartmentManagerSeeder
```

## Default Login Credentials

After creating using any method above:

- **Email**: `manager@sampleapartment.com` (or whatever you set)
- **Password**: `password` (change this in production!)
- **Panel**: `http://localhost/app`

## Important Notes

1. **Apartment managers MUST have a tenant_id** - they belong to a specific apartment complex
2. **Role must be 'apartment_manager'** - this allows access to the `/app` panel
3. **Change default password** - Never use 'password' in production!

## Quick One-Liner (Tinker)

If you already have a tenant, use this one-liner:

```bash
php artisan tinker --execute="
\$tenant = \App\Models\Tenant::first();
\$manager = \App\Models\User::create([
    'tenant_id' => \$tenant->id,
    'name' => 'Apartment Manager',
    'email' => 'manager@aptkey.com',
    'password' => bcrypt('password'),
    'role' => 'apartment_manager',
]);
echo 'Manager created: ' . \$manager->email . ' / password';
"
```

