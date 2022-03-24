<?php
namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AddDescriptionToPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Permission::query()
            ->chunk(20,function ($permissions){
                foreach ($permissions as $permission){
                    if ($permission->description==null)
                        $permission->update(["description"=>ucfirst(str_replace("-"," ",$permission->name))]);
                }
            });
    }
}
