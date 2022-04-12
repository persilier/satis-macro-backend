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
use Satis2020\ServicePackage\Traits\ImportIdentite;
use Satis2020\ServicePackage\Traits\ImportUniteTypeUnite;

/**
 * Class UniteTypeUnite
 * @package Satis2020\ServicePackage\Imports
 */
class UniteTypeUnite implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, ImportUniteTypeUnite, ImportIdentite;
    private $myInstitution;
    private $withoutInstituion;
    private $errors; // array to accumulate errors

    public function __construct($myInstitution, $withoutInstituion = false)
    {
        $this->myInstitution = $myInstitution;
        $this->withoutInstituion = $withoutInstituion;
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();

        if(empty($collection)){

            throw new CustomException(__("messages.empty_unity_file_alert",[],app()->getLocale()), 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

            $validator = Validator::make($row, $this->rulesImport($row));
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

                $data = $this->mergeMyInstitution($row);

                $data = $this->getIdInstitution($data, 'institution', 'name');

                $data = $this->getIds($data, 'unit_types', 'name_type_unite', 'name');

                $this->storeImportUniteTypeUnite($data, $row['name_type_unite']);

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
