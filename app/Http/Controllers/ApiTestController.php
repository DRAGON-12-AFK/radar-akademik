<?php

namespace App\Http\Controllers;

use App\Services\UnujaApiService;
use Illuminate\Http\JsonResponse;

class ApiTestController extends Controller
{
    protected UnujaApiService $unujaApi;

    public function __construct(UnujaApiService $unujaApi)
    {
        $this->unujaApi = $unujaApi;
    }

    /**
     * Endpoint lokal untuk menguji koneksi dan otorisasi API UNUJA.
     * URL: /api-test
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->unujaApi->authorize();

        return response()->json([
            'is_configured' => $this->unujaApi->isConfigured(),
            'target_url' => config('services.unuja.base_url') . '/portal/data/authorize',
            'result' => $result,
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }
}
