<?php

namespace App\Http\Controllers;

use App\Models\WasteObject;
use App\Services\PythonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WasteObjectController extends Controller
{
    protected PythonService $python;

    protected function pythonBase(): string
    {
        return rtrim(config('services.python_service.url') ?? env('PYTHON_SERVICE_URL', 'http://127.0.0.1:8001'), '/');
    }

    protected function pythonHeaders(): array
    {
        $h = ['Accept' => 'application/json'];
        if ($token = env('PYTHON_SERVICE_TOKEN')) {
            $h['X-Internal-Token'] = $token;
        }
        return $h;
    }

    public function __construct(PythonService $python)
    {
        $this->python = $python;
    }

    // quick index for review
    public function index(Request $request)
    {
        $query = WasteObject::query();

        if ($request->filled('bin_id')) {
            $query->where('bin_id', $request->bin_id);
        }
        if ($request->filled('classification')) {
            $query->where('classification', $request->classification);
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json($results);
    }

    public function cameraStart(Request $request)
    {
        try {
            $resp = Http::withHeaders($this->pythonHeaders())
                ->timeout(15)
                ->post($this->pythonBase() . '/camera/start');

            if ($resp->successful()) {
                return response()->json(['message' => $resp->json()['message'] ?? 'Camera started']);
            }

            Log::warning('cameraStart non-200', ['status' => $resp->status(), 'body' => $resp->body()]);
            return response()->json(['message' => 'Unable to start camera. Please try again.'], 500);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('cameraStart connection failed', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Detection service is not available. Please ensure it is running.'], 503);

        } catch (\Exception $e) {
            Log::error('cameraStart exception', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Unable to connect to camera. Please try again.'], 500);
        }
    }

    public function cameraStop(Request $request)
    {
        try {
            $resp = Http::withHeaders($this->pythonHeaders())
                ->timeout(10)
                ->post($this->pythonBase() . '/camera/stop');

            if ($resp->successful()) {
                return response()->json(['message' => $resp->json()['message'] ?? 'Camera stopped']);
            }

            Log::warning('cameraStop non-200', ['status' => $resp->status(), 'body' => $resp->body()]);
            return response()->json(['message' => 'Camera stop failed'], 500);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Service is down/unreachable - camera is effectively stopped
            Log::info('cameraStop - service unreachable, treating as stopped', ['err' => $e->getMessage()]);
            return response()->json([
                'message' => 'Camera stopped (detection service offline)',
                'warning' => 'Detection service is not running'
            ], 200); // Return 200 since the goal (stop camera) is achieved

        } catch (\Exception $e) {
            Log::error('cameraStop exception', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Unexpected error stopping camera'], 500);
        }
    }

    public function cameraStatus()
    {
        try {
            $resp = Http::withHeaders($this->pythonHeaders())
                ->timeout(5)
                ->get($this->pythonBase() . '/camera/status');

            if ($resp->successful()) {
                // pass through the python status object
                return response()->json($resp->json());
            }

            Log::warning('cameraStatus non-200', ['status' => $resp->status(), 'body' => $resp->body()]);
            return response()->json(['running' => false, 'last_result' => null], 503);
        } catch (\Exception $e) {
            Log::error('cameraStatus exception', ['err' => $e->getMessage()]);
            return response()->json(['running' => false, 'last_result' => null], 503);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bin_id' => 'nullable|exists:bins,id',
            'classification' => 'required|string|max:100',
            'score' => 'nullable|numeric|min:0|max:1',
            'model_name' => 'nullable|string|max:100',
        ]);

        $waste = WasteObject::create($data);

        return response()->json([
            'success' => true,
            'data' => $waste,
        ], 201);
    }


    public function detect(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
            'bin_id' => 'nullable|exists:bins,id', // optional if you want to attach to a bin
        ]);

        $image = $request->file('image');

        // prefer config/services.php entry; fallback to env for convenience
        $base = config('services.python_service.url') ?? env('PYTHON_SERVICE_URL', 'http://127.0.0.1:8001');
        // endpoint: use /predict if your service exposes that; change to /infer if needed
        $endpoint = rtrim($base, '/') . '/predict';

        // optional internal token for security (set PYTHON_SERVICE_TOKEN in your .env)
        $headers = [
            'Accept' => 'application/json',
        ];
        if ($token = env('PYTHON_SERVICE_TOKEN')) {
            $headers['X-Internal-Token'] = $token;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout((int) config('services.python_service.timeout', env('PYTHON_SERVICE_TIMEOUT', 30)))
                ->retry(2, 100) // retry twice with 100ms pause
                ->attach('file', fopen($image->getRealPath(), 'r'), $image->getClientOriginalName())
                ->post($endpoint);
        } catch (\Exception $e) {
            Log::error('Python service request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Detection service unreachable');
        }

        if (! $response->successful()) {
            Log::warning('Python service returned non-200', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $endpoint,
            ]);
            abort(500, 'Detection service error');
        }

        $data = $response->json();

        // Expected minimal shape from Python: ['classification' => 'Biodegradable', 'score' => 0.92]
        // Normalize different shapes if necessary
        $classification = $data['classification'] ?? data_get($data, 'detections.0.classification') ?? null;
        $score = isset($data['score']) ? floatval($data['score']) : (float) (data_get($data, 'detections.0.score') ?? 0.0);
        $modelName = $data['model_name'] ?? data_get($data, 'model_name') ?? null;

        if (! $classification) {
            Log::error('Python response missing classification', ['response' => $data]);
            abort(500, 'Invalid detection response');
        }

        // persist to DB (re-using your store format)
        $waste = WasteObject::create([
            'bin_id' => $request->input('bin_id'),
            'classification' => $classification,
            'score' => $score,
            'model_name' => $modelName,
        ]);

        // Optionally broadcast the new result to UI (Livewire/pusher) here, or dispatch a job:
        // Example: dispatch(new \App\Jobs\ProcessDetectionResult($waste, $data));
        // For immediate return to client:
        return response()->json([
            'success' => true,
            'data' => $waste,
            'raw' => $data, // optional: include full python response for debugging
        ], 201);
    }

    public function show($id)
    {
        $wasteObject = WasteObject::findOrFail($id);

        return view('classifications.show', compact('wasteObject'));
    }
}
