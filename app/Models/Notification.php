<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',      // e.g. 'bin', 'user', 'system'
        'title',
        'message',
        'level',     // 'info', 'warning', 'error'
        'is_read',   // bool
    ];

    // Cast is_read to boolean
    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
