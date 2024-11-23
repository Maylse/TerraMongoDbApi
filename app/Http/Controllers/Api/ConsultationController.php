<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationLog;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{

    //FOR FINDERS
    public function getFinderRequests(Request $request): JsonResponse
    {
        // Fetch all consultation requests for the authenticated finder
        $requests = ConsultationRequest::where('finder_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['expert', 'surveyor']) // Load the related expert and surveyor data
            ->get();
    
        // Return the list of requests
        return response()->json([
            'requests' => $requests
        ], 200);
    }

    public function requestExpertConsultation(Request $request): JsonResponse
    {
         // Validate the incoming request data for experts
         $request->validate([
            'expert_id' => 'required|exists:users,id', // Ensure the user exists and is an expert
            'message' => 'required|string|max:500', // Limit message length
            'date' => 'required|date', // Validate the date field
            'time' => 'required|date_format:h:i A', // Ensure time is in 12-hour format (with AM/PM)
            'location' => 'required|string|max:255', // Ensure location is a string with max length
            'rate' => 'required|numeric|min:0', // Validate the rate field to be a number greater than or equal to 0
        ]);

        // Fetch the expert ID based on the user ID of the authenticated user
        $expert = User::where('user_type', 'expert')
                      ->where('id', $request->expert_id) // Assuming you have expert_id in the request
                      ->first();
    
        if (!$expert) {
            return response()->json([
                'message' => 'Expert not found.',
            ], 404);
        }
    
        // Create the consultation request for the expert
        $consultation = ConsultationRequest::create([
            'finder_id' => $request->user()->id, // Get the authenticated finder's ID
            'expert_id' => $expert->id, // Expert's ID from the retrieved user
            'message' => $request->message,
             'status' => 'pending',
             'date' => $request->date, // Save the date
             'time' => $this->formatTime($request->time), // Save the time in 12-hour format
             'location' => $request->location, // Save the location
             'rate' => $request->rate, // Save the rate
        ]);
    
        // Return success response
        return response()->json([
            'message' => 'Consultation request sent to expert successfully.',
            'consultation' => $consultation,
        ], 201);
    }
    
    public function requestSurveyorConsultation(Request $request): JsonResponse
    {
        // Validate the incoming request data for surveyors
        $request->validate([
            'expert_id' => 'required|exists:users,id', // Ensure the user exists and is an expert
            'message' => 'required|string|max:500', // Limit message length
            'date' => 'required|date', // Validate the date field
            'time' => 'required|date_format:h:i A', // Ensure time is in 12-hour format (with AM/PM)
            'location' => 'required|string|max:255', // Ensure location is a string with max length
            'rate' => 'required|numeric|min:0', // Validate the rate field to be a number greater than or equal to 0
        ]);
    
        // Fetch the surveyor ID based on the user ID of the authenticated user
        $surveyor = User::where('user_type', 'surveyor')
                        ->where('id', $request->surveyor_id) // Assuming you have surveyor_id in the request
                        ->first();
    
        if (!$surveyor) {
            return response()->json([
                'message' => 'Surveyor not found.',
            ], 404);
        }
    
        // Create the consultation request for the expert
        $consultation = ConsultationRequest::create([
            'finder_id' => $request->user()->id, // Get the authenticated finder's ID
            'surveyor_id' => $surveyor->id, // Expert's ID from the retrieved user
            'message' => $request->message,
             'status' => 'pending',
             'date' => $request->date, // Save the date
             'time' => $this->formatTime($request->time), // Save the time in 12-hour format
             'location' => $request->location, // Save the location
             'rate' => $request->rate, // Save the rate
        ]);
    
        // Return success response
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
    public function getExpertConsultationRequests(Request $request): JsonResponse{
    // Check if the authenticated user is an expert
    if ($request->user()->user_type !== 'expert') {
        return response()->json([
            'message' => 'Unauthorized. Only experts can access this resource.'
        ], 403);
    }
    // Fetch consultation requests where the expert_id matches the logged-in expert's ID
    $requests = ConsultationRequest::where('expert_id', $request->user()->id)
    ->where('status', 'pending')
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

    // Fetch consultation requests where the surveyor_id matches the logged-in surveyor's ID
    $requests = ConsultationRequest::where('surveyor_id', $request->user()->_id)
    ->where('status', 'pending')
    ->get();

    // Handle case when no requests are found
    if ($requests->isEmpty()) {
        return response()->json([
            'message' => 'No consultation requests found.'
        ], 404);
    }

    // Return the list of consultation requests
    return response()->json([
        'message' => 'Consultation requests retrieved successfully.',
        'requests' => $requests,
        'count' => $requests->count(),
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

    public function getConsultationLogs(Request $request): JsonResponse {
        // Get the authenticated user
        $user = $request->user();

        // Get the consultation logs for the user, filtering by their user type (expert or surveyor)
        $consultationLogs = ConsultationLog::where('user_id', $user->id)
            ->with('consultationRequest') // Eager load the related consultation request details
            ->get();

        // Check if consultation logs are found
        if ($consultationLogs->isEmpty()) {
            return response()->json(['message' => 'No consultation logs found for this user.'], 404);
        }

        // Return the consultation logs in JSON format
        return response()->json([
            'message' => 'Consultation logs retrieved successfully.',
            'consultation_logs' => $consultationLogs
        ], 200);
    }


}