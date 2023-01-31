<?php


namespace Satis2020\ServicePackage\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\InactivityReactivationHistory;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Rules\IsValidPasswordRules;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
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
    protected function getUserWithRoleName($user)
    {

        $user['role'] = $user->role();
        return $user;

    }


    /**
     * @param bool $myInstitution
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    protected function getAllUser($myInstitution = false, $paginate = null, $search_text = null)
    {


        $users = User::with(['identite.staff', 'roles']);
        if ($myInstitution) {
            $institution = $this->institution();
            $users = $users->whereHas('identite', function ($query) use ($institution) {
                $query->whereHas('staff', function ($q) use ($institution) {
                    $q->where('institution_id', $institution->id);
                });
            });
        }

        if ($search_text) {
            $users = $users->whereHas("identite", function ($query) use ($search_text) {
                $query->where('firstname', 'like', "%$search_text%")
                    ->orWhere('lastname', 'like', "%$search_text%")
                    ->orwhereJsonContains('telephone', $search_text)
                    ->orwhereJsonContains('email', $search_text);
            })->orWhereHas("roles", function ($q) use ($search_text) {
                $q->where('name', 'like', "%$search_text%");
            });
        }

        if ($paginate) {
            return $users->paginate($paginate);
        } else {
            return $users->get();
        }
    }

    /**
     * @param bool $institution
     * @param bool $update
     * @return array
     */
    protected function rulesCreateUser($institution = true, $update = false)
    {

        if ($update) {

            $rules = [
                'new_password' => ['nullable', 'confirmed', new IsValidPasswordRules],
                'roles' => 'required|array',
            ];

        } else {

            $rules = [
                'password' => ['required', 'confirmed', new IsValidPasswordRules],
                'identite_id' => 'required|exists:identites,id',
                'roles' => 'required|array',
            ];
        }

        if ($institution) {

            $rules['institution_id'] = 'required|exists:institutions,id';

        }

        return $rules;
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function verifiedRoleTypeInstitution($request)
    {

        $identite = Identite::with('staff.institution.institutionType')->whereHas('staff', function ($query) use ($request) {

            $query->where('institution_id', $request->institution_id);

        })->doesntHave('user')->findOrFail($request->identite_id);

        return [
            'identite' => $identite,
            'roles' => $this->verifiedRole($request, $identite)
        ];
    }


    /**
     * @param $request
     * @param $identiteRole
     * @return mixed
     */
    protected function storeUser($request, $identiteRole)
    {

        $identite = $identiteRole['identite'];

        $roles = $identiteRole['roles'];

        $user = User::create([

            'username' => $identite->email[0],
            'password' => bcrypt($request->password),
            'identite_id' => $identite->id

        ]);

        $user->assignRole($roles);

        return $user;
    }


    /**
     * @param $user
     * @param bool $myInstitution
     * @return mixed
     */
    protected function getOneUser($user, $myInstitution = false)
    {

        if ($myInstitution) {

            if ($user->identite->staff->institution->id !== $this->institution()->id) {

                throw new CustomException("Ce rôle n'existe pas pour ce type d'institution.");

            }

        }

        return $user->load('roles');

    }


    /**
     * @param $institution
     * @return array
     */
    protected function getAllIdentitesRoles($institution)
    {

        $identites = Identite::with('staff')->whereHas('staff', function ($q) use ($institution) {

            $q->where('institution_id', $institution->id);

        })->doesntHave('user')->get();

        return [

            'identites' => $identites,
            'roles' => $this->getAllRolesInstitutionTypes($institution)

        ];
    }


    /**
     * @param $user
     * @return mixed
     */
    protected function myUser($user)
    {


        try {

            if ($user->identite->staff->institution->id !== $this->institution()->id) {

                throw new CustomException("Impossible de modifier le mot de passe de cet utilisateur.");

            }

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer cet utilisateur.");
        }

    }


    /**
     * @param $request
     * @param $user
     * @return mixed
     */
    protected function updatePassword($request, $user)
    {
        //dd($user()->token());
        $user->update(['password' => Hash::make($request->new_password)]);

        return $user;

    }

    /**
     * @param $user
     * @param bool $my
     * @return mixed
     */
    protected function statusUser($user, $my = false)
    {

        if ($my) {

            $this->myUser($user);
        }

        $status = NULL;

        if (is_null($user->disabled_at)) {

            $status = Carbon::now();

        } else {
            /*
             *
             */
            $configs = $this->getMetadataByName(Metadata::AUTH_PARAMETERS);

            if ($this->inactivityTimeIsPassed($user, $configs)) {
                InactivityReactivationHistory::create([
                    'user_id' => $user->id,
                ]);
            }

        }

        $user->update(['disabled_at' => $status]);

        return $user;
    }


    /**
     * @param $user
     * @param bool $my
     * @return mixed
     */
    protected function getAllRolesInstitutionUser($user, $my = false)
    {

        if ($my) {

            $this->myUser($user);
        }

        try {

            $roles = $this->getAllRolesInstitutionTypes($user->identite->staff->institution);

        } catch (\Exception $exception) {

            throw new CustomException("Impossible de récupérer les rôles de l'institution de cet utilisateur.");
        }

        return $roles;
    }


    /**
     * @param $request
     * @param $identite
     * @return mixed
     */
    protected function verifiedRole($request, $identite)
    {

        $roles = collect([]);
        foreach ($request->roles as $key => $value) {

            $role = Role::where('name', $value)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

            if (in_array($identite->staff->institution->institutionType->name, $role->institution_types)) {

                $roles->push($role);

            } else {

                throw new CustomException("Le champ rôle est invalide.");
            }

        }
        return $roles;

    }


    /**
     * @param $institution
     * @return mixed
     */
    protected function getAllRolesInstitutionTypes($institution)
    {

        return Role::where('guard_name', 'api')->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->get()->filter(function ($role) use ($institution) {

            return (is_array($role->institution_types) && in_array($institution->institutionType->name, $role->institution_types));

        })->flatten()->all();

    }


    /**
     * @param $user
     * @param $roles
     * @return mixed
     */
    protected function remokeAssigneRole($user, $roles)
    {

        $role_old = $user->load('roles');

        if (!is_null($role_old)) {

            DB::table(config('permission.table_names.model_has_roles'))->where(config('permission.column_names.model_morph_key'), $user->id)->delete();

        }

        $user->assignRole($roles);

        return $user;
    }


}
