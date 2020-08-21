<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Trait UserTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait UserTrait
{

    /**
     * @param $user
     * @return mixed
     */
    protected function getUserWithRoleName($user){

         $user['role'] = $user->role();
         return $user;

    }

    /**
     * @param bool $myInstitution
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    protected function getAllUser($myInstitution = false){

        $users = User::with(['identite.staff']);

        if($myInstitution){

            $institution = $this->institution();

            $users = $users->whereHas('identite', function($query) use ($institution){
                $query->whereHas('staff', function($q) use ($institution){
                    $q->where('institution_id', $institution->id);
                });
            });
        }

        return $users->get()->map(function($user){
            return $this->getUserWithRoleName($user);
        });

    }


    /**
     * @param bool $institution
     * @return array
     */
    protected function rulesCreateUser($institution = true){

        $rules = [
            'password' => 'required|min:8|confirmed',
            'identite_id' => 'required|exists:identites,id',
            'role' => 'required|exists:roles,name',
        ];

        if($institution){

            $rules[ 'institution_id'] = 'required|exists:institutions,id';
        }

        return $rules;
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function verifiedRoleTypeInstitution($request){

        $identite = Identite::with('staff.institution.institutionType')->whereHas('staff', function($query) use ($request){

            $query->where('institution_id', $request->institution_id);

        })->doesntHave('user')->findOrFail($request->identite_id);

        $role = Role::where('name', $request->role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        if(is_array($role->institution_types) && in_array($identite->staff->institution->institutionType->name, $role->institution_types)){

            return [
                'identite' => $identite,
                'role' => $role
            ];

        }

        throw new CustomException("Ce rôle n'existe pas pour ce type d'institution.");
    }


    /**
     * @param $request
     * @param $identiteRole
     * @return mixed
     */
    protected function storeUser($request, $identiteRole){

        $identite = $identiteRole['identite'];

        $role = $identiteRole['role'];

        $user = User::create([
            'username' => $identite->email[0],
            'password' => bcrypt($request->password),
            'identite_id' => $identite->id
        ]);

        $user->assignRole($role);

        return $user;
    }


    /**
     * @param $user
     * @param bool $myInstitution
     * @return mixed
     */
    protected function getOneUser($user, $myInstitution = false){

        $institution = $this->institution();

        if($myInstitution){

            if($user->identite->staff->institution->id !== $institution->id){

                throw new CustomException("Ce rôle n'existe pas pour ce type d'institution.");

            }

        }

        return $this->getUserWithRoleName($user);

    }


    /**
     * @param $institution
     * @return array
     */
    protected function getAllIdentitesRoles($institution){

        $identites = Identite::with('staff')->whereHas('staff', function($q) use ($institution){

            $q->where('institution_id', $institution->id);

        })->doesntHave('user')->get();

        $roles = Role::where('guard_name', 'api')->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->get()->filter(function($role) use ($institution){

            return (is_array($role->institution_types) && in_array($institution->institutionType->name, $role->institution_types));

        })->flatten()->all();

        return [
            'identites' => $identites,
            'roles' => $roles
        ];
    }

    /**
     * @param bool $myChangePassword
     * @return array
     */
    protected function rulesChangePassword($myChangePassword = false){

        $rules = [
            'new_password' => ['required'],
            'password_confirmation' => ['same:new_password'],
        ];

        if($myChangePassword){

            $rules['current_password'] = ['required'];
        }

        return $rules;

    }


    protected function rulesChangeRole(){

        $rules = [
            'new_password' => ['required'],
            'password_confirmation' => ['same:new_password'],
        ];

        return $rules;
    }


    /**
     * @param $user
     * @return mixed
     */
    protected function myUser($user){


        try{

            if($user->identite->staff->institution->id !== $this->institution()->id){

                throw new CustomException("Impossible de modifier le mot de passe de cet utilisateur.");

            }

        }catch (\Exception $exception){

            throw new CustomException("Impossible de récupérer cet utilisateur.");
        }

    }





    /**
     * @param $request
     * @param $user
     * @return mixed
     */
    protected function updatePassword($request, $user){

        $user->update(['password'=> Hash::make($request->new_password)]);

        return $user;

    }

}