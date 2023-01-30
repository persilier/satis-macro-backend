<?php

namespace Satis2020\ServicePackage\Imports\Claims;

use Carbon\Carbon;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\RoleValidationForImport;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\ImportClaim;
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\ImportStaff;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

class TransactionClaimImport implements OnEachRow, WithHeadingRow, WithChunkReading//, ShouldQueue
{
    use Importable, SkipsFailures, DataUserNature, ImportClaim, ImportIdentite, IdentiteVerifiedTrait, VerifyUnicity;


    private $etat; // action for
    private $errors; // array to accumulate errors
    private $myInstitution;
    private $with_client;
    private $with_relationship;
    private $with_unit;
    private $hasError = false;



    /**
     * Client constructor.
     * @param $etat
     * @param $myInstitution
     * @param $with_client
     * @param $with_relationship
     * @param $with_unit
     */
    public function __construct($etat, $myInstitution, $with_client, $with_relationship, $with_unit)
    {
        $this->etat = $etat;
        $this->myInstitution = $myInstitution;
        $this->with_client = $with_client;
        $this->with_relationship = $with_relationship;
        $this->with_unit = $with_unit;
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

        if(array_key_exists('date_evenement', $row)){
            if(is_numeric($row['date_evenement'])){
                $row['date_evenement'] = Carbon::instance(Date::excelToDateTimeObject($row['date_evenement']))->format('Y-m-d H:i');
            }
        }

        $validator = $this->validateRow($row);

        $row = $this->transformRowBeforeStoring($row);

        $error['data'] =  $row;

        if (!$validator->fails()) {
            $row = $this->recupIdsData($row, $this->with_client, $this->with_relationship, $this->with_unit);

            if(!$identite = $this->identiteVerifiedImport($row)){

                $identite = $this->storeIdentite($row);

            }else{

                if($this->etat){
                    Identite::query()
                        ->where("id",$identite->id)
                        ->update($this->fillableIdentite($row));
                }

            }

            $status = $this->getStatus($row, $this->with_client, $this->with_relationship, $this->with_unit);

            $this->storeClaim($row, $identite, $status, $this->with_client, $this->with_relationship, $this->with_unit);

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
    protected function transformRowBeforeStoring($row)
    {

        foreach ($row as $key => $value) {
            $data[$key] = trim($value);
        }


        // conversions email and telephone en table
        $data = $this->explodeValueRow($row, 'email', $separator = '/');
        $data = $this->explodeValueRow($data, 'telephone', $separator = '/', true);


        return $data;
    }

    public function headingRow(): int
    {
        return 2;
    }
}
