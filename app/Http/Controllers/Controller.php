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
use Satis2020\ServicePackage\Traits\Notification as NotificationTrait;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Notifications\Recurrence;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Notifications\ReviveStaff;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, NotificationTrait, CreateClaim, DataUserNature;

    public function index()
    {
        $claim = Claim::findOrFail("4a6f1d6d-85d6-4349-a28b-32116dde7806");

        $identities = $this->getStaffToReviveIdentities($claim);

        //$identities[1]->notify(new ReviveStaff($claim, "CC"));
        
        Notification::send($this->getStaffToReviveIdentities($claim), new ReviveStaff($claim, "CC"));

        dd($identities[1]);
        
        return response()->json([], 200);
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


    /**
     * @param $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcelReports($file){

        return response()->download(storage_path('app/'.$file));
    }
}
