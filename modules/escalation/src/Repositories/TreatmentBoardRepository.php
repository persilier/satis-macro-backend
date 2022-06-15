<?php

namespace Satis2020\Escalation\Repositories;

use Satis2020\Escalation\Models\TreatmentBoard;
use Satis2020\ServicePackage\Models\User;
/**
 * Class TreatmentBoardRepository
 * @package Satis2020\Escalation\Repositories
 */
class TreatmentBoardRepository
{
    /**
     * @var TreatmentBoard
     */
    private $treatmentBoard;

    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->treatmentBoard = new TreatmentBoard;
    }

    public function getById($id)
    {
        return $this->treatmentBoard
            ->newQuery()
            ->findOrFail($id)
            ->load('members');
    }


    /**
     * @param $data
     * @param null $members
     */
    public function store($data, $members=null)
    {
        $treatmentBoard = $this->treatmentBoard
            ->newQuery()
            ->create($data)->refresh()->load("members");
        if ($members) {
            $treatmentBoard->members()->sync($members);
        }
        return $treatmentBoard;
    }
        /**
         * @param $data
         * @param $treatmentBoard
         * @param null $members
         */
    public function update($data,$treatmentBoard, $members=null)
    {
        $treatmentBoard
            ->update($data);

        if ($members){
            $treatmentBoard->load('members');
            $treatmentBoard->members()->sync($members);
        }

        return $treatmentBoard->refresh()->load('members');
    }
}