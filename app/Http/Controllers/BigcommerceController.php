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
       
         // Rutas y nombres de archivos
         $inputFilePath = storage_path('app/public/input_file/BIGCOMMERCE.xlsx');
        
         // Subir el archivo CSV a Zoho WorkDrive
         $serviceWorkdrive = new ZohoWorkdriveService();
         $upload_file = $serviceWorkdrive->uploadFile($outputFileName, env('ZOHO_WORKDRIVE_FOLDER_OUTPUT_CSV'), $outputFilePath);
 
         if ($upload_file['status'] == 'SUCCESS') {
             $resource_id = $upload_file['data'][0]['attributes']['resource_id'];
             $share_file = $serviceWorkdrive->createExternaLinks($resource_id, $outputFileName);
 
             if (!empty($share_file->data)) {
                 if (!empty($share_file->data->attributes)) {
                     $attributes = $share_file->data->attributes;
                     $link = $attributes->link;
                     // Aquí puedes manejar el enlace compartido
                 }
             }
         }
     }

    
    /**
     * @OA\Get(
     *     path="/api/downloadFileExcel",
     *     summary="Test API entrada",
     *     description="Download Excel File from Workdrive",
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
    public function downloadFileExcel()
    {
        $file_name = 'BIGCOMMERCE.xlsx'; // Aquí debes especificar el archivo XLSX
        $file_path_to_save = storage_path('app/public/input_file/');
        $serviceWorkdrive = new ZohoWorkdriveService();
        $respuesta = $serviceWorkdrive->downloadFile($file_name, env('ZOHO_WORKDRIVE_FOLDER_INPUT_XLS'), $file_path_to_save);
        var_dump($respuesta);
 

       /* aqui la logica para descargar el file **/
       
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
