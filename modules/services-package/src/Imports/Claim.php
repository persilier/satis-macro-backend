<?php
namespace Satis2020\ServicePackage\Imports;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use ErrorException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\ImportClaim;
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Throwable;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class Claim implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, ImportClaim, ImportIdentite, IdentiteVerifiedTrait, VerifyUnicity;

    private $etat; // action for
    private $errors; // array to accumulate errors
    private $myInstitution;
    private $with_client;
    private $with_relationship;
    private $with_unit;

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
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();

        if(empty($collection)){

            throw new CustomException("Le fichier excel d'import des réclamations est vide.", 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

            if(array_key_exists('date_evenement', $row)){
                if(is_numeric($row['date_evenement'])){
                    $row['date_evenement'] = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_evenement']))->format('Y-m-d H:i');
                }
            }
            // conversions email and telephone en table
            $data = $this->explodeValueRow($row, 'email', $separator = '/');
            $data = $this->explodeValueRow($data, 'telephone', $separator = '/', true);

            $validator = Validator::make($row, $this->rules($row, $this->with_client, $this->with_relationship, $this->with_unit));
            // fields validations
            if ($validator->fails()) {

                $errors_validations = [];

                foreach ($validator->errors()->messages() as $messages) {

                    foreach ($messages as $error) {

                        $errors_validations[] = $error;

                    }
                }

                $this->errors[$key] = [

                    'error' => $errors_validations,
                    'data' => $row
                ];

            }else {

                $data = $this->recupIdsData($data, $this->with_client, $this->with_relationship, $this->with_unit);

                if(!$identite = $this->identiteVerifiedImport($data)){

                    $identite = $this->storeIdentite($data);

                }else{

                   if($this->etat){
                       Identite::query()
                           ->where("id",$identite->id)
                           ->update($this->fillableIdentite($data));
                   }

                }

                $status = $this->getStatus($data, $this->with_client, $this->with_relationship, $this->with_unit);

                $this->storeClaim($data, $identite, $status, $this->with_client, $this->with_relationship, $this->with_unit);

            }

        }

    }

    public function headingRow(): int
    {
        return 2;
    }

    // this function returns all validation errors after import
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
