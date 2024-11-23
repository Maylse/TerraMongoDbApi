<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Update; // Assuming you have an Update model
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [
            'updates' => Update::all()
        ];
        return response()->json($data, 200);
    }
}
