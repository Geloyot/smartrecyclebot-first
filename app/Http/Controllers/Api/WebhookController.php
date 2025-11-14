<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WasteObject;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receive(Request $request)
    {
        // simple API key check
        $apiKey = $request->header('X-Api-Key');
        if (!$apiKey || $apiKey !== config('services.devices.api_key')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'request_id' => 'nullable|string',
            'bin_id' => 'nullable|exists:bins,id',
            'classification' => 'required|string|max:100',
            'score' => 'nullable|numeric|min:0|max:1',
            'model_name' => 'nullable|string|max:100',
            'captured_at' => 'nullable|date',
        ]);

        try {
            // Create waste_object record
            $w = WasteObject::create([
                'bin_id' => $data['bin_id'] ?? null,
                'classification' => $data['classification'],
                'score' => $data['score'] ?? null,
                'model_name' => $data['model_name'] ?? null,
            ]);

            // Optional: create notification if score low or other logic
            if (!empty($w)) {
                // Example: notify admin if score < 0.40
                if ($w->score !== null && $w->score < 0.40) {
                    Notification::create([
                        'user_id' => null,
                        'type' => 'Classification',
                        'title' => 'Low confidence classification',
                        'message' => "Bin {$w->bin_id}: {$w->classification} (score {$w->score})",
                        'level' => 'warning',
                        'is_read' => false,
                    ]);
                }
            }

            return response()->json(['success' => true, 'id' => $w->id], 201);
        } catch (\Throwable $e) {
            Log::error('Webhook receive error: ' . $e->getMessage(), ['payload' => $request->all()]);
            return response()->json(['message' => 'Internal error'], 500);
        }
    }
}
