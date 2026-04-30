<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log($adminId, $action, $target = null, $ip = null)
    {
        AuditLog::create([
            'admin_id'   => $adminId,
            'action'     => $action,
            'target'     => $target,
            'ip_address' => $ip,
        ]);
    }
}