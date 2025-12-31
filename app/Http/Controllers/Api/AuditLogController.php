<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with('user:id,first_name,last_name,email')
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->action, function ($q, $action) {
                $q->where('action', $action);
            })
            ->when($request->model_type, function ($q, $modelType) {
                $q->where('model_type', $modelType);
            })
            ->when($request->search, function ($q, $term) {
                $q->where(function ($query) use ($term) {
                    $query->where('action', 'like', "%{$term}%")
                        ->orWhere('model_type', 'like', "%{$term}%")
                        ->orWhere('ip_address', 'like', "%{$term}%");
                });
            })
            ->when($request->date_from, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        return response()->json(
            $query->latest()->paginate($request->integer('per_page', 50))
        );
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        return response()->json($auditLog->load('user:id,first_name,last_name,email'));
    }
}
