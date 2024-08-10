<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigcommerceController;
use App\Http\Controllers\ExcelController;


Route::get('/testsalida', [BigcommerceController::class, 'uploadFileCsv']);
Route::get('/testentrada', [BigcommerceController::class, 'downloadFileExcel']);

Route::post('/process-excel', [ExcelController::class, 'processExcel']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});