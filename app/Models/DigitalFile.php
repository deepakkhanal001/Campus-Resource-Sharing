<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DigitalFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'digital_files';

    protected $fillable = [
        'resource_id',
        'file_path',
        'file_name',
        'file_type',
        'size',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
