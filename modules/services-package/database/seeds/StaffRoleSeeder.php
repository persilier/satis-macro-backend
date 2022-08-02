<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffRoleSeeder extends Seeder
{
    public function createCollectorRoleHolding()
    {
        // create admin roles
        $roleCollector = Role::create(['name' => 'collector-holding', 'guard_name' => 'api']);

        $roleCollector->givePermissionTo([
            Permission::where('name', 'store-claim-against-any-institution')->where('guard_name', 'api')->firstOrFail(),
        ]);
    }

    public function createCollectorRoleFilial()
    {
        // create admin roles
        $roleCollector = Role::create(['name' => 'collector', 'guard_name' => 'api']);

        $roleCollector->givePermissionTo([
            Permission::where('name', 'store-claim-against-my-institution')->where('guard_name', 'api')->firstOrFail(),
        ]);
    }

    public function createCollectorRoleObservatory()
    {
        // create admin roles
        $roleCollector = Role::create(['name' => 'collector-observatory', 'guard_name' => 'api']);

        $roleCollector->givePermissionTo([
            Permission::where('name', 'store-claim-without-client')->where('guard_name', 'api')->firstOrFail()
        ]);
    }

    public function createCatererRole()
    {
        // create admin roles
        $roleCaterer = Role::create(['name' => 'caterer', 'guard_name' => 'api']);

        $roleCaterer->givePermissionTo([
            Permission::where('name', 'list-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail(),
            Permission::where('name', 'show-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail(),
            Permission::where('name', 'rejected-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail(),
            Permission::where('name', 'self-assignment-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail(),
            Permission::where('name', 'assignment-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail(),
            Permission::where('name', 'unfounded-claim-awaiting-treatment')->where('guard_name', 'api')->firstOrFail()
        ]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createCollectorRoleHolding();
        $this->createCollectorRoleFilial();
        $this->createCollectorRoleObservatory();
        $this->createCatererRole();
    }
}
