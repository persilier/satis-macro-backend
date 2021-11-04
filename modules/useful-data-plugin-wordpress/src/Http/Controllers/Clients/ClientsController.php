<?php

namespace Satis2020\UsefulDataPluginWordpress\Http\Controllers\Clients;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Institution;

class ClientsController extends Controller
{
    public function __construct()
    {
        $this->middleware('set.language');
        $this->middleware('client.credentials');
    }


    public function show($accountNumber)
    {
        return response()->json(Account::with('client_institution.client.identite')
            ->where('number', $accountNumber)
            ->firstOrFail()
            ->client_institution->client->identite, 200);
    }

}
