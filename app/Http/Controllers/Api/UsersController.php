<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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
   
       // Fetch users where the user_type is 'expert' along with their expert details
       $experts = User::where('user_type', 'expert')
           ->with('landExpert') // Eager load the correct relationship
           ->get();
   
       // Format the response to include the additional fields
       $formattedExperts = $experts->map(function($expert) {
           return [
               'id' => $expert->id,
               'name' => $expert->name,
               'email' => $expert->email,
               'certification_id' => $expert->landExpert->certification_id ?? null,
               'license_number' => $expert->landExpert->license_number ?? null,
               'pricing' => $expert->landExpert->pricing ?? null,
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

    // Fetch users where the user_type is 'surveyor' along with their surveyor details
    $surveyors = User::where('user_type', 'surveyor')
        ->with('surveyor') // Eager load the surveyor relationship
        ->get();

    // Format the response to include the additional fields
    $formattedSurveyors = $surveyors->map(function($surveyor) {
        return [
            'id' => $surveyor->id,
            'name' => $surveyor->name,
            'email' => $surveyor->email,
            'certification_id' => $surveyor->surveyor->certification_id ?? null,
            'license_number' => $surveyor->surveyor->license_number ?? null,
            'pricing' => $surveyor->surveyor->pricing ?? null,
        ];
    });

    // Return the list of surveyors
    return response()->json([
        'surveyors' => $formattedSurveyors
    ], 200);
}

}
