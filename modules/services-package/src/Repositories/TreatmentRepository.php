<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\User;
/**
 * Class UserRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class TreatmentRepository
{
    /**
     * @var User
     */
    private $treatment;

    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->treatment = new Treatment();
    }

    /***
     * @param $id
     * @param string $type
     * @return mixed
     */
    public function getById($id,$type=Treatment::NORMAL) {

        return $this->treatment
            ->newQuery()
            ->where("type",$type)
            ->where('id',$id)
            ->first();
    }

    /***
     * @param $claimId
     * @param string $type
     * @return mixed
     */
    public function getByClaimId($claimId,$type=Treatment::NORMAL) {

        return $this->treatment
            ->newQuery()
            ->where("type",$type)
            ->where('claim_id',$claimId)
            ->first();
    }



}