<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
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

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, NotificationTrait, CreateClaim, DataUserNature;


    public function index(Request $request)
    {
        #dd(config('auth.password_reset_link'));
        #dd(parse_url($request->headers->get('origin'),  PHP_URL_HOST));
        //dd(Config::get('email-claim-configuration.app_url_incoming_mail').route('passport.token', null, false));
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

        //$proxyConfigs = Config::get('proxy');

        //if ($proxyConfigs['http_proxy'] || $proxyConfigs['https_proxy']){
        //    dump('yes');
        //}else{
       //     dump('no');
       // }

        //verif
        return response()->json(MessageApiMethod::uimcecSMSGateway($request->phone,"Test SMS IUMCEC 2"));
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

    public function claimRef()
    {
        $subject = "[SATISPR-202201001437-INDEPENDANT] Accusé de reception";
        $content = "Bonjour M ATTA YAYA ARAFATH, [SATISPR-202201001437-INDEPENDANTE] Nous acusons reception de votre [SATISPR-202201001437-INDEPENDANTE] reclamation en ce jour ! [SATISPR-202201001437-INDEPENDANTE]";

        $references = array_unique(array_merge(extractClaimRefs($subject),extractClaimRefs($content)));

        return response()->json($references);
    }


}
