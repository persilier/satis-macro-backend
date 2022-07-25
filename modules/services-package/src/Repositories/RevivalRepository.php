<?php


namespace Satis2020\ServicePackage\Repositories;


use Satis2020\ServicePackage\Models\Revival;

class RevivalRepository
{

    /**
     * @var Revival
     */
    private $revival;

    public function __construct()
    {
        $this->revival = new Revival;
    }


    public function getUnitRevivals($unitId,$size=15)
    {
        return $this->revival
            ->newQuery()
            ->with("claim:reference,id,status","createdBy.identite","institution:name,id","staff.identite")
            ->where('staff_unit_id',$unitId)
            ->paginate($size);
    }

    public function getStaffRevivals($staffId,$size=15)
    {
        return $this->revival
            ->newQuery()
            ->with("claim:reference,id,status","createdBy.identite","institution:name,id","staff.identite")
            ->where('targeted_staff_id',$staffId)
            ->paginate($size);
    }

    public function storeRevival($data)
    {
        return  $this->revival
                    ->newQuery()
                    ->create($data)
                    ->refresh();
    }
}