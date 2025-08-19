<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'requires_approval'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_approval' => 'boolean'
    ];
}