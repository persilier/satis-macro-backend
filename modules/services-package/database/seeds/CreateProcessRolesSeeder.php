<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateProcessRolesSeeder extends Seeder
{

    public function createRole($roleName, $rolePermissions)
    {
        $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();
        if (!is_null($role)) {
            $role->forceDelete();
        }
        $role = Role::create(['name' => $roleName, 'guard_name' => 'api']);
        $role->givePermissionTo($rolePermissions);
    }

    public function createMissingPermission()
    {
        Permission::create(['name' => 'list-claim-assignment-to-staff', 'guard_name' => 'api']);
        Permission::create(['name' => 'show-claim-assignment-to-staff', 'guard_name' => 'api']);
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $nature = env('APP_NATURE');

        if ($nature === 'DEVELOP') {
            $collection = collect([
                'collector-holding' => [
                    'store-claim-against-any-institution'
                ],
                'collector-filial-pro' => [
                    'store-claim-against-my-institution'
                ],
                'collector-observatory' => [
                    'store-claim-without-client'
                ],
                'staff' => [
                    'list-claim-awaiting-treatment',
                    'show-claim-awaiting-treatment',
                    'rejected-claim-awaiting-treatment',
                    'self-assignment-claim-awaiting-treatment',
                    'assignment-claim-awaiting-treatment',
                    'list-claim-assignment-to-staff',
                    'show-claim-assignment-to-staff',
                ]
            ]);
        }

        if ($nature === 'MACRO') {
            $collection = collect([
                'collector-holding' => [
                    'store-claim-against-any-institution'
                ],
                'collector-filial-pro' => [
                    'store-claim-against-my-institution'
                ],
                'staff' => [
                    'list-claim-awaiting-treatment',
                    'show-claim-awaiting-treatment',
                    'rejected-claim-awaiting-treatment',
                    'self-assignment-claim-awaiting-treatment',
                    'assignment-claim-awaiting-treatment',
                    'list-claim-assignment-to-staff',
                    'show-claim-assignment-to-staff',
                ]
            ]);
        }

        if ($nature === 'HUB') {
            $collection = collect([
                'collector-observatory' => [
                    'store-claim-without-client'
                ],
                'staff' => [
                    'list-claim-awaiting-treatment',
                    'show-claim-awaiting-treatment',
                    'rejected-claim-awaiting-treatment',
                    'self-assignment-claim-awaiting-treatment',
                    'assignment-claim-awaiting-treatment',
                    'list-claim-assignment-to-staff',
                    'show-claim-assignment-to-staff',
                ]
            ]);
        }

        if ($nature === 'PRO') {
            $collection = collect([
                'collector-filial-pro' => [
                    'store-claim-against-my-institution'
                ],
                'staff' => [
                    'list-claim-awaiting-treatment',
                    'show-claim-awaiting-treatment',
                    'rejected-claim-awaiting-treatment',
                    'self-assignment-claim-awaiting-treatment',
                    'assignment-claim-awaiting-treatment',
                    'list-claim-assignment-to-staff',
                    'show-claim-assignment-to-staff',
                ]
            ]);
        }

        $collection->map(function ($item, $key) use ($nature) {
            $this->createRole($key, $item);
            return $item;
        });

    }
}
