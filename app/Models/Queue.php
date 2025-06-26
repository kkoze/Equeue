<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'number',
        'dept',
        'status',
        'served_at',
        'completed_at'
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Generate reference number
    public static function generateReferenceNumber($dept)
    {
        $prefix = strtoupper(substr($dept, 0, 3));
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())
                    ->where('dept', $dept)
                    ->count() + 1;
        
        return $prefix . '-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // Get next queue number for department
    public static function getNextNumber($dept)
    {
        $lastQueue = self::where('dept', $dept)
                        ->whereDate('created_at', today())
                        ->orderBy('number', 'desc')
                        ->first();
        
        return $lastQueue ? $lastQueue->number + 1 : 1;
    }

    // Scope for waiting queues
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    // Scope for serving queues
    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }

    // Scope for today's queues
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Scope for specific department
    public function scopeDepartment($query, $dept)
    {
        return $query->where('dept', $dept);
    }

    // Mark as serving
    public function markAsServing()
    {
        $this->update([
            'status' => 'serving',
            'served_at' => now()
        ]);
    }

    // Mark as completed
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    // Mark as cancelled
    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }

    // Get waiting time in minutes
    public function getWaitingTimeAttribute()
    {
        if ($this->served_at) {
            return $this->created_at->diffInMinutes($this->served_at);
        }
        
        return $this->created_at->diffInMinutes(now());
    }

    // Get service time in minutes
    public function getServiceTimeAttribute()
    {
        if ($this->served_at && $this->completed_at) {
            return $this->served_at->diffInMinutes($this->completed_at);
        }
        
        return null;
    }
}