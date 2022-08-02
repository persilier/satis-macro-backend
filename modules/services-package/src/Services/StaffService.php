<?php


namespace Satis2020\ServicePackage\Services;



use Satis2020\ServicePackage\Repositories\StaffRepository;

class StaffService
{

    /**
     * @var StaffRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new StaffRepository();
    }

    public function getStaffsByIdentity($identityIds)
    {
       return $this->repository->getStaffsByIdentities($identityIds);
    }

    public function getStaffById($staffId)
    {
        return $this->repository->getStaffById($staffId);
    }
}