<?php

namespace Satis2020\Search\Http\Controllers\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Identite;
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
     * @param Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, $institution)
    {
        $recherche = $request->query('r');

        $columnSelected = ['id', 'firstname', 'lastname', 'telephone', 'email', 'ville', 'sexe'];

        $identitiesQueries = [
            'clientsQuery' => Identite::query()
                ->with([
                    'client:id,identites_id',
                    'client.client_institutions' => function ($query) use ($institution) {
                        $query->where('institution_id', '=', $institution)
                            ->select('id', 'institution_id', 'client_id');
                    },
                    'client.client_institutions.accounts:id,number,client_institution_id',
                ]),
            'claimersQuery' => Identite::query()
                ->select($columnSelected)
                ->whereHas('claims', function ($query) use ($institution) {
                    $query->where('institution_targeted_id', $institution);
                })
        ];

        foreach ($identitiesQueries as $key => $query) {
            $identitiesQueries[$key] = $query
                ->where(function ($query) use ($recherche) {
                    $query->where('firstname', 'like', "%$recherche%")
                        ->orWhere('lastname', 'like', "%$recherche%");
                });
        }

        $identities = $identitiesQueries['clientsQuery']
            ->union($identitiesQueries['claimersQuery'])
            ->distinct()
            ->take(10)
            ->get($columnSelected);

        $filtered = [];

        foreach ($identities as $identity) {

            $fullName = $identity->firstname . ' ' . $identity->lastname;

            if ($identity->telephone) {
                $fullName .= ' / ';
                $counter = 0;
                foreach ($identity->telephone as $telephone) {
                    $fullName .= ($counter == count($identity->telephone) - 1) ? $telephone : $telephone . ' , ';
                }
            }

            try {
                $accounts = $identity->client->client_institutions->pluck('accounts')->collapse();
            } catch (\Exception $exception) {
                $accounts = [];
            }

            $filtered[] = [
                'identityId' => $identity->id,
                'identity' => $identity->only($columnSelected),
                'accounts' => $accounts,
                'fullName' => $fullName,
            ];
        }

        return response()->json($filtered, JsonResponse::HTTP_OK);
    }

}
