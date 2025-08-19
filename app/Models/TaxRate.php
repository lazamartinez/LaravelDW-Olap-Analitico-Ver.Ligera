<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'description',
        'is_active'
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean'
    ];
}