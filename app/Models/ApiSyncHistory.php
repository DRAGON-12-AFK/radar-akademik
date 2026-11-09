<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiSyncHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'api_sync_histories';

    protected $fillable = [
        'endpoint',
        'filter_type',
        'identifier',
        'user_id',
        'status',
        'records_count',
        'duration_ms',
        'error_message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'records_count' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

