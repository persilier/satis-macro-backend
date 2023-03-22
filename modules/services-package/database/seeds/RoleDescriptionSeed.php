<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use PgSql\Lob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RoleDescriptionSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = \Spatie\Permission\Models\Role::all();

        foreach ($roles as $role) {
            if ($role->name == 'admin-filial')
                $role->update(['description' => "Administrateur niveau filiale"]);

            if ($role->name == 'pilot-filial')
                $role->update(['description' => "Pilote niveau filiale"]);

            if ($role->name == 'collector-filial-pro' && env('APP_NATURE') == 'MACRO')
                error_log($role->name);
                $role->update(['description' => "Collecteur niveau filiale"]);

            if ($role->name == 'staff')
                $role->update(['description' => "Staff"]);

            if ($role->name == 'admin-holding')
                $role->update(['description' => "Administrateur niveau holding"]);

            if ($role->name == 'pilot-holding')
                $role->update(['description' => "Pilote niveau holding"]);

            if ($role->name == 'collector-holding')
                $role->update(['description' => "Collecteur niveau holding"]);

            if ($role->name == 'admin-pro')
                $role->update(['description' => "Administrateur"]);

            if ($role->name == 'pilot')
                $role->update(['description' => "Pilote"]);

            if (($role->name == 'collector-filial-pro' && env('APP_NATURE') == 'PRO') || $role->name == 'collector-observatory')
                $role->update(['description' => "Collecteur"]);

            if ($role->name == 'admin-observatory')
                $role->update(['description' => "Administrateur"]);

            if ($role->name == 'satisfaction-mesure' && (env('APP_NATURE') == 'PRO' || env('APP_NATURE') == 'MACRO'))
                $role->update(['description' => "Role de mesure les observation"]);

            if ($role->name == 'internal-controller' && (env('APP_NATURE') == 'PRO' || env('APP_NATURE') == 'MACRO'))
                $role->update(['description' => "Contrôle interne"]);
        }
    }
}
