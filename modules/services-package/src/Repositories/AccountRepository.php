<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Account;
/**
 * Class AccountRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class AccountRepository
{

    private $account;


    public function __construct(Account $account)
    {
        $this->account = $account;
    }


    public function getById($id) {
        return $this->account->find($id);
    }


    public function create($data)
    {
        return $this->account->create($data);
    }

}