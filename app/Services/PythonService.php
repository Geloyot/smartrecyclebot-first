<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

class PythonService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python_service.url');
        $this->timeout = (int) config('services.python_service.timeout', 10);
    }

    /**
     * Call /infer by sending an image file (multipart).
     * $file can be an instance of UploadedFile or local path string.
     */
    public function inferFromFile($file)
    {
        try {
            $http = Http::timeout($this->timeout)
                        ->retry(2, 100) // 2 retries, 100ms pause
                        ->withHeaders([
                            'Accept' => 'application/json',
                        ]);

            if (is_string($file)) {
                // local path
                $response = $http->attach('file', fopen($file, 'r'))->post($this->baseUrl . '/infer');
            } else {
                // UploadedFile from a request
                $response = $http->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                                 ->post($this->baseUrl . '/infer');
            }

            $response->throw(); // throw if 4xx/5xx
            return $response->json();
        } catch (RequestException $e) {
            // log and rethrow or return null/structured error
            Log::error('PythonService::inferFromFile failed', [
                'message' => $e->getMessage(),
                'url' => $this->baseUrl . '/infer',
            ]);
            return null;
        }
    }

    /**
     * GET /predict_latest
     */
    public function getLatestPrediction()
    {
        try {
            $response = Http::timeout($this->timeout)
                            ->retry(2, 100)
                            ->acceptJson()
                            ->get($this->baseUrl . '/predict_latest');

            $response->throw();
            return $response->json();
        } catch (RequestException $e) {
            Log::warning('PythonService::getLatestPrediction failed', ['err' => $e->getMessage()]);
            return null;
        }
    }
}
