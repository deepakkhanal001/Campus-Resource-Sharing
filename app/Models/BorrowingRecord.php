<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'borrowing_records';

    protected $fillable = [
        'resource_id',
        'requester_id',
        'owner_id',
        'status',
        'request_message',
        'rejection_reason',
        'pickup_date',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
