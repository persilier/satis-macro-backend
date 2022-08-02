<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Client;
/**
 * Class ClientRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class ClientRepository
{

    protected $client;

    /***
     * ClientRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->client->find($id);
    }

    /***
     * @param $identityId
     * @return mixed
     */
    public function getByIdentity($identityId) {
        return $this->client->where('identites_id', $identityId)->first();
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->client->create($data);
    }

}