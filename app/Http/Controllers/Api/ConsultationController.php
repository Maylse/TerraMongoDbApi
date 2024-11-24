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
        // Authenticate and validate user type
        if (!$request->user() || $request->user()->user_type !== 'finder') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
    
        // Fetch the finder document using user_id
        $finder = Finder::where('user_id', $request->user()->id)->first();
    
        if (!$finder) {
            return response()->json(['message' => 'Finder not found.'], 404);
        }
    
        // Fetch consultation requests where 'finder_id' matches the finder's '_id'
        $requests = ConsultationRequest::where('finder_id', $finder->_id)->get();
    
        return response()->json(['requests' => $requests], 200);
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
        // Validate the incoming request data
        $request->validate([
            'message' => 'required|string|max:500', // Limit message length
            'date' => 'required|date', // Ensure date is valid
            'time' => 'required|date_format:h:i A', // Ensure time is in 12-hour format (with AM/PM)
            'location' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0', // Ensure rate is a valid number and non-negative
        ]);
    
        // Find the consultation request by ID and verify it belongs to the authenticated user
        $consultationRequest = ConsultationRequest::where('id', $id)
            ->where('finder_id', $request->user()->id) // Ensure the request belongs to the authenticated finder
            ->first();
    
        if (!$consultationRequest) {
            return response()->json(['message' => 'Consultation request not found or unauthorized.'], 404);
        }
    
        // Check if the status is still pending
        if ($consultationRequest->status !== 'pending') {
            return response()->json(['message' => 'You can only edit requests with a pending status.'], 403);
        }
    
        // Update the fields
        $consultationRequest->message = $request->message;
        $consultationRequest->date = $request->date;
        $consultationRequest->time = $this->formatTime($request->time); // Ensure time is properly formatted
        $consultationRequest->location = $request->location; // Save the location
        $consultationRequest->rate = $request->rate;
    
        // Save the updated consultation request
        try {
            $consultationRequest->save();
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the request.'], 500);
        }
    
        return response()->json([
            'message' => 'Consultation request updated successfully.',
            'request' => $consultationRequest,
        ], 200);
    }
    
    public function deleteRequest(Request $request, $id): JsonResponse
    {
        // Find the consultation request by ID
        $consultationRequest = ConsultationRequest::where('id', $id)
            ->where('finder_id', $request->user()->id) // Ensure the request belongs to the authenticated finder
            ->first();
    
        if (!$consultationRequest) {
            return response()->json(['message' => 'Consultation request not found or unauthorized.'], 404);
        }
    
        // Check if the status is still pending
        if ($consultationRequest->status !== 'pending') {
            return response()->json(['message' => 'You can only delete requests with a pending status.'], 403);
        }
    
        // Delete the request
        $consultationRequest->delete();
    
        return response()->json(['message' => 'Consultation request deleted successfully.'], 200);
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
public function acceptRequest(Request $request, $id): JsonResponse {
    // Validate the request
    $request->validate([
        'response_message' => 'nullable|string|max:500',
    ]);

    // Find the consultation request
    $consultationRequest = ConsultationRequest::findOrFail($id);

    // Check if the authenticated user is authorized to accept the request
    $user = $request->user();
    if (($user->user_type === 'expert' && $consultationRequest->expert_id === $user->id) ||
        ($user->user_type === 'surveyor' && $consultationRequest->surveyor_id === $user->id)) {

        // Update consultation request status
        $consultationRequest->status = 'accepted';
        $consultationRequest->response_message = $request->input('response_message');
        $consultationRequest->save();

        // Create a log entry with additional fields
        ConsultationLog::create([
            'consultation_request_id' => $consultationRequest->id,
            'user_id' => $user->id,
            'status' => 'accepted',
            'response_message' => $request->input('response_message'),
            'message' => $consultationRequest->message,       // Pass consultation request data
            'date' => $consultationRequest->date,
            'time' => $consultationRequest->time,
            'location' => $consultationRequest->location,
            'rate' => $consultationRequest->rate,
        ]);

        return response()->json([
            'message' => 'Consultation request accepted successfully.',
            'consultation' => $consultationRequest,
        ], 200);
    }

    return response()->json(['message' => 'Unauthorized action.'], 403);
}

public function declineRequest(Request $request, $id): JsonResponse {
    // Validate the request
    $request->validate([
        'response_message' => 'nullable|string|max:500',
    ]);

    // Find the consultation request
    $consultationRequest = ConsultationRequest::findOrFail($id);

    // Check if the authenticated user is authorized to decline the request
    $user = $request->user();
    if (($user->user_type === 'expert' && $consultationRequest->expert_id === $user->id) ||
        ($user->user_type === 'surveyor' && $consultationRequest->surveyor_id === $user->id)) {

        // Update consultation request status
        $consultationRequest->status = 'declined';
        $consultationRequest->response_message = $request->input('response_message');
        $consultationRequest->save();

        // Create a log entry with additional fields
        ConsultationLog::create([
            'consultation_request_id' => $consultationRequest->id,
            'user_id' => $user->id,
            'status' => 'declined',
            'response_message' => $request->input('response_message'),
            'message' => $consultationRequest->message,       // Pass consultation request data
            'date' => $consultationRequest->date,
            'time' => $consultationRequest->time,
            'location' => $consultationRequest->location,
            'rate' => $consultationRequest->rate,
        ]);

        return response()->json([
            'message' => 'Consultation request declined successfully.',
            'consultation' => $consultationRequest,
        ], 200);
    }

    return response()->json(['message' => 'Unauthorized action.'], 403);
}

}
