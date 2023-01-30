<?php

namespace Satis2020\Escalation\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

    public function getAll($size=15)
    {
        return $this->treatmentBoard
            ->newQuery()
            ->with([
                'members.identite',
                'members.unit',
                'members.position',
                'claim.claimObject'
            ])
            ->paginate($size);
    }

    public function getById($id)
    {
        return $this->treatmentBoard
            ->newQuery()
            ->with([
                'members.identite',
                'members.unit',
                'members.position',
                'claim.claimObject'
            ])
            ->findOrFail($id);
    }


    /**
     * @param $data
     * @param null $members
     */
    public function store($data,$members=null)
    {
        $treatmentBoard = $this->treatmentBoard
            ->newQuery()
            ->create($data)->refresh()->load("members");
        if (!empty($members)) {
            $treatmentBoard->members()->sync($members);
        }
        return $treatmentBoard;
    }

    /**
     * @param $data
     * @param $treatmentBoardId
     * @param null $members
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function update($data,$treatmentBoardId, $members=null)
    {
        $treatmentBoard = $this->getById($treatmentBoardId);
        $treatmentBoard->update($data);

        if (!empty($members)){
            $treatmentBoard->members()->sync($members);
        }

        return $treatmentBoard->refresh()->load('members');
    }

    public function getStandardBoard()
    {
        return $this->treatmentBoard
            ->newQuery()
            ->where('type',TreatmentBoard::STANDARD)
            ->first();
    }
}