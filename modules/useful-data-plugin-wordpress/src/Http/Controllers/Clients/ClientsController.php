<?php

namespace Satis2020\UsefulDataPluginWordpress\Http\Controllers\Clients;

use Satis2020\ServicePackage\Http\Controllers\Controller;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Identite;
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
        $account = Account::with('client_institution.client.identite')
            ->where('number', $accountNumber)
            ->firstOrFail();

        $identityWithClientAccount = Identite::with('client.client_institution.accounts')
            ->whereHas('client', function ($q) use ($account){
                $q->whereHas('client_institution', function ($p) use ($account){
                    $p->where('institution_id', $account->client_institution->institution_id);
                });
            })->findOrFail($account->client_institution->client->identite->id);

        $identityWithClientAccount['account'] = [
            'id' => $account->id,
            'number' => $account->number
        ];

        return response()->json($identityWithClientAccount,200);
    }

}
