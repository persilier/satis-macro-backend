<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\CategoryClient;
/**
 * Class CategoryClientRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class CategoryClientRepository
{
    /***
     * @var CategoryClient
     */
    protected $categoryClient;

    /***
     * CategoryClientRepository constructor.
     * @param CategoryClient $categoryClient
     */
    public function __construct(CategoryClient $categoryClient)
    {
        $this->categoryClient = $categoryClient;
    }

    /***
     * @return \Illuminate\Database\Eloquent\Collection|CategoryClient[]
     */
    public function getAll() {
        return $this->categoryClient->all();
    }

    /****
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->categoryClient->find($id);
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->categoryClient->create($data);
    }

    /***
     * @param $value
     * @return mixed
     */
    public function getByName($value)
    {
        $categoryClients = $this->getAll();
        return $categoryClient = $categoryClients->first(function ($item, $key) use ($value) {
            return $item->name === $value ;
        });
    }

}