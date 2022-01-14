<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Client\RequestException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\MessageApiMethod;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\InstitutionMessageApi;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Requests\UpdatePasswordRequest;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\Notification as NotificationTrait;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\Recurrence;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Notifications\ReviveStaff;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, NotificationTrait, CreateClaim, DataUserNature;

    public function testPassword(UpdatePasswordRequest $request)
    {
        dd($request);
    }

    public function index()
    {
        $institution = Institution::query()->first();
        $params = InstitutionMessageApi::query()
            ->where('institution_id',$institution->id)
            ->first()->params;


        $text = "Hello this is an example sms from Satis";

        $response = MessageApiMethod::toOceanicsms(
            "satisuimcec",
            $params['password'],
            $params['from'],
            "22996475848",
            $text,
            $params['api'],
            $institution->id
        );

        dd(is_string($response->body()));
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
    public function downloadExcelReports($file){

        return response()->download(storage_path('app/'.$file));
    }

    public function londoSMSApi($username, $password ,$client, $app, $id, $priority, $to, $text)
    {
        $headers = [
            "Authorization" => "Basic ".base64_encode("$username:$password")
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
