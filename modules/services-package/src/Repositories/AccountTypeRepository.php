<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\AccountType;
/**
 * Class AccountTypeRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class AccountTypeRepository
{
    /***
     * @var AccountType
     */
    protected $accountType;

    /****
     * AccountTypeRepository constructor.
     * @param AccountType $accountType
     */
    public function __construct(AccountType $accountType)
    {
        $this->accountType = $accountType;
    }

    /***
     * @return \Illuminate\Database\Eloquent\Collection|AccountType[]
     */
    public function getAll() {
        return $this->accountType->all();
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->accountType->find($id);
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->accountType->create($data);
    }

    /***
     * @param $value
     * @return mixed
     */
    public function getByName($value)
    {
        $accountTypes = $this->getAll();
        return $accountType = $accountTypes->first(function ($item, $key) use ($value) {
            return $item->name === $value ;
        });
    }

}