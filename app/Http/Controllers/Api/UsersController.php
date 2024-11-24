<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\LandExpert;
use App\Models\Surveyor;
use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
   // Fetch experts
   public function getExperts(Request $request): JsonResponse
{
    // Check if the authenticated user is a finder
    if ($request->user()->user_type !== 'finder') {
        return response()->json([
            'message' => 'Unauthorized. Only finders can access this resource.'
        ], 403);
    }

    // Fetch experts from the land_experts table that are referenced by user_id
    $landExperts = LandExpert::with('user') // Eager load the associated user data
        ->get();

    // Format the response to include expert details
    $formattedExperts = $landExperts->map(function($landExpert) {
        return [
            'id' => $landExpert->id,
            'name' => $landExpert->user->name,  // Access user info through the relationship
            'email' => $landExpert->user->email,
            'certification_id' => $landExpert->certification_id,
            'license_number' => $landExpert->license_number
        ];
    });

    // Return the list of experts
    return response()->json([
        'experts' => $formattedExperts
    ], 200);
}

   
    // Fetch surveyors
public function getSurveyors(Request $request): JsonResponse
{
    // Check if the authenticated user is a finder
    if ($request->user()->user_type !== 'finder') {
        return response()->json([
            'message' => 'Unauthorized. Only finders can access this resource.'
        ], 403);
    }

     // Fetch experts from the land_experts table that are referenced by user_id
     $surveyors = Surveyor::with('user') // Eager load the associated user data
     ->get();

    // Format the response to include the additional fields
    $formattedSurveyors = $surveyors->map(function($surveyor) {
        return [
            'id' => $surveyor->id,
            'name' => $surveyor->name,
            'email' => $surveyor->email,
            'certification_id' => $surveyor->surveyor->certification_id,
            'license_number' => $surveyor->surveyor->license_number
        ];
    });

    // Return the list of surveyors
    return response()->json([
        'surveyors' => $formattedSurveyors
    ], 200);
}

}
