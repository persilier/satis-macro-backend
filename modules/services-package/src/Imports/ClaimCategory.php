<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class ClaimCategory implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, \Satis2020\ServicePackage\Traits\ClaimCategory;

    private $errors; // array to accumulate errors

    public function __construct()
    {

    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();

        if(empty($collection)){

            throw new CustomException("Le fichier excel d'import des catégories de réclamation est vide.", 404);
        }

        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

            $validator = Validator::make($row, $this->rules());

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

                $this->storeImportClaimCategory($row);

            }

        }

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
