<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoOAuthService;
use App\Services\ZohoWorkdriveService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;



class BigcommerceController extends Controller
{

/**
     * @OA\Get(
     *     path="/api/uploadFileCsv",
     *     summary="Test API",
     *     description="Upload CSV File to Workdrive",
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

     public function uploadFileCsv()
     {
       
        $file_name = 'testsubidacsv.csv';   /* nombre archivo csv**/ 
        $file_path = storage_path('app/public/output_file/' . $file_name);
        $serviceWorkdrive = new ZohoWorkdriveService();
        $upload_file = $serviceWorkdrive->uploadFile($file_name, env('ZOHO_WORKDRIVE_FOLDER_OUTPUT_CSV'), $file_path);
        if ($upload_file['status'] == 'SUCCESS') {
                        $resource_id = $upload_file['data'][0]['attributes']['resource_id'];
                        $share_file = $serviceWorkdrive->createExternaLinks($resource_id, $file_name);

                        if (!empty($share_file->data)) {
                            if (!empty($share_file->data->attributes)) {
                                $attributes = $share_file->data->attributes;
                                $link = $attributes->link;


                            }
                        }
                   //return ResponseHelper::error($th->getMessage(), 400);

        }
     }

    
    /**
     * @OA\Get(
     *     path="/api/downloadFileExcel",
     *     summary="Download Excel File from Workdrive",
     *     description="Download Excel File from Workdrive",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="archivo descargado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function downloadFileExcel()
    {
        //$file_name = 'BIGCOMMERCE.xlsx'; //archivo a descargar
        $file_name = 'testbajada.xlsx'; //nombre archivo xls
        $file_path_to_save = storage_path('app/public/input_file/');
        $serviceWorkdrive = new ZohoWorkdriveService();
        $respuesta = $serviceWorkdrive->downloadFile($file_name, env('ZOHO_WORKDRIVE_FOLDER_INPUT_XLS'), $file_path_to_save);
 
    }


    /**
     * @OA\Get(
     *     path="/api/process-excel",
     *     summary="Test API",
     *     description="Process Excel file and convert to CSV format",
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

     public function processExcelCsv()
     {
       
         // Rutas y nombres de archivos
         $inputFilePath = storage_path('app/public/input_file/BIGCOMMERCE.xlsx');
         $outputFileName = 'outputcvs.csv';
         $outputFilePath = storage_path('app/public/output_file/' . $outputFileName);
         // Procesar el archivo Excel antes de subirlo
         $this->processExcelFile($inputFilePath, $outputFilePath, 'Sheet1', 540); 
     }
 
     private function processExcelFile($inputPath, $outputPath, $sheet, $columns)
     {
         // Crear un buffer para capturar la salida del comando
         $buffer = new BufferedOutput();
 
         // Ejecutar el comando Artisan para procesar el archivo Excel
         $exitCode = Artisan::call('excel:process', [
             'path' => $inputPath,
             'outputCsv' => $outputPath,
             'sheet' => $sheet,
             'columns' => $columns,
         ], $buffer);
 
         if ($exitCode !== 0) {
             throw new \Exception('Hubo un problema al procesar el archivo Excel.');
         }
     }


}
