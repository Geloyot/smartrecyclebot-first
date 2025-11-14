<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BinReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'bin_id',
        'fill_level',   // e.g. percentage (0–100)
        'status',       // e.g. 'LOW', 'MEDIUM', 'ALMOST FULL', 'FULL'
    ];

    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }
}
