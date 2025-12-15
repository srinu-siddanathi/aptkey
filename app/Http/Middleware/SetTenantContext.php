<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * This middleware sets the tenant context for the authenticated user
     * in the Apartment Admin Panel. It ensures all queries are scoped
     * to the user's tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->tenant_id) {
            // Set tenant ID in session for easy access
            session(['current_tenant_id' => Auth::user()->tenant_id]);
            
            // You can also set it in a service container binding
            app()->instance('tenant_id', Auth::user()->tenant_id);
        }

        return $next($request);
    }
}

