<?php

namespace Satis2020\Search\Http\Controllers\Client;

use Illuminate\Support\Str;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\Search;

class ClientController extends ApiController
{

    use Search, Notification;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Institution $institution
     * @return \Illuminate\Http\Response
     */

    public function index(Institution $institution)
    {
        $institution->client_institutions->load(['client.identite', 'accounts']);

        $clients = collect([]);

        $institution->client_institutions->map(function ($item, $key) use ($clients) {

            $fullName = $item->client->identite->firstname . ' ' . $item->client->identite->lastname . ' / ';

            $i = 0;
            foreach ($item->client->identite->telephone as $telephone) {
                $i++;
                $fullName .= ($i == count($item->client->identite->telephone)) ? $telephone : $telephone . ' , ';
            }

            if (!is_null($item->accounts)){
                $item->accounts->each(function ($account,$k){
                    $account->makeVisible('account_number');
                    $account->makeHidden('number');
                });
            }

            $clients->push([
                'identityId' => $item->client->identite->id,
                'identity' => $item->client->identite,
                'client' => $item->client,
                'accounts' => $item->accounts,
                'fullName' => $fullName,
                'contains' => Str::contains(Str::lower($this->remove_accent($fullName)), Str::lower($this->remove_accent(request()->r)))
            ]);

            return $item;

        });

        // get the claimers of the institution
        $institution->claims->map(function ($item, $key) use ($clients) {

            $fullName = $item->claimer->firstname . ' ' . $item->claimer->lastname . ' / ';

            $i = 0;
            foreach ($item->claimer->telephone as $telephone) {
                $i++;
                $fullName .= ($i == count($item->claimer->telephone)) ? $telephone : $telephone . ' , ';
            }

            $clients->push([
                'identityId' => $item->claimer->id,
                'identity' => $item->claimer,
                'client' => null,
                'accounts' => [],
                'fullName' => $fullName,
                'contains' => Str::contains(Str::lower($this->remove_accent($fullName)), Str::lower($this->remove_accent(request()->r)))
            ]);

            return $item;

        });

        $filtered = $clients->filter(function ($value, $key) {
            return $value['contains'];
        });

        return response()->json($filtered->unique('identityId')->take(10)->values(), 200);
    }

}
