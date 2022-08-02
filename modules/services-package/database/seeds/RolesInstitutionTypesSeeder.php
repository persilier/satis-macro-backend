<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesInstitutionTypesSeeder extends Seeder
{

    public function addInstitutionTypeToRole($role, $institutionType)
    {
        $institution_types = is_null($role->institution_types)
            ? []
            : json_decode($role->institution_types);

        if (!in_array($institutionType, $institution_types)) {
            array_push($institution_types, $institutionType);
        }

        $role->update(['institution_types' => json_encode($institution_types)]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $holdingRoles = [
            "admin-holding",
            "pilot-holding",
            "supervisor-holding",
            "collector-holding",
            "staff"
        ];

        $filialRoles = [
            "admin-filial",
            "pilot-filial",
            "supervisor-filial",
            "collector-filial-pro",
            "staff"
        ];

        $observatoryRoles = [
            "admin-observatory",
            "pilot",
            "supervisor-observatory",
            "collector-observatory",
            "staff"
        ];

        $memberRoles = [
            "supervisor-membre",
            "staff"
        ];

        $independantRoles = [
            "admin-pro",
            "pilot",
            "supervisor-pro",
            "collector-filial-pro",
            "staff"
        ];

        foreach (Role::where('guard_name', 'api')->get() as $role) {
            if (in_array($role->name, $holdingRoles)) {
                $this->addInstitutionTypeToRole($role, 'holding');
            }
            if (in_array($role->name, $filialRoles)) {
                $this->addInstitutionTypeToRole($role, 'filiale');
            }
            if (in_array($role->name, $observatoryRoles)) {
                $this->addInstitutionTypeToRole($role, 'observatory');
            }
            if (in_array($role->name, $memberRoles)) {
                $this->addInstitutionTypeToRole($role, 'membre');
            }
            if (in_array($role->name, $independantRoles)) {
                $this->addInstitutionTypeToRole($role, 'independant');
            }
        }

    }
}
