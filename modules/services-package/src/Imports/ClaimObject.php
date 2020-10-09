<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Traits\DataUserNature;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class ClaimObject implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, \Satis2020\ServicePackage\Traits\ClaimObject;

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

        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

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

                $data = $this->getIds($row, 'claim_categories', 'claim_category', 'name');

                $data = $this->getIds($data, 'severity_levels', 'severity_level','name');

                $this->storeImportClaimObject($data);

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
