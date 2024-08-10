<?php

namespace App\Services;

use CURLFile;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class ZohoWorkdriveService
{
    private $url = "https://workdrive.zoho.com/api/v1/";
    private $urlwdrive = "https://www.zohoapis.com/workdrive/api/v1/";
    private $upload_url = "https://upload.zoho.com/workdrive-api/v1/";
    private $zohoOAuthService;
    private $client;

    /**
     * ZohoWorkdriveService constructor.
     */
    public function __construct()
    {
        $this->zohoOAuthService = new ZohoOAuthService();
        $this->client = new Client();
    }

    /**
     * @param $url
     * @return string
     */
    private function urlApi($url)
    {
        return $this->url . $url;
    }

    /**
     * @return array
     */
    private function headers()
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Zoho-oauthtoken ' . $this->zohoOAuthService->getAccessToken()->access_token,
        ];
    }

    public function uploadFile($file_name, $folder_code, $file_path)
    {
        try {
            $full_path = $file_path;
            $cf = new CURLFile($full_path);

            $data = array(
                'file' => $cf,
            );
            $token = $this->zohoOAuthService->getAccessToken()->access_token;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->upload_url . 'stream/upload',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken ' . $token,
                    'Content-type: multipart/form-data',
                    'x-filename: ' . $file_name,
                    'x-parent_id: ' . $folder_code,
                    'upload-id: ' . $file_path,
                    'x-streammode: 1'
                ),
            ));
            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            Log::error('error', ['message' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'FAILURE',
                'error' => $e->getMessage()
            ];
        }
    }

    public function downloadFile($file_name, $folder_code, $file_path)
    {
        try {
            $full_path = $file_path;
            $cf = new CURLFile($full_path);

            $data = array(
                'file' => $cf,
            );
            $token = $this->zohoOAuthService->getAccessToken()->access_token;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->urldrive . 'files/' . $folder_code . '/files',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Zoho-oauthtoken ' . $token,
                ],
            ]);
            $response = curl_exec($curl);
            dd($this->urldrive . 'files/' . $folder_code . '/files');
            $response = json_decode($response, true);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            Log::error('error', ['message' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'FAILURE',
                'error' => $e->getMessage()
            ];
        }
    }

    public function createExternaLinks($resource_id, $link_name)
    {
        try {
            $data = [
                'data' => [
                    'attributes' => [
                        'resource_id' => $resource_id,
                        'link_name' => $link_name,
                        'request_user_data' => false,
                        'allow_download' => true,
                        'role_id' => 34
                    ],
                    'type' => 'links'
                ]
            ];

            $token = $this->zohoOAuthService->getAccessToken()->access_token;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->url . 'links',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken ' . $token,
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($response);

            return $response;
        } catch (\Exception $e) {
            Log::error('createExternaLinks error', ['message' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'FAILURE',
                'error' => $e->getMessage()
            ];
        }
    }
}
