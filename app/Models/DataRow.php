<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRow extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'date',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
