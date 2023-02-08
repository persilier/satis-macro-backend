<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;

trait Search
{

    function searchClient($request,$institution)
    {
        $recherche = $request->query('r');
        $rechercheType = $request->query('type', 'name_or_phone','raison_sociale');

        $query = Identite::query()
            ->leftJoin('clients', 'identites.id', '=', 'clients.identites_id')
            ->leftJoin('client_institution', 'clients.id', '=', 'client_institution.client_id')
            ->leftJoin('category_clients', 'category_clients.id', '=', 'client_institution.category_client_id')
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
                    ->orWhereRaw('`identites`.`raison_sociale` LIKE ?', ["%$recherche%"])
                    ->orwhereJsonContains('telephone', $recherche);
            });
        }

        $identities = $query->select([
            'identites.id as id',
            'identites.id as identityId',
            'category_client_id',
            'category_clients.name as category_name',
            'client_id',
            'identites.firstname',
            'identites.lastname',
            'identites.telephone',
            'identites.email',
            'identites.ville',
            'identites.sexe',
            'identites.type_client',
            'identites.raison_sociale',
            'accounts.id as accountId',
            'accounts.number as accountNumber'
        ])
            ->distinct()
            ->get()
            ->groupBy('identityId')
            ->take(5);

        $filtered = [];

        foreach ($identities as $identityId => $identityAccounts) {

            if ($identityAccounts[0]->raison_sociale != null) {
                $fullName = $identityAccounts[0]->raison_sociale ;

            } else {
                $fullName = $identityAccounts[0]->firstname . ' ' . $identityAccounts[0]->lastname;
            }
            


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
                'fullName' => $fullName,
                'category_client_id'=> $identityAccounts[0]->category_client_id,
                'category_name'=> $identityAccounts[0]->category_name,
                'client_id'=> $identityAccounts[0]->client_id,
            ];
        }

        return $filtered;
    }
}