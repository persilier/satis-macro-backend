<?php

namespace Satis2020\MonitoringClaimMyInstitution\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
/**
 * Class RolesTableSeeder
 * @package Satis2020\MonitoringClaimMyInstitution\Database\Seeds
 */
class RolesTableSeeder extends Seeder
{

    function storeStaff($institution, $nature){

        $faker = Faker::create();

        $type_unit = UnitType::create([
            'name' => $faker->name,
            'description' => $faker->text,
            'can_be_target' => 1,
            'can_treat' => 1
        ]);

        

        if ($nature === 'HUB') {
            
            $unit = Unit::create([
                'name' => $faker->name,
                'description' => $faker->text,
                'unit_type_id' =>  $type_unit->id
            ]);

        }else{

            $unit = Unit::create([
                'name' => $faker->name,
                'description' => $faker->text,
                'unit_type_id' =>  $type_unit->id,
                'institution_id'=> $institution->id
            ]);
        }


        $identite = Identite::create([
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'sexe' => 'M',
            'telephone' => [$faker->phoneNumber],
            'email' => [$faker->unique()->safeEmail],
            'ville' => $faker->city,
        ]);

        $position = Position::create([
            'name'=> $faker->name,
            'description' => $faker->text
        ]);

        $institution->positions()->sync([$position->id]);

        $staff = Staff::create([
            'identite_id'=> $identite->id,
            'unit_id' => $unit->id,
            'institution_id' => $institution->id,
            'position_id' => $position->id
        ]);

        $user = User::create([
            'username' => $identite->email[0],
            'password' => bcrypt('123456789'),
            'identite_id' => $identite->id
        ]);

        return $user;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Role::flushEventListeners();

        $nature = env('APP_NATURE');

        // create permissions
        $permission_list = Permission::create(['name' => 'list-monitoring-claim-my-institution', 'guard_name' => 'api']);

        if ($nature === 'DEVELOP') {
            // create admin roles
            $role_supervisor = Role::create(['name' => 'supervisor-filial', 'guard_name' => 'api']);

            $role_supervisor->givePermissionTo(
                $permission_list->name
            );
        }

        if ($nature === 'MACRO') {
            $role_filial = Role::create(['name' => 'supervisor-filial', 'guard_name' =>  'api']);

            $role_filial->givePermissionTo(
                $permission_list->name
            );
        }


        if ($nature === 'PRO') {
            $role_pro = Role::create(['name' => 'supervisor-pro', 'guard_name' =>  'api']);

            $role_pro->givePermissionTo(
                $permission_list->name
            );
        }



        if ($nature === 'HUB') {
            $role_membre = Role::create(['name' => 'supervisor-membre', 'guard_name' =>  'api']);

            $role_membre->givePermissionTo(
                $permission_list->name
            );
        }

        $institutions = Institution::with(['institutionType'])->whereHas('institutionType')->get();

        foreach($institutions as $institution){
            if($institution->institutionType->name === 'filiale'){
                $user = $this->storeStaff($institution, $nature);
                $user->assignRole($role_filial);
            }

            if($institution->institutionType->name === 'pro'){
                $user = $this->storeStaff($institution, $nature);
                $user->assignRole($role_pro);
            }

            if($institution->institutionType->name === 'membre'){
                $user = $this->storeStaff($institution, $nature);
                $user->assignRole($role_membre);
            }
        }

    }
}
