<?php

namespace Satis2020\ServicePackage\Imports\Staff;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use Satis2020\ServicePackage\Rules\RoleValidationForImport;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\ImportStaff;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class TransactionStaffImport implements OnEachRow, WithHeadingRow, WithChunkReading//, ShouldQueue
{
    use Importable, SkipsFailures, DataUserNature, ImportIdentite, ImportStaff, VerifyUnicity;

    protected $myInstitution;
    protected $data;
    private $errors; // array to accumulate errors
    private $etat;
    private $unitRequired;
    private $stop_identite_exist;
    private $hasError = false;



    /***
     * TransactionClientImport constructor.
     * @param $myInstitution
     * @param $data
     */
    public function __construct($myInstitution, $data)
    {
        $this->myInstitution = $myInstitution;
        $this->data = $data;
        $this->myInstitution = $myInstitution;
        $this->unitRequired = $data['unitRequired'];
        $this->stop_identite_exist = $data['stop_identite_exist'];
        $this->etat = $data['etat'];
        $this->errors = [];
    }

    /***
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /***
     * @param Row $row
     * @throws ValidationException
     */
    public function onRow(Row $row)
    {

        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $validator = $this->validateRow($row);

        $row = $this->transformRowBeforeStoring($row);

        $error['data'] =  $row;

        if (!$validator->fails()) {
                $data = $this->modifiedDataKeysInId($row);

                // Institution Verification
                $verifiedInstitution = $this->handleInstitutionVerification($row);

                if (!$verifiedInstitution['status']) {
                    $error['message'] =  $verifiedInstitution['message'];
                    $this->hasError = true;
                } else {
                    // Unité Verification
                    $verifiedUnit = $this->handleUnitVerification($data);

                    if (!$verifiedUnit['status']) {
                        $error['message'] =  $verifiedUnit['message'];
                        $this->hasError = true;
                    } else {
                        $staff = $this->verificationAndStoreStaff($data);

                        if ($staff['status'] === false) {
                            $error['message'] =  $staff['message'];
                            $this->hasError = true;

                        }
                    }
                }

                if ($this->hasError){
                    array_push($this->errors,$error);
                }

            } else {
            Log::error($validator->errors());
            $error=['messages'=>$validator->getMessageBag()->getMessages(),'data'=>$row,"line"=>$rowIndex];
            array_push($this->errors,$error);
            $this->hasError = true;
        }

    }

    public function getImportErrors()
    {
        return $this->errors;
    }

    protected function validateRow($row)
    {
        return Validator::make($row, $this->rules($row));
    }

    /***
     * @param $data
     * @return mixed
     */
    protected function transformRowBeforeStoring($data)
    {

        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }

        $institutionName = $this->myInstitution->name;

        if (array_key_exists('institution', $data)) {
            $institution = $this->data['institutions']->firstWhere('name', $data['institution']);

            if ($institution) {
                $institutionName = $institution->name;
            }
        }


        $data['institution'] = $institutionName;

        $data['roles'] = !empty($data['roles']) ? explode('/', $data['roles']) : [];

        foreach ($data['roles'] as $key => $value) {
            $value = preg_replace("/\s+/", "", $value);
     //       $value = preg_replace("/-/", "", $value);
            $value = preg_replace("/\./", "", $value);

            if (empty($value)) {
                unset($data['roles'][$key]);
            } else {
                $data['roles'][$key] = $value;
            }
        }

        $data['telephone'] = !empty($data['telephone']) ? explode('/', $data['telephone']) : [];

        foreach ($data['telephone'] as $key => $value) {
            $value = preg_replace("/\s+/", "", $value);
            $value = preg_replace("/-/", "", $value);
            $value = preg_replace("/\./", "", $value);

            if (empty($value)) {
                unset($data['telephone'][$key]);
            } else {
                $data['telephone'][$key] = $value;
            }
        }

        $data['email'] = !empty($data['email']) ? explode('/', $data['email']) : [];

        foreach ($data['email'] as $key => $value) {
            $value = trim($value);
            $value = Str::lower($value);

            if (empty($value)) {
                unset($data['email'][$key]);
            } else {
                $data['email'][$key] = $value;
            }
        }

       // dd($data);
        return $data;
    }

    public function headingRow(): int
    {
        return 2;
    }
}
