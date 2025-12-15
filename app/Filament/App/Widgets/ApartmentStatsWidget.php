<?php

namespace App\Filament\App\Widgets;

use App\Models\Complaint;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApartmentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = Auth::user()->tenant_id;

        $totalResidents = User::where('tenant_id', $tenantId)
            ->where('role', 'resident')
            ->count();
        
        $totalUnits = Unit::where('tenant_id', $tenantId)->count();
        $occupiedUnits = Unit::where('tenant_id', $tenantId)
            ->where('is_occupied', true)
            ->count();
        
        $totalInvoices = Invoice::where('tenant_id', $tenantId)->count();
        $pendingInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'overdue'])
            ->count();
        
        $totalDues = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->sum(DB::raw('amount - paid_amount')) ?? 0;
        
        $openComplaints = Complaint::where('tenant_id', $tenantId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        $totalExpenses = Expense::where('tenant_id', $tenantId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount') ?? 0;

        return [
            Stat::make('Total Residents', $totalResidents)
                ->description('Registered residents')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            
            Stat::make('Units', "{$occupiedUnits} / {$totalUnits}")
                ->description('Occupied / Total units')
                ->descriptionIcon('heroicon-o-home')
                ->color('success'),
            
            Stat::make('Pending Invoices', $pendingInvoices)
                ->description('Out of ' . $totalInvoices . ' total')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning'),
            
            Stat::make('Total Dues', '₹' . number_format($totalDues, 2))
                ->description('Outstanding amount')
                ->descriptionIcon('heroicon-o-currency-rupee')
                ->color('danger'),
            
            Stat::make('Open Complaints', $openComplaints)
                ->description('Requiring attention')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('info'),
            
            Stat::make('Monthly Expenses', '₹' . number_format($totalExpenses, 2))
                ->description('This month')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('gray'),
        ];
    }
}

