<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\Notification as NotificationTrait;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, NotificationTrait, CreateClaim, DataUserNature;

    public function index()
    {
//        $sendMail = $this->londoSMSApi(
//            "BciGatewayLogin",
//            "k6cfThDiZKKRFYgH63RKL49jD604xF4M16K" ,
//            "BCI",
//            "SATISPROBCI",
//            "TEST001",
//            1,
//            "242064034953",
//            'TEST SMS API BCI'
//        );

        $headers = [
            "Authorization" => "Basic " . base64_encode("BciGatewayLogin:k6cfThDiZKKRFYgH63RKL49jD604xF4M16K")
        ];
        $data = [
            '_id' => "TEST001",
            'priority' => 1,
            'telephone' => "24205593171",
            'message' => "TEST",
            'source' => [
                'client' => "BCI",
                'app' => "SATIS PRO BCI"
            ]
        ];
        $response = Http::withHeaders($headers)->post("https://gateway.londo-tech.com/api/v1/send/sms", $data);

        if ($response->successful() && optional($response->json())['message'] == "message sent successfully."){
            dd("good");
        }else {
            dd("not good");
        }

        dd($response);

    }

    public function download(File $file)
    {
        return response()->download(public_path($file->url), "{$file->title}");
    }

    public function claimReference(Institution $institution)
    {
        return response()->json($this->createReference($institution->id), 200);
    }


    /**
     * @param $file
     * @return BinaryFileResponse
     */
    public function downloadExcels($file)
    {

        $files = [
            'clients' => ['url' => "/storage/excels/clients.xlsx", 'name' => 'clients.xlsx'],
            'staffs' => ['url' => "/storage/excels/staffs.xlsx", 'name' => 'staffs.xlsx'],
            'units' => ['url' => "/storage/excels/unite-type-unite.xlsx", 'name' => 'unite-type-unite.xlsx'],
            'categories' => ['url' => "/storage/excels/categories.xlsx", 'name' => 'categories.xlsx'],
            'objects' => ['url' => "/storage/excels/objects.xlsx", 'name' => 'objects.xlsx'],
            'institutions' => ['url' => "/storage/excels/institutions.xlsx", 'name' => 'institutions.xlsx'],
            'claims' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-against-my-institution' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-against-any-institution' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-without-client' => ['url' => "/storage/excels/claims-without-client.xlsx", 'name' => 'claims.xlsx'],
            'add-profils' => ['url' => "/storage/excels/add-profils.xlsx", 'name' => 'add-profils.xlsx']
        ];

        return response()->download(public_path($files[$file]['url']), $files[$file]['name']);
    }


    /**
     * @param $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcelReports($file)
    {

        return response()->download(storage_path('app/' . $file));
    }

    public function londoSMSApi($username, $password, $client, $app, $id, $priority, $to, $text)
    {
        $headers = [
            "Authorization" => "Basic " . base64_encode("$username:$password")
        ];
        $data = [
            '_id' => $id,
            'priority' => $priority,
            'telephone' => $to,
            'message' => $text,
            'source' => [
                'client' => $client,
                'app' => $app
            ]
        ];
        return Http::withHeaders($headers)->post("https://gateway.londo-tech.com/api/v1/send/sms", $data);

    }
}
