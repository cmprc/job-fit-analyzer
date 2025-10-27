<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\AnalysisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Job routes
Route::apiResource('jobs', JobController::class);

// Candidate routes
Route::apiResource('candidates', CandidateController::class);

// Analysis routes
Route::get('analyses', [AnalysisController::class, 'index']);
Route::post('analyses', [AnalysisController::class, 'store']);
Route::post('analyses/analyze-all', [AnalysisController::class, 'analyzeAll']);
Route::get('analyses/{id}', [AnalysisController::class, 'show']);
Route::delete('analyses/{id}', [AnalysisController::class, 'destroy']);
