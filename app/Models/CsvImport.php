<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    protected $fillable = [
        'filename',
        'checksum',
        'total_rows',
        'new_rows',
        'updated_rows',
        'removed_rows',
        'unchanged_rows',
        'summary',
        'status',
        'progress',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'progress' => 'integer',
        ];
    }
}
