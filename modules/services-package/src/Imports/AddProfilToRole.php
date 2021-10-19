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
use Satis2020\ServicePackage\Traits\ImportStaff;

/**
 * Class AddProfilToRole
 * @package Satis2020\ServicePackage\Imports
 */
class AddProfilToRole implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures, DataUserNature, ImportIdentite, ImportStaff;

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

        if (empty($collection)) {
            throw new CustomException("Le fichier excel d'import d'association des profils aux rôles est vide.", 404);
        }

        // iterating each row and validating it:
        foreach ($collection as $key => $row) {

            $data = $this->explodeValueRow($row, 'roles', $separator = '/');

            $validator = Validator::make($row, $this->rulesAddProfilToRole());

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
                if (! $this->addProfils($data)) {
                    $this->errors[$key] = ['data' => $row] ?? (!$this->errors[$key]);
                    $this->errors[$key]['conflits']['role'] = "Impossible d'associer des rôles à ce profil.";
                }

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
