<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigcommerceController;


Route::get('/testsalida', [BigcommerceController::class, 'uploadFileCsv']);
Route::get('/testentrada', [BigcommerceController::class, 'downloadFileExcel']);
Route::get('/process-excel', [BigcommerceController::class, 'processExcelCsv']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});