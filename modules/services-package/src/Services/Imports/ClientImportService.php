<?php

namespace Satis2020\ServicePackage\Services\Imports\ClientImportService;

class ClientImportService
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function store($myInstitution, $stopIdentityExist, $updateIdentity)
    {
        return true;
    }
}