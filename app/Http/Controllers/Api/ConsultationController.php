<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationLog;
use App\Models\ConsultationRequest;
use App\Models\Finder;
use App\Models\LandExpert;
use App\Models\Surveyor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class ConsultationController extends Controller
{

    //FOR FINDERS
    public function getFinderRequests(Request $request): JsonResponse
    {

        // Authenticate user and check if they are a finder
    if (!$request->user()) {
        return response()->json([
            'message' => 'Unauthorized. No token provided or the token is invalid.'
        ], 401);
    }
    if ($request->user()->user_type !== 'finder') {
        return response()->json([
            'message' => 'Unauthorized. Only finders can access this resource.'
        ], 403);
    }
    
        // Fetch the finder's details using the authenticated user's ID
        $finder = Finder::where('user_id', $request->user()->id)->first();
        
        if (!$finder) {
            return response()->json([
                'message' => 'Finder not found.'
            ], 404);
        }
    
        // Fetch consultation requests where 'finder_id' matches the finder's '_id'
        $requests = ConsultationRequest::where('finder_id', $finder->_id)->get();
    
        // Return the list of consultation requests
        return response()->json([
            'requests' => $requests
        ], 200);

    }
    
    
    
    public function requestExpertConsultation(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'expert_id' => 'required|exists:land_experts,_id',
            'message' => 'required|string|max:500',
            'date' => 'required|date',
            'time' => 'required|date_format:h:i A',
            'location' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
        ]);
    
        // Check if the expert exists
        $expert = LandExpert::find($request->expert_id);
        if (!$expert) {
            return response()->json(['message' => 'Expert not found.'], 404);
        }
    
        // Fetch the authenticated finder
        $finder = Finder::where('user_id', $request->user()->id)->first();
        if (!$finder) {
            return response()->json(['message' => 'Finder profile not found.'], 404);
        }
    
        // Create the consultation request
        $consultation = ConsultationRequest::create([
            'finder_id' => $finder->_id,  // Use finder's '_id'
            'expert_id' => $expert->_id,
            'finder_name' => $finder->name,
            'message' => $request->message,
            'status' => 'pending',
            'date' => $request->date,
            'time' => $this->formatTime($request->time),
            'location' => $request->location,
            'rate' => $request->rate,
        ]);
    
        return response()->json([
            'message' => 'Consultation request sent to expert successfully.',
            'consultation' => $consultation,
        ], 201);
    }

    public function requestSurveyorConsultation(Request $request): JsonResponse
{
    // Validate the incoming request data
    $request->validate([
        'surveyor_id' => 'required|exists:surveyors,_id',
        'message' => 'required|string|max:500',
        'date' => 'required|date',
        'time' => 'required|date_format:h:i A',
        'location' => 'required|string|max:255',
        'rate' => 'required|numeric|min:0',
    ]);

    // Check if the surveyor exists
    $surveyor = Surveyor::find($request->surveyor_id);
    if (!$surveyor) {
        return response()->json(['message' => 'Surveyor not found.'], 404);
    }

    // Fetch the authenticated finder
    $finder = Finder::where('user_id', $request->user()->id)->first();
    if (!$finder) {
        return response()->json(['message' => 'Finder profile not found.'], 404);
    }

    // Create the consultation request
    $consultation = ConsultationRequest::create([
        'finder_id' => $finder->_id,
        'surveyor_id' => $surveyor->_id,
        'finder_name' => $finder->name,
        'message' => $request->message,
        'status' => 'pending',
        'date' => $request->date,
        'time' => $this->formatTime($request->time),
        'location' => $request->location,
        'rate' => $request->rate,
    ]);

    return response()->json([
        'message' => 'Consultation request sent to surveyor successfully.',
        'consultation' => $consultation,
    ], 201);
}
    //FORMAT TIME
    protected function formatTime($time)
        {
            try {
                // Convert the time to a 12-hour format (with AM/PM)
                $formattedTime = \Carbon\Carbon::createFromFormat('h:i A', $time)->format('g:i A');
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid time format'], 400);
            }
            
            return $formattedTime;
        }

    //UPDATE 
    public function updateRequest(Request $request, $id): JsonResponse
    {
        // Authenticate and validate user type
        if (!$request->user() || $request->user()->user_type !== 'finder') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
    
        // Fetch the finder document using the authenticated user's ID
        $finder = Finder::where('user_id', $request->user()->id)->first();
    
        if (!$finder) {
            return response()->json(['message' => 'Finder not found.'], 404);
        }
    
        // Find the consultation request and ensure it belongs to the authenticated finder
        $consultationRequest = ConsultationRequest::where('_id', $id)
            ->where('finder_id', $finder->_id)  // Ensure this request belongs to the finder
            ->first();
    
        if (!$consultationRequest) {
            return response()->json(['message' => 'Consultation request not found or unauthorized.'], 404);
        }
    
        // Validate the incoming request data
        $request->validate([
            'message' => 'required|string|max:500',
            'date' => 'required|date',
            'time' => 'required|date_format:h:i A',
            'location' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
        ]);
    
        // Check if the status is still pending
        if ($consultationRequest->status !== 'pending') {
            return response()->json(['message' => 'You can only edit requests with a pending status.'], 403);
        }
    
        // Update the fields
        $consultationRequest->message = $request->message;
        $consultationRequest->date = $request->date;
        $consultationRequest->time = $this->formatTime($request->time);
        $consultationRequest->location = $request->location;
        $consultationRequest->rate = $request->rate;
    
        // Save the updated consultation request
        try {
            $consultationRequest->save();
            return response()->json([
                'message' => 'Consultation request updated successfully.',
                'request' => $consultationRequest,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the request.'], 500);
        }
    }
    
    
    public function deleteRequest(Request $request, $id): JsonResponse
    {
        // Authenticate and validate user type
        if (!$request->user() || $request->user()->user_type !== 'finder') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
    
        // Fetch the finder document using the authenticated user's ID
        $finder = Finder::where('user_id', $request->user()->id)->first();
    
        if (!$finder) {
            return response()->json(['message' => 'Finder not found.'], 404);
        }
    
        // Find the consultation request and ensure it belongs to the authenticated finder
        $consultationRequest = ConsultationRequest::where('_id', $id)
            ->where('finder_id', $finder->_id)  // Verify ownership
            ->first();
    
        if (!$consultationRequest) {
            return response()->json(['message' => 'Consultation request not found or unauthorized.'], 404);
        }
    
        // Check if the status is still pending
        if ($consultationRequest->status !== 'pending') {
            return response()->json(['message' => 'You can only delete requests with a pending status.'], 403);
        }
    
        // Delete the consultation request
        try {
            $consultationRequest->delete();
            return response()->json(['message' => 'Consultation request deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the request.'], 500);
        }
    }
    
    //FOR EXPERTS
    public function getExpertConsultationRequests(Request $request): JsonResponse

    {

        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized. No token provided or the token is invalid.'
            ], 401);
        }

        // Check if the authenticated user is an expert
        if ($request->user()->user_type !== 'expert') {
            return response()->json([
                'message' => 'Unauthorized. Only experts can access this resource.'
            ], 403);
        }
    
        // Fetch the expert's ID from the 'land_experts' collection based on the logged-in user
        $landExpert = LandExpert::where('user_id', $request->user()->id)->first();
    
        if (!$landExpert) {
            return response()->json([
                'message' => 'Expert not found.'
            ], 404);
        }
    
        // Fetch consultation requests where the expert_id matches the expert's _id in the land_experts collection
        $requests = ConsultationRequest::where('expert_id', $landExpert->_id)
                                        ->get();
    
        // Return the list of consultation requests
        return response()->json([
            'requests' => $requests
        ], 200);
    }
    //FOR SURVEYORS
    public function getSurveyorConsultationRequests(Request $request): JsonResponse
{
    // Check if the user is authenticated
    if (!$request->user()) {
        return response()->json([
            'message' => 'Unauthorized. No token provided or the token is invalid.'
        ], 401);
    }

    // Check if the authenticated user is a surveyor
    if ($request->user()->user_type !== 'surveyor') {
        return response()->json([
            'message' => 'Unauthorized. Only surveyors can access this resource.'
        ], 403);
    }

   // Fetch the expert's ID from the 'land_experts' collection based on the logged-in user
   $surveyor = Surveyor::where('user_id', $request->user()->id)->first();
    

    // Fetch consultation requests where the expert_id matches the expert's _id in the land_experts collection
    $requests = ConsultationRequest::where('surveyor_id', $surveyor->_id)
    ->get();

  
    // Handle case when no requests are found
    if ($requests->isEmpty()) {
        return response()->json([
            'message' => 'No consultation requests found.'
        ], 404);
    }

     // Return the list of consultation requests
     return response()->json([
        'requests' => $requests
    ], 200);
}


