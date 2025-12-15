<?php

namespace App\Http\Controllers\Api\Resident;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Get all invoices for the authenticated resident
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $invoices = Invoice::where('resident_id', $user->id)
            ->with(['unit:id,block,unit_number'])
            ->orderBy('created_at', 'desc')
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
                    'description' => $invoice->description,
                    'line_items' => $invoice->line_items,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get a specific invoice
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $invoice = Invoice::where('resident_id', $user->id)
            ->with(['unit:id,block,unit_number,type'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'unit' => [
                    'id' => $invoice->unit->id,
                    'identifier' => ($invoice->unit->block ? $invoice->unit->block . ' - ' : '') . $invoice->unit->unit_number,
                    'type' => $invoice->unit->type,
                ],
                'amount' => (float) $invoice->amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'outstanding' => (float) $invoice->outstanding_amount,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'is_overdue' => $invoice->isOverdue(),
                'description' => $invoice->description,
                'line_items' => $invoice->line_items,
                'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
                'payment_method' => $invoice->payment_method,
                'transaction_id' => $invoice->transaction_id,
            ],
        ]);
    }

    /**
     * Mark invoice as paid (after payment gateway integration)
     */
    public function markAsPaid(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:online,cheque,cash,upi,bank_transfer',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::where('resident_id', $user->id)->findOrFail($id);

        $paidAmount = (float) $request->paid_amount;
        $newPaidAmount = (float) $invoice->paid_amount + $paidAmount;

        // Update invoice
        $invoice->paid_amount = $newPaidAmount;
        $invoice->payment_method = $request->payment_method;
        $invoice->transaction_id = $request->transaction_id;
        $invoice->paid_at = now();

        // Update status
        if ($newPaidAmount >= $invoice->amount) {
            $invoice->status = 'paid';
        } elseif ($newPaidAmount > 0) {
            $invoice->status = 'partial';
        }

        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'paid_amount' => (float) $invoice->paid_amount,
                'outstanding' => (float) $invoice->outstanding_amount,
                'status' => $invoice->status,
            ],
        ]);
    }
}
