<?php

namespace App\Http\Controllers;

use App\Models\Bin;
use App\Models\BinReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BinController extends Controller
{
    public function binReadingRead(Request $request) {
        $validated = $request->validate([
            'bio' => 'required|numeric|min:0|max:100',
            'nonbio' => 'required|numeric|min:0|max:100',
        ]);

        DB::table('bin_readings')->insert([
            ['bin_id' => 1, 'fill_level' => $validated['bio'], 'created_at' => now(), 'updated_at' => now()],
            ['bin_id' => 2, 'fill_level' => $validated['nonbio'], 'created_at' => now(), 'updated_at' => now()],
        ]);

        return response()->json(['status' => 'saved']);
    }
}
