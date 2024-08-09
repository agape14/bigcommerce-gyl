<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoOAuthService;
use App\Services\ZohoWorkdriveService;
use Illuminate\Support\Facades\Storage;
/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="This is a sample API"
 * )
 */


class BigcommerceController extends Controller
{

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


public function uploadFile2()
    {
      /* $variable = new ZohoOAuthService();
       $token_var = $variable->getAccessToken()->access_token;*/
        $file_name = 'ejemplo.pdf';
        /*$path = 'pdf/' . $file_name;

            $pdf->SetCompression(true);
            $outputContent = $pdf->Output('', 'S');

        Storage::disk('public')->put($path, $outputContent);*/


        $file_path = storage_path('app/public/pdf/' . $file_name);

        $serviceWorkdrive = new ZohoWorkdriveService();
        $upload_file = $serviceWorkdrive->uploadFile($file_name, env('ZOHO_WORKDRIVE_FOLDER'), $file_path);
        dd($upload_file);
        /*if ($upload_file['status'] == 'SUCCESS') {
                        $resource_id = $upload_file['data'][0]['attributes']['resource_id'];
                        $share_file = $serviceWorkdrive->createExternaLinks($resource_id, $file_name);

                        if (!empty($share_file->data)) {
                            if (!empty($share_file->data->attributes)) {
                                $attributes = $share_file->data->attributes;
                                $link = $attributes->link;


                            }
                        }
                   return ResponseHelper::error($th->getMessage(), 400);

        }*/
}
}
