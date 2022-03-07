<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\EmailValidationRules;

/**
 * Trait ClientTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait ClientTrait
{
    /**
     * Rules Validation Store Client
     * @param bool $requestInstitution
     * @return array
     */
    protected function rulesClient($requestInstitution = false)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => [
                'required', 'array', new EmailValidationRules,
            ],
            'ville' => 'required|string',
            'number' => 'required|string',
            'account_type_id' => 'required|exists:account_types,id',
            'category_client_id' => 'required|exists:category_clients,id',
            'others' => 'array',
            'other_attributes' => 'array',
        ];

        if ($requestInstitution) {

            $rules['institution_id'] = 'required|exists:institutions,id';

        }

        return $rules;
    }

    /**
     * Rules Validation Store Account
     * @param bool $requestInstitution
     * @return array
     */
    protected function rulesAccount($requestInstitution = false)
    {
        $rules = [
            'number' => 'required|string',
            'account_type_id' => 'required|exists:account_types,id',
        ];

        if ($requestInstitution) {

            $rules['institution_id'] = 'required|exists:institutions,id';

        }

        return $rules;
    }


    /**
     * @param $request
     * @return mixed
     */
    protected function storeIdentite($request)
    {
        $store = [

            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'sexe' => $request->sexe,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'ville' => $request->ville,
            'other_attributes' => $request->other_attributes
        ];

        return $identite = Identite::create($store);
    }

    /**
     * @param $request
     * @param $identiteId
     * @param bool $identityConfirm
     * @return array
     */
    protected function storeClient($request, $identiteId, $identityConfirm = false)
    {
        $store = [
            'identites_id' => $identiteId,
            'others' => $request->others
        ];


        if ($identityConfirm) {

            if (!$client = Client::where('identites_id', $identiteId)->first()) {

                $client = Client::create($store);

            }

        } else {

            $client = Client::create($store);

        }

        return $client;
    }


    /**
     * @param $request
     * @param $clientId
     * @param $institutionId
     * @param bool $identityConfirm
     * @return array
     */
    protected function storeClientInstitution($request, $clientId, $institutionId, $identityConfirm = false)
    {
        $store = [
            'category_client_id' => $request->category_client_id,
            'client_id' => $clientId,
            'institution_id' => $institutionId
        ];

        if ($identityConfirm) {

            if (!$clientInstitution = ClientInstitution::where('client_id', $clientId)->where('institution_id', $institutionId)->first()) {

                $clientInstitution = ClientInstitution::create($store);

            }

        } else {

            $clientInstitution = ClientInstitution::create($store);
        }

        return $clientInstitution;
    }

    /**
     * @param $request
     * @param $clientInstitutionId
     * @return array
     */
    protected function storeAccount($request, $clientInstitutionId)
    {
        $store = [

            'client_institution_id' => $clientInstitutionId,
            'account_type_id' => $request->account_type_id,
            'number' => $request->number
        ];

        return $account = Account::create($store);
    }

    /**
     * @param $institutionId
     * @param $clientId
     * @return Builder|Model
     */
    protected function getOneClientByInstitution($institutionId, $clientId)
    {

        return ClientInstitution::with(
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        )->where('institution_id', $institutionId)->where('client_id', $clientId)->firstOrFail();

    }

    /**
     * @param $institutionId
     * @return Builder[]|Collection
     * @throws CustomException
     */
    protected function getAllClientByInstitution($institutionId)
    {
        $clients = ClientInstitution::query()
            ->with([
                'client:id,identites_id',
                'client.identite:id,firstname,lastname,email,telephone',
                'category_client:id,name',
                'institution:id,name',
                'accounts',
                'accounts.accountType:id,name',
            ])
            ->whereHas("accounts",function (Builder $builder){
                $builder->whereNull("deleted_at");
            })
            ->where('institution_id', $institutionId)
            ->take(15)
            ->get();

        return $clients;
    }

    /**
     * @param $institutionId
     * @param $accountId
     * @return Builder|Model
     * @throws CustomException
     */
    protected function getOneAccountClientByInstitution($institutionId, $accountId)
    {

        try {

            $client = ClientInstitution::with([
                'client.identite',
                'category_client',
                'institution',
                'accounts.accountType' => function ($query) use ($accountId) {
                    $query->where('id', $accountId);
                }

            ])->where('institution_id', $institutionId)->firstOrFail();

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de retrouver ce compte client.");
        }

        return $client;
    }


    /**
     * @param $accountId
     * @return Builder|Model
     * @throws CustomException
     */
    protected function getOneAccountClient($accountId)
    {

        try {

            $client = ClientInstitution::with([
                'client.identite',
                'category_client',
                'institution',
                'accounts.accountType'
            ])->where(function ($query) use ($accountId) {
                $query->whereHas('accounts', function ($q) use ($accountId) {
                    $q->where('id', $accountId);
                });
            })->firstOrFail();

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de retrouver ce compte client.");

        }

        return $client;
    }


    /**
     * @param $number
     * @return array
     * @throws CustomException
     */
    protected function handleAccountClient($number)
    {
        try {

            $account = Account::where('number', $number)->first();

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de retrouver ce compte client.");

        }

        if (!is_null($account)) {

            return ['code' => 409, 'status' => false, 'message' => 'Impossible d\'enregistrer ce compte. Ce numéro de compte existe déjà.'];

        }

        return ['status' => true];
    }


    /**
     * @param $request
     * @param $clientId
     * @return Builder|Model
     */
    protected function getOneClient($request, $clientId)
    {
        return ClientInstitution::with([
            'client.identite',
            'institution',
            'category_client'
        ])->where('client_id', $clientId)->where('institution_id', $request->institution_id)->first();

    }


}
