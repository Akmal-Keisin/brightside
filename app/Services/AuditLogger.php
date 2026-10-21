<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Record an auditable action with the acting user and request context.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(
        string $action,
        Model $subject,
        array $oldValues = [],
        array $newValues = [],
        ?string $reason = null,
        ?User $actor = null,
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => $actor?->id ?? $request?->user()?->id,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
