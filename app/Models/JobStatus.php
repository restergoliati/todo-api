<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStatus extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'type',
        'status',
        'progress',
        'message',
        'result',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}