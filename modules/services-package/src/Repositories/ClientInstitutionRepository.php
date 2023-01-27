<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\ClientInstitution;
/**
 * Class ClientInstitutionRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class ClientInstitutionRepository
{

    private $clientInstitution;


    public function __construct(ClientInstitution $clientInstitution)
    {
        $this->clientInstitution = $clientInstitution;
    }


    public function getById($id) {
        return $this->clientInstitution->find($id);
    }


    public function getByInstitutionAndClient($institutionId, $clientId) {
        return $this->clientInstitution->where('client_id', $clientId)->where('institution_id', $institutionId)->first();
    }


    public function create($data)
    {
        return $this->clientInstitution->create($data);
    }




}