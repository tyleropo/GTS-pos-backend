<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logChange($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logChange($model, 'updated');
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logChange($model, 'deleted');
    }

    /**
     * Log the model change to audit_logs table.
     */
    protected function logChange(Model $model, string $action): void
    {
        // Get the authenticated user
        $user = auth()->user();

        // Skip if no user is authenticated (e.g., seeding)
        if (!$user) {
            return;
        }

        // Get IP address
        $ipAddress = request()->ip();

        // Create audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'ip_address' => $ipAddress,
        ]);
    }
}
