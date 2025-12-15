<?php

namespace App\Http\Controllers\Api\Resident;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get resident dashboard data
     * 
     * Returns:
     * - Total dues (outstanding invoices)
     * - Recent transactions (invoices)
     * - Upcoming due dates
     * - Quick stats
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Ensure user is a resident
        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only residents can access this endpoint.',
            ], 403);
        }

        // Calculate total dues (sum of outstanding amounts)
        $totalDues = Invoice::where('resident_id', $user->id)
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as total_due')
            ->value('total_due') ?? 0;

        // Get recent transactions (last 10 invoices)
        $recentTransactions = Invoice::where('resident_id', $user->id)
            ->with(['unit:id,block,unit_number'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'unit' => ($invoice->unit->block ? $invoice->unit->block . ' - ' : '') . $invoice->unit->unit_number,
                    'amount' => (float) $invoice->amount,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'outstanding' => (float) $invoice->outstanding_amount,
                    'status' => $invoice->status,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'is_overdue' => $invoice->isOverdue(),
                ];
            });

        // Get upcoming due dates (next 30 days)
        $upcomingDues = Invoice::where('resident_id', $user->id)
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->with(['unit:id,block,unit_number'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'unit' => ($invoice->unit->block ? $invoice->unit->block . ' - ' : '') . $invoice->unit->unit_number,
                    'amount' => (float) $invoice->outstanding_amount,
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'days_until_due' => now()->diffInDays($invoice->due_date, false),
                ];
            });

        // Quick stats
        $stats = [
            'total_due' => (float) $totalDues,
            'pending_invoices' => Invoice::where('resident_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'overdue_invoices' => Invoice::where('resident_id', $user->id)
                ->where('status', 'overdue')
                ->count(),
            'paid_this_month' => Invoice::where('resident_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('paid_amount') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_transactions' => $recentTransactions,
                'upcoming_dues' => $upcomingDues,
            ],
        ]);
    }
}
