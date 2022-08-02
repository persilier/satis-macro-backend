<?php
namespace Satis2020\ServicePackage\Policies;
use Illuminate\Auth\Access\HandlesAuthorization;
use Satis2020\ServicePackage\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param User $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        return $user->id === $model->id || in_array('can-view-any-user',
                $user->getPermissionsViaRoles()->pluck('name')->toArray());
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param User $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        return $user->id === $model->id || in_array('can-update-any-user',
                $user->getPermissionsViaRoles()->pluck('name')->toArray());
    }

}
