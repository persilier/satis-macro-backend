<?php

namespace Satis2020\MyClaimIncomingByEmail\Http\Controllers\IncomingMails;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;
use Satis2020\ServicePackage\Traits\CreateClaim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\TestSmtpConfiguration;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class IncomingMailsController extends Controller
{
    use ClaimIncomingByEmail, TestSmtpConfiguration, CreateClaim, DataUserNature, VerifyUnicity;

    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function store(Request $request)
    {
        $configuration = EmailClaimConfiguration::where('email', $request->route('email'))->first();
        Log::info("configuration ".json_encode($configuration));
        if (! $configuration) {
            return json_encode([]);
        }
        Log::info("request mail ".json_encode($request->all()));
        return json_encode(($this->readEmails($request, 'html_text', 'incomplete', $configuration)));
    }

}
