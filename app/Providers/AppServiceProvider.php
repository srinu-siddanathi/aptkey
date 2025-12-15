<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global scope for tenant isolation
        // This ensures all queries automatically filter by tenant_id
        // when a tenant context is set
        Builder::macro('forTenant', function ($tenantId) {
            return $this->where('tenant_id', $tenantId);
        });
    }
}

