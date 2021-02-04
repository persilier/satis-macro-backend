<?php

namespace App\Http\Controllers;

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
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\Recurrence;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Notification, CreateClaim, DataUserNature;

    public function index()
    {
        $claim = Claim::findOrFail("e74c2f16-a603-4147-8489-78d485a23a11");
        dd($this->getStaffToReviveIdentities($claim));
        return response()->json($this->getStaffToReviveIdentities($claim), 200);
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
            'categories' => ['url' => "/storage/excels/categories.xlsx", 'name' => 'categories.xlsx'],
            'objects' => ['url' => "/storage/excels/objects.xlsx", 'name' => 'objects.xlsx'],
            'institutions' => ['url' => "/storage/excels/institutions.xlsx", 'name' => 'institutions.xlsx'],
            'claims' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-against-my-institution' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-against-any-institution' => ['url' => "/storage/excels/claims.xlsx", 'name' => 'claims.xlsx'],
            'claims-without-client' => ['url' => "/storage/excels/claims-without-client.xlsx", 'name' => 'claims.xlsx']
        ];
        return response()->download(public_path($files[$file]['url']), $files[$file]['name']);
    }
}
