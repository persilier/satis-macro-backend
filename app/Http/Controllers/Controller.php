<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\Notification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Notification;

    public function index()
    {
        $claim = \Satis2020\ServicePackage\Models\Claim::find("0155686b-55d9-4ebe-ada1-79191e1b9b82");
        $identity = \Satis2020\ServicePackage\Models\Identite::find("b7acf27c-894e-4d9c-866e-efb67ad9188a");
        //$discussion = Discussion::findOrFail("54ca6988-7139-4042-ada2-bb1af8a13af2");
        //$message = Message::findOrFail("bc3e54ba-d377-4486-b1d7-10158cc2b881");

        //dd($this->getStaffIdentities($discussion->staff->pluck('id')->all()));

        return $identity->notify(new \Satis2020\ServicePackage\Notifications\AssignedToStaff($claim));
    }

    public function download(File $file)
    {
        return response()->download(public_path($file->url), "{$file->title}");
    }
}
