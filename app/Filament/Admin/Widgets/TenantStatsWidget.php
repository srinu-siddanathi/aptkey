<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $totalManagers = User::where('role', 'apartment_manager')->count();
        $totalResidents = User::where('role', 'resident')->count();

        return [
            Stat::make('Total Apartment Complexes', $totalTenants)
                ->description('All registered complexes')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('primary'),
            
            Stat::make('Active Complexes', $activeTenants)
                ->description('Currently active')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Apartment Managers', $totalManagers)
                ->description('Total managers across all complexes')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
            
            Stat::make('Total Residents', $totalResidents)
                ->description('All residents across all complexes')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('warning'),
        ];
    }
}

