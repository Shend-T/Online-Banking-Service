<?php

namespace App\Models;

use App\Models\Account;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['account_id', 'type', 'amount', 'counterparty_account', 'balance_after'])]
class Transaction extends Model
{
    use HasFactory;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
