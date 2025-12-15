<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Resident\DashboardController;
use App\Http\Controllers\Api\Resident\InvoiceController;
use App\Http\Controllers\Api\Resident\ComplaintController;
use App\Http\Controllers\Api\Resident\NoticeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Resident Mobile App
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/resident/dashboard', [DashboardController::class, 'index']);
    
    // Invoices
    Route::get('/resident/invoices', [InvoiceController::class, 'index']);
    Route::get('/resident/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/resident/invoices/{id}/pay', [InvoiceController::class, 'markAsPaid']);
    
    // Complaints
    Route::get('/resident/complaints', [ComplaintController::class, 'index']);
    Route::get('/resident/complaints/{id}', [ComplaintController::class, 'show']);
    Route::post('/resident/complaints', [ComplaintController::class, 'store']);
    
    // Notices
    Route::get('/resident/notices', [NoticeController::class, 'index']);
    Route::get('/resident/notices/{id}', [NoticeController::class, 'show']);
    
    // Profile
    Route::get('/resident/profile', [AuthController::class, 'profile']);
    Route::put('/resident/profile', [AuthController::class, 'updateProfile']);
    
    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

