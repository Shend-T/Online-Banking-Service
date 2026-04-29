<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['full_name', 'email', 'password'])]
#[Hidden(['password'])]
class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
