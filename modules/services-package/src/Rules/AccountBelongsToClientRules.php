<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;

class AccountBelongsToClientRules implements Rule
{

    protected $identite_id;
    protected $institution_id;

    public function __construct($institution_id, $identite_id)
    {
        $this->institution_id = $institution_id;
        $this->identite_id = $identite_id;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws CustomException
     */

    public function passes($attribute, $value)
    {

        try {
            $accounts = DB::table('accounts')
                ->join('client_institution', function ($join) {
                    $join->on('accounts.client_institution_id', '=', 'client_institution.id');
                })
                ->join('institutions', function ($join) {
                    $join->on('client_institution.institution_id', '=', 'institutions.id')
                        ->where('institutions.id', '=', $this->institution_id);
                })
                ->join('clients', function ($join) {
                    $join->on('client_institution.client_id', '=', 'clients.id');
                })
                ->join('identites', function ($join) {
                    $join->on('clients.identites_id', '=', 'identites.id')
                        ->where('identites.id', '=', $this->identite_id);
                })
                ->select('accounts.*')
                ->get();

        } catch (\Exception $exception) {
            throw new CustomException("Can't retrieve the client accounts");
        }

        // search the account into the accounts list of the client chosen
        $search = $accounts->search(function ($item, $key) use ($value) {
            return $item->id === $value;
        });

        return $search !== false;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The account must belong to the chosen client';
    }

}
