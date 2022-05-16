<?php


namespace Satis2020\ServicePackage\Services;


use Satis2020\ServicePackage\Repositories\UserRepository;

class UserService
{

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository  $repository)
    {
        $this->repository = $repository;
    }

    public function updateUserByIdentity($identityId,$data)
    {
        return $this->repository->updateUserByIdentity($identityId,$data);
    }
}