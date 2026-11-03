<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resources';

    protected $fillable = [
        'title',
        'description',
        'type',
        'owner_id',
        'category_id',
        'availability_status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function digitalFiles(): HasMany
    {
        return $this->hasMany(DigitalFile::class);
    }

    public function borrowingRecords(): HasMany
    {
        return $this->hasMany(BorrowingRecord::class);
    }
}
