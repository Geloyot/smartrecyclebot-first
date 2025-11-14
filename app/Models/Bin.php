<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',     // Additional Info
        'type',     // Bin Type: Bio, Non-bio
        'notified_full',
        'last_fill_level',
        'last_full_fill_level',
    ];

    protected $casts = [
        'notified_full'         => 'boolean',
        'last_fill_level'       => 'float',
        'last_full_fill_level'  => 'float',
    ];

    public function readings()
    {
        return $this->hasMany(BinReading::class);
    }

    public function waste_objects()
    {
        return $this->hasMany(WasteObject::class);
    }
}
