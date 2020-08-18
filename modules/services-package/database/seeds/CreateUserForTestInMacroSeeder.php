<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Discussion;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Message;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Role;

class CreateUserForTestInMacroSeeder extends Seeder
{

    public function createUser($identity, $roleName)
    {
        $user = User::create([
            'id' => (string)Str::uuid(),
            'username' => $identity->email[0],
            'password' => bcrypt('123456789'),
            'identite_id' => $identity->id,
            'disabled_at' => null
        ]);

        $user->assignRole(
            Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail()
        );

        return $user;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Identite::all()->map(function ($item, $key) {

//            if (in_array($item->email[0], ['dga.macro@satis.com', 'cons.macro@satis.com'])) {
//                $this->createUser($item, 'staff');
//            }
//
//            if (in_array($item->email[0], ['dgac.macro@satis.com', 'consc.macro@satis.com'])) {
//                $this->createUser($item, 'collector-holding');
//            }
//
//            if (in_array($item->email[0], ['dgap.macro@satis.com', 'consp.macro@stais.com'])) {
//                $this->createUser($item, 'pilot-holding');
//            }

            if (in_array($item->email[0], ['consp.macro@satis.com'])) {
                $this->createUser($item, 'pilot-holding');
            }

            return true;
        });
    }
}
