<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Role;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;

class MacroStaffSeeder extends Seeder
{

    public function createIdentity()
    {
        $faker = Faker::create();

        $sexe = ['M','F'];

        $firstName =  $faker->firstName;
        $lastName =   $faker->lastName;
        $email = strtolower($firstName.$lastName.mt_rand(100,999))."@dmdconsult.com";
        return $identity = Identite::create([
            'id' => (string)Str::uuid(),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'sexe' => $sexe[random_int(0,1)],
            'telephone' => [mt_rand(10000000,99999999)],
            'email' => [$email],
        ]);
    }

    public function createUser($identity)
    {
        return $user = User::create([
            'id' => (string)Str::uuid(),
            'username' => $identity->email[0],
            'password' => bcrypt('123456789'),
            'identite_id' => $identity->id,
            'disabled_at' => null
        ]);
    }

    public function createStaff($unit,$identity)
    {

        return $staff = Staff::create([
            'id' => (string)Str::uuid(),
            'identite_id' => $identity->id,
            'position_id' => Position::all()->random()->id,
            'institution_id' => $unit->institution->id,
            'unit_id' => $unit->id,
            'feedback_preferred_channels' => ["email"]
        ]);
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("Génération des agents en cours...");

        $units = Unit::with('institution.institutionType')->get();
        foreach ($units as $unit) {
            $this->command->info("--------------------------- INSTITUTION {$unit->name} ---------------------------");
            $roles = Role::query()->get();
            foreach ($roles as $role){
                $this->command->info("Création des agents pour le rôle {$role->name}...");

                foreach(range(0,3) as $i){
                    $identity = $this->createIdentity();
                    $staff = $this->createStaff($unit,$identity);
                    $user = $this->createUser($identity);

                    $user->assignRole([$role->name]);
                    if ($i=0){
                        if ($role->name == 'staff'){
                            $unit->update(['lead_id' => $staff->id]);
                        }
                        if ($role->name == 'pilot'){
                            $unit->institution->update(['active_pilot_id' => $staff->id]);
                        }
                    }
                }
                $this->command->info("Création des agents pour le rôle {$role->name} terminée avec succès...");
            }
        }

        $this->command->info("La génération des agent est terminée avec succès !");

    }
}
