<?php


namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Rules\NameModelRules;

/**
 * Trait ImportClient
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportStaff
{

    /**
     * @return mixed
     */
    public function rules(){

        $rules = $this->rulesIdentite();

        $rules['position'] = ['required',
            new NameModelRules(['table' => 'positions', 'column'=> 'name']),
        ];

        if ($this->unitRequired){

            $rules['unite'] = ['required',
                new NameModelRules(['table' => 'units', 'column'=> 'name']),
            ];

        }else{

            $rules['unite'] = [
                new NameModelRules(['table' => 'units', 'column'=> 'name']),
            ];
        }


        //$rules['other_attributes_staffs'] = 'array';

        if (!$this->myInstitution){

            $rules['institution'] = 'required|exists:institutions,name';
        }

        return $rules;
    }


    /**
     * @param $row
     * @return array|bool
     */
    protected function handleUnitVerification($row)
    {
        if($this->unitRequired){

            if(Unit::find($row['unite'])->institution_id !== $row['institution']){

                return [
                    'status' => false,
                    'message' => 'L\'unité que vous avez choisir n\'existe pas dans cette institution.'
                ];
            }
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
        $message = '';

        $verifyPhone = $this->handleInArrayUnicityVerification($row['telephone'], 'identites', 'telephone');

        $verifyEmail = $this->handleInArrayUnicityVerification($row['email'], 'identites', 'email');

        if(!$verifyPhone['status']){

            $identite = $verifyPhone['entity'];

        }

        if(!$verifyEmail['status']){

            $identite = $verifyEmail['entity'];
        }


        if(!$identite){

            $identite = $this->storeIdentite($row);
            $staff = $this->storeStaff($row, $identite);

        }else{

            if(!$this->stop_identite_exist){

                $status = false;
                $message = 'Un identité a été retrouvé avec les informations du staff.';

            }else{

                if($this->etat){

                    $identite->update($this->fillableIdentite($row));

                }

                if(!$staff = Staff::where('identite', $identite->id)->where('institution_id', $row['institution'])->first()){

                    $staff = $this->storeStaff($row, $identite);

                }else{

                    $status = false;
                    $message = 'A Staff already exist in the institution';
                }

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
    protected function storeStaff($row, $identite){

        $data = [
            'identite_id' => $identite->id,
            'position_id' => $row['position'],
            'institution_id' => $row['institution'],
            'others' => $row['other_attributes_staffs']
        ];

        if($this->unitRequired){

            $data['unit_id'] = $row['unite'];
        }

        $store =  Staff::create($data);

        return $store;
    }


    /**
     * @param $data
     * @return mixed
     */
    protected function modifiedDataKeysInId($data){

        $data = $this->mergeMyInstitution($data);

        $data = $this->getIdInstitution($data, 'institution', 'name');

        $data = $this->getIds($data, 'positions', 'position', 'name');

        if($this->unitRequired){

            $data = $this->getIds($data, 'units', 'unite','name');
        }

        return $data;
    }


}