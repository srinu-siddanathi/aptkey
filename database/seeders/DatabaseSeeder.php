<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@aptkey.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        $this->command->info('✅ Super Admin created: admin@aptkey.com / password');

        // Create Sample Tenant
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Siva Balaji Arcade'],
            [
                'address' => '123 Main Street, Sector 5',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zip_code' => '400001',
                'phone' => '+91-1234567890',
                'email' => 'info@sivabalajiarcade.com',
                'is_active' => true,
                'onboarded_at' => now(),
            ]
        );

        $this->command->info("✅ Tenant created: {$tenant->name}");

        // Create Apartment Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@aptkey.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Apartment Manager',
                'password' => Hash::make('password'),
                'role' => 'apartment_manager',
                'phone' => '+91-9876543210',
            ]
        );

        $this->command->info('✅ Apartment Manager created: manager@aptkey.com / password');

        // Create Sample Residents
        $residents = [];
        for ($i = 1; $i <= 5; $i++) {
            $resident = User::create([
                'tenant_id' => $tenant->id,
                'name' => "Resident {$i}",
                'email' => "resident{$i}@aptkey.com",
                'password' => Hash::make('password'),
                'role' => 'resident',
                'phone' => "+91-98765432{$i}0",
            ]);
            $residents[] = $resident;
        }

        $this->command->info('✅ Created 5 sample residents');

        // Create Sample Units
        $units = [];
        $blocks = ['A', 'B', 'C'];
        $unitTypes = ['1BHK', '2BHK', '3BHK', '2BHK', '3BHK'];
        $residentIndex = 0;
        
        foreach ($blocks as $blockIndex => $block) {
            for ($floor = 1; $floor <= 3; $floor++) {
                for ($unitNum = 1; $unitNum <= 4; $unitNum++) {
                    $unitNumber = str_pad($floor, 2, '0', STR_PAD_LEFT) . str_pad($unitNum, 2, '0', STR_PAD_LEFT);
                    
                    $unit = Unit::create([
                        'tenant_id' => $tenant->id,
                        'resident_id' => isset($residents[$residentIndex]) ? $residents[$residentIndex]->id : null,
                        'block' => $block,
                        'unit_number' => $unitNumber,
                        'type' => $unitTypes[array_rand($unitTypes)],
                        'area_sqft' => rand(600, 2000),
                        'monthly_maintenance' => rand(3000, 8000),
                        'is_occupied' => isset($residents[$residentIndex]),
                    ]);
                    $units[] = $unit;
                    
                    if (isset($residents[$residentIndex])) {
                        $residentIndex++;
                    }
                }
            }
        }

        $this->command->info('✅ Created sample units');

        // Create Sample Invoices
        foreach ($residents as $resident) {
            $residentUnits = Unit::where('resident_id', $resident->id)->get();
            
            if ($residentUnits->isEmpty()) {
                continue;
            }
            
            foreach ($residentUnits as $unit) {
                // Create some past invoices
                for ($i = 1; $i <= 3; $i++) {
                    $invoiceDate = now()->subMonths($i);
                    $dueDate = $invoiceDate->copy()->addMonth();
                    
                    Invoice::create([
                        'tenant_id' => $tenant->id,
                        'unit_id' => $unit->id,
                        'resident_id' => $resident->id,
                        'invoice_number' => 'INV-' . $invoiceDate->format('Ymd') . '-' . strtoupper(uniqid()),
                        'invoice_date' => $invoiceDate,
                        'due_date' => $dueDate,
                        'amount' => $unit->monthly_maintenance,
                        'paid_amount' => $i > 1 ? $unit->monthly_maintenance : 0, // Older invoices are paid
                        'status' => $i > 1 ? 'paid' : ($dueDate->isPast() ? 'overdue' : 'pending'),
                        'description' => "Monthly maintenance for {$invoiceDate->format('F Y')}",
                        'paid_at' => $i > 1 ? $dueDate->copy()->addDays(rand(1, 5)) : null,
                        'payment_method' => $i > 1 ? ['online', 'cheque', 'cash'][array_rand(['online', 'cheque', 'cash'])] : null,
                    ]);
                }
            }
        }

        $this->command->info('✅ Created sample invoices');

        // Create Sample Expenses
        $expenseCategories = ['maintenance', 'repair', 'security', 'cleaning', 'utilities', 'staff_salary'];
        for ($i = 1; $i <= 10; $i++) {
            Expense::create([
                'tenant_id' => $tenant->id,
                'created_by' => $manager->id,
                'title' => "Expense {$i}",
                'description' => "Sample expense description {$i}",
                'category' => $expenseCategories[array_rand($expenseCategories)],
                'amount' => rand(500, 5000),
                'expense_date' => now()->subDays(rand(1, 30)),
                'vendor' => "Vendor {$i}",
                'receipt_number' => 'RCP-' . strtoupper(uniqid()),
            ]);
        }

        $this->command->info('✅ Created sample expenses');

        // Create Sample Complaints
        $complaintCategories = ['plumbing', 'electrical', 'cleaning', 'security', 'parking', 'noise', 'elevator'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        
        foreach ($residents as $resident) {
            for ($i = 1; $i <= 2; $i++) {
                $status = $statuses[array_rand($statuses)];
                Complaint::create([
                    'tenant_id' => $tenant->id,
                    'raised_by' => $resident->id,
                    'unit_id' => $resident->units->first()?->id,
                    'ticket_number' => 'TKT-' . date('Ymd') . '-' . strtoupper(uniqid()),
                    'subject' => "Complaint {$i} from {$resident->name}",
                    'description' => "This is a sample complaint description {$i}",
                    'category' => $complaintCategories[array_rand($complaintCategories)],
                    'priority' => $priorities[array_rand($priorities)],
                    'status' => $status,
                    'assigned_to' => $status !== 'open' ? $manager->id : null,
                    'resolved_at' => $status === 'resolved' ? now()->subDays(rand(1, 10)) : null,
                    'resolution_notes' => $status === 'resolved' ? 'Issue resolved successfully' : null,
                ]);
            }
        }

        $this->command->info('✅ Created sample complaints');

        // Create Sample Notices
        $noticeTypes = ['announcement', 'maintenance', 'event', 'important', 'general'];
        for ($i = 1; $i <= 5; $i++) {
            Notice::create([
                'tenant_id' => $tenant->id,
                'created_by' => $manager->id,
                'title' => "Notice {$i}",
                'content' => "This is sample notice content {$i}. Important information for all residents.",
                'type' => $noticeTypes[array_rand($noticeTypes)],
                'priority' => ['normal', 'high', 'urgent'][array_rand(['normal', 'high', 'urgent'])],
                'publish_date' => now()->subDays(rand(1, 30)),
                'expiry_date' => now()->addDays(rand(30, 90)),
                'is_published' => true,
                'views_count' => rand(0, 50),
            ]);
        }

        $this->command->info('✅ Created sample notices');

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('  Super Admin: admin@aptkey.com / password');
        $this->command->info('  Apartment Manager: manager@aptkey.com / password');
        $this->command->info('  Residents: resident1@aptkey.com to resident5@aptkey.com / password');
    }
}
