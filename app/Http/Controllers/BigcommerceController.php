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
     *     tags={"Bigcommerce"},
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
    *     tags={"Bigcommerce"},
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
     *     tags={"Bigcommerce"},
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
        try {
            // Rutas y nombres de archivos
            $inputFilePath = storage_path('app/public/input_file/BIGCOMMERCE.xlsx');
            $outputFileName = 'BIGCOMME_11.csv';
            $outputFilePath = storage_path('app/public/output_file/' . $outputFileName);
    
            // Capturar tiempo de inicio
            $startTime = now();
    
            // Procesar el archivo Excel antes de subirlo
            $this->processExcelFile($inputFilePath, $outputFilePath, $startTime);
    
            // Capturar tiempo de finalización
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
    
            // Retornar respuesta JSON con estado 200 y detalles del procesamiento
            return response()->json([
                'status' => 'success',
                'message' => 'Archivo procesado correctamente.',
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'duration_minutes' => $duration
            ], 200);
        } catch (\Exception $e) {
            // Capturar tiempo de finalización en caso de error
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
    
            // Retornar respuesta JSON con estado 500 y detalles del error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'duration_minutes' => $duration
            ], 500);
        }
     }
 
     private function processExcelFile($inputPath, $outputPath, $startTime)
     {
        // Crear un buffer para capturar la salida del comando
        $buffer = new BufferedOutput();

        // Ejecutar el comando Artisan para procesar el archivo Excel
        $exitCode = Artisan::call('excel:processspout:spout', [
            'path' => $inputPath,
            'outputCsv' => $outputPath,
        ], $buffer);

        if ($exitCode !== 0) {
            throw new \Exception('Hubo un problema al procesar el archivo Excel.');
        }

        // Mostrar la salida del comando en los logs (opcional)
        \Log::info($buffer->fetch());
     }

     /**
     * @OA\Get(
     *     path="/api/process-bigcommerce",
     *     summary="Process Bigcommerce",
     *     description="Inicia el proceso de Bigcommerce y retorna un mensaje de éxito.",
     *     operationId="processBigcommerce",
     *     tags={"Bigcommerce"},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Archivo procesado y subido correctamente."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud incorrecta"
     *     ),
     * )
     */
    public function processBigcommerce(){
        $file_name = 'BIGCOMMERCE.xlsx';
        $file_path_to_save = storage_path('app/public/input_file/');
        $outputFileName = 'BIGCOMME_11.csv';
        $outputFilePath = storage_path('app/public/output_file/' . $outputFileName);
    
        try {
            // Paso 1: Descargar el archivo Excel desde Zoho WorkDrive
            $serviceWorkdrive = new ZohoWorkdriveService();
            $download_response = $serviceWorkdrive->downloadFilev2($file_name, env('ZOHO_WORKDRIVE_FOLDER_INPUT_XLS'), $file_path_to_save);
            
            // Validación de descarga exitosa
            if (!$download_response['status']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $download_response['message'].' - AGape',
                    'error' => $download_response['error'].' - AGape' ?? null,
                ], 500);
            }
    
            // Paso 2: Procesar el archivo Excel a CSV
            $inputFilePath = $file_path_to_save . $file_name;
            $startTime = now();
    
            $this->processExcelFile($inputFilePath, $outputFilePath, $startTime);
    
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
    
            // Validación de procesamiento exitoso
            if (!file_exists($outputFilePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al procesar el archivo Excel a CSV.'
                ], 500);
            }
    
            // Paso 3: Subir el archivo CSV a Zoho WorkDrive
            $upload_file = $serviceWorkdrive->uploadFile($outputFileName, env('ZOHO_WORKDRIVE_FOLDER_OUTPUT_CSV'), $outputFilePath);
    
            // Validación de subida exitosa
            if ($upload_file['status'] !== 'SUCCESS') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al subir el archivo CSV a Zoho WorkDrive.'
                ], 500);
            }
    
            // Generar link compartido para el archivo subido
            $resource_id = $upload_file['data'][0]['attributes']['resource_id'];
            $share_file = $serviceWorkdrive->createExternaLinks($resource_id, $outputFileName);
    
            $link = null;
            if (!empty($share_file->data)) {
                $attributes = $share_file->data->attributes ?? null;
                if (!empty($attributes->link)) {
                    $link = $attributes->link;
                }
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Archivo procesado y subido correctamente.',
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'duration_minutes' => $duration,
                'share_link' => $link ?? 'No se pudo generar el enlace compartido.'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error durante el proceso: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

}
