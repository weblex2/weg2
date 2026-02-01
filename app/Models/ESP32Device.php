<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class ESP32Device extends Model
{
    protected $table= "esp32_devices";
    protected $fillable = [
        'device_id',
        'hostname',
        'ip_address',
        'mac_address',
        'interval',
        'last_seen',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Check if device is online (last seen within 2x interval)
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }

        $threshold = $this->interval * 2;
        return $this->last_seen->diffInSeconds(now()) < $threshold;
    }

    // Human readable interval
    public function intervalHuman(): string
    {
        $seconds = $this->interval;

        if ($seconds < 60) {
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            $minutes = round($seconds / 60);
            return "{$minutes}m";
        } elseif ($seconds < 86400) {
            $hours = round($seconds / 3600, 1);
            return "{$hours}h";
        } else {
            $days = round($seconds / 86400, 1);
            return "{$days}d";
        }
    }

    // Scope for active devices
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for online devices
    public function scopeOnline($query)
    {
        return $query->where('last_seen', '>', now()->subHours(24));
    }

    // Update last seen timestamp
    public function updateLastSeen(): void
    {
        $this->update(['last_seen' => now()]);
    }
}
