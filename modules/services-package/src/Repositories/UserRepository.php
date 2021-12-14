<?php

namespace Satis2020\ServicePackage\Repositories;

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

}