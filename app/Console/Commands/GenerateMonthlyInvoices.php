<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly 
                            {--tenant= : Generate for specific tenant ID}
                            {--month= : Month (1-12), defaults to current month}
                            {--year= : Year, defaults to current year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly maintenance invoices for all occupied units';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?: now()->month;
        $year = $this->option('year') ?: now()->year;
        $tenantId = $this->option('tenant');

        $tenants = $tenantId 
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->error('No active tenants found.');
            return Command::FAILURE;
        }

        $invoiceDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $dueDate = $invoiceDate->copy()->endOfMonth();

        $totalGenerated = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->name}");

            // Get all occupied units for this tenant
            $units = Unit::where('tenant_id', $tenant->id)
                ->where('is_occupied', true)
                ->whereNotNull('resident_id')
                ->get();

            foreach ($units as $unit) {
                // Check if invoice already exists for this month
                $existingInvoice = Invoice::where('tenant_id', $tenant->id)
                    ->where('unit_id', $unit->id)
                    ->where('resident_id', $unit->resident_id)
                    ->whereYear('invoice_date', $year)
                    ->whereMonth('invoice_date', $month)
                    ->first();

                if ($existingInvoice) {
                    $this->warn("  Invoice already exists for Unit {$unit->unit_number} - {$unit->resident->name}");
                    continue;
                }

                // Generate invoice number
                $invoiceNumber = 'INV-' . $invoiceDate->format('Ymd') . '-' . strtoupper(Str::random(6));

                // Create invoice
                Invoice::create([
                    'tenant_id' => $tenant->id,
                    'unit_id' => $unit->id,
                    'resident_id' => $unit->resident_id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'amount' => $unit->monthly_maintenance,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'description' => "Monthly maintenance for {$invoiceDate->format('F Y')}",
                ]);

                $this->info("  ✓ Generated invoice for Unit {$unit->unit_number} - {$unit->resident->name}");
                $totalGenerated++;
            }
        }

        $this->info("");
        $this->info("✅ Generated {$totalGenerated} invoices for {$invoiceDate->format('F Y')}");

        return Command::SUCCESS;
    }
}
