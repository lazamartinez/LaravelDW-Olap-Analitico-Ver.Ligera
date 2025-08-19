<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class DynamicModel extends Model
{
    protected $guarded = [];
    protected $casts = [
        'schema' => 'array',
        'relations' => 'array'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function createPhysicalModel()
    {
        $tableName = "dynamic_{$this->slug}";
        
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                
                foreach ($this->schema as $field) {
                    $column = $table->{$field['type']}($field['name']);
                    
                    if ($field['nullable'] ?? false) {
                        $column->nullable();
                    }
                    
                    if ($field['unique'] ?? false) {
                        $column->unique();
                    }
                }
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
}