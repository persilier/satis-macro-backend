<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Institution;
/**
 * Class InstitutionRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class InstitutionRepository
{
    /**
     * @var Institution
     */
    private $file;
    /**
     * @var Institution
     */
    private $institution;


    /**
     * InstitutionRepository constructor.
     * @param Institution $institution
     */
    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
    }

    /***
     *
     * @return mixed
     */
    public function getAll() {
        return $this->institution->newQuery()->get();
    }




}