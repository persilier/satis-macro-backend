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
        $rechercheType = $request->query('type', 'name_or_phone');

        $query = Identite::query()
            ->leftJoin('clients', 'identites.id', '=', 'clients.identites_id')
            ->leftJoin('client_institution', 'clients.id', '=', 'client_institution.client_id')
            ->leftJoin('accounts', 'client_institution.id', '=', 'accounts.client_institution_id')
            ->leftJoin('claims', 'identites.id', '=', 'claims.claimer_id')
            ->whereRaw(
                '( (`claims`.`id` IS NOT NULL AND `claims`.`institution_targeted_id` = ?) OR (`client_institution`.`id` IS NOT NULL AND `client_institution`.`institution_id` = ?) )',
                [$institution, $institution]
            );

        if ($rechercheType == 'account_number') {
            $query = $query->whereRaw(
                '`accounts`.`number` = ?',
                [$recherche]
            );
        } else {
            $query = $query->where(function ($query) use ($recherche) {
                $query->whereRaw('(`identites`.`firstname` LIKE ?)', ["%$recherche%"])
                    ->orWhereRaw('`identites`.`lastname` LIKE ?', ["%$recherche%"])
                    ->orwhereJsonContains('telephone', $recherche);
            });
        }

        $identities = $query->select([
            'identites.id as id',
            'identites.id as identityId',
            'identites.firstname',
            'identites.lastname',
            'identites.telephone',
            'identites.email',
            'identites.ville',
            'identites.sexe',
            'accounts.id as accountId',
            'accounts.number as accountNumber'
        ])
            ->get()
            ->groupBy('identityId')
            ->take(5);

        $filtered = [];

        foreach ($identities as $identityId => $identityAccounts) {

            $fullName = $identityAccounts[0]->firstname . ' ' . $identityAccounts[0]->lastname;

            if ($identityAccounts[0]->telephone) {
                $fullName .= ' / ';
                $counter = 0;
                foreach ($identityAccounts[0]->telephone as $telephone) {
                    $fullName .= ($counter == count($identityAccounts[0]->telephone) - 1) ? $telephone : $telephone . ' , ';
                    $counter++;
                }
            }

            $accounts = [];
            foreach ($identityAccounts as $identityAccount) {
                if ($identityAccount->accountId) {
                    $account = new \stdClass();
                    $account->id = $identityAccount->accountId;
                    $account->number = $identityAccount->accountNumber;
                    $accounts[] = $account;
                }
            }

            $identity = $identityAccounts[0];

            $filtered[] = [
                'identityId' => $identityId,
                'identity' => $identity,
                'accounts' => $accounts,
                'fullName' => $fullName
            ];
        }

        return response()->json($filtered, JsonResponse::HTTP_OK);
    }

}