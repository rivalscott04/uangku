<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_code',
        'price',
        'starts_at',
        'ends_at',
        'approved_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(?CarbonInterface $at = null): bool
    {
        $at = $at ?: now();

        if (! $this->approved_at) {
            return false;
        }

        return $at->between($this->starts_at, $this->ends_at);
    }
}


