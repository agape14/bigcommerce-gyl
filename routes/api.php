<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigcommerceController;


Route::get('/uploadfilecsv', [BigcommerceController::class, 'uploadFileCsv']);
Route::get('/descargafileexcel', [BigcommerceController::class, 'downloadFileExcel']);
Route::get('/process-excel', [BigcommerceController::class, 'processExcelCsv']);
Route::get('/process-bigcommerce', [BigcommerceController::class, 'processBigcommerce']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});