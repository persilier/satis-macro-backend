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
        $claim = \Satis2020\ServicePackage\Models\Claim::find("a9a79643-cffc-443f-a7d9-df53e1fb8f81");
        $identity = \Satis2020\ServicePackage\Models\Identite::find("ed2f15a9-2b1b-4549-b48f-65fcf50d8cd9");
        //$discussion = Discussion::findOrFail("54ca6988-7139-4042-ada2-bb1af8a13af2");
        //$message = Message::findOrFail("bc3e54ba-d377-4486-b1d7-10158cc2b881");

        //dd($this->getStaffIdentities($discussion->staff->pluck('id')->all()));

        return $identity->notify(new \Satis2020\ServicePackage\Notifications\ReminderAfterDeadline($claim, '2h'));
    }

    public function download(File $file)
    {
        return response()->download(public_path($file->url), "{$file->title}");
    }
}
