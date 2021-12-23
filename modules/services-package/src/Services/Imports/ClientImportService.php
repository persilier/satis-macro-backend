<?php

namespace Satis2020\ServicePackage\Services\Imports;

use Satis2020\ServicePackage\Repositories\AccountRepository;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Repositories\AccountTypeRepository;
use Satis2020\ServicePackage\Repositories\CategoryClientRepository;
use Satis2020\ServicePackage\Repositories\ClientInstitutionRepository;
use Satis2020\ServicePackage\Repositories\ClientRepository;
use Satis2020\ServicePackage\Repositories\IdentityRepository;
use Satis2020\ServicePackage\Repositories\InstitutionRepository;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class ClientImportService
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait, SecureDelete;

    protected $identityRepository;
    protected $clientRepository;
    protected $clientInstitutionRepository;
    protected $accountRepository;
    protected $categoryClientRepository;
    protected $accountTypeRepository;
    protected $institutionRepository;

    public function __construct()
    {
        $this->identityRepository = app(IdentityRepository::class);
        $this->clientRepository = app(ClientRepository::class);
        $this->clientInstitutionRepository = app(ClientInstitutionRepository::class);
        $this->accountRepository = app(AccountRepository::class);
        $this->categoryClientRepository = app(CategoryClientRepository::class);
        $this->accountTypeRepository = app(AccountTypeRepository::class);
        $this->institutionRepository = app(InstitutionRepository::class);
    }

    public function store($client, $stopIdentityExist, $updateIdentity)
    {
        $accountExist = false;
        $phoneExist = false;
        $emailExist = false;

        $institution = $this->institutionRepository->getByName($client['institution']);
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

                    $identity = $this->identityRepository->getByTelephonesOrEmails($client);

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

    /***
     * @param $client
     * @param $identity
     * @return mixed
     */
    protected function updateIdentity($client, $identity)
    {
        return $this->identityRepository->update($this->fillableIdentity($client), $identity->id);
    }


    protected function storeClientAccount($client, $identity, $institution)
    {
        $clientStore = $this->storeClient($identity->id);

        $clientInstitution = $this->storeClientInstitution($client, $clientStore->id, $institution);

        $account = $this->storeAccount($client, $clientInstitution->id);
    }

    /***
     * @param $client
     * @return mixed
     */
    protected function storeIdentity($client)
    {
        return $this->identityRepository->create($this->fillableIdentity($client));
    }

    /***
     * @param $identityId
     * @return mixed
     */
    protected function storeClient($identityId)
    {
        if (!$clientStore = $this->clientRepository->getByIdentity($identityId)) {
            $clientStore = $this->clientRepository->create(['identites_id' => $identityId]);
        }

        return $clientStore;
    }

    /***
     * @param $client
     * @param $clientId
     * @param $institution
     * @return mixed
     */
    protected function storeClientInstitution($client, $clientId, $institution)
    {
        if (!$clientInstitution = $this->clientInstitutionRepository->getByInstitutionAndClient($institution->id, $clientId)){

            $clientInstitution = $this->clientInstitutionRepository->create([
                'category_client_id'  => $this->categoryClientRepository->getByName($client['category_client'])->id,
                'client_id' => $clientId,
                'institution_id'  => $institution->id
            ]);
        }

        return $clientInstitution;
    }

    /***
     * @param $client
     * @param $clientInstitutionId
     * @return mixed
     *
     */
    protected function storeAccount($client, $clientInstitutionId)
    {
        return $this->accountRepository->create([
            'client_institution_id' => $clientInstitutionId,
            'account_type_id'  => $this->accountTypeRepository->getByName($client['account_type'])->id,
            'number'  => $client['account_number']
        ]);
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

}