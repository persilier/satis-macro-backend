<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Identite;

class ClientBelongsToInstitutionRules implements Rule
{

    protected $institution_id;

    public function __construct($institution_id)
    {
        $this->institution_id = $institution_id;
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

        $institution = DB::table('institutions')
            ->join('client_institution', function ($join) {
                $join->on('institutions.id', '=', 'client_institution.institution_id')
                    ->where('client_institution.institution_id', '=', $this->institution_id);
            })
            ->join('clients', function ($join) {
                $join->on('clients.id', '=', 'client_institution.client_id');
            })
            ->join('identites', function ($join) use ($value) {
                $join->on('clients.identites_id', '=', 'identites.id')
                    ->where('identites.id', '=', $value);
            })
            ->first();

        $claimer = Claim::with(['claimer'])
            ->where('claimer_id', $value)
            ->where('institution_targeted_id', $this->institution_id)
            ->first();

        return !is_null($institution) || !is_null($claimer);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The client must belong to the chosen institution';
    }

}
