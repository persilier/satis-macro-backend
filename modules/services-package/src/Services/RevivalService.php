<?php


namespace Satis2020\ServicePackage\Services;


use Satis2020\ServicePackage\Repositories\RevivalRepository;
use Satis2020\ServicePackage\Traits\DataUserNature;

class RevivalService
{
    use DataUserNature;

    /**
     * @var RevivalRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new RevivalRepository();
    }


    public function getUnitRevivals($unitId,$size=15)
    {
        return $this->repository->getUnitRevivals($unitId,$size);
    }

    public function getStaffRevivals($staffId,$size=15)
    {
        return $this->repository->getStaffRevivals($staffId,$size);
    }


    public function storeRevival($staffs,$message,$claim)
    {
        foreach ($staffs as $staff){

            $data = [
                "claim_id"=>$claim->id,
                "message"=>$message,
                "staff_unit_id"=>$this->staff()->unit_id,
                "claim_status"=>$claim->status,
                "targeted_staff_id"=>$staff->id,
                "institution_id"=>$this->institution()->id,
                "created_by"=>$this->staff()->id,
                ];

            $this->repository->storeRevival($data);
        }

    }
}