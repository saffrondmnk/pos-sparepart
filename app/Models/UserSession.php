<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->logout_at) {
            $endTime = now();
        } else {
            $endTime = $this->logout_at;
        }

        $diff = $this->login_at->diff($endTime);
        
        $parts = [];
        if ($diff->h > 0) {
            $parts[] = $diff->h . 'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . 'm';
        }
        if ($diff->s > 0 || empty($parts)) {
            $parts[] = $diff->s . 's';
        }

        return implode(' ', $parts);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}