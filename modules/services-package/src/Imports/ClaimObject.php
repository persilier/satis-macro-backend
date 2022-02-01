<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Traits\DataUserNature;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class ClaimObject implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, \Satis2020\ServicePackage\Traits\ClaimObject;

    private $myInstitution;
    private $withoutInstitution;
    private $errors; // array to accumulate errors

    /***
     * ClaimObject constructor.
     * @param $myInstitution
     * @param bool $withoutInstitution
     */
    public function __construct($myInstitution = false, $withoutInstitution = false)
    {
        $this->myInstitution = $myInstitution;
        $this->withoutInstitution = $withoutInstitution;
    }

    /**
     * @param Collection $collection
     * @return void
     * @throws CustomException
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();

        if(empty($collection)){
            throw new CustomException("Le fichier excel d'import des objects de réclamations est vide.", 404);
        }

        foreach ($collection as $key => $row) {

            if ($this->myInstitution) {
                $row['institution'] = $this->myInstitution;
            }

            $validator = Validator::make($row, $this->rulesImport());
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

            } else {

                $data = $this->getIds($row, 'claim_categories', 'category', 'name');

                $this->storeImportClaimObject($data, $row['category']);

            }

        }



    }

    /***
     * @return int
     */
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
