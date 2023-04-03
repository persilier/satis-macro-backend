<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\InstitutionType;
use Satis2020\ServicePackage\Models\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Trait UserTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait RoleTrait
{

    /**
     * @param $request
     * @return Model|Role
     */
    protected function createRole($request)
    {

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'guard_name' => 'api',
            'is_editable' => 1,
            'institution_types' => json_encode($request->institutionTypes)
        ]);

        return $role->syncPermissions($request->permissions);
    }


    /**
     * @param $request
     * @param $role
     * @return mixed
     */
    protected function updateRole($request, $role)
    {

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'institution_types' => $request->institutionTypes,
        ]);

        return $role->syncPermissions($request->permissions);
    }


    /**
     * @param $request
     * @param $role
     * @return array
     */
    protected function editRole($request, $role)
    {

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        $this->checkIsEditableRole($role);

        $types = $role->institution_types;

        $request->merge(['institutionTypes' => $types]);

        $dataCreate = $this->getAllDatecreateRole($request);

        return [
            "role" => $role,
            "modulesPermissionsRole" => $this->getModulesPermissionsForRole($role),
            "modulesPermissions" => $dataCreate['modulesPermissions'],
            "institutionType" => $types,
            "institutionTypes" => $dataCreate['institutionTypes']
        ];
    }


    /**
     * @param $request
     * @return array
     */
    protected function getAllDatecreateRole($request)
    {

        $permissions = [];

        $institutionTypes = InstitutionType::all();

        $institutionTypesNames = InstitutionType::all()->pluck('name');

        foreach ($institutionTypes as $institutionType) {

            $request->merge(['institutionTypes' => [$institutionType->name]]);
            $permissions[$institutionType->name] = $this->getAllPermissions($request)->filter(function ($value,  $key) {
                return count($value->permissions) !== 0;
            })->values();
        }

        if (count($institutionTypes) > 1) {

            $request->merge(['institutionTypes' => $institutionTypesNames]);
            $permissions['all'] = $this->getAllPermissions($request);
        }

        return [
            'institutionTypes' => $institutionTypes,
            'modulesPermissions' => $permissions
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function getAllPermissions($request)
    {

        return Module::all()->map(function ($item) use ($request) {

            $item['permissions'] = Permission::where('guard_name', 'api')->where('module_id', $item->id)->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->get()->filter(function ($permission) use ($request) {

                if (count($request->institutionTypes) == 1) {

                    return (is_array($permission->institution_types) && in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $permission->institution_types));
                } else {

                    return (is_array($permission->institution_types) && in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $permission->institution_types)
                        && in_array(InstitutionType::whereName($request->institutionTypes[1])->firstOrFail()->name, $permission->institution_types));
                }
            })->flatten()->all();

            return $item;
        });
    }



    /**
     * @param $role
     * @return Builder[]|Collection
     */
    protected function getModulesPermissionsForRole($role)
    {

        return $role->load('permissions');
    }


    /**
     * @param $role
     * @return array
     */
    protected function getRole($role)
    {

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->firstOrFail();

        return [
            'role' => $role,
            'module' => $this->getModulesPermissionsForRole($role),
        ];
    }


    /**
     * @param null $role
     * @return array
     */
    protected function rule($role = NULL)
    {

        return  [
            'name' => ['required', Rule::unique(config('permission.table_names.roles'))->where(function ($q) use ($role) {
                return $q->where('name', '!=', $role);
            })],
            'permissions' => 'required|array',
            'institutionTypes' => 'required|array',
            'description' => 'required|string'
        ];
    }


    /**
     * @param $request
     */
    protected function verifiedStore($request)
    {

        $nbreType = count($request->institutionTypes);

        foreach ($request->permissions as $permission) {

            $institutionType = Permission::where('guard_name', 'api')->whereNotNull('module_id')->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->where('name', $permission)->firstOrFail()->institution_types;

            if ($nbreType == 2) {

                if (!in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $institutionType) || !in_array(InstitutionType::whereName($request->institutionTypes[1])->firstOrFail()->name, $institutionType)) {

                    throw new CustomException(__('errors.cant_give_permission', ['permission' => $permission], app()->getLocale()));
                }
            } else {

                if (!in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $institutionType)) {

                    throw new CustomException(__('errors.cant_give_permission', ['permission' => $permission], app()->getLocale()));
                }
            }
        }
    }


    /**
     * @param $role
     */
    protected function checkIsEditableRole($role)
    {

        if ($role->is_editable == 0) {

            throw new CustomException(__('errors.cant_delete_role', [], app()->getLocale()));
        }
    }

    protected function checkIsUsedRole($role)
    {

        $item = Role::withCount('users')->findOrFail($role->id);
        if ($item->users_count != 0) {
            throw new CustomException("Impossible de supprimer ce role car il est déjà attribué.");
        }
    }
}
