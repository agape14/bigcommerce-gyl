<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigcommerceController;

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

/**
 * @OA\Get(
 *     path="/api/test",
 *     summary="Test API",
 *     description="Returns a test message",
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="API test route works!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found"
 *     )
 * )
 */
// Route::get('test', [BigcommerceController::class,'generateAccessToken']);
Route::get('test', [BigcommerceController::class,'uploadFile2']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