//BOTH SURVEYOR AND LAND EXPERT

public function acceptRequest(Request $request, $id): JsonResponse
{
    return $this->handleRequestAction($request, $id, 'accepted');
}

public function declineRequest(Request $request, $id): JsonResponse
{
    return $this->handleRequestAction($request, $id, 'declined');
}

// Shared logic for accepting or declining requests
private function handleRequestAction(Request $request, $id, string $action): JsonResponse
{
    // Check if the user is authenticated
    if (!$request->user()) {
        return response()->json([
            'message' => 'Unauthorized. No token provided or the token is invalid.'
        ], 401);
    }

    // Validate the request
    $request->validate([
        'response_message' => 'nullable|string|max:500',
    ]);

    // Determine user type and fetch corresponding surveyor or expert data
    $user = $request->user();
    if ($user->user_type === 'surveyor') {
        $profile = Surveyor::where('user_id', $user->id)->first();
        $roleField = 'surveyor_id';
    } elseif ($user->user_type === 'expert') {
        $profile = LandExpert::where('user_id', $user->id)->first();
        $roleField = 'expert_id';
    } else {
        return response()->json(['message' => 'Unauthorized. Only experts or surveyors can access this resource.'], 403);
    }

    // Ensure the profile exists
    if (!$profile) {
        return response()->json(['message' => ucfirst($user->user_type) . ' profile not found.'], 404);
    }

    // Find the consultation request belonging to the authenticated expert/surveyor
    $consultationRequest = ConsultationRequest::where('_id', $id)
        ->where($roleField, $profile->_id)
        ->first();

    // Check if the consultation request exists
    if (!$consultationRequest) {
        return response()->json(['message' => 'Consultation request not found or unauthorized.'], 404);
    }

    // Update consultation request status
    $consultationRequest->status = $action;
    $consultationRequest->response_message = $request->input('response_message');
    $consultationRequest->save();

    // Create a log entry with additional fields
    ConsultationLog::create([
        'consultation_request_id' => $consultationRequest->id,
        'user_id' => $user->id,
        'status' => $action,
        'response_message' => $request->input('response_message'),
        'message' => $consultationRequest->message,
        'date' => $consultationRequest->date,
        'time' => $consultationRequest->time,
        'location' => $consultationRequest->location,
        'rate' => $consultationRequest->rate,
    ]);

    return response()->json([
        'message' => "Consultation request {$action} successfully.",
        'consultation' => $consultationRequest,
    ], 200);
}


}
