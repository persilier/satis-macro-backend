<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\ExplodeEmailRules;
use Satis2020\ServicePackage\Rules\ExplodeTelephoneRules;
use Satis2020\ServicePackage\Rules\NameModelRules;

/**
 * Trait ImportClient
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportClient
{


    /**
     * @return mixed
     */
    public function rules(){

        $rules = $this->rulesIdentite();

        $rules['account_number'] = 'required|string';

        $rules['account_type'] = ['required',

            new NameModelRules(['table' => 'account_types', 'column'=> 'name']),
        ];

        $rules['category_client'] = ['required',

            new NameModelRules(['table' => 'category_clients', 'column'=> 'name']),
        ];

//        $rules['other_attributes_clients'] = 'array';

        if (!$this->myInstitution){

            $rules['institution'] = 'required|exists:institutions,name';

        }

        return $rules;
    }


    /**
     * @param $row
     * @param $identiteId
     * @return array
     */
    protected function storeClient($row, $identiteId)
    {
        if(!$client = Client::where('identites_id', $identiteId)
            ->first()
        ){
            $store = [
                'identites_id' => $identiteId,
//                'others'  => $row['other_attributes_clients'],
            ];

            $client = Client::create($store);
        }

        return $client;

    }


    /**
     * @param $row
     * @param $clientId
     * @return array
     */
    protected function storeClientInstitution($row, $clientId)
    {
        if(!$clientInstitution = ClientInstitution::where('institution_id', $row['institution'])

            ->where('client_id', $clientId)
            ->first()
        ){

            $store = [

                'category_client_id'  => $row['category_client'],
                'client_id' => $clientId,
                'institution_id'  => $row['institution']
            ];

            $clientInstitution = ClientInstitution::create($store);
        }

        return $clientInstitution;
    }

    /**
     * @param $row
     * @param $clientInstitutionId
     * @return array
     */
    protected function storeAccount($row, $clientInstitutionId)
    {
        $store = [

            'client_institution_id' => $clientInstitutionId,
            'account_type_id'  => $row['account_type'],
            'number'  => $row['account_number']
        ];

        return $account = Account::create($store);
    }



}
