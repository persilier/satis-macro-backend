<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
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

    /***
     * @param $client
     * @param $stopIdentityExist
     * @param $updateIdentity
     */
    protected function store($client, $stopIdentityExist, $updateIdentity)
    {
        $accountExist = false;
        $phoneExist = false;
        $emailExist = false;

        $institution = $this->getInstitutionByName($client['institution']);
        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($client['account_number']);

        if (!$verifyAccount['status']) {
            $accountExist = true;
        }
        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($client['telephone'], 'identites', 'telephone', 'telephone', $institution->id);

        if (!$verifyPhone['status']) {
            $phoneExist = true;
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($client['email'], 'identites', 'email', 'email', $institution->id);

        if (!$verifyEmail['status']) {
            $emailExist = true;
        }

        if (!$accountExist) {

            if ($emailExist || $phoneExist) {

                if (!$stopIdentityExist) {

                    $identity = $this->getIdentityByTelephonesOrEmails($client);

                    if ($updateIdentity) {
                        $identity = $this->updateIdentity($client, $identity);
                    }

                    $this->storeClientAccount($client, $identity, $institution);

                }

            } else {

                $identity = $this->storeIdentity($client);

                $this->storeClientAccount($client, $identity, $institution);

            }
        }

    }


    protected function storeClientAccount($client, $identity, $institution)
    {
        $clientStore = $this->storeClient($identity->id);

        $clientInstitution = $this->storeClientInstitution($client, $clientStore->id, $institution);

        $account = $this->storeAccount($client, $clientInstitution->id);
    }


    /***
     * @param $identityId
     * @return mixed
     */
    protected function storeClient($identityId)
    {
        if (!$clientStore = Client::where('identites_id', $identityId)->first()) {
            $clientStore = Client::create(['identites_id' => $identityId]);
        }

        return $clientStore;
    }

    /***
     * @param $client
     * @param $clientInstitutionId
     * @return mixed
     *
     */
    protected function storeAccount($client, $clientInstitutionId)
    {
        return Account::create([
            'client_institution_id' => $clientInstitutionId,
            'account_type_id' => $this->getInstanceByColumnJsonTranslate(
                $client['account_type'],
                AccountType::class, 'name'
            )->id,
            'number'  => $client['account_number']
        ]);
    }

    /***
     * @param $client
     * @param $clientId
     * @param $institution
     * @return mixed
     */
    protected function storeClientInstitution($client, $clientId, $institution)
    {
        if (!$clientInstitution = ClientInstitution::where('client_id', $clientId)->where('institution_id', $institution->id)->first()){

            $clientInstitution = ClientInstitution::create([
                'category_client_id' => $this->getInstanceByColumnJsonTranslate(
                    $client['category_client'],
                    CategoryClient::class, 'name'
                )->id,
                'client_id' => $clientId,
                'institution_id'  => $institution->id
            ]);
        }

        return $clientInstitution;
    }




    /***
     * @param $client
     * @return mixed
     */
    protected function storeIdentity($client)
    {
        return Identite::create($this->fillableIdentity($client));
    }

    /***
     * @param $name
     * @return mixed
     */
    protected function getInstitutionByName($name)
    {
        return Institution::where('name' , $name)->first();
    }

    /***
     * @param $values
     * @param string $attribute
     * @return mixed|null
     */
    protected function getIdentityByTelephonesOrEmails($values, $attribute = 'email')
    {
        $identities = Identite::all();

        foreach ($values as $value) {
            if ($identity = $this->getIdentityByTelephoneOrEmail($identities, $value, $attribute)) {
                return $identity;
            }
        }

        return null;
    }


    /***
     * @param $identities
     * @param $value
     * @param string $attribute
     * @return mixed
     */
    protected function getIdentityByTelephoneOrEmail($identities, $value, $attribute)
    {
        return $identity = $identities->first(function ($item, $key) use ($value, $attribute) {
            return $item->{$attribute} && in_array($value, $item->{$attribute});
        });
    }


    /***
     * @param $client
     * @param $identity
     * @return mixed
     */
    protected function updateIdentity($client, $identity)
    {
        return Identite::find($identity->id)->update($this->fillableIdentity($client));
    }

    /***
     * @param $data
     * @return array
     */
    protected function fillableIdentity($data)
    {
        return [
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'sexe'      => $data['sexe'],
            'telephone' => $data['telephone'],
            'email'     => $data['email'],
            'ville'     => $data['ville'],
        ];
    }

    /***
     * @param $value
     * @param $model
     * @param $columnName
     * @return mixed
     */
    public function getInstanceByColumnJsonTranslate($value, $model, $columnName)
    {
        $data = $model::all();
        return $data = $data->first(function ($item, $key) use ($value, $columnName) {
            return $item->{$columnName} === $value ;
        });
    }

//    /**
//     * @return mixed
//     */
//    public function rules(){
//
//        $rules = $this->rulesIdentite();
//
//        $rules['account_number'] = 'required|string';
//
//        $rules['account_type'] = ['required',
//
//            new NameModelRules(['table' => 'account_types', 'column'=> 'name']),
//        ];
//
//        $rules['category_client'] = ['required',
//
//            new NameModelRules(['table' => 'category_clients', 'column'=> 'name']),
//        ];
//
////        $rules['other_attributes_clients'] = 'array';
//
//        if (!$this->myInstitution){
//
//            $rules['institution'] = 'required|exists:institutions,name';
//
//        }
//
//        return $rules;
//    }


//    /**
//     * @param $row
//     * @param $identiteId
//     * @return array
//     */
//    protected function storeClient($row, $identiteId)
//    {
//        if(!$client = Client::where('identites_id', $identiteId)
//            ->first()
//        ){
//            $store = [
//                'identites_id' => $identiteId,
////                'others'  => $row['other_attributes_clients'],
//            ];
//
//            $client = Client::create($store);
//        }
//
//        return $client;
//
//    }

//
//    /**
//     * @param $row
//     * @param $clientId
//     * @return array
//     */
//    protected function storeClientInstitution($row, $clientId)
//    {
//        if(!$clientInstitution = ClientInstitution::where('institution_id', $row['institution'])
//
//            ->where('client_id', $clientId)
//            ->first()
//        ){
//
//            $store = [
//
//                'category_client_id'  => $row['category_client'],
//                'client_id' => $clientId,
//                'institution_id'  => $row['institution']
//            ];
//
//            $clientInstitution = ClientInstitution::create($store);
//        }
//
//        return $clientInstitution;
//    }
//
//    /**
//     * @param $row
//     * @param $clientInstitutionId
//     * @return array
//     */
//    protected function storeAccount($row, $clientInstitutionId)
//    {
//        $store = [
//
//            'client_institution_id' => $clientInstitutionId,
//            'account_type_id'  => $row['account_type'],
//            'number'  => $row['account_number']
//        ];
//
//        return $account = Account::create($store);
//    }



}
