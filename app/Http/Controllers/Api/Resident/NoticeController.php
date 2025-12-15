<?php

namespace App\Http\Controllers\Api\Resident;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /**
     * Get all active notices for the authenticated resident
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

        // Get resident's unit IDs
        $unitIds = $user->units->pluck('id')->toArray();

        // Get notices that are:
        // 1. Published
        // 2. For the resident's tenant
        // 3. Currently active (not expired, publish date passed)
        // 4. Either for all units (target_units is null) or for resident's units
        $notices = Notice::where('tenant_id', $user->tenant_id)
            ->where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function ($query) use ($unitIds) {
                $query->whereNull('target_units')
                    ->orWhereJsonContains('target_units', $unitIds);
            })
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            })
            ->with(['creator:id,name'])
            ->orderBy('publish_date', 'desc')
            ->get()
            ->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'type' => $notice->type,
                    'priority' => $notice->priority,
                    'publish_date' => $notice->publish_date->format('Y-m-d'),
                    'expiry_date' => $notice->expiry_date?->format('Y-m-d'),
                    'created_by' => $notice->creator ? $notice->creator->name : null,
                    'views_count' => $notice->views_count,
                    'created_at' => $notice->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notices,
        ]);
    }

    /**
     * Get a specific notice and increment view count
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

        // Get resident's unit IDs
        $unitIds = $user->units->pluck('id')->toArray();

        $notice = Notice::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->where(function ($query) use ($unitIds) {
                $query->whereNull('target_units')
                    ->orWhereJsonContains('target_units', $unitIds);
            })
            ->with(['creator:id,name'])
            ->firstOrFail();

        // Increment view count
        $notice->incrementViews();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notice->id,
                'title' => $notice->title,
                'content' => $notice->content,
                'type' => $notice->type,
                'priority' => $notice->priority,
                'publish_date' => $notice->publish_date->format('Y-m-d'),
                'expiry_date' => $notice->expiry_date?->format('Y-m-d'),
                'created_by' => $notice->creator ? $notice->creator->name : null,
                'views_count' => $notice->views_count,
                'created_at' => $notice->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
