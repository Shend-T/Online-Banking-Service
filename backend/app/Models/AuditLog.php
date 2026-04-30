<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['admin_id', 'action', 'target', 'ip_address'])]
class AuditLog extends Model
{
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}