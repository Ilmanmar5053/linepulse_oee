<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'severity',
        'status',
        'description',
        'record_id',
        'old_value',
        'new_value',
        'ip_address',
        'url',
        'user_agent',
        'execution_time_ms',
        'hash',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'execution_time_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit trail log entry with SHA256 cryptographic chain hashing
     */
    public static function record(
        string $action,
        string $module,
        ?string $description = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        $recordId = null,
        string $severity = 'info',
        string $status = 'SUCCESS',
        ?int $executionTimeMs = null
    ): self {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System / Auto-Process';
        $userRole = $user ? ($user->roles->first()?->display_name ?? $user->role ?? 'System') : 'System Admin';

        // Retrieve last log hash for cryptographic chaining (Tamper-evident ISO/BSSN standard)
        $lastLog = self::latest('id')->first();
        $prevHash = $lastLog ? ($lastLog->hash ?? 'GENESIS_BLOCK_OEE_AUDIT_TRAIL') : 'GENESIS_BLOCK_OEE_AUDIT_TRAIL';

        $ipAddress = Request::ip() ?? '127.0.0.1';
        $userAgent = Request::header('User-Agent') ?? 'CLI / Local Service';
        $currentUrl = Request::fullUrl() ?? '/';
        $timestamp = now()->toIso8601String();

        $hashPayload = json_encode([
            'prev' => $prevHash,
            'ts' => $timestamp,
            'user' => $userName,
            'action' => $action,
            'module' => $module,
            'rec_id' => (string)$recordId,
            'old' => $oldValue,
            'new' => $newValue,
            'ip' => $ipAddress,
        ]);
        $digitalHash = hash('sha256', $hashPayload);

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => strtoupper($action),
            'module' => $module,
            'severity' => strtolower($severity),
            'status' => strtoupper($status),
            'description' => $description,
            'record_id' => $recordId !== null ? (string)$recordId : null,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $ipAddress,
            'url' => substr($currentUrl, 0, 500),
            'user_agent' => substr($userAgent, 0, 1000),
            'execution_time_ms' => $executionTimeMs,
            'hash' => $digitalHash,
        ]);
    }

    /**
     * Compute field-by-field diff between old_value and new_value
     */
    public function getDiff(): array
    {
        $old = $this->old_value ?? [];
        $new = $this->new_value ?? [];

        if (!is_array($old)) $old = [];
        if (!is_array($new)) $new = [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $differences = [];

        foreach ($allKeys as $key) {
            // Ignore technical timestamp keys unless specifically altered
            if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'])) {
                continue;
            }

            $hasOld = array_key_exists($key, $old);
            $hasNew = array_key_exists($key, $new);
            $valOld = $hasOld ? $old[$key] : null;
            $valNew = $hasNew ? $new[$key] : null;

            if (!$hasOld && $hasNew) {
                $differences[] = [
                    'field' => $key,
                    'type' => 'ADDED',
                    'old' => null,
                    'new' => $valNew,
                ];
            } elseif ($hasOld && !$hasNew) {
                $differences[] = [
                    'field' => $key,
                    'type' => 'REMOVED',
                    'old' => $valOld,
                    'new' => null,
                ];
            } elseif ($valOld !== $valNew) {
                // If both are arrays/objects or scalar mismatch
                if (json_encode($valOld) !== json_encode($valNew)) {
                    $differences[] = [
                        'field' => $key,
                        'type' => 'MODIFIED',
                        'old' => $valOld,
                        'new' => $valNew,
                    ];
                }
            }
        }

        return $differences;
    }
}
