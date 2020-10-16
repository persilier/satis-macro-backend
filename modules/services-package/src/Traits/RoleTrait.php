<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\InstitutionType;
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
    protected function createRole($request){

        $role = Role::create(['name' => $request->name, 'guard_name' => 'api', 'institution_types' => json_encode ($request->institutionTypes)]);

        return $role->syncPermissions($request->permissions);

    }


    /**
     * @param $request
     * @param $role
     * @return mixed
     */
    protected function updateRole($request, $role){

        $role->update([
            'name' => $request->name,
            'institution_types' => json_encode($request->institutionTypes),
        ]);

        return $role->syncPermissions($request->permissions);
    }


    /**
     * @param $request
     * @param $role
     * @return array
     */
    protected function editRole($request, $role){

        $role = Role::whereName($role)->where('guard_name', 'api')->withCasts(['institution_types' => 'array'])->with('permissions')->firstOrFail();

        $types = $role->institution_types;

        $request->merge(['institutionTypes' => $types]);

        return [
            "role" => $role,
            "permissions" => $this->getAllPermissions($request),
            "institutionType" => $types,
            "institutionTypes" => InstitutionType::all()
        ];

    }

    /**
     * @param $request
     * @return mixed
     */
    protected function getAllPermissions($request){

        return Permission::where('guard_name', 'api')->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->get()->filter(function($permission) use ($request){

            if(count($request->institutionTypes) == 1){

                return (is_array($permission->institution_types) && in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $permission->institution_types));

            }else{

                return (is_array($permission->institution_types) && in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $permission->institution_types)  && in_array(InstitutionType::whereName($request->institutionTypes[1])->firstOrFail()->name, $permission->institution_types));
            }

        })->flatten()->all();

    }


    /**
     * @param null $role
     * @return array
     */
    protected function rule($role = NULL){

        return  [
            'name' => 'required|unique:'.config('permission.table_names.roles').',name,'.$role.',name',
            'permissions' => 'required|array',
            'institutionTypes' => 'required|array'
        ];
    }


    /**
     * @param $request
     */
    protected function verifiedStore($request){

        $nbreType = count($request->institutionTypes);

        foreach ($request->permissions as $permission){

            $institutionType = Permission::where('guard_name', 'api')->whereNotNull('institution_types')->withCasts(['institution_types' => 'array'])->where('name', $permission)->firstOrFail()->institution_types;

            if($nbreType == 2){

                if(!in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $institutionType) || !in_array(InstitutionType::whereName($request->institutionTypes[1])->firstOrFail()->name, $institutionType)){

                    throw new CustomException("Impossible d'attribuer la permission {$permission} à ce rôle.");
                }

            }else{

                if(!in_array(InstitutionType::whereName($request->institutionTypes[0])->firstOrFail()->name, $institutionType)){

                    throw new CustomException("Impossible d'attribuer la permission  {$permission} à ce rôle.");
                }

            }

        }
    }


}
