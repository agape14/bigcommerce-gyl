<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoOAuthService;
use App\Services\ZohoWorkdriveService;
use Illuminate\Support\Facades\Storage;



class BigcommerceController extends Controller
{

/**
     * @OA\Get(
     *     path="/api/testsalida",
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


    public function uploadFileCsv()
    {
      
        $file_name = 'csvfile.pdf';   /* aqui va el archivo csv**/ 
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
     *     path="/api/testentrada",
     *     summary="Test API entrada",
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
    public function downloadFileExcel()
    {
        $file_name = 'inputfile.pdf';   /* aqui va el archivo xls**/ 
        $file_path_to_save = storage_path('app/public/input_file/');
        $serviceWorkdrive = new ZohoWorkdriveService();
        $download_file = $serviceWorkdrive->downloadFile($file_name, env('ZOHO_WORKDRIVE_FOLDER_INPUT_XLS'), $file_path_to_save);
       /* aqui la logica para descargar el file **/
       
    }

}
