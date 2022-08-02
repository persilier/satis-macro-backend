<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\User;
/**
 * Class UserRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class UserRepository
{
    /**
     * @var User
     */
    private $user;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->user->find($id);
    }

    /**
     * @param $email
     * @return
     */
    public function getByEmail($email)
    {
        return $this->user->where('username', $email)->first();
    }

    /***
     * @param $data
     * @param $id
     * @return mixed
     */
    public function update($data, $id) {
        $user = $this->getById($id);
        $user->update($data);
        return $user->refresh();
    }

    /**
     * @param $identityId
     * @param $data
     * @return User
     */
    public function updateUserByIdentity($identityId,$data)
    {
        $this->user->newQuery()
                    ->where("identite_id",$identityId)->update($data);

        return $this->user->refresh();
    }

    /**
     * @param $identityId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getUserByIdentity($identityId)
    {
        return $this->user->newQuery()
            ->where("identite_id",$identityId)->first();

    }

    public function getUserByInstitution($institutionId)
    {
        return $this->user->with(['identite.staff'])
            ->join('identites', function($join)  use ($institutionId){
                $join->on('users.identite_id', '=', 'identites.id')
                    ->join('staff', function ($j) use ($institutionId){
                        $j->on('identites.id', '=', 'staff.identite_id')
                        ->where('staff.institution_id', $institutionId);
                    });
            })->where('institution_id', $institutionId)
            ->select('users.*')
            ->get();
    }

    public function getInstitutionByUser($user_id)
    {
        $staff = $this->getById($user_id)->load('identite.staff')->identite->staff;
        return Institution::query()->findOrFail($staff->institution_id);
    }

}