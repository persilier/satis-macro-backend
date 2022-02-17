<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Institution;
/**
 * Class InstitutionRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class InstitutionRepository
{
    /***
     * @var Institution
     */
    protected $institution;

    /***
     * InstitutionRepository constructor.
     * @param Institution $institution
     */
    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
    }

    /****
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->institution->find($id);
    }

    /****
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->institution->create($data);
    }

    /***
     * @param $name
     * @return mixed
     */
    public function getByName($name)
    {
        return $this->institution->where('name' , $name)->first();
    }

}