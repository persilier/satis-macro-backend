<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsInstitutionTypesSeeder extends Seeder
{

    public function addInstitutionTypeToPermission($permission, $institutionType)
    {
        $institution_types = is_null($permission->institution_types)
            ? []
            : json_decode($permission->institution_types);

        if (!in_array($institutionType, $institution_types)) {
            array_push($institution_types, $institutionType);
        }

        $permission->update(['institution_types' => json_encode($institution_types)]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $holdingPermissions = [
            'list-any-institution', 'store-any-institution', 'update-any-institution', 'destroy-any-institution', 'show-any-institution',
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-any-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-claim-archived', 'show-claim-archived',
            'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
            'list-client-from-any-institution', 'store-client-from-any-institution', 'update-client-from-any-institution', 'destroy-client-from-any-institution', 'show-client-from-any-institution',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-all-institution', 'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions',  'store-discussion',  'destroy-discussion',  'list-discussion-contributors',  'add-discussion-contributor',  'remove-discussion-contributor',  'contribute-discussion',

        ];

        $filialPermissions = [
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-my-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-claim-archived', 'show-claim-archived',
            'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
            'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions',  'store-discussion',  'destroy-discussion',  'list-discussion-contributors',  'add-discussion-contributor',  'remove-discussion-contributor',  'contribute-discussion',

        ];

        $observatoryPermissions = [
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-without-client',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-any-institution', 'show-claim-awaiting-validation-any-institution', 'validate-treatment-any-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-claim-archived', 'show-claim-archived',
            'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-all-institution', 'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions',  'store-discussion',  'destroy-discussion',  'list-discussion-contributors',  'add-discussion-contributor',  'remove-discussion-contributor',  'contribute-discussion',

        ];

        $memberPermissions = [
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions',  'store-discussion',  'destroy-discussion',  'list-discussion-contributors',  'add-discussion-contributor',  'remove-discussion-contributor',  'contribute-discussion',

        ];

        $independantPermissions = [
            'list-any-institution', 'store-any-institution', 'update-any-institution', 'destroy-any-institution', 'show-any-institution',
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-my-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-claim-archived', 'show-claim-archived',
            'list-claim-satisfaction-measured', 'update-claim-satisfaction-measured',
            'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions',  'store-discussion',  'destroy-discussion',  'list-discussion-contributors',  'add-discussion-contributor',  'remove-discussion-contributor',  'contribute-discussion',

        ];

        foreach (Permission::where('guard_name', 'api')->get() as $permission) {
            if (in_array($permission->name, $holdingPermissions)) {
                $this->addInstitutionTypeToPermission($permission, 'holding');
            }
            if (in_array($permission->name, $filialPermissions)) {
                $this->addInstitutionTypeToPermission($permission, 'filiale');
            }
            if (in_array($permission->name, $observatoryPermissions)) {
                $this->addInstitutionTypeToPermission($permission, 'observatory');
            }
            if (in_array($permission->name, $memberPermissions)) {
                $this->addInstitutionTypeToPermission($permission, 'membre');
            }
            if (in_array($permission->name, $independantPermissions)) {
                $this->addInstitutionTypeToPermission($permission, 'independant');
            }
        }

    }
}
