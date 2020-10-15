<?php


namespace Satis2020\ServicePackage\Traits;

use Spatie\Permission\Models\Role;

trait ActivePilot
{

    protected function checkIfStaffIsPilot($staff)
    {
        // we verify if he has an user account
        $user = null;

        if (!is_null($staff->identite)) {
            $user = $staff->identite->user;
        }

        if (is_null($user)) {
            return false;
        }

        // we verify if it exists a pilot role according to the institution type found
        $roleName = $this->getPilotRoleNameByInstitution($staff->institution);

        if (is_null($roleName)) {
            return false;
        }

        return $user->hasRole($roleName);
    }

    protected function getPilotRoleNameByInstitution($institution)
    {
        // we verify if he has an institution type
        $institutionType = null;

        if (!is_null($institution)) {
            $institutionType = $institution->institutionType;
        }

        if (is_null($institutionType)) {
            return null;
        }

        // we verify if it exists a pilot role according to the institution type found
        $roleName = null;

        if ($institutionType->name == 'holding') {
            if (Role::where('name', 'pilot-holding')->where('guard_name', 'api')->exists()) {
                $roleName = 'pilot-holding';
            }
        }

        if ($institutionType->name == 'filiale') {
            if (Role::where('name', 'pilot-filial')->where('guard_name', 'api')->exists()) {
                $roleName = 'pilot-filial';
            }
        }

        if ($institutionType->name == 'observatory' || $institutionType->name == 'membre' || $institutionType->name == 'independant') {
            if (Role::where('name', 'pilot')->where('guard_name', 'api')->exists()) {
                $roleName = 'pilot';
            }
        }

        if (is_null($roleName)) {
            return null;
        }

        return $roleName;

    }

}