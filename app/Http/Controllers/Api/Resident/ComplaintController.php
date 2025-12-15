<?php

namespace App\Http\Controllers\Api\Resident;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * Get all complaints for the authenticated resident
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

        $complaints = Complaint::where('raised_by', $user->id)
            ->with(['unit:id,block,unit_number', 'assignee:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'ticket_number' => $complaint->ticket_number,
                    'subject' => $complaint->subject,
                    'description' => $complaint->description,
                    'category' => $complaint->category,
                    'priority' => $complaint->priority,
                    'status' => $complaint->status,
                    'unit' => $complaint->unit ? [
                        'id' => $complaint->unit->id,
                        'identifier' => ($complaint->unit->block ? $complaint->unit->block . ' - ' : '') . $complaint->unit->unit_number,
                    ] : null,
                    'assigned_to' => $complaint->assignee ? [
                        'id' => $complaint->assignee->id,
                        'name' => $complaint->assignee->name,
                    ] : null,
                    'resolution_notes' => $complaint->resolution_notes,
                    'resolved_at' => $complaint->resolved_at?->format('Y-m-d H:i:s'),
                    'created_at' => $complaint->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }

    /**
     * Get a specific complaint
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

        $complaint = Complaint::where('raised_by', $user->id)
            ->with(['unit:id,block,unit_number', 'assignee:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $complaint->id,
                'ticket_number' => $complaint->ticket_number,
                'subject' => $complaint->subject,
                'description' => $complaint->description,
                'category' => $complaint->category,
                'priority' => $complaint->priority,
                'status' => $complaint->status,
                'unit' => $complaint->unit ? [
                    'id' => $complaint->unit->id,
                    'identifier' => ($complaint->unit->block ? $complaint->unit->block . ' - ' : '') . $complaint->unit->unit_number,
                ] : null,
                'assigned_to' => $complaint->assignee ? [
                    'id' => $complaint->assignee->id,
                    'name' => $complaint->assignee->name,
                    'email' => $complaint->assignee->email,
                ] : null,
                'resolution_notes' => $complaint->resolution_notes,
                'resolved_at' => $complaint->resolved_at?->format('Y-m-d H:i:s'),
                'created_at' => $complaint->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $complaint->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Create a new complaint
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'resident') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:plumbing,electrical,cleaning,security,parking,noise,elevator,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        // Verify unit belongs to user's tenant
        if ($request->unit_id) {
            $unit = Unit::where('id', $request->unit_id)
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
        }

        // Generate unique ticket number
        $ticketNumber = 'TKT-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $complaint = Complaint::create([
            'tenant_id' => $user->tenant_id,
            'raised_by' => $user->id,
            'unit_id' => $request->unit_id,
            'ticket_number' => $ticketNumber,
            'subject' => $request->subject,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint raised successfully',
            'data' => [
                'id' => $complaint->id,
                'ticket_number' => $complaint->ticket_number,
                'subject' => $complaint->subject,
                'status' => $complaint->status,
                'created_at' => $complaint->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }
}
