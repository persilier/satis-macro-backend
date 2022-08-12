<?php


namespace Satis2020\ServicePackage\Repositories;


use Satis2020\ServicePackage\Models\Staff;

class StaffRepository
{

    /**
     * @var Staff
     */
    private $staff;

    public function __construct()
    {
        $this->staff = new Staff;
    }

    public function getStaffsByIdentities($identityIds)
    {
        return $this->staff->newQuery()
                ->whereIn('identite_id',$identityIds)
                ->get();
    }

    function getStaffById($staffId)
    {
        return $this->staff->newQuery()
            ->with("institution","identite")
            ->where('id',$staffId)
            ->first();
    }
}