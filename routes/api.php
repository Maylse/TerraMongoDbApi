<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;



Route::middleware(['cors'])->group(function () {
   
    //ENTRY POINTS
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    //FORGOT RESET
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile/update', [AuthController::class, 'updateProfile']);

        //FOR FINDERS
        Route::get('/experts', [UsersController::class, 'getExperts']);
        Route::get('/surveyors', [UsersController::class, 'getSurveyors']); 

         //CONSULTATION REQUEST
         Route::get('/consultation/getAllRequests', [ConsultationController::class, 'getFinderRequests']);
         Route::post('/request-consultation/expert', [ConsultationController::class, 'requestExpertConsultation']);
         Route::post('/request-consultation/surveyor', [ConsultationController::class, 'requestSurveyorConsultation']);
         Route::put('/consultation/updateRequest/{id}', [ConsultationController::class, 'updateRequest']);
         Route::delete('/consultation/deleteRequest/{id}', [ConsultationController::class, 'deleteRequest']);
 
        //FOR EXPERTS
        Route::get('/consultation/requests/expert', [ConsultationController::class, 'getExpertConsultationRequests']);
        //FOR SURVEYORS
        Route::get('/consultation/requests/surveyor', [ConsultationController::class, 'getSurveyorConsultationRequests']);
        //FOR BOTH EXPERTS AND SURVEYORS
        Route::post('/consultation/accept/{id}', [ConsultationController::class, 'acceptRequest']);
        Route::post('/consultation/decline/{id}', [ConsultationController::class, 'declineRequest']);
    
    });
     //ADMIN ROUTES
     Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
        Route::get('/admin/getAllUsers', [AdminController::class, 'getAllUsers']); // Add this line
        Route::get('/admin/dashboard', [AdminController::class, 'index']);
        Route::get('/admin/getAllUpdates', [AdminController::class, 'getAllUpdates']);
        Route::post('/admin/postUpdate', [AdminController::class, 'postUpdate']);
        Route::put('/admin/editUpdate/{id}', [AdminController::class, 'editUpdate']);
        Route::delete('/admin/deleteUpdate/{id}', [AdminController::class, 'deleteUpdate']);
        //TERMINATE A USER
        Route::delete('/admin/deleteUser/{id}', [AdminController::class, 'terminateUser']);
    });
});

    




