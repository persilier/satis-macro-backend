<?php
namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Models\Role;

class AddDescriptionToRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Role::query()
            ->chunk(20,function ($roles){
                foreach ($roles as $role){
                    if ($role->description==null)
                        $role->update(["description"=>ucfirst(str_replace("-"," ",$role->name))]);
                }
            });
    }
}
