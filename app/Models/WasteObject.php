<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteObject extends Model
{
    protected $fillable = [
        'bin_id',
        'classification',
        'score',
        'model_name',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    // Relationship to Bin
    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }
}
