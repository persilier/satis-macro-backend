<?php


namespace Satis2020\ServicePackage\Requests;


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
}