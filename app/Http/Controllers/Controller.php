<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Client\RequestException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\Notification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Notification, CreateClaim, DataUserNature;

    public function index()
    {
        $claim = \Satis2020\ServicePackage\Models\Claim::find("a9a79643-cffc-443f-a7d9-df53e1fb8f81");
        $identity = \Satis2020\ServicePackage\Models\Identite::find("ed2f15a9-2b1b-4549-b48f-65fcf50d8cd9");
        //$discussion = Discussion::findOrFail("54ca6988-7139-4042-ada2-bb1af8a13af2");
        //$message = Message::findOrFail("bc3e54ba-d377-4486-b1d7-10158cc2b881");

        dd($this->getStaffIdentities(["3b4bcea9-ea57-4e29-956c-5c2702a1c04c", "343c2188-32e6-44b6-a090-6045f9b4b86d", "7d2c8ca6-bd0d-49f6-a238-34608d02dee4"], ["7d2c8ca6-bd0d-49f6-a238-34608d02dee4"]));

        return $claim;
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
    public function downloadExcels($file){

        $files = [
            'clients' => ['url' => "/storage/excels/clients.xlsx" , 'name' => 'clients.xlsx'],
            'staffs' => ['url' => "/storage/excels/staffs.xlsx" , 'name' => 'staffs.xlsx']
        ];
        return response()->download(public_path($files[$file]['url']), $files[$file]['name']);
    }
}
