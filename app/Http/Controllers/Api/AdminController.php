<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Models\Update;
use App\Models\Finder;
use App\Models\LandExpert;
use App\Models\Surveyor;
use App\Models\ConsultationRequest;
use App\Http\Controllers\Controller;
use App\Models\ConsultationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [
            'updates' => Update::all(), // Example: Fetch all updates
            // Add more finder-specific data here
        ];
        
        return response()->json($data, 200);
    }

    public function getAllUsers(): JsonResponse
{
    // Fetch all users except the admin
    $users = User::where('is_admin', null)->get(); // Adjust the condition if you want to include/exclude certain users

    return response()->json([
        'users' => $users,
    ], 200);
}

public function getConsultationLogs(): JsonResponse
{
    // Fetch all consultation logs
    $consultationLogs = ConsultationLog::with('consultationRequest')->get();

    return response()->json([
        'consultation_logs' => $consultationLogs,
    ], 200);
}


    public function getAllUpdates(): JsonResponse
    {
        // Fetch all updates
        $updates = Update::all();
    
        return response()->json([
            'updates' => $updates,
        ], 200);
    }
    
    // Post updates or helpful information
    public function postUpdate(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Create a new update
        $update = Update::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Update posted successfully',
            'update' => $update,
        ], 201);
    }

    // Edit updates
    public function editUpdate(Request $request, $id)
    {
        $update = Update::find($id);
        if (!$update) {
            return response()->json(['message' => 'Update not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Update the existing post
        $update->title = $request->title;
        $update->content = $request->content;
        $update->save();

        return response()->json([
            'message' => 'Update edited successfully',
            'update' => $update,
        ], 200);
    }
    // Delete updates
    public function deleteUpdate($id)
    {
        $update = Update::find($id);
        if (!$update) {
            return response()->json(['message' => 'Update not found'], 404);
        }

        $update->delete();

        return response()->json([
            'message' => 'Update deleted successfully',
        ], 200);
    }

    public function terminateUser($id): JsonResponse
    {
        // Find the user by ID
        $user = User::find($id);
    
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
    
        // Retrieve all finders associated with this user
        $finders = Finder::where('user_id', $id)->get();
    
        // Delete related Consultation Requests for each finder
        foreach ($finders as $finder) {
            ConsultationRequest::where('finder_id', $finder->_id)->delete();
        }
    
        // Manually delete related documents in other collections
        Surveyor::where('user_id', $id)->delete();
        LandExpert::where('user_id', $id)->delete();
        Finder::where('user_id', $id)->delete();  // Delete all finders associated with the user
    
        // Now delete the user
        $user->delete();
    
        return response()->json([
            'message' => 'User terminated successfully.'
        ], 200);
    }
    
}
