<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Imports\Institution;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Role;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Rules\AddProfilToRoleValidation;
use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Rules\RoleValidationForImport;

/**
 * Trait ImportClient
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportStaff
{

    /**
     * @param $row
     * @return mixed
     */
    public function rules($row)
    {

        $rules = $this->rulesIdentite();

        $rules['position'] = ['required'];
        $rules['email'] = ['required',];

        if ($this->unitRequired) {

            $rules['unite'] = ['required',
                new NameModelRules(['table' => 'units', 'column' => 'name']),
            ];

        } else {

            $rules['unite'] = [
                new NameModelRules(['table' => 'units', 'column' => 'name']),
            ];
        }

        if (!$this->myInstitution) {
            $rules['institution'] = 'required|exists:institutions,name';
        }

        /*$rules['roles'] = [
            'required', new RoleValidationForImport($row['institution']),
        ];*/

        return $rules;
    }


    public function rulesAddProfilToRole()
    {

        return [
            "profil" => 'required|exists:roles,name',
            "roles" => ['required', new AddProfilToRoleValidation()]
        ];

    }


    /**
     * @param $row
     * @return array|bool
     */
    protected function handleUnitVerification($row)
    {
        if ($this->unitRequired) {

            if (Unit::find($row['unite'])->institution_id !== $row['institution']) {

                return [
                    'status' => false,
                    'message' => __('messages.unit_do_not_exist',[],getAppLang())
                ];
            }
        }

        return ['status' => true];
    }

    /**
     * @param $row
     * @return array|bool
     */
    protected function handleInstitutionVerification($row)
    {
        $institution = \Satis2020\ServicePackage\Models\Institution::query()->where('name', $row['institution'])->first();
        $myInstitution = $this->institution();

        if($institution->id!=$myInstitution->id){
            return [
                'status' => false,
                'message' => 'Institution non valide.'
            ];
        }
        return ['status' => true];
    }


    /**
     * @param $row
     * @return array
     */
    protected function verificationAndStoreStaff($row)
    {
        $status = true;
        $identite = false;
        $staff = false;
        $message = '';

        $verifyPhone = $this->handleInArrayUnicityVerification($row['telephone'], 'identites', 'telephone');

        $verifyEmail = $this->handleInArrayUnicityVerification($row['email'], 'identites', 'email');

        if (!$verifyPhone['status']) {

            $identite = $verifyPhone['entity'];

        }

        if (!$verifyEmail['status']) {

            $identite = $verifyEmail['entity'];
        }


        if (!$identite) {

            $identite = $this->storeIdentite($row);
            $staff = $this->storeStaff($row, $identite);

        } else {

            if (!$this->stop_identite_exist) {

                $status = false;
                $message = __('messages.unit_found_for_staff',[],getAppLang());

            } else {

                if ($this->etat) {
                    $identite = Identite::find($identite->id);
                    $identite->update($this->fillableIdentite($row));
                }

                /*if (!$staff = Staff::where('identite_id', $identite->id)->where('institution_id', $row['institution'])
                    ->first()) {*/
                    $staff = $this->storeStaff($row, $identite);

                /*} else {

                    $status = false;
                    $message = 'A Staff already exist in the institution';
                }*/

            }

        }

        return [
            'status' => $status,
            'staff' => $staff,
            'message' => $message
        ];

    }


    /**
     * @param $row
     * @param $identite
     * @return mixed
     */
    protected function storeStaff($row, $identite)
    {

        $lang = app()->getLocale();

        $positions = DB::table('positions')->whereNull('deleted_at')->get();
        $position = $positions->filter(function ($item) use ($lang, $row) {
            $name = json_decode($item->name)->{$lang};
            if ($name === $row['position'])
                return $item;
            else
                return null;
        })->first();

        if ($position==null) {
            $position = Position::create([
                'name' => $row['position'],
                'description' => null,
                'others' => null
            ]);
        }

        $data = [
            'identite_id' => $identite->id,
            'position_id' => $position->id,
            'institution_id' => $row['institution'],
            'others' => null,
            'feedback_preferred_channels' => ["email"]
        ];

        if ($this->unitRequired) {

            $data['unit_id'] = $row['unite'];
        }

        $store = Staff::query()->updateOrCreate([
            'identite_id' => $identite->id,
        ],$data);

        $verifyRole = Role::where('name', $row['roles'])
            ->where('guard_name', 'api')
            ->withCasts(['institution_types' => 'array'])
            ->first();


        if(!empty($row['roles']) && $verifyRole) {

            $user = User::updateOrCreate(
                ['username' => $identite->email[0]],
                [
                    'username' => $identite->email[0],
                    'password' => bcrypt('satis'),
                    'identite_id' => $identite->id
                ]);
            $user->syncRoles(Role::whereIn('name', $row['roles'])->where('guard_name', 'api')->get());

        }

        return $store;
    }


    /**
     * @param $data
     * @return mixed
     */
    protected function modifiedDataKeysInId($data)
    {

        $data = $this->mergeMyInstitution($data);

        $data = $this->getIdInstitution($data, 'institution', 'name');

        if ($this->unitRequired) {

            $data = $this->getIds($data, 'units', 'unite', 'name');
        }

        return $data;
    }


    protected function addProfils($data)
    {
        try {
            $users = User::whereHas('roles', function ($q) use ($data) {
                $q->where('name', $data['profil']);
            })->get();
            foreach ($users as $user) {
                $user->assignRole(Role::whereIn('name', $data['roles'])->get());
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }


}