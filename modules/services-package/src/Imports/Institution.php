<?php
namespace Satis2020\ServicePackage\Imports;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\InstitutionTrait;

/**
 * Class Client
 * @package Satis2020\ServicePackage\Imports
 */
class Institution implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, institutionTrait, DataUserNature;

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

            throw new CustomException("Le fichier excel d'import des institutions est vide.", 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

            $validator = Validator::make($row, Arr::only($this->rules(), ['name', 'acronyme', 'iso_code', 'default_currency_slug']));

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

                $this->storeImportInstitution($row);

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
