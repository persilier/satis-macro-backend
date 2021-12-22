<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Identite as Identity;
/**
 * Class IdentityRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class IdentityRepository
{
    /***
     * @var Identity
     */
    private $identity;

    /***
     * IdentityRepository constructor.
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /***
     * @return \Illuminate\Database\Eloquent\Collection|Identity[]
     */
    public function getAll()
    {
        return $this->identity->all();
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->identity->find($id);
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->identity->create($data);
    }

    public function getByTelephonesOrEmails($values, $attribute = 'email')
    {
        $identities = $this->getAll();

        foreach ($values as $value) {
            if ($identity = $this->getByTelephoneOrEmail($identities, $value, $attribute)) {
                return $identity;
            }
        }

        return null;
    }

    /***
     * @param $identities
     * @param $value
     * @param string $attribute
     * @return mixed
     */
    protected function getByTelephoneOrEmail($identities, $value, $attribute)
    {
        return $identity = $identities->first(function ($item, $key) use ($value, $attribute) {
            return $item->{$attribute} && in_array($value, $item->{$attribute});
        });
    }
    /***
     * @param $data
     * @param $id
     * @return mixed
     */
    public function update($data, $id) {
        $identity = $this->getById($id);
        $identity->update($data);
        return $identity->refresh();
    }

}