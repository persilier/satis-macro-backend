<?php

namespace Satis2020\AnyClaimIncomingByEmail\Http\Controllers\IncomingMails;

use Illuminate\Http\Request;
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
        if (! $configuration = EmailClaimConfiguration::where('email', $request->route('email'))->first()) {
            return json_encode([]);
        }

        return json_encode(($this->readEmails($request, 'html_text', 'incomplete', $configuration)));
    }

}
