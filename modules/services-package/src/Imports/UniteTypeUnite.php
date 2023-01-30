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
    private $withoutInstitution;
    private $errors; // array to accumulate errors

    public function __construct($myInstitution, $withoutInstitution = false)
    {
        $this->myInstitution = $myInstitution;
        $this->withoutInstitution = $withoutInstitution;
        $this->errors = [];
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {

        $collection = $collection->toArray();
        $index = 1;
        if(empty($collection)){

            throw new CustomException(__("messages.empty_unity_file_alert",[],app()->getLocale()), 404);
        }
        // iterating each row and validating it:
        foreach ($collection as $key => $row) {
            $index++;
            $validator = Validator::make($row, $this->rulesImport($row));
            // fields validations
            if ($validator->fails()) {
                $error=['messages'=>$validator->getMessageBag()->getMessages(),'data'=>$row,"line"=>$index];
                array_push($this->errors,$error);
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
