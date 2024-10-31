<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Update; // Assuming you have an Update model
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Retrieve different information based on user type
        if ($user->user_type === 'finder') {
            // Fetch info relevant to finders
            $data = [
                'updates' => Update::all(), // Example: Fetch all updates
                // Add more finder-specific data here
            ];
        } elseif ($user->user_type === 'expert') {
            // Fetch info relevant to experts
            $data = [
                'updates' => Update::all(), // Example: Fetch all updates
                // Add more expert-specific data here
            ];
        } elseif ($user->user_type === 'surveyor') {
            // Fetch info relevant to surveyors
            $data = [
                'updates' => Update::all(), // Example: Fetch all updates
                // Add more surveyor-specific data here
            ];
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($data, 200);
    }
}
